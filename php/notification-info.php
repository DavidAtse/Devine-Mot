<?php
require_once __DIR__ . '/config.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

$secRestantes = creneau_secondes_restantes();
$creneau      = creneau_actuel();
$libelle      = creneau_libelle($creneau);

if ($secRestantes <= 1260 && $secRestantes > 0) {
    $min   = max(1, (int) ceil($secRestantes / 60));
    $title = "⏰ Plus que {$min} min ! — iMots CI 🇨🇮";
    $body  = "Vite ! Trouve le mot de {$libelle} avant qu'il ne disparaisse ! 🔥";
} else {
    $jourNum = jour_numero();
    $title   = '🇨🇮 iMots CI';
    $body    = 'Jour #' . ($jourNum + 1) . ' — Le mot de ' . $libelle . ' t\'attend ! 🔥';
}

echo json_encode([
    'title' => $title,
    'body'  => $body,
], JSON_UNESCAPED_UNICODE);
