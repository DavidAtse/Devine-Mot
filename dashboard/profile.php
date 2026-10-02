<?php
session_start();
require_once __DIR__ . '/../php/config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../inscription/login.php');
    exit();
}

$conn   = db_connect();
$userId = (int) $_SESSION['user_id'];

$stmt = $conn->prepare('
    SELECT
        COUNT(*)                                AS total,
        SUM(trouve = 1)                         AS victoires,
        MIN(CASE WHEN trouve = 1 THEN tentatives END) AS meilleur
    FROM scores
    WHERE user_id = ?
');
$stmt->bind_param('i', $userId);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

$total     = (int)   $data['total'];
$victoires = (int)   $data['victoires'];
$meilleur  = $data['meilleur'] !== null ? (int) $data['meilleur'] : null;
$taux      = $total > 0 ? round(($victoires / $total) * 100, 1) : 0;
$serie     = _calculer_serie($conn, $userId);

$stmt2 = $conn->prepare('SELECT username FROM users WHERE id = ?');
$stmt2->bind_param('i', $userId);
$stmt2->execute();
$user     = $stmt2->get_result()->fetch_assoc();
$username = htmlspecialchars($user['username'] ?? '');
$conn->close();

// Dernier score du jour
function _calculer_serie(mysqli $conn, int $userId): int {
    $stmt = $conn->prepare('
        SELECT date_jour, trouve FROM scores
        WHERE user_id = ?
        ORDER BY date_jour DESC
    ');
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $rows  = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $serie = 0;
    $prev  = null;
    foreach ($rows as $row) {
        if ($row['trouve'] != 1) break;
        $d = new DateTime($row['date_jour']);
        if ($prev !== null) {
            $diff = (clone $prev)->diff($d)->days;
            if ($diff !== 1) break;
        }
        $serie++;
        $prev = $d;
    }
    return $serie;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Profil – Mot du Jour CI</title>
    <link rel="stylesheet" href="dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="icon" type="image/x-icon" href="../assets/1200x630wa-removebg-preview.png">
</head>
<body>
    <nav class="dash-nav">
        <a href="../index.php"><i class="fa-solid fa-arrow-left"></i> Retour au jeu</a>
        <a href="leaderboard.php"><i class="fa-solid fa-trophy"></i> Classement</a>
        <a href="../inscription/logout.php" class="btn-logout"><i class="fa-solid fa-right-from-bracket"></i> Déconnexion</a>
    </nav>

    <h1>📊 Mon Profil</h1>
    <p class="pseudo">👤 <?= $username ?></p>

    <div class="card">
        <div class="stat">
            <span class="stat-icon">🎮</span>
            <span class="stat-label">Parties jouées</span>
            <span class="highlight"><?= $total ?></span>
        </div>
        <div class="stat">
            <span class="stat-icon">🏆</span>
            <span class="stat-label">Victoires</span>
            <span class="highlight"><?= $victoires ?></span>
        </div>
        
        <div class="stat">
            <span class="stat-icon">⚡</span>
            <span class="stat-label">Meilleur score</span>
            <span class="highlight"><?= $meilleur !== null ? "{$meilleur} essai" . ($meilleur > 1 ? 's' : '') : '—' ?></span>
        </div>
        <div class="stat">
            <span class="stat-icon">🔥</span>
            <span class="stat-label">Série en cours</span>
            <span class="highlight"><?= $serie ?> jour<?= $serie > 1 ? 's' : '' ?></span>
        </div>
    </div>

    <?php if ($total === 0): ?>
    <p style="text-align:center;opacity:.5;margin-top:20px">Aucune partie jouée. <a href="../index.php" style="color:#F77F00">Jouer maintenant →</a></p>
    <?php endif; ?>
</body>
</html>
