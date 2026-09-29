<?php
/**
 * push-send.php — Envoi quotidien des notifications push (lazy trigger)
 * Appelé automatiquement lors de la première visite du jour (via index.php).
 * Utilise un verrou fichier pour éviter les envois multiples simultanés.
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/../php/webpush/WebPush.php';

define('VAPID_SUBJECT',     'mailto:udje266@gmail.com');
define('VAPID_PUBLIC_KEY',  'BHpcuD9GQ0Q6PoQHujWBr3l-vKzQPr4YhBYY5HqJHK5Z6iFy23f-q8kmN22PKI3F8n3UYcHHpf2leBjP5GDe3-w');
define('VAPID_PRIVATE_KEY', getenv('VAPID_PRIVATE_KEY') ?: __DIR__ . '/../keys/vapid_private.pem');
define('LOCK_FILE',         sys_get_temp_dir() . '/mdj_push_' . date('Y-m-d') . '.lock');

header('Content-Type: application/json; charset=utf-8');

/* ── Verrou : un seul envoi par jour ── */
if (file_exists(LOCK_FILE)) {
    echo json_encode(['ok' => true, 'skipped' => true, 'reason' => 'already_sent_today']);
    exit;
}

/* Créer le fichier lock avant de commencer */
file_put_contents(LOCK_FILE, date('c'));

$conn  = db_connect();
$today = date('Y-m-d');

/* Récupère toutes les souscriptions dont l'utilisateur n'a PAS encore joué aujourd'hui */
$result = $conn->query(
    "SELECT ps.id, ps.endpoint, ps.p256dh, ps.auth
     FROM push_subscriptions ps
     LEFT JOIN scores s ON s.user_id = ps.user_id AND s.date_jour = '{$today}'
     WHERE s.id IS NULL"
);

if (!$result || $result->num_rows === 0) {
    $conn->close();
    echo json_encode(['ok' => true, 'sent' => 0]);
    exit;
}

$subscriptions = $result->fetch_all(MYSQLI_ASSOC);
$conn->close();

$wp   = new WebPush(VAPID_SUBJECT, VAPID_PUBLIC_KEY, VAPID_PRIVATE_KEY);
$sent = 0;
$dead = [];

foreach ($subscriptions as $sub) {
    $res = $wp->send([
        'endpoint' => $sub['endpoint'],
        'keys'     => ['p256dh' => $sub['p256dh'], 'auth' => $sub['auth']],
    ]);

    if ($res['ok']) {
        $sent++;
    } elseif (in_array($res['http_code'], [404, 410], true)) {
        // Endpoint expiré → on le supprime
        $dead[] = (int) $sub['id'];
    }
}

/* Supprime les souscriptions mortes */
if ($dead) {
    $conn2  = db_connect();
    $ids    = implode(',', $dead);
    $conn2->query("DELETE FROM push_subscriptions WHERE id IN ($ids)");
    $conn2->close();
}

echo json_encode(['ok' => true, 'sent' => $sent, 'removed' => count($dead)]);
