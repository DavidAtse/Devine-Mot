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

    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email']    ?? '');
    $password = $_POST['password']      ?? '';

    // --- Validation ---
    if ($username === '' || $email === '' || $password === '') {
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

$conn->close();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription – Mot du Jour CI</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="icon" type="image/x-icon" href="../assets/1200x630wa-removebg-preview.png">
</head>
<body>
    <div class="deco deco-1"></div>
    <div class="deco deco-2"></div>
    <div class="deco deco-3"></div>

    <div class="card">
        <div class="flag-stripe"></div>

        <div class="card-header">
            <span class="emoji">✍️</span>
            <h1>Rejoins le <span>Jeu</span></h1>
            <p>Crée ton compte gratuitement</p>
        </div>

        <?php if ($erreur): ?>
            <div class="msg erreur"><?= htmlspecialchars($erreur) ?></div>
        <?php endif; ?>

        <form method="POST" autocomplete="off">
            <?= csrf_field() ?>

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
</body>
</html>
