<?php
require_once __DIR__ . '/config.php';

session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    exit;
}

$conn = db_connect();
$mot  = assigner_mot_creneau($conn, date('Y-m-d'), creneau_actuel());
$conn->close();

echo $mot;
?>
