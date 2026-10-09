<?php
session_start();
require_once __DIR__ . '/../php/config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../inscription/login.php');
    exit();
}

$conn = db_connect();

$stmt = $conn->prepare('
    SELECT
        u.username,
        COUNT(s.id)                 AS victoires,
        MIN(s.created_at)           AS premier_trouve,
        MAX(s.created_at)           AS dernier_trouve,
        MIN(s.tentatives)           AS meilleur,
        ROUND(AVG(s.tentatives), 1) AS moy
    FROM scores s
    JOIN users u ON u.id = s.user_id
    WHERE s.trouve = 1
    GROUP BY s.user_id, u.username
    ORDER BY victoires DESC, dernier_trouve ASC
    LIMIT 10
');
$stmt->execute();
$joueurs = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$conn->close();

$medals = ['🥇', '🥈', '🥉'];
$userId = (int) $_SESSION['user_id'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <link rel="icon" href="../favicon.ico" sizes="any">
    <link rel="icon" type="image/png" sizes="32x32" href="../assets/icons/favicon-32.png">
    <link rel="icon" type="image/png" sizes="192x192" href="../assets/icons/icon-192.png">
    <link rel="apple-touch-icon" sizes="180x180" href="../assets/icons/icon-180.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Classement – Mot du Jour CI</title>
    <link rel="stylesheet" href="dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
    <nav class="dash-nav">
        <a href="../index.php"><i class="fa-solid fa-arrow-left"></i> Retour au jeu</a>
        <a href="profile.php"><i class="fa-solid fa-user"></i> Mon Profil</a>
        <a href="../inscription/logout.php" class="btn-logout"><i class="fa-solid fa-right-from-bracket"></i> Déconnexion</a>
    </nav>

    <h1>🏆 Classement</h1>
    <p style="text-align:center;font-size:13px;color:#aaa;margin-top:-6px;margin-bottom:22px;">
        Classé par victoires, puis par ordre d'arrivée ⚡
    </p>

    <div class="container">
        <?php if (empty($joueurs)): ?>
            <p style="text-align:center;padding:30px;opacity:.5">Aucune victoire enregistrée pour l'instant.</p>
        <?php else: ?>
        <table class="leaderboard-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Joueur</th>
                    <th>🏆</th>
                    <th>⏱️ Trouvé à</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($joueurs as $i => $row): 
                $dateV = !empty($row['dernier_trouve']) ? new DateTime($row['dernier_trouve']) : null;
                $heureTxt = $dateV ? ($dateV->format('Y-m-d') === date('Y-m-d') ? $dateV->format('H\hi') : $dateV->format('d/m H\hi')) : '-';
            ?>
                <tr class="<?= $i < 3 ? 'top-' . ($i + 1) : '' ?>">
                    <td><?= $medals[$i] ?? '#' . ($i + 1) ?></td>
                    <td><?= htmlspecialchars($row['username']) ?></td>
                    <td><?= $row['victoires'] ?></td>
                    <td title="<?= (int)$row['meilleur'] ?> essai(s)"><?= $heureTxt ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</body>
</html>
