<?php
session_start();
require_once __DIR__ . '/php/config.php';
require_once __DIR__ . '/php/csrf.php';
require_once __DIR__ . '/php/security.php';

// Headers de sécurité
appliquer_headers_securite();

// Empêche le cache navigateur — critique pour la sécurité multi-utilisateur
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');

if (!isset($_SESSION['user_id'])) {
    header('Location: inscription/login.php');
    exit();
}

$conn   = db_connect();
$userId = (int) $_SESSION['user_id'];
$stmt   = $conn->prepare('SELECT username, is_admin FROM users WHERE id = ?');
$stmt->bind_param('i', $userId);
$stmt->execute();
$user      = $stmt->get_result()->fetch_assoc();
$username  = htmlspecialchars($user['username'] ?? 'Joueur');
$isAdmin   = (bool) ($user['is_admin'] ?? false);
$csrfToken = csrf_token();
$jourNum   = jour_numero();
$conn->close();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mot du Jour CI 🇨🇮</title>
    <link rel="stylesheet" href="style/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="icon" type="image/svg+xml" href="assets/icons/icon-192.svg">
    <link rel="manifest" href="manifest.json">
    <meta name="theme-color" content="#F77F00">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="MDJ CI">
    <link rel="apple-touch-icon" href="assets/icons/icon-192.svg">
