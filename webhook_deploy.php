<?php
/**
 * webhook_deploy.php — Déploiement automatique lors d'un `git push` sur GitHub.
 * Protégé par une clé secrète partagée.
 */
header('Content-Type: application/json; charset=utf-8');

$secret = 'imots_deploy_2026';

// 1. Vérification de sécurité
$providedSecret = $_GET['key'] ?? '';
$isGithub = false;

if (isset($_SERVER['HTTP_X_HUB_SIGNATURE_256'])) {
    $payload = file_get_contents('php://input');
    $hash = 'sha256=' . hash_hmac('sha256', $payload, $secret);
    if (hash_equals($hash, $_SERVER['HTTP_X_HUB_SIGNATURE_256'])) {
        $isGithub = true;
    }
}

if (!$isGithub && $providedSecret !== $secret) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'Accès refusé. Clé ou signature invalide.']);
    exit;
}

// 2. Déploiement via Git
$cmd = 'cd /home/imots/www && git fetch origin main 2>&1 && git reset --hard origin/main 2>&1';
$output = shell_exec($cmd);

// 3. Réponse
echo json_encode([
    'ok' => true,
    'message' => 'Déploiement terminé avec succès !',
    'timestamp' => date('Y-m-d H:i:s'),
    'git_output' => trim($output ?? '')
]);
