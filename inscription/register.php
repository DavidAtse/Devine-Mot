<?php
session_start();
require_once __DIR__ . '/../php/config.php';
require_once __DIR__ . '/../php/csrf.php';
require_once __DIR__ . '/../php/security.php';
appliquer_headers_securite();

$conn = db_connect();

$erreur = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // --- CSRF ---
    csrf_check_form();

    // --- Rate-limiting (anti-spam mass register) ---
    $now = time();
    if (!isset($_SESSION['reg_lockout'])) $_SESSION['reg_lockout'] = 0;
    if (!isset($_SESSION['reg_attempts'])) $_SESSION['reg_attempts'] = 0;

    if ($_SESSION['reg_lockout'] > $now) {
        $reste = ceil(($_SESSION['reg_lockout'] - $now) / 60);
        $erreur = "Trop de tentatives. Réessaie dans {$reste} minute(s).";
    } else {
        $username = trim($_POST['username'] ?? '');
        $email    = trim($_POST['email']    ?? '');
        $password = $_POST['password']      ?? '';

        // Honeypot anti-bot : le champ "website" doit rester vide
        if (!empty($_POST['website'])) {
            $erreur = '❌ Inscription invalide.';
        } elseif ($username === '' || $email === '' || $password === '') {
            $erreur = '❌ Tous les champs sont obligatoires.';
    } elseif (mb_strlen($username) < 2 || mb_strlen($username) > 30) {
        $erreur = '❌ Le pseudo doit faire entre 2 et 30 caractères.';
    } elseif (!preg_match('/^[a-zA-Z0-9_\-]+$/', $username)) {
        $erreur = '❌ Pseudo invalide (lettres, chiffres, tirets et underscores uniquement).';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erreur = '❌ Adresse email invalide.';
    } elseif (strlen($email) > 100) {
        $erreur = '❌ Email trop long.';
    } elseif (mb_strlen($password) < 6) {
        $erreur = '❌ Le mot de passe doit faire au moins 6 caractères.';
    } else {
        // Vérifier unicité pseudo / email
        $chk = $conn->prepare('SELECT id FROM users WHERE username = ? OR email = ?');
        $chk->bind_param('ss', $username, $email);
        $chk->execute();

        if ($chk->get_result()->num_rows > 0) {
            $erreur = '❌ Ce pseudo ou cet email est déjà pris.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $ins  = $conn->prepare('INSERT INTO users (username, email, password) VALUES (?, ?, ?)');
            $ins->bind_param('sss', $username, $email, $hash);

            if ($ins->execute()) {
                header('Location: login.php?nouveau=1');
                exit();
            } else {
                $erreur = '❌ Erreur lors de la création du compte, réessaie.';
            }
        }
        }
    }
    
    // Si on arrive ici avec une erreur, c'est un échec d'inscription
    if ($erreur) {
        $_SESSION['reg_attempts']++;
        if ($_SESSION['reg_attempts'] >= 5) {
            $_SESSION['reg_lockout'] = time() + 5 * 60; // 5 min block
            $_SESSION['reg_attempts'] = 0;
            $erreur = '❌ Trop de tentatives. Inscriptions bloquées 5 minutes.';
        }
    } else {
        $_SESSION['reg_attempts'] = 0;
    }
}

$conn->close();
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
    <title>iMots CI – Connexion</title>
    <meta name="robots" content="noindex, nofollow">
    <link rel="stylesheet" href="style.css?v=3">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <meta name="theme-color" content="#0A1A0F">
</head>
<body>
    <div class="deco deco-1"></div>
    <div class="deco deco-2"></div>
    <div class="deco deco-3"></div>

    <div class="card">
        <div class="flag-stripe"></div>

        <div class="card-header">
            <img class="logo-img" src="../assets/logo.png" alt="iMots CI - Les mots d'ici, un défi chaque jour" width="240" height="137">
            <p>Crée ton compte gratuitement</p>
        </div>

        <?php if ($erreur): ?>
            <div class="msg erreur"><?= htmlspecialchars($erreur) ?></div>
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
                        placeholder="Entre 2 et 30 caractères"
                        required
                        minlength="2"
                        maxlength="30"
                        pattern="[a-zA-Z0-9_\-]+"
                        title="Lettres, chiffres, tirets, underscores"
                        value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '' ?>"
                    >
                    <i class="fa-solid fa-user"></i>
                </div>
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <div class="input-wrap">
                    <input
                        type="email"
                        id="email"
                        name="email"
                        placeholder="ton@email.com"
                        required
                        maxlength="100"
                        value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>"
                    >
                    <i class="fa-solid fa-envelope"></i>
                </div>
            </div>

            <div class="form-group">
                <label for="password">Mot de passe <small style="color:rgba(253,248,240,.4);font-weight:600">(6 caractères min.)</small></label>
                <div class="input-wrap">
                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="••••••••"
                        required
                        minlength="6"
                    >
                    <i class="fa-solid fa-lock"></i>
                </div>
            </div>

            <button type="submit" class="btn-principal">🚀 CRÉER MON COMPTE</button>
        </form>

        <div class="separator">OU</div>

        <div class="card-footer">
            Déjà inscrit ? <a href="login.php">Se connecter 🔥</a>
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
