<?php
/**
 * CSRF helper — génère et vérifie les tokens anti-forgery.
 *
 * Utilisation formulaire  : echo csrf_field()         → <input type="hidden" …>
 * Utilisation AJAX        : header X-CSRF-Token        → csrf_check_ajax()
 */

function csrf_token(): string {
    if (session_status() !== PHP_SESSION_ACTIVE) session_start();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token()) . '">';
}

/** Vérifie le token dans un formulaire POST. Arrête l'exécution si invalide. */
function csrf_check_form(): void {
    $token = $_POST['csrf_token'] ?? '';
    if (!hash_equals(csrf_token(), $token)) {
        http_response_code(403);
        exit('Requête invalide (CSRF).');
    }
}

/** Vérifie le token envoyé via le header X-CSRF-Token (AJAX). */
function csrf_check_ajax(): void {
    $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (!hash_equals(csrf_token(), $token)) {
        http_response_code(403);
        header('Content-Type: application/json');
        echo json_encode(['erreur' => 'Requête invalide.']);
        exit;
    }
}
?>
