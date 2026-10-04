<?php
/**
 * router.php — Routeur pour le serveur PHP intégré (Railway / dev local).
 * Gère : fichiers statiques, 404 personnalisé, réécriture d'URL de base.
 */

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// 1. Si l'URI correspond à un fichier ou dossier existant → le servir normalement
if ($uri !== '/' && file_exists(__DIR__ . $uri)) {
    return false; // Laisse PHP servir le fichier statique
}

// 2. Redirection racine → page de connexion
// (redirection HTTP et non require : sinon les chemins relatifs style.css,
//  ../assets/... se résolvent depuis "/" et la page s'affiche sans CSS)
if ($uri === '/') {
    header('Location: /inscription/login.php', true, 302);
    exit;
}

// 3. Supprime l'extension .php si l'URL est sans extension
$withPhp = __DIR__ . $uri . '.php';
if (file_exists($withPhp)) {
    require $withPhp;
    exit;
}

// 4. Route introuvable → page 404 personnalisée
http_response_code(404);
require __DIR__ . '/404.php';
