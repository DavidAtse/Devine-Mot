<?php
/**
 * push-schedule.php — Notifications Push intelligentes et planifiées.
 *
 * Circuit d'une journée :
 * - Si l'utilisateur N'A PAS encore joué : notif toutes les heures de 8h à 22h.
 * - Si l'utilisateur A TROUVÉ le mot     : notif toutes les 5h pour "prépare-toi pour demain".
 *
 * Ce script est appelé par Railway Cron (toutes les heures).
 * Il ne s'exécute pas entre 23h et 7h (heure d'Abidjan).
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/../php/webpush/WebPush.php';

date_default_timezone_set('Africa/Abidjan');

define('VAPID_SUBJECT',     'mailto:daatsey24@gmail.com');
define('VAPID_PUBLIC_KEY',  'BHpcuD9GQ0Q6PoQHujWBr3l-vKzQPr4YhBYY5HqJHK5Z6iFy23f-q8kmN22PKI3F8n3UYcHHpf2leBjP5GDe3-w');
define('VAPID_PRIVATE_KEY', getenv('VAPID_PRIVATE_KEY') ?: __DIR__ . '/../keys/vapid_private.pem');

header('Content-Type: application/json; charset=utf-8');

$now       = new DateTime();
$heure     = (int) $now->format('G'); // 0-23
$today     = $now->format('Y-m-d');

// Pas de notif la nuit (23h → 7h)
if ($heure < 8 || $heure > 22) {
    echo json_encode(['ok' => true, 'skipped' => true, 'reason' => 'hors_plage_horaire']);
    exit;
}

$conn = db_connect();
$wp   = new WebPush(VAPID_SUBJECT, VAPID_PUBLIC_KEY, VAPID_PRIVATE_KEY);

$sent_rappel    = 0;
$sent_demain    = 0;
$dead           = [];

// ─── 1. Utilisateurs qui N'ONT PAS encore joué aujourd'hui ───────────────
// → Notif de rappel toutes les heures
$stmt = $conn->prepare("
    SELECT ps.id, ps.endpoint, ps.p256dh, ps.auth
    FROM push_subscriptions ps
    LEFT JOIN scores s ON s.user_id = ps.user_id AND s.date_jour = ?
    WHERE s.id IS NULL
");
$stmt->bind_param('s', $today);
$stmt->execute();
$nonJoue = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$messagesRappel = [
    "🔥 T'as pas encore deviné le mot du jour ! Lance-toi !",
    "🇨🇮 Le mot du jour t'attend ! Tu peux le trouver !",
    "⏰ Il te reste du temps ! Viens deviner le mot du jour.",
    "🧠 Ton cerveau est prêt ? Le mot du jour n'attend que toi !",
    "🎯 Reviens jouer ! Tu es à quelques lettres du mot mystère.",
];
$msgRappel = $messagesRappel[$heure % count($messagesRappel)];

foreach ($nonJoue as $sub) {
    $res = $wp->send([
        'endpoint' => $sub['endpoint'],
        'keys'     => ['p256dh' => $sub['p256dh'], 'auth' => $sub['auth']],
    ]);
    if ($res['ok']) {
        $sent_rappel++;
    } elseif (in_array($res['http_code'], [404, 410], true)) {
        $dead[] = (int)$sub['id'];
    }
}

// ─── 2. Utilisateurs qui ONT trouvé le mot : notif toutes les 5h ─────────
// Heures de notif "prépare-toi" : 8h, 13h, 18h (heure d'Abidjan)
$heuresDemain = [8, 13, 18];
if (in_array($heure, $heuresDemain)) {
    $stmt2 = $conn->prepare("
        SELECT ps.id, ps.endpoint, ps.p256dh, ps.auth
        FROM push_subscriptions ps
        INNER JOIN scores s ON s.user_id = ps.user_id AND s.date_jour = ? AND s.trouve = 1
    ");
    $stmt2->bind_param('s', $today);
    $stmt2->execute();
    $dejaGagne = $stmt2->get_result()->fetch_all(MYSQLI_ASSOC);

    $msgsDemain = [
        "🌅 Bravo pour aujourd'hui ! Prépare-toi, un nouveau mot mystère arrive demain !",
        "🏆 Tu as trouvé le mot ! Reviens demain pour un nouveau défi ivoirien.",
        "🇨🇮 Tu t'en sors bien ! Sois prêt(e) pour le mot du jour de demain.",
    ];
    $msgDemain = $msgsDemain[$heure % count($msgsDemain)];

    foreach ($dejaGagne as $sub) {
        $res = $wp->send([
            'endpoint' => $sub['endpoint'],
            'keys'     => ['p256dh' => $sub['p256dh'], 'auth' => $sub['auth']],
        ]);
        if ($res['ok']) {
            $sent_demain++;
        } elseif (in_array($res['http_code'], [404, 410], true)) {
            $dead[] = (int)$sub['id'];
        }
    }
}

// Nettoyer les souscriptions mortes
if ($dead) {
    $ids    = implode(',', array_unique($dead));
    $conn->query("DELETE FROM push_subscriptions WHERE id IN ($ids)");
}

$conn->close();

echo json_encode([
    'ok'            => true,
    'heure'         => $heure,
    'rappel_envoye' => $sent_rappel,
    'demain_envoye' => $sent_demain,
    'dead_removed'  => count(array_unique($dead)),
]);
