<?php
/**
 * jeko_webhook.php — Réception des notifications de paiement Jeko Africa.
 * Valide automatiquement les dons dès que le paiement est confirmé par Jeko.
 */
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Méthode non autorisée.']);
    exit;
}

$rawInput = file_get_contents('php://input');
if (!$rawInput) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Corps de requête vide.']);
    exit;
}

$payload = json_decode($rawInput, true);
if (!is_array($payload)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Format JSON invalide.']);
    exit;
}

// Vérification de signature Jeko (optionnelle si secret configuré dans l'environnement)
$webhookSecret = getenv('JEKO_WEBHOOK_SECRET') ?: '';
$signatureHeader = $_SERVER['HTTP_JEKO_SIGNATURE'] ?? $_SERVER['HTTP_X_JEKO_SIGNATURE'] ?? '';

if ($webhookSecret !== '' && $signatureHeader !== '') {
    $expectedSignature = hash_hmac('sha256', $rawInput, $webhookSecret);
    if (!hash_equals($expectedSignature, $signatureHeader)) {
        http_response_code(401);
        echo json_encode(['ok' => false, 'error' => 'Signature invalide.']);
        exit;
    }
}

// Extraction du statut et du montant
// Jeko transmet le statut au niveau racine ou dans un objet data/payment
$status = strtolower($payload['status'] ?? $payload['data']['status'] ?? $payload['event'] ?? '');
$isSuccess = in_array($status, ['success', 'successful', 'paid', 'completed', 'payment.success'], true);

$amount = (int)($payload['amount'] ?? $payload['data']['amount'] ?? $payload['data']['totalAmount'] ?? 0);
$customerName = trim($payload['clientName'] ?? $payload['data']['clientName'] ?? $payload['customer']['name'] ?? 'Donateur Jeko');
$paymentMethod = trim($payload['paymentMethod'] ?? $payload['data']['paymentMethod'] ?? 'Jeko');

if ($isSuccess && $amount > 0) {
    $conn = db_connect();

    // 1. Chercher s'il existe une tentative récente en attente avec ce même montant
    $stmtFind = $conn->prepare("
        SELECT id FROM dons 
        WHERE statut = 'en_attente' AND montant = ? 
        ORDER BY id DESC LIMIT 1
    ");
    $stmtFind->bind_param('i', $amount);
    $stmtFind->execute();
    $found = $stmtFind->get_result()->fetch_assoc();

    if ($found && !empty($found['id'])) {
        $donId = (int)$found['id'];
        $upd = $conn->prepare("UPDATE dons SET statut = 'confirme', moyen = ? WHERE id = ?");
        $moyenFinal = 'Jeko (' . ($paymentMethod ?: 'Mobile Money') . ')';
        $upd->bind_param('si', $moyenFinal, $donId);
        $upd->execute();
    } else {
        // 2. Sinon insérer directement un don confirmé
        $stmtIns = $conn->prepare("
            INSERT INTO dons (donateur, montant, moyen, statut, source) 
            VALUES (?, ?, ?, 'confirme', 'jeko_webhook')
        ");
        $moyenFinal = 'Jeko (' . ($paymentMethod ?: 'Mobile Money') . ')';
        $stmtIns->bind_param('sis', $customerName, $amount, $moyenFinal);
        $stmtIns->execute();
    }

    $conn->close();
}

http_response_code(200);
echo json_encode([
    'ok' => true,
    'message' => 'Notification traitée avec succès.'
]);
