<?php
/**
 * config.php — Configuration globale et connexion DB.
 * Inclure avec require_once dans chaque fichier PHP.
 *
 * En production (Railway), les variables d'environnement MySQL sont
 * injectées automatiquement par le plugin Railway MySQL :
 *   MYSQLHOST, MYSQLPORT, MYSQLUSER, MYSQLPASSWORD, MYSQLDATABASE
 *
 * En local (XAMPP), les valeurs de fallback sont utilisées.
 */

// 1. Tente de lire une URL complète (ex: mysql://user:pass@host:port/db)
$dbUrl = getenv('MYSQL_PUBLIC_URL') ?: getenv('MYSQL_URL') ?: getenv('DATABASE_URL');
if ($dbUrl) {
    $parsed = parse_url($dbUrl);
    define('DB_HOST', $parsed['host'] ?? 'localhost');
    define('DB_PORT', (int)($parsed['port'] ?? 3306));
    define('DB_USER', $parsed['user'] ?? 'root');
    define('DB_PASS', $parsed['pass'] ?? '');
    define('DB_NAME', ltrim($parsed['path'], '/') ?: 'jeu_mot');
} else {
    // 2. Sinon, utilise les variables séparées ou les valeurs locales
    define('DB_HOST', getenv('MYSQLHOST')     ?: 'localhost');
    define('DB_PORT', (int)(getenv('MYSQLPORT') ?: 3306));
    define('DB_USER', getenv('MYSQLUSER')     ?: 'root');
    define('DB_PASS', getenv('MYSQLPASSWORD') ?: '');
    define('DB_NAME', getenv('MYSQLDATABASE') ?: 'jeu_mot');
}

define('GAME_LAUNCH_DATE', '2026-01-01'); // date de référence pour le calcul du jour
define('TIMEZONE', 'Africa/Abidjan');

date_default_timezone_set(TIMEZONE);

/**
 * Retourne une connexion MySQLi en utf8mb4.
 * En cas d'erreur, répond proprement (JSON ou HTML) et arrête.
 */
function db_connect(): mysqli {
    try {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
        $conn->set_charset('utf8mb4');
        _assurer_schema_creneaux($conn);
        return $conn;
    } catch (Exception $e) {
        // Journaliser l'erreur côté serveur SANS exposer les infos sensibles
        error_log('[DevineMot] DB connexion échouée : ' . $e->getMessage());

        $isAjax = (
            (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') ||
            (isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json')) ||
            (isset($_SERVER['HTTP_X_CSRF_TOKEN']))
        );
        if ($isAjax) {
            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode(['erreur' => 'Erreur serveur. Réessaie dans quelques instants.']);
        } else {
            http_response_code(500);
            echo '<!DOCTYPE html><html lang="fr"><head><meta charset="UTF-8"><title>Erreur — DevineMot CI</title></head>
                <body style="font-family:sans-serif;text-align:center;margin-top:80px;background:#1a1a1a;color:#fff;">
                <h2>⚠️ Service temporairement indisponible</h2>
                <p>Nous rencontrons un problème technique. Merci de réessayer dans quelques instants.</p>
                <a href="/" style="color:#F77F00;">← Retour à l\'accueil</a>
                </body></html>';
        }
        exit;
    }
}

/**
 * Retourne le numéro du jour depuis la date de lancement.
 */
function jour_numero(): int {
    $launch = new DateTime(GAME_LAUNCH_DATE);
    $today  = new DateTime(date('Y-m-d'));
    return (int) $launch->diff($today)->days;
}
/**
 * ====== 4 MOTS PAR JOUR (créneaux de 6h, heure d'Abidjan) ======
 * 0 : 00h-06h | 1 : 06h-12h | 2 : 12h-18h | 3 : 18h-00h
 */
define('CRENEAUX_PAR_JOUR', 4);
define('HEURES_PAR_CRENEAU', 6);

/** Créneau courant (0 à 3). */
function creneau_actuel(): int {
    return intdiv((int) date('G'), HEURES_PAR_CRENEAU);
}

/** Secondes restantes avant le prochain mot. */
function creneau_secondes_restantes(): int {
    $depuisMinuit = ((int) date('G')) * 3600 + ((int) date('i')) * 60 + (int) date('s');
    $fin = (intdiv((int) date('G'), HEURES_PAR_CRENEAU) + 1) * HEURES_PAR_CRENEAU * 3600;
    return max(1, $fin - $depuisMinuit);
}

/** Libellé lisible d'un créneau, ex : "06h - 12h". */
function creneau_libelle(int $c): string {
    $debut = $c * HEURES_PAR_CRENEAU;
    $fin   = ($debut + HEURES_PAR_CRENEAU) % 24;
    return sprintf('%02dh - %02dh', $debut, $fin);
}

/** Position du mot dans la liste ordonnée (4 mots consommés par jour). */
function index_mot_courant(): int {
    return jour_numero() * CRENEAUX_PAR_JOUR + creneau_actuel();
}

/**
 * Migration automatique et idempotente : ajoute la colonne `creneau`
 * dans `scores` et `mots_du_jour` (les anciennes lignes = créneau 0).
 */
function _assurer_schema_creneaux(mysqli $conn): void {
    try {
        $r = $conn->query("SHOW COLUMNS FROM scores LIKE 'creneau'");
        if ($r && $r->num_rows === 0) {
            $conn->query("ALTER TABLE scores ADD COLUMN creneau TINYINT NOT NULL DEFAULT 0 AFTER date_jour");
        }
        $r = $conn->query("SHOW COLUMNS FROM mots_du_jour LIKE 'creneau'");
        if ($r && $r->num_rows === 0) {
            $conn->query("ALTER TABLE mots_du_jour
                ADD COLUMN creneau TINYINT NOT NULL DEFAULT 0 AFTER date_jour,
                DROP INDEX date_jour,
                ADD UNIQUE KEY uq_date_creneau (date_jour, creneau)");
        }
    } catch (Throwable $e) {
        error_log('[iMots] Migration créneaux : ' . $e->getMessage());
    }
}

?>
