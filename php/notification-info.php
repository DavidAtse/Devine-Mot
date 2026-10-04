<?php
require_once __DIR__ . '/config.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

$jourNum = jour_numero();
$body    = 'Jour #' . ($jourNum + 1) . ' — Le mot de ' . creneau_libelle(creneau_actuel()) . ' t\'attend ! 🔥';

echo json_encode([
    'title' => '🇨🇮 iMots CI',
    'body'  => $body,
], JSON_UNESCAPED_UNICODE);
