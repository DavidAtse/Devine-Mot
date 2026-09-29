<?php session_start(); ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, follow">
    <title>Politique de Cookies — DevineMot CI</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            background-color: #1a1a1a;
            color: #e0e0e0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 16px;
            line-height: 1.7;
            min-height: 100vh;
        }

        /* ─── En-tête ─── */
        header {
            background-color: #111111;
            border-bottom: 3px solid #F77F00;
            padding: 18px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
        }
        .site-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #F77F00;
            text-decoration: none;
            letter-spacing: 1px;
        }
        .site-title span { color: #ffffff; }

        nav.legal-nav {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }
        nav.legal-nav a {
            color: #cccccc;
            text-decoration: none;
            font-size: 0.85rem;
            padding: 5px 12px;
            border: 1px solid #444;
            border-radius: 20px;
            transition: all 0.2s;
        }
        nav.legal-nav a:hover,
        nav.legal-nav a.active {
            color: #F77F00;
            border-color: #F77F00;
            background-color: rgba(247, 127, 0, 0.08);
        }
        nav.legal-nav a.active { font-weight: 600; }

        /* ─── Contenu ─── */
        main {
            max-width: 860px;
            margin: 0 auto;
            padding: 48px 24px 80px;
        }

        .page-badge {
            display: inline-block;
            background-color: rgba(247, 127, 0, 0.15);
            color: #F77F00;
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            padding: 4px 12px;
            border-radius: 4px;
            margin-bottom: 16px;
        }

        h1 {
            font-size: 2rem;
            color: #ffffff;
            margin-bottom: 8px;
            line-height: 1.2;
        }

        .meta {
            font-size: 0.85rem;
            color: #888;
            margin-bottom: 40px;
            padding-bottom: 24px;
            border-bottom: 1px solid #2e2e2e;
        }
        .meta strong { color: #aaa; }

        section { margin-bottom: 40px; }

        h2 {
            font-size: 1.2rem;
            color: #F77F00;
            margin-bottom: 14px;
            padding-left: 12px;
            border-left: 3px solid #F77F00;
        }

        p { margin-bottom: 14px; }
        p:last-child { margin-bottom: 0; }

        ul, ol { padding-left: 22px; margin-bottom: 14px; }
        li { margin-bottom: 6px; }

        code {
            background-color: #2a2a2a;
            color: #F77F00;
            padding: 2px 6px;
            border-radius: 4px;
            font-family: 'Courier New', Courier, monospace;
            font-size: 0.88em;
        }

        /* ─── Bannière de statut ─── */
        .status-banner {
            background: linear-gradient(135deg, #1a2e1a 0%, #1e3a1e 100%);
            border: 1px solid #3a7a3a;
            border-radius: 10px;
            padding: 24px 28px;
            margin-bottom: 40px;
            display: flex;
            align-items: center;
            gap: 20px;
        }
        .status-banner .emoji { font-size: 3rem; flex-shrink: 0; }
        .status-banner h2 {
            font-size: 1.1rem;
            color: #7ecf7e;
            border: none;
            padding: 0;
            margin-bottom: 6px;
        }
        .status-banner p { font-size: 0.9rem; color: #b8d8b0; margin: 0; }

        /* ─── Tableau des cookies ─── */
        .cookie-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            font-size: 0.9rem;
        }
        .cookie-table th {
            background-color: #2a2a2a;
            color: #F77F00;
            text-align: left;
            padding: 10px 14px;
            border-bottom: 2px solid #F77F00;
        }
        .cookie-table td {
            padding: 12px 14px;
            border-bottom: 1px solid #2e2e2e;
            vertical-align: top;
        }
        .cookie-table tr:last-child td { border-bottom: none; }
        .cookie-table tr:hover td { background-color: #1f1f1f; }

        .tag {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 0.75rem;
            font-weight: 700;
        }
        .tag-necessary {
            background-color: rgba(92, 184, 92, 0.2);
            color: #7ecf7e;
            border: 1px solid #3a5a2a;
        }
        .tag-session {
            background-color: rgba(100, 149, 237, 0.2);
            color: #90b8f0;
            border: 1px solid #2a3a5a;
        }

        /* ─── Encadrés ─── */
        .info-box {
            background-color: #1f2a1a;
            border: 1px solid #3a5a2a;
            border-left: 4px solid #5cb85c;
            border-radius: 6px;
            padding: 16px 20px;
            margin: 20px 0;
            font-size: 0.92rem;
            color: #b8d8b0;
        }
        .info-box strong { color: #7ecf7e; }

        .warn-box {
            background-color: #2a1f1a;
            border: 1px solid #5a3a2a;
            border-left: 4px solid #F77F00;
            border-radius: 6px;
            padding: 16px 20px;
            margin: 20px 0;
            font-size: 0.92rem;
            color: #d8c0a0;
        }
        .warn-box strong { color: #F77F00; }

        /* ─── Grille "Pas de..." ─── */
        .no-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 12px;
            margin: 20px 0;
        }
        .no-card {
            background-color: #1e1e1e;
            border: 1px solid #2e2e2e;
            border-radius: 8px;
            padding: 14px 16px;
            text-align: center;
        }
        .no-card .no-icon { font-size: 1.8rem; margin-bottom: 6px; }
        .no-card p { font-size: 0.82rem; color: #999; margin: 0; }
        .no-card .no-label { font-size: 0.88rem; color: #e07c7c; font-weight: 600; margin-bottom: 4px; }

        /* ─── Navigateurs ─── */
        .browser-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 12px;
            margin: 20px 0;
        }
        .browser-card {
            background-color: #222;
            border: 1px solid #333;
            border-radius: 8px;
            padding: 14px 16px;
        }
        .browser-card h3 { color: #F77F00; font-size: 0.92rem; margin-bottom: 6px; }
        .browser-card a { color: #cccccc; text-decoration: none; font-size: 0.82rem; }
        .browser-card a:hover { color: #F77F00; text-decoration: underline; }

        /* ─── Contact ─── */
        .contact-card {
            background-color: #222;
            border: 1px solid #333;
            border-radius: 8px;
            padding: 20px 24px;
            display: flex;
            align-items: flex-start;
            gap: 16px;
        }
        .contact-card .icon { font-size: 2rem; flex-shrink: 0; }
        .contact-card h3 { color: #ffffff; margin-bottom: 6px; font-size: 1rem; }
        .contact-card a { color: #F77F00; text-decoration: none; }
        .contact-card a:hover { text-decoration: underline; }

        /* ─── Pied de page ─── */
        footer {
            background-color: #111;
            border-top: 1px solid #2e2e2e;
            text-align: center;
            padding: 24px;
            font-size: 0.82rem;
            color: #555;
        }
        footer a { color: #F77F00; text-decoration: none; }
        footer a:hover { text-decoration: underline; }

        @media (max-width: 600px) {
            h1 { font-size: 1.5rem; }
            header { flex-direction: column; align-items: flex-start; }
            .status-banner { flex-direction: column; gap: 12px; }
            .cookie-table { font-size: 0.8rem; }
            .cookie-table th, .cookie-table td { padding: 8px 10px; }
        }
    </style>
</head>
<body>

<header>
    <a href="../index.php" class="site-title">Devine<span>Mot</span> CI</a>
    <nav class="legal-nav" aria-label="Pages légales">
        <a href="../index.php">🏠 Accueil</a>
        <a href="confidentialite.php">🔒 Confidentialité</a>
        <a href="conditions.php">📜 CGU</a>
        <a href="cookies.php" class="active">🍪 Cookies</a>
    </nav>
</header>

<main>
    <div class="page-badge">Légal</div>
    <h1>Politique de Cookies</h1>
    <p class="meta">
        <strong>Version :</strong> 1.0 &nbsp;|&nbsp;
        <strong>Date d'entrée en vigueur :</strong> 29 septembre 2026 &nbsp;|&nbsp;
        <strong>Site :</strong> DevineMot CI
    </p>

    <!-- ─── Bannière verte ─── -->
    <div class="status-banner" role="note" aria-label="Statut de confidentialité cookies">
        <div class="emoji">✅</div>
        <div>
            <h2>Aucune bannière de consentement requise</h2>
            <p>
                DevineMot CI n'utilise que des cookies <strong>strictement nécessaires</strong> au
                fonctionnement du site (sessions PHP, sécurité CSRF). Conformément à la
                Loi ivoirienne n°2013-450 et aux recommandations de l'ARTCI, ces cookies ne
                requièrent pas votre consentement préalable explicite, car ils sont indispensables
                au service que vous avez explicitement demandé.
            </p>
        </div>
    </div>

    <!-- ─── 1. Qu'est-ce qu'un cookie ─── -->
    <section>
        <h2>1. Qu'est-ce qu'un cookie ?</h2>
        <p>
            Un <strong>cookie</strong> est un petit fichier texte qu'un site web dépose sur votre
            ordinateur, smartphone ou tablette lorsque vous le visitez. Il est stocké dans votre
            navigateur et permet au site de vous reconnaître lors de vos visites.
        </p>
        <p>
            Il existe plusieurs catégories de cookies : les cookies strictement nécessaires, les
            cookies de performance (analytics), les cookies de fonctionnalité et les cookies
            publicitaires. <strong>DevineMot CI n'utilise que la première catégorie.</strong>
        </p>
    </section>

    <!-- ─── 2. Ce que nous n'utilisons PAS ─── -->
    <section>
        <h2>2. Ce que nous n'utilisons pas</h2>
        <div class="no-grid">
            <div class="no-card">
                <div class="no-icon">📊</div>
                <div class="no-label">❌ Google Analytics</div>
                <p>Aucun outil de statistiques de trafic</p>
            </div>
            <div class="no-card">
                <div class="no-icon">📣</div>
                <div class="no-label">❌ Publicité ciblée</div>
                <p>Aucun cookie publicitaire ou réseau de pub</p>
            </div>
            <div class="no-card">
                <div class="no-icon">👤</div>
                <div class="no-label">❌ Réseaux sociaux</div>
                <p>Aucun pixel Facebook, Twitter ou autre</p>
            </div>
            <div class="no-card">
                <div class="no-icon">🔍</div>
                <div class="no-label">❌ Suivi inter-sites</div>
                <p>Aucun tracking de votre navigation hors du jeu</p>
            </div>
            <div class="no-card">
                <div class="no-icon">🤝</div>
                <div class="no-label">❌ Cookies tiers</div>
                <p>Aucun partenaire externe ne dépose de cookies</p>
            </div>
            <div class="no-card">
                <div class="no-icon">💳</div>
                <div class="no-label">❌ Paiement</div>
                <p>Aucun cookie de prestataire de paiement</p>
            </div>
        </div>
    </section>

    <!-- ─── 3. Cookies utilisés ─── -->
    <section>
        <h2>3. Cookies utilisés par DevineMot CI</h2>
        <p>
            DevineMot CI utilise exclusivement des cookies générés par <strong>PHP</strong>,
            de nature technique et de sécurité. Aucun cookie n'est partagé avec des tiers.
        </p>

        <table class="cookie-table" aria-label="Liste des cookies utilisés">
            <thead>
                <tr>
                    <th>Nom du cookie</th>
                    <th>Type</th>
                    <th>Durée</th>
                    <th>Finalité</th>
                    <th>Tiers ?</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><code>PHPSESSID</code></td>
                    <td>
                        <span class="tag tag-necessary">Nécessaire</span><br>
                        <span class="tag tag-session" style="margin-top:4px;">Session</span>
                    </td>
                    <td>Durée de la session (fermeture du navigateur)</td>
                    <td>
                        Identifiant de session PHP. Maintient votre connexion active
                        entre les pages du jeu. Stocke votre état d'authentification
                        et vos données de jeu en cours.
                    </td>
                    <td style="color:#7ecf7e; font-weight:600;">Non</td>
                </tr>
                <tr>
                    <td><code>csrf_token</code><br><small style="color:#888;">(stocké en session)</small></td>
                    <td>
                        <span class="tag tag-necessary">Nécessaire</span><br>
                        <span class="tag tag-session" style="margin-top:4px;">Sécurité</span>
                    </td>
                    <td>Durée de la session</td>
                    <td>
                        Jeton de protection contre les attaques CSRF
                        (<em>Cross-Site Request Forgery</em>). Inclus dans les
                        formulaires pour vérifier l'authenticité des requêtes. Indispensable
                        à la sécurité du service.
                    </td>
                    <td style="color:#7ecf7e; font-weight:600;">Non</td>
                </tr>
            </tbody>
        </table>

        <div class="info-box">
            <strong>ℹ️ Cookies de session uniquement :</strong> Le cookie <code>PHPSESSID</code>
            est un cookie de session : il est automatiquement supprimé lorsque vous fermez votre
            navigateur. Il ne contient aucune information personnelle identifiable — uniquement
            un identifiant aléatoire qui pointe vers des données stockées côté serveur.
        </div>
    </section>

    <!-- ─── 4. Cadre légal ─── -->
    <section>
        <h2>4. Cadre légal en Côte d'Ivoire</h2>
        <p>
            La gestion des cookies en Côte d'Ivoire est encadrée par la
            <strong>Loi n°2013-450 du 19 juin 2013 relative à la protection des données à
            caractère personnel</strong> et les recommandations de l'<strong>ARTCI</strong>.
        </p>
        <p>
            Conformément à ces textes, les cookies <strong>strictement nécessaires</strong> au
            fonctionnement d'un service explicitement demandé par l'utilisateur sont exemptés
            de l'obligation de recueil du consentement préalable. C'est le cas des cookies de
            session et de sécurité utilisés par DevineMot CI.
        </p>
        <div class="warn-box">
            <strong>📌 Engagement de transparence :</strong> Bien que la loi n'exige pas de
            bannière de consentement pour ces cookies, nous choisissons de vous informer
            clairement et de manière proactive de l'existence et de la finalité de ces cookies,
            par souci de transparence envers nos utilisateurs.
        </div>
    </section>

    <!-- ─── 5. Gérer les cookies ─── -->
    <section>
        <h2>5. Comment gérer ou supprimer les cookies</h2>
        <p>
            Vous pouvez contrôler et supprimer les cookies depuis les paramètres de votre navigateur.
            Notez cependant que la suppression du cookie de session <code>PHPSESSID</code>
            vous déconnectera automatiquement du jeu.
        </p>

        <div class="browser-grid">
            <div class="browser-card">
                <h3>🦊 Firefox</h3>
                <a href="https://support.mozilla.org/fr/kb/protection-renforcee-contre-pistage-firefox" target="_blank" rel="noopener noreferrer">
                    Gérer les cookies Firefox →
                </a>
            </div>
            <div class="browser-card">
                <h3>🔵 Chrome</h3>
                <a href="https://support.google.com/chrome/answer/95647?hl=fr" target="_blank" rel="noopener noreferrer">
                    Gérer les cookies Chrome →
                </a>
            </div>
            <div class="browser-card">
                <h3>🧭 Safari</h3>
                <a href="https://support.apple.com/fr-fr/guide/safari/sfri11471/mac" target="_blank" rel="noopener noreferrer">
                    Gérer les cookies Safari →
                </a>
            </div>
            <div class="browser-card">
                <h3>🌐 Edge</h3>
                <a href="https://support.microsoft.com/fr-fr/microsoft-edge/supprimer-les-cookies-dans-microsoft-edge-63947406-40ac-c3b8-57b9-2a946a29ae09" target="_blank" rel="noopener noreferrer">
                    Gérer les cookies Edge →
                </a>
            </div>
            <div class="browser-card">
                <h3>🟠 Opera</h3>
                <a href="https://help.opera.com/en/latest/web-preferences/#cookies" target="_blank" rel="noopener noreferrer">
                    Gérer les cookies Opera →
                </a>
            </div>
        </div>

        <div class="info-box">
            <strong>⚠️ Conséquence du refus de cookies :</strong> Si vous désactivez complètement
            les cookies dans votre navigateur, le jeu DevineMot CI ne pourra pas maintenir votre
            connexion entre les pages. Vous serez déconnecté à chaque navigation, et certaines
            fonctionnalités ne seront pas disponibles.
        </div>
    </section>

    <!-- ─── 6. Stockage local ─── -->
    <section>
        <h2>6. Stockage local du navigateur (localStorage / Service Worker)</h2>
        <p>
            En complément des cookies, DevineMot CI peut utiliser les technologies suivantes,
            qui sont distinctes des cookies mais stockent des informations dans votre navigateur :
        </p>
        <ul>
            <li>
                <strong>localStorage / sessionStorage :</strong> Peut être utilisé pour stocker
                des préférences de jeu localement (son, thème visuel). Ces données restent
                sur votre appareil et ne sont pas envoyées à nos serveurs.
            </li>
            <li>
                <strong>Service Worker :</strong> Si vous vous abonnez aux notifications push,
                un Service Worker est enregistré dans votre navigateur pour recevoir les
                notifications, même lorsque le site n'est pas ouvert. Vous pouvez désactiver
                ce Service Worker depuis les paramètres de votre navigateur à tout moment.
            </li>
        </ul>
    </section>

    <!-- ─── 7. Évolutions futures ─── -->
    <section>
        <h2>7. Évolutions futures de cette politique</h2>
        <p>
            Si DevineMot CI venait à intégrer de nouveaux types de cookies (par exemple, des
            outils d'analyse anonymisés ou des fonctionnalités sociales), cette politique de
            cookies serait mise à jour <strong>avant</strong> leur déploiement, et vous en seriez
            informé clairement. Un mécanisme de consentement adapté serait alors mis en place.
        </p>
    </section>

    <!-- ─── 8. Contact ─── -->
    <section>
        <h2>8. Contact</h2>
        <div class="contact-card">
            <div class="icon">✉️</div>
            <div>
                <h3>Une question sur les cookies ?</h3>
                <p>Contactez-nous à l'adresse :</p>
                <p><a href="mailto:daatsey24@gmail.com">daatsey24@gmail.com</a></p>
            </div>
        </div>
    </section>
</main>

<footer>
    <p>
        &copy; <?php echo date('Y'); ?> DevineMot CI &mdash;
        <a href="confidentialite.php">Confidentialité</a> &bull;
        <a href="conditions.php">CGU</a> &bull;
        <a href="cookies.php">Cookies</a> &bull;
        <a href="../index.php">Accueil</a>
    </p>
    <p style="margin-top:8px;">Cookies strictement nécessaires — Aucun consentement requis — Loi n°2013-450 CI</p>
</footer>

</body>
</html>
