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

define('DB_HOST', getenv('MYSQLHOST')     ?: 'localhost');
define('DB_PORT', (int)(getenv('MYSQLPORT') ?: 3306));
define('DB_USER', getenv('MYSQLUSER')     ?: 'root');
define('DB_PASS', getenv('MYSQLPASSWORD') ?: '');
define('DB_NAME', getenv('MYSQLDATABASE') ?: 'jeu_mot');
define('GAME_LAUNCH_DATE', '2026-01-01'); // date de référence pour le calcul du jour
define('TIMEZONE', 'Africa/Abidjan');

date_default_timezone_set(TIMEZONE);

/**
 * Retourne une connexion MySQLi en utf8mb4.
 * En cas d'erreur, répond proprement (JSON ou HTML) et arrête.
 */
function db_connect(): mysqli {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);

    if ($conn->connect_error) {
        $isAjax = (
            (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') ||
            (isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json')) ||
            (isset($_SERVER['HTTP_X_CSRF_TOKEN']))
        );
        if ($isAjax) {
            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode(['erreur' => 'Erreur serveur.']);
        } else {
            http_response_code(500);
            echo '<!DOCTYPE html><html><body style="font-family:sans-serif;text-align:center;margin-top:80px">
                <h2>⚠️ Erreur serveur</h2><p>Impossible de joindre la base de données.</p></body></html>';
        }
        exit;
    }

    $conn->set_charset('utf8mb4');
    return $conn;
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
