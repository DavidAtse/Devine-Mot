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
        COUNT(s.id)             AS victoires,
        MIN(s.tentatives)       AS meilleur,
        ROUND(AVG(s.tentatives), 1) AS moy
    FROM scores s
    JOIN users u ON u.id = s.user_id
    WHERE s.trouve = 1
    GROUP BY s.user_id, u.username
    ORDER BY victoires DESC, meilleur ASC
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Classement – Mot du Jour CI</title>
    <link rel="stylesheet" href="dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="icon" type="image/x-icon" href="../assets/1200x630wa-removebg-preview.png">
</head>
<body>
    <nav class="dash-nav">
        <a href="../index.php"><i class="fa-solid fa-arrow-left"></i> Retour au jeu</a>
        <a href="profile.php"><i class="fa-solid fa-user"></i> Mon Profil</a>
        <a href="../inscription/logout.php" class="btn-logout"><i class="fa-solid fa-right-from-bracket"></i> Déconnexion</a>
    </nav>

    <h1>🏆 Classement</h1>

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
                    <th>⚡ Meilleur</th>
                    <th>Moy.</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($joueurs as $i => $row): ?>
                <tr class="<?= $i < 3 ? 'top-' . ($i + 1) : '' ?>">
                    <td><?= $medals[$i] ?? '#' . ($i + 1) ?></td>
                    <td><?= htmlspecialchars($row['username']) ?></td>
                    <td><?= $row['victoires'] ?></td>
                    <td><?= $row['meilleur'] ?> essai<?= $row['meilleur'] > 1 ? 's' : '' ?></td>
                    <td><?= $row['moy'] ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</body>
</html>
