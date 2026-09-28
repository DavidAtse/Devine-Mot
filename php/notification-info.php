<?php
require_once __DIR__ . '/config.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

$jourNum = jour_numero();
$body    = 'Jour #' . ($jourNum + 1) . ' — As-tu trouvé le mot du jour ? 🔥';

echo json_encode([
    'title' => '🇨🇮 Mot du Jour CI',
    'body'  => $body,
], JSON_UNESCAPED_UNICODE);
