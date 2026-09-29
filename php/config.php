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
?>
