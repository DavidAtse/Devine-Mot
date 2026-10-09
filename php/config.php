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

// 0. Environnement Alwaysdata (hôte imots.alwaysdata.net ou dossier /home/imots)
$isAlwaysdata = (
    str_contains($_SERVER['HTTP_HOST'] ?? '', 'alwaysdata.net') ||
    is_dir('/home/imots') ||
    getenv('ALWAYSDATA') !== false
);

// Redirection automatique des requêtes depuis l'ancien serveur Railway vers le site officiel
if (isset($_SERVER['HTTP_HOST']) && str_contains($_SERVER['HTTP_HOST'], 'railway.app')) {
    header('Location: https://imots.alwaysdata.net' . ($_SERVER['REQUEST_URI'] ?? '/'), true, 302);
    exit;
}

if ($isAlwaysdata) {
    // Mot de passe sécurisé : lu depuis variable d'environnement ou fichier externe hors webroot (/home/imots/env_db.php)
    $adPass = getenv('ALWAYSDATA_DB_PASS');
    if (!$adPass && file_exists('/home/imots/env_db.php')) {
        $adConfig = require '/home/imots/env_db.php';
        $adPass = $adConfig['DB_PASS'] ?? '';
    }
    define('DB_HOST', 'mysql-imots.alwaysdata.net');
    define('DB_PORT', 3306);
    define('DB_USER', 'imots');
    define('DB_PASS', $adPass ?: (getenv('DB_PASS') ?: ''));
    define('DB_NAME', 'imots_jeu');
} else {
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
        // 2. Sinon, utilise les variables séparées ou les valeurs locales (XAMPP)
        define('DB_HOST', getenv('MYSQLHOST')     ?: 'localhost');
        define('DB_PORT', (int)(getenv('MYSQLPORT') ?: 3306));
        define('DB_USER', getenv('MYSQLUSER')     ?: 'root');
        define('DB_PASS', getenv('MYSQLPASSWORD') ?: '');
        define('DB_NAME', getenv('MYSQLDATABASE') ?: 'jeu_mot');
    }
}

