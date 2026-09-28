<?php
require_once __DIR__ . '/config.php';
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['erreur' => 'Non connecté.']);
    exit;
}

$conn      = db_connect();
$hier      = date('Y-m-d', strtotime('-1 day'));
$avantHier = date('Y-m-d', strtotime('-2 day'));

$stmt = $conn->prepare('SELECT date_jour, UPPER(mot) AS mot FROM mots_du_jour WHERE date_jour IN (?, ?) ORDER BY date_jour DESC');
$stmt->bind_param('ss', $hier, $avantHier);
$stmt->execute();
$rows = $stmt->get_result();

$mots = [];
while ($row = $rows->fetch_assoc()) {
    $mots[$row['date_jour']] = $row['mot'];
}

$conn->close();
echo json_encode([
    'hier'      => $mots[$hier]      ?? null,
    'avantHier' => $mots[$avantHier] ?? null,
]);
?>
