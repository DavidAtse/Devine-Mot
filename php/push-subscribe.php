<?php
session_start();
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/csrf.php';

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'Non connecté']);
    exit;
}

csrf_check_ajax();

$body   = json_decode(file_get_contents('php://input'), true);
$action = $body['action'] ?? ''; // 'subscribe' | 'unsubscribe'

if (!in_array($action, ['subscribe', 'unsubscribe'], true)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Action invalide']);
    exit;
}

$endpoint = trim($body['subscription']['endpoint'] ?? '');
$p256dh   = trim($body['subscription']['keys']['p256dh'] ?? '');
$auth     = trim($body['subscription']['keys']['auth'] ?? '');

if ($action === 'subscribe') {
    if (!$endpoint || !$p256dh || !$auth) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'Données manquantes']);
        exit;
    }

    $conn   = db_connect();
    $userId = (int) $_SESSION['user_id'];

    // Upsert : si l'endpoint existe déjà pour cet utilisateur, on met à jour
    $stmt = $conn->prepare(
        'INSERT INTO push_subscriptions (user_id, endpoint, p256dh, auth)
         VALUES (?, ?, ?, ?)
         ON DUPLICATE KEY UPDATE p256dh = VALUES(p256dh), auth = VALUES(auth), created_at = NOW()'
    );
    $stmt->bind_param('isss', $userId, $endpoint, $p256dh, $auth);
    $stmt->execute();
    $conn->close();

    echo json_encode(['ok' => true]);

} else { // unsubscribe
    $conn   = db_connect();
    $userId = (int) $_SESSION['user_id'];

    $stmt = $conn->prepare('DELETE FROM push_subscriptions WHERE user_id = ? AND endpoint = ?');
    $stmt->bind_param('is', $userId, $endpoint);
    $stmt->execute();
    $conn->close();

    echo json_encode(['ok' => true]);
}