</head>
<body>
    <main>
        <!-- ===== SIDEBAR GAUCHE ===== -->
        <div class="infos">

            <!-- Barre navigation -->
            <div class="info-logo">
                <a href="#" id="openRules" title="Règles du jeu">
                    <i class="fa-solid fa-circle-info"></i> Règles
                </a>

                <div class="nav-links">
                    <a href="dashboard/profile.php">📊 Profil</a>
                    <a href="dashboard/leaderboard.php">🏆 Classement</a>
                    <?php if ($isAdmin): ?>
                    <a href="admin/mots.php" style="color:#F77F00;border-color:rgba(247,127,0,0.4)">⚙️ Admin</a>
                    <?php endif; ?>
                </div>

                <a href="inscription/logout.php" class="btn-deconnect">
                    <i class="fa-solid fa-right-from-bracket"></i> Déco
                </a>
            </div>

            <!-- MODAL RÈGLES -->
            <div class="modal" id="rulesModal">
                <div class="modal-content">
                    <span class="close" data-close="rulesModal">&times;</span>
                    <h3>🇨🇮 Règles du jeu</h3>
                    <p>
                        Devine le <strong>mot du jour</strong> ivoirien.<br>
                        Plus ton mot est proche, plus la température monte 🔥
                    </p>
                    <p>On compare chaque lettre à la <strong>même position</strong> que dans le mot du jour.</p>
                    <p><strong>Exemple :</strong></p>
                    <ul>
                        <li>Mot du jour : <strong>ABOBO</strong></li>
                        <li>Mot proposé : <strong>ABIDJAN</strong></li>
                        <li>Comparaison : A ✅, B ✅, I ❌, D ❌, J ❌</li>
                        <li>Lettres correctes : 2 sur 5 = 40%</li>
                    </ul>
                    <p>
                        🔹 Les lettres <span style="color:#22c55e;font-weight:bold">vertes</span> sont à la bonne position.<br>
                        🔹 Les mots les plus hauts sont les plus proches.<br>
                        🔹 Le mot change chaque jour à minuit.
                    </p>
                </div>
            </div>

            <!-- MODAL DÉFINITION DU MOT -->
            <div class="modal" id="definitionModal">
                <div class="modal-content def-modal-content">
                    <span class="close" data-close="definitionModal">&times;</span>
                    <div class="def-header">
                        <span class="def-icon">&#128218;</span>
                        <h3 id="defMotTitre"></h3>
                    </div>
                    <div id="defContenu" class="def-corps"></div>
                    <p class="def-footer">Mot du Jour CI &#127464;&#127470;</p>
                </div>
            </div>

            <!-- Bloc instruction + tableau température -->
            <div class="instruction">
                <h3>Jour n°<?= $jourNum + 1 ?></h3>

                <p>🥶🥶🥶🥶🥶🥶🥶🥶🥶</p>
                <h3>ÉCHELLE DE TEMPÉRATURE</h3>
                <table>
                    <thead>
                        <tr id="en-tete">
                            <th>%</th>
                            <th>Niveau</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td>100</td>  <td>🥳</td></tr>
                        <tr><td>80–99</td><td>😮</td></tr>
                        <tr><td>60–79</td><td>🔥</td></tr>
                        <tr><td>40–59</td><td>🥵</td></tr>
                        <tr><td>20–39</td><td>😎</td></tr>
                        <tr><td>1–19</td> <td>🥶</td></tr>
                        <tr><td>0</td>    <td>🧊</td></tr>
                    </tbody>
                </table>
                <p>Bonne chance ! 💪</p>
            </div>
        </div>

        <!-- ===== ZONE JEU ===== -->
        <div class="game">
            <header>
                <img src="assets/logo.svg" alt="logo Mot du Jour CI" style="max-width:340px;width:100%">
                <p>Trouvez le mot secret du jour !</p>
                <p id="countdown"></p>
                <span class="bienvenue">👋 <?= $username ?></span>
            </header>

            <!-- Tuiles indices : longueur + lettres confirmées -->
            <div class="tuiles-wrapper">
                <div id="tuiles" class="tuiles" aria-label="Indices">
                    <!-- Générées par JS après fetch de la longueur -->
                </div>
                <p class="tuiles-hint">Les lettres <span style="color:#22c55e">vertes</span> sont à la bonne position</p>
            </div>

            <!-- Zone input -->
            <div class="guess-box">
                <input type="text" id="guessInput" placeholder="Entre un mot…" autocomplete="off" spellcheck="false">
                <button id="guessBtn">Valider</button>
            </div>
            <p id="message"></p>

            <!-- Tableau des tentatives -->
            <div class="results-wrapper">
                <table class="results">
                    <thead>
                        <tr>
                            <th>N°</th>
                            <th>Mot</th>
                            <th>🌡️</th>
                            <th>%</th>
                        </tr>
                    </thead>
                    <tbody id="resultsBody"></tbody>
                </table>
            </div>

            <!-- Bouton partager + notification -->
            <div style="padding:16px 24px;display:flex;flex-direction:column;gap:10px;">
                <button onclick="partagerScore()" id="shareButton" style="width:100%">
                    &#128228; Partager mon score
                </button>
                <button id="defBtn" onclick="ouvrirDefinition()" style="display:none;width:100%;background:rgba(0,158,96,0.12);border:1px solid rgba(0,158,96,0.35);color:#00c96a;">
                    &#128218; Voir la définition du mot
                </button>
                <button id="notifBtn" onclick="toggleNotification()" style="width:100%;background:rgba(247,127,0,0.12);border:1px solid rgba(247,127,0,0.35);color:#F77F00;display:none">
                    &#128276; Activer les rappels quotidiens
                </button>
            </div>
        </div>
    </main>

    <footer style="text-align:center;padding:20px 16px 32px;">
        <nav aria-label="Liens légaux" style="display:flex;justify-content:center;gap:20px;flex-wrap:wrap;">
            <a href="legal/confidentialite.php" style="color:rgba(232,224,212,0.35);font-size:0.78rem;text-decoration:none;">🔒 Confidentialité</a>
            <a href="legal/conditions.php"      style="color:rgba(232,224,212,0.35);font-size:0.78rem;text-decoration:none;">📜 CGU</a>
            <a href="legal/cookies.php"         style="color:rgba(232,224,212,0.35);font-size:0.78rem;text-decoration:none;">🍪 Cookies</a>
        </nav>
        <p style="color:rgba(232,224,212,0.2);font-size:0.72rem;margin-top:8px;">© <?= date('Y') ?> DevineMot CI 🇨🇮 — Fait avec ❤️ en Côte d'Ivoire</p>
    </footer>

    <script>
        window.username   = <?= json_encode($username) ?>;
        window.csrfToken  = <?= json_encode($csrfToken) ?>;
        window.vapidKey   = 'BHpcuD9GQ0Q6PoQHujWBr3l-vKzQPr4YhBYY5HqJHK5Z6iFy23f-q8kmN22PKI3F8n3UYcHHpf2leBjP5GDe3-w';
        // BASE_PATH calculé dynamiquement : '/' en prod Railway, chemin local en XAMPP
        window.BASE_PATH  = <?= json_encode(rtrim(dirname($_SERVER['SCRIPT_NAME']), '/')) ?>;
    </script>
    <script src="js/main.js"></script>
</body>
</html>
