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

// Lien de paiement (Jeko / Mobile Money / Wave)
$payment_link = getenv('PAYMENT_LINK') ?: getenv('JEKO_PAYMENT_LINK') ?: getenv('WAVE_PAYMENT_LINK') ?: 'https://pay.jeko.africa/pl/de22040e-0dc4-4558-870a-d08a4ffa8f79';

$conn->close();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <link rel="icon" href="favicon.ico" sizes="any">
    <link rel="icon" type="image/png" sizes="32x32" href="assets/icons/favicon-32.png">
    <link rel="icon" type="image/png" sizes="192x192" href="assets/icons/icon-192.png">
    <link rel="apple-touch-icon" sizes="180x180" href="assets/icons/icon-180.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>iMots CI 🇨🇮 – Les mots d'ici, un défi chaque jour</title>
    <meta name="description" content="Les mots d ici, un defi chaque jour ! ! Le jeu de mots ivoirien 100% local. Un nouveau défi chaque jour.">
    <meta name="robots" content="noindex, nofollow"><!-- Protège les pages authentifiées des moteurs de recherche -->

    <!-- ===== Open Graph (Facebook, WhatsApp, LinkedIn) ===== -->
    <meta property="og:type"        content="website">
    <meta property="og:url"         content="https://imots.alwaysdata.net/">
    <meta property="og:title" content="iMots CI 🇨🇮 – Les mots d'ici, un défi chaque jour">
    <meta property="og:description" content="Devine le mot ivoirien du jour ! Un défi culturel 100% local. Rejoins la communauté !">
    <meta property="og:image"       content="https://imots.alwaysdata.net/assets/og-preview.jpg">
    <meta property="og:image:width"  content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt"   content="iMots CI — Jeu de mots ivoirien">
    <meta property="og:locale"      content="fr_CI">
    <meta property="og:site_name" content="iMots CI">

    <!-- ===== Twitter / X Card ===== -->
    <meta name="twitter:card"        content="summary_large_image">
    <meta name="twitter:title" content="iMots CI 🇨🇮 – Les mots d'ici, un défi chaque jour">
    <meta name="twitter:description" content="Un nouveau mot ivoirien à deviner chaque jour !">
    <meta name="twitter:image"       content="https://imots.alwaysdata.net/assets/og-preview.jpg">
    <meta name="twitter:image:alt"   content="iMots CI — Jeu de mots ivoirien">

    

    <!-- ===== PWA ===== -->
    <link rel="manifest" href="manifest.json">
    <meta name="theme-color"                   content="#1B7A3E">
    <meta name="apple-mobile-web-app-capable"  content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title"    content="iMots CI">
    <meta name="mobile-web-app-capable"        content="yes">
    <meta name="application-name"              content="iMots CI">

    <!-- ===== Styles ===== -->
    <link rel="stylesheet" href="style/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" crossorigin="anonymous">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
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

            <!-- Bloc instruction + tableau température -->
            <div class="instruction">
                <h3>Jour n°<?= $jourNum + 1 ?> · Mot <?= creneau_actuel() + 1 ?>/4 (<?= creneau_libelle(creneau_actuel()) ?>)</h3>

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
                <img class="logo-img" src="assets/logo.png" alt="iMots CI - Les mots d'ici, un défi chaque jour" width="260" height="149">
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
            <div style="padding:16px 24px; display:flex; flex-direction:column; gap:12px;">
                <button onclick="partagerScore()" id="shareButton" class="btn-principal" style="width:100%; padding: 15px; font-size: 15px; border-radius: 10px; font-weight: 700; background: #22c55e; color: #fff; box-shadow: 0 4px 10px rgba(34, 197, 94, 0.3); text-transform: uppercase;">
                    PARTAGER MON SCORE
                </button>
                <button id="defBtn" onclick="ouvrirDefinition()" class="btn-secondaire" style="display:none; width:100%; padding: 15px; font-size: 15px; border-radius: 10px; border: 2px solid #22c55e; color: #22c55e; font-weight: 700;">
                    Voir la définition du mot
                </button>
                <button id="notifBtn" onclick="toggleNotification()" class="btn-principal" style="display:none; width:100%; padding: 15px; font-size: 15px; border-radius: 10px; background: #FACC15; color: #1a1008; font-weight: 700; box-shadow: 0 4px 10px rgba(250, 204, 21, 0.3);">
                    Activer les rappels
                </button>
                <button id="donateOpenBtn" onclick="ouvrirDonate()" class="btn-principal" style="width:100%; padding: 15px; font-size: 15px; border-radius: 10px; background: linear-gradient(135deg, #FF9900, #F77F00); color: #1a1008; font-weight: 700; box-shadow: 0 4px 10px rgba(247, 127, 0, 0.3); text-transform: uppercase;">
                    SOUTENIR LE JEU
                </button>
            </div>
        </div>


            <!-- DÉFINITION PERMANENTE (reste affichée jusqu'au prochain mot) -->
            <div class="def-inline" id="defInline" hidden>
                <div class="def-inline-top">
                    <span class="def-inline-badge" id="defInlinePartenaire" hidden>⭐ Partenaire officiel</span>
                    <span class="def-inline-label">📚 Mot trouvé</span>
                </div>
                <h3 id="defInlineMot" class="def-inline-mot"></h3>
                <div id="defInlineTexte" class="def-inline-texte"></div>
                <p class="def-inline-note">Un nouveau mot arrive au prochain créneau 🇨🇮</p>
            </div>
    </main>

    

            <!-- MODAL RÈGLES -->
            <div class="modal" id="rulesModal">
                <div class="modal-content">
                    <span class="close" data-close="rulesModal">&times;</span>
                    <h3>🇨🇮 Règles du jeu</h3>
                    <p>
                        Devine le <strong>mot ivoirien</strong> en cours.<br>
                        Plus ton mot est proche, plus la température monte 🔥
                    </p>
                    <p>On compare chaque lettre à la <strong>même position</strong> que dans le mot à trouver.</p>
                    <p><strong>Exemple :</strong></p>
                    <ul>
                        <li>Mot à trouver : <strong>ABOBO</strong></li>
                        <li>Mot proposé : <strong>ABIDJAN</strong></li>
                        <li>Comparaison : A ✅, B ✅, I ❌, D ❌, J ❌</li>
                        <li>Lettres correctes : 2 sur 5 = 40%</li>
                    </ul>
                    <p>
                        🔹 Les lettres <span style="color:#22c55e;font-weight:bold">vertes</span> sont à la bonne position.<br>
                        🔹 Les mots les plus hauts sont les plus proches.<br>
                        🔹 Il y a <strong>4 mots par jour</strong> : un nouveau mot toutes les 6 heures.<br>
                        &nbsp;&nbsp;&nbsp;&nbsp;🕛 00h - 06h &nbsp;|&nbsp; 🌅 06h - 12h &nbsp;|&nbsp; ☀️ 12h - 18h &nbsp;|&nbsp; 🌙 18h - 00h<br>
                        🔹 Chaque mot trouvé te rapporte une victoire : plus tu joues et plus vite tu trouves, plus tu montes au classement !
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
                    <p class="def-footer">iMots CI &#127464;&#127470;</p>
                </div>
            </div>

    <!-- MODAL SOUTENIR (Dons) -->
    <!-- MODAL SOUTENIR (Dons) -->
    <div class="modal" id="donateModal">
        <div class="modal-content def-modal-content" style="max-width: 440px;">
            <span class="close" data-close="donateModal">&times;</span>
            <div class="def-header">
                <span class="def-icon" style="font-size:38px;">☕</span>
                <h3 style="font-size:20px;">Soutenir iMots CI</h3>
            </div>
            <div class="def-corps" style="text-align: center; padding: 15px;">
                <p style="font-size: 14px; margin-bottom: 16px; color: var(--texte); opacity: 0.9;">
                    Le jeu est 100% gratuit et sans publicité intrusive. Offrez-nous un garba pour soutenir les serveurs ! 🇨🇮
                </p>
                
                <div id="donateStep1">
                    <p style="font-size:13px; text-align:left; color:var(--orange); margin-bottom:8px; font-weight:bold;">Montant de ton soutien (FCFA) :</p>
                    <input type="number" id="customAmount" placeholder="Saisis le montant (ex: 1000)..." value="" min="100" style="width:100%; padding:12px; border-radius:8px; border:2px solid rgba(247,127,0,0.5); background:rgba(253,248,240,0.05); color:var(--texte); font-size:18px; font-weight:bold; margin-bottom:18px; text-align:center; outline:none;">
                    
                    <button id="btnProceedDonate" class="btn-principal" style="width:100%; padding:14px; font-size:15px; font-weight:800; background:linear-gradient(135deg, #FF9900, #F77F00);">
                        Continuer vers le paiement <i class="fa-solid fa-arrow-right" style="margin-left:6px;"></i>
                    </button>
                </div>

                <div id="donateStep2" style="display:none; padding: 10px 0;">
                    <p style="font-size: 14px; margin-bottom: 14px;">Montant sélectionné : <strong id="donateAmountStr" style="color:var(--orange); font-size:18px;">1 000</strong> <span style="color:var(--orange); font-weight:700;">FCFA</span></p>
                    
                    <button id="payJeko" class="btn-principal" style="background: linear-gradient(135deg, #10B981, #059669); color: white; padding:14px; margin-bottom: 12px; display: flex; align-items: center; justify-content: center; gap: 10px; width: 100%; font-size:15px; font-weight:800; box-shadow: 0 4px 14px rgba(16, 185, 129, 0.35);">
                        <i class="fa-solid fa-lock"></i> Payer en toute sécurité (Jeko)
                    </button>

                    <div style="display:flex; flex-wrap:wrap; justify-content:center; gap:6px; margin-top:8px;">
                        <span style="background:rgba(20,185,252,0.15); color:#14B9FC; border:1px solid rgba(20,185,252,0.3); padding:4px 8px; border-radius:6px; font-size:11px; font-weight:700;">🌊 Wave</span>
                        <span style="background:rgba(255,102,0,0.15); color:#ff8533; border:1px solid rgba(255,102,0,0.3); padding:4px 8px; border-radius:6px; font-size:11px; font-weight:700;">🍊 Orange</span>
                        <span style="background:rgba(234,179,8,0.15); color:#facc15; border:1px solid rgba(234,179,8,0.3); padding:4px 8px; border-radius:6px; font-size:11px; font-weight:700;">🟡 MTN</span>
                        <span style="background:rgba(59,130,246,0.15); color:#60a5fa; border:1px solid rgba(59,130,246,0.3); padding:4px 8px; border-radius:6px; font-size:11px; font-weight:700;">🔵 Moov</span>
                        <span style="background:rgba(255,255,255,0.1); color:#fff; border:1px solid rgba(255,255,255,0.2); padding:4px 8px; border-radius:6px; font-size:11px; font-weight:700;">💳 Carte Visa/MC</span>
                    </div>

                    <button type="button" id="btnBackToStep1" style="margin-top:16px; background:none; border:none; color:var(--gris); font-size:12px; cursor:pointer; text-decoration:underline;">
                        ← Modifier le montant
                    </button>
                </div>

                <div id="donateLoading" style="display:none; padding: 20px;">
                    <i class="fa-solid fa-spinner fa-spin" style="font-size: 34px; color: var(--orange); margin-bottom: 12px;"></i>
                    <p style="font-size:14px;">Ouverture du portail sécurisé Jeko...</p>
                </div>

                <div id="donateSuccess" style="display:none; padding: 20px;">
                    <span class="def-icon" style="font-size: 46px; margin-bottom: 10px;">🎉</span>
                    <h4 style="color: var(--vert); margin-bottom: 10px; font-size:18px;">Merci beaucoup !</h4>
                    <p style="font-size: 14px;">Ton soutien fait grandir le jeu et motive toute l'équipe.</p>
                </div>
            </div>
        </div>
    </div>

    <footer style="text-align:center;padding:20px 16px 32px;">
        <nav aria-label="Liens légaux" style="display:flex;justify-content:center;gap:20px;flex-wrap:wrap;">
            <a href="legal/confidentialite.php" style="color:rgba(232,224,212,0.35);font-size:0.78rem;text-decoration:none;">🔒 Confidentialité</a>
            <a href="legal/conditions.php"      style="color:rgba(232,224,212,0.35);font-size:0.78rem;text-decoration:none;">📜 CGU</a>
            <a href="legal/cookies.php"         style="color:rgba(232,224,212,0.35);font-size:0.78rem;text-decoration:none;">🍪 Cookies</a>
        </nav>
        <p style="color:rgba(232,224,212,0.2);font-size:0.72rem;margin-top:8px;">© <?= date('Y') ?> iMots CI 🇨🇮 — Fait avec ❤️ en Côte d'Ivoire</p>
    </footer>

    <script>
        window.username   = <?= json_encode($username) ?>;
        window.csrfToken  = <?= json_encode($csrfToken) ?>;
        window.vapidKey   = 'BHpcuD9GQ0Q6PoQHujWBr3l-vKzQPr4YhBYY5HqJHK5Z6iFy23f-q8kmN22PKI3F8n3UYcHHpf2leBjP5GDe3-w';
        
        // Lien de paiement (Jeko / Mobile Money / Wave)
        window.paymentLink     = <?= json_encode($payment_link) ?>;
        window.wavePaymentLink = <?= json_encode($payment_link) ?>; // alias
        
        // BASE_PATH calculé dynamiquement : '/' en prod Railway, chemin local en XAMPP
        window.BASE_PATH  = <?= json_encode(rtrim(dirname($_SERVER['SCRIPT_NAME']), '/')) ?>;
        window.CRENEAU           = <?= creneau_actuel() ?>;
        window.PROCHAIN_MOT_DANS = <?= creneau_secondes_restantes() ?>;
    </script>
    <script src="js/main.js"></script>

    <!-- BANNIERE PWA -->
    <div id="pwaInstallBanner" style="display:none; position:fixed; bottom:20px; left:50%; transform:translateX(-50%); width:90%; max-width:400px; background:var(--sombre2); border: 2px solid var(--vert); border-radius:12px; padding:15px; box-shadow:0 10px 30px rgba(0,0,0,0.5); z-index:9999; text-align:center;">
        <button onclick="fermerPwaBanner()" style="position:absolute; top:5px; right:10px; background:none; border:none; color:var(--texte); font-size:24px; cursor:pointer; line-height:1;">&times;</button>
        <div style="display:flex; align-items:center; gap:10px; text-align:left;">
            <img src="assets/icons/icon-76.png" style="width:50px; height:50px; border-radius:10px; box-shadow: 0 4px 10px rgba(0,0,0,0.3);">
            <div>
                <h3 style="margin:0; font-size:16px; color:var(--orange);">Installer iMots CI</h3>
                <p id="pwaInstallText" style="margin:5px 0 0 0; font-size:13px; color:var(--blanc); line-height:1.4;">Joue plus facilement, installe le jeu directement sur ton écran d'accueil !</p>
            </div>
        </div>
        <button id="pwaInstallBtn" class="btn-principal" style="width:100%; margin-top:15px; padding:10px; font-size:14px; display:none; background:var(--vert); box-shadow:none;">Installer l'application</button>
    </div>
</body>
</html>