define('GAME_LAUNCH_DATE', '2026-10-09'); // Lancement officiel aujourd'hui = Jour #1 !
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
        $r2 = $conn->query("SHOW COLUMNS FROM mots_du_jour LIKE 'est_partenaire'");
        if ($r2 && $r2->num_rows === 0) {
            $conn->query("ALTER TABLE mots_du_jour ADD COLUMN est_partenaire TINYINT(1) NOT NULL DEFAULT 0 AFTER mot");
        }
        $conn->query("CREATE TABLE IF NOT EXISTS `dons` (
            `id` INT NOT NULL AUTO_INCREMENT,
            `user_id` INT DEFAULT NULL,
            `donateur` VARCHAR(100) NOT NULL,
            `montant` INT NOT NULL DEFAULT 0,
            `moyen` VARCHAR(50) NOT NULL DEFAULT 'Wave',
            `statut` VARCHAR(20) NOT NULL DEFAULT 'confirme',
            `source` VARCHAR(50) NOT NULL DEFAULT 'site',
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `idx_statut` (`statut`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        // Rétroactivité : insère les 3 premiers dons reçus avant la mise en place du système si la table est vide
        $chkDons = $conn->query("SELECT COUNT(*) FROM `dons`");
        if ($chkDons && (int)$chkDons->fetch_row()[0] === 0) {
            $conn->query("INSERT INTO `dons` (`donateur`, `montant`, `moyen`, `statut`, `source`, `created_at`) VALUES
                ('Soutien Wave (Reçu)', 1000, 'Wave', 'confirme', 'manuel', '2026-10-04 14:30:00'),
                ('Soutien Wave (Reçu)', 500, 'Wave', 'confirme', 'manuel', '2026-10-05 10:15:00'),
                ('Soutien Wave (Reçu)', 500, 'Wave', 'confirme', 'manuel', '2026-10-05 18:20:00')");
        }

        // Rétroactivité / Nettoyage : les clics enregistrés depuis le site non validés passent en 'en_attente'
        $conn->query("UPDATE `dons` SET `statut` = 'en_attente' WHERE `source` = 'site' AND `statut` = 'confirme'");

        // Lancement officiel du jeu : Jour 1 (2026-10-09 à 12h) - Remise à zéro de tous les scores et tentatives de test
        $conn->query("CREATE TABLE IF NOT EXISTS `system_flags` (
            `flag_key` VARCHAR(50) NOT NULL PRIMARY KEY,
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $chkReset = $conn->query("SELECT flag_key FROM `system_flags` WHERE flag_key = 'reset_lancement_officiel_20261009'");
        if ($chkReset && $chkReset->num_rows === 0) {
            $conn->query("DELETE FROM `scores`");
            $conn->query("ALTER TABLE `scores` AUTO_INCREMENT = 1");
            $conn->query("DELETE FROM `tentatives`");
            $conn->query("ALTER TABLE `tentatives` AUTO_INCREMENT = 1");
            $conn->query("INSERT INTO `system_flags` (`flag_key`) VALUES ('reset_lancement_officiel_20261009')");
        }
    } catch (Throwable $e) {
        error_log('[iMots] Migration créneaux : ' . $e->getMessage());
    }
}


/**
 * Assure qu'un mot est assigné de façon stable et persistante à un créneau (date, créneau).
 * Si le mot existe déjà dans mots_du_jour, il est retourné tel quel (aucun recalcul).
 * Sinon, un mot disponible est choisi et verrouillé dans mots_du_jour.
 */
function assigner_mot_creneau(mysqli $conn, string $date, int $creneau): string {
    // 1. Déjà présent et non vide ?
    $stmt = $conn->prepare('SELECT UPPER(mot) AS mot FROM mots_du_jour WHERE date_jour = ? AND creneau = ?');
    $stmt->bind_param('si', $date, $creneau);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    if ($row && !empty($row['mot'])) {
        return strtoupper(trim($row['mot']));
    }

    // 2. Mots déjà assignés récemment pour éviter les doublons rapprochés
    $dejaAssignes = [];
    $resU = $conn->query("SELECT UPPER(mot) FROM mots_du_jour WHERE mot IS NOT NULL AND mot != '' ORDER BY date_jour DESC, creneau DESC LIMIT 120");
    if ($resU) {
        while ($rU = $resU->fetch_row()) {
            if (!empty($rU[0])) $dejaAssignes[] = strtoupper(trim($rU[0]));
        }
    }

    $total = (int) $conn->query('SELECT COUNT(*) FROM mots WHERE ordre IS NOT NULL')->fetch_row()[0];
    if ($total === 0) return '';

    // Calcul de l'index de départ déterministe
    $launch = new DateTime(GAME_LAUNCH_DATE);
    $dObj   = new DateTime($date);
    $jNum   = (int) $launch->diff($dObj)->days;
    $baseIdx = ($jNum * CRENEAUX_PAR_JOUR + $creneau) % $total;

    $motChoisi = '';
    // Trouver le premier mot non utilisé récemment
    for ($attempt = 0; $attempt < min(80, $total); $attempt++) {
        $idx = ($baseIdx + $attempt) % $total;
        $q = $conn->query("SELECT UPPER(mot) FROM mots WHERE ordre IS NOT NULL ORDER BY ordre ASC LIMIT 1 OFFSET {$idx}");
        if ($q && $r = $q->fetch_row()) {
            $cand = strtoupper(trim($r[0] ?? ''));
            if ($cand !== '' && (!in_array($cand, $dejaAssignes, true) || $attempt >= 50)) {
                $motChoisi = $cand;
                break;
            }
        }
    }

    if ($motChoisi === '') {
        $q = $conn->query("SELECT UPPER(mot) FROM mots WHERE ordre IS NOT NULL ORDER BY ordre ASC LIMIT 1 OFFSET {$baseIdx}");
        $motChoisi = $q ? strtoupper(trim($q->fetch_row()[0] ?? '')) : '';
    }

    if ($motChoisi !== '') {
        $ins = $conn->prepare('INSERT INTO mots_du_jour (date_jour, creneau, mot) VALUES (?, ?, ?)
                               ON DUPLICATE KEY UPDATE mot = VALUES(mot)');
        $ins->bind_param('sis', $date, $creneau, $motChoisi);
        $ins->execute();
    }

    return $motChoisi;
}

/**
 * Pré-planifie et verrouille les mots pour les N prochains jours dans mots_du_jour.
 * Garantit que la liste des prochains mots ne bouge JAMAIS quand un mot est supprimé ou ajouté.
 */
function garantir_mots_planifies(mysqli $conn, int $nbJours = 7): void {
    for ($d = 0; $d < $nbJours; $d++) {
        $date = date('Y-m-d', strtotime("+{$d} day"));
        for ($c = 0; $c < CRENEAUX_PAR_JOUR; $c++) {
            assigner_mot_creneau($conn, $date, $c);
        }
    }
}


/**
 * Retourne les détails du créneau immédiatement suivant le créneau actif.
 */
function prochain_creneau(): array {
    $c = creneau_actuel();
    $today = date('Y-m-d');
    if ($c < CRENEAUX_PAR_JOUR - 1) {
        $nextDate  = $today;
        $nextC     = $c + 1;
        $labelJour = "Aujourd'hui";
    } else {
        $nextDate  = date('Y-m-d', strtotime('+1 day'));
        $nextC     = 0;
        $labelJour = "Demain";
    }
    return [
        'date'       => $nextDate,
        'creneau'    => $nextC,
        'label_jour' => $labelJour,
        'label_slot' => creneau_libelle($nextC),
        'libelle'    => "{$labelJour} · " . creneau_libelle($nextC),
    ];
}

?>
