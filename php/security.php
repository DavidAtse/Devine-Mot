<?php
/**
 * security.php — Headers de sécurité HTTP centralisés.
 * À inclure en PREMIER dans tous les fichiers PHP publics.
 *
 * Protections :
 * - X-Frame-Options       : empêche le clickjacking (intégration dans une iframe)
 * - X-Content-Type-Options: empêche le MIME-sniffing
 * - Referrer-Policy       : limite les infos transmises lors de clics sur liens externes
 * - Permissions-Policy    : désactive les APIs navigateur non utilisées (caméra, micro...)
 * - Strict-Transport-Security : force HTTPS (HSTS)
 * - Content-Security-Policy   : empêche l'injection de scripts non autorisés (XSS)
 */
function appliquer_headers_securite(bool $estAjax = false): void {
    // Empêche l'affichage du site dans une <iframe> (anti-clickjacking)
    header('X-Frame-Options: DENY');

    // Empêche le navigateur de "deviner" le type MIME
    header('X-Content-Type-Options: nosniff');

    // Contrôle les infos envoyées lors de la navigation sortante
    header('Referrer-Policy: strict-origin-when-cross-origin');

    // Désactive les fonctionnalités navigateur non nécessaires
    header('Permissions-Policy: camera=(), microphone=(), geolocation=(), payment=()');

    // Force HTTPS pendant 1 an (uniquement en production)
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    }

    // Content Security Policy : n'autorise que nos propres ressources
    // + FontAwesome CDN pour les icônes + la VAPID key inline script
    if (!$estAjax) {
        header(
            "Content-Security-Policy: " .
            "default-src 'self'; " .
            "script-src 'self' 'unsafe-inline'; " .  // unsafe-inline pour les variables window.* dans index.php
            "style-src 'self' 'unsafe-inline' https://cdnjs.cloudflare.com; " .
            "font-src 'self' https://cdnjs.cloudflare.com; " .
            "img-src 'self' data:; " .
            "connect-src 'self'; " .
            "worker-src 'self'; " .
            "frame-ancestors 'none';"
        );
    }
}

/**
 * Vérifie si la requête vient d'un robot/agent IA (pour accessibilité).
 * Utilisé pour décider d'afficher ou non certains éléments interactifs.
 */
function est_bot(): bool {
    $ua = strtolower($_SERVER['HTTP_USER_AGENT'] ?? '');
    return preg_match('/(bot|crawler|spider|googlebot|bingbot|slurp|duckduckbot|baiduspider|yandexbot|gpt|claude|gemini)/i', $ua) === 1;
}
?>
