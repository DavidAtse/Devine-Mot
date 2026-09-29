<?php
session_start();
require_once __DIR__ . '/../php/config.php';
require_once __DIR__ . '/../php/csrf.php';
require_once __DIR__ . '/../php/security.php';
appliquer_headers_securite();

$conn = db_connect();

$erreur = '';
$succes = '';

// Message de bienvenue après inscription
if (isset($_GET['nouveau'])) {
    $succes = '✅ Compte créé avec succès, connecte-toi !';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // --- CSRF ---
    csrf_check_form();

    // --- Rate-limiting simple via session ---
    $now = time();
    if (!isset($_SESSION['login_attempts'])) $_SESSION['login_attempts'] = 0;
    if (!isset($_SESSION['login_lockout']))  $_SESSION['login_lockout']  = 0;

    if ($_SESSION['login_lockout'] > $now) {
        $reste = ceil(($_SESSION['login_lockout'] - $now) / 60);
        $erreur = "Trop de tentatives. Réessaie dans {$reste} minute(s).";
    } else {

        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        // Honeypot anti-bot : le champ "website" doit rester vide
        if (!empty($_POST['website'])) {
            // Un bot a rempli le champ caché → on ignore silencieusement
            $erreur = '❌ Pseudo ou mot de passe incorrect.';
        } elseif ($username === '' || $password === '') {
            $erreur = '❌ Pseudo et mot de passe requis.';
        } else {
            $stmt = $conn->prepare('SELECT id, username, password FROM users WHERE username = ?');
            $stmt->bind_param('s', $username);
            $stmt->execute();
            $user = $stmt->get_result()->fetch_assoc();

            // Message identique que l'utilisateur existe ou non → anti-énumération
            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id']  = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['login_attempts'] = 0;
                session_regenerate_id(true); // Prévenir la fixation de session
                header('Location: ../index.php');
                exit();
            } else {
                $_SESSION['login_attempts']++;
                if ($_SESSION['login_attempts'] >= 5) {
                    $_SESSION['login_lockout']  = $now + 10 * 60; // 10 min
                    $_SESSION['login_attempts'] = 0;
                    $erreur = '❌ Trop de tentatives. Compte bloqué 10 minutes.';
                } else {
                    $erreur = '❌ Pseudo ou mot de passe incorrect.';
                }
            }
        }
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>iMots CI – Connexion</title>
    <meta name="robots" content="noindex, nofollow">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Favicons -->
    <link rel="icon" type="image/svg+xml" href="../assets/icons/icon-192.svg">
    <link rel="icon" type="image/png" href="../assets/icons/icon-192.png" sizes="192x192">
    <link rel="apple-touch-icon" href="../assets/icons/icon-192.png">
    <link rel="mask-icon" href="../assets/icons/icon-192.svg" color="#F77F00">
    <meta name="theme-color" content="#1A1008">
</head>
<body>
    <div class="deco deco-1"></div>
    <div class="deco deco-2"></div>
    <div class="deco deco-3"></div>

    <div class="card">
        <div class="flag-stripe"></div>

        <div class="card-header">
            <span class="emoji">🇨🇮</span>
            <h1>Mot du Jour <span>CI</span></h1>
            <p>Connecte-toi pour jouer</p>
        </div>

        <?php if ($erreur): ?>
            <div class="msg erreur"><?= htmlspecialchars($erreur) ?></div>
        <?php endif; ?>
        <?php if ($succes): ?>
            <div class="msg succes"><?= htmlspecialchars($succes) ?></div>
        <?php endif; ?>

        <form method="POST" autocomplete="off" novalidate>
            <?= csrf_field() ?>
            <!-- Honeypot anti-bot (ne pas supprimer) -->
            <div style="position:absolute;left:-9999px;top:-9999px;opacity:0;" aria-hidden="true" tabindex="-1">
                <label for="website">Ne pas remplir</label>
                <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
            </div>

            <div class="form-group">
                <label for="username">Pseudo</label>
                <div class="input-wrap">
                    <input
                        type="text"
                        id="username"
                        name="username"
                        placeholder="Ton nom de joueur"
                        required
                        maxlength="50"
                        value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '' ?>"
                    >
                    <i class="fa-solid fa-user"></i>
                </div>
            </div>

            <div class="form-group">
                <label for="password">Mot de passe</label>
                <div class="input-wrap">
                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="••••••••"
                        required
                    >
                    <i class="fa-solid fa-lock"></i>
                </div>
            </div>

            <button type="submit" class="btn-principal">🔥 ENTRER AU JEU</button>
        </form>

        <div class="separator">OU</div>

        <div class="card-footer">
            Pas encore inscrit ? <a href="register.php">Créer un compte 🚀</a>
        </div>
    </div>

    <footer style="text-align:center;padding:24px 16px 32px;margin-top:16px;">
        <nav aria-label="Liens légaux" style="display:flex;justify-content:center;gap:20px;flex-wrap:wrap;">
            <a href="../legal/confidentialite.php" style="color:rgba(232,224,212,0.45);font-size:0.8rem;text-decoration:none;">🔒 Confidentialité</a>
            <a href="../legal/conditions.php"      style="color:rgba(232,224,212,0.45);font-size:0.8rem;text-decoration:none;">📜 CGU</a>
            <a href="../legal/cookies.php"         style="color:rgba(232,224,212,0.45);font-size:0.8rem;text-decoration:none;">🍪 Cookies</a>
        </nav>
        <p style="color:rgba(232,224,212,0.25);font-size:0.75rem;margin-top:10px;">© <?= date('Y') ?> iMots CI 🇨🇮</p>
    </footer>
</body>
</html>
