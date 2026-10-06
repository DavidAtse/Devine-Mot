<?php
/**
 * enregistrer-don.php — Enregistre un soutien financier (Wave ou autre).
 */
session_start();
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/csrf.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'erreur' => 'Méthode non autorisée.']);
    exit;
}

// CSRF check
csrf_check_ajax();

$montant = (int) ($_POST['montant'] ?? 0);
$moyen   = trim($_POST['moyen'] ?? 'Wave');

if ($montant <= 0) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'erreur' => 'Montant invalide.']);
    exit;
}

// Cap max raisonnable pour éviter les abus ou saisies erronées (ex: 1 000 000 FCFA max par don)
if ($montant > 1000000) {
    $montant = 1000000;
}

$conn   = db_connect();
$userId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
$pseudo = 'Anonyme';

if ($userId) {
    $stmt = $conn->prepare('SELECT username FROM users WHERE id = ?');
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $uRow = $stmt->get_result()->fetch_assoc();
    if ($uRow && !empty($uRow['username'])) {
        $pseudo = $uRow['username'];
    }
}

$stmtIns = $conn->prepare('INSERT INTO dons (user_id, donateur, montant, moyen, statut, source) VALUES (?, ?, ?, ?, "confirme", "site")');
$stmtIns->bind_param('isis', $userId, $pseudo, $montant, $moyen);
$ok = $stmtIns->execute();

$conn->close();

echo json_encode([
    'ok' => $ok,
    'message' => 'Soutien enregistré.'
]);
