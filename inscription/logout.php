<?php
session_start();

// Récupérer le username avant de détruire la session (pour nettoyer le localStorage côté client)
$logoutUsername = $_SESSION['username'] ?? '';

// Nettoyer la session
$_SESSION = [];
session_destroy();

// Empêche le cache de cette page
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
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
    <title>Déconnexion – Mot du Jour CI</title>
    <link rel="stylesheet" href="style.css?v=3">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script>
        // Ne PAS supprimer les données de jeu (hist, conf, len) :
        // elles sont nécessaires pour restaurer les tuiles et l'historique
        // à la reconnexion. Le reset quotidien (main.js) les nettoie à minuit.
        // On ne supprime rien ici pour préserver la progression du jour.
        // Redirection automatique après 3 secondes
        setTimeout(() => {
            window.location.href = "login.php";
        }, 3000);
    </script>
</head>
<body>
    <div class="deco deco-1"></div>
    <div class="deco deco-2"></div>
    <div class="deco deco-3"></div>

    <div class="card">
        <div class="flag-stripe"></div>

        <span class="logout-icon">👋</span>

        <div class="card-header">
            <h1>À la <span>prochaine</span> !</h1>
        </div>

        <p class="logout-text">
            Tu t'es déconnecté avec succès.<br>
            Reviens demain pour le nouveau mot du jour !<br>
            <strong style="color: var(--orange);">Redirection dans 3 secondes…</strong>
        </p>

        <a href="login.php" class="btn-principal" style="display:block;text-align:center;text-decoration:none;padding:15px;border-radius:14px;font-family:'Paytone One',sans-serif;font-size:17px;letter-spacing:1.5px;background:linear-gradient(135deg,#F77F00,#e06000);color:#fff;box-shadow:0 6px 24px rgba(247,127,0,0.4);">
            🔐 SE RECONNECTER
        </a>

        <a href="register.php" class="btn-secondaire">Créer un nouveau compte</a>
    </div>
</body>
</html>