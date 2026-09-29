<?php session_start(); ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, follow">
    <title>Conditions Générales d'Utilisation — iMots CI</title>
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

        h3 {
            font-size: 1rem;
            color: #cccccc;
            margin: 18px 0 8px;
        }

        p { margin-bottom: 14px; }
        p:last-child { margin-bottom: 0; }

        ul, ol {
            padding-left: 22px;
            margin-bottom: 14px;
        }
        li { margin-bottom: 6px; }

        /* ─── Badges d'état ─── */
        .badge {
            display: inline-block;
            padding: 2px 10px;
            border-radius: 12px;
            font-size: 0.78rem;
            font-weight: 700;
            margin-left: 6px;
            vertical-align: middle;
        }
        .badge-green {
            background-color: rgba(92, 184, 92, 0.2);
            color: #7ecf7e;
            border: 1px solid #3a5a2a;
        }
        .badge-orange {
            background-color: rgba(247, 127, 0, 0.15);
            color: #F77F00;
            border: 1px solid #5a3a00;
        }
        .badge-red {
            background-color: rgba(220, 53, 69, 0.2);
            color: #e07c7c;
            border: 1px solid #5a2a2a;
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

        .danger-box {
            background-color: #2a1a1a;
            border: 1px solid #5a2a2a;
            border-left: 4px solid #dc3545;
            border-radius: 6px;
            padding: 16px 20px;
            margin: 20px 0;
            font-size: 0.92rem;
            color: #d8b0b0;
        }
        .danger-box strong { color: #e07c7c; }

        /* ─── Règles de jeu ─── */
        .rules-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 16px;
            margin: 20px 0;
        }
        .rule-card {
            background-color: #222;
            border: 1px solid #333;
            border-radius: 8px;
            padding: 16px 18px;
        }
        .rule-card .rule-icon { font-size: 1.6rem; margin-bottom: 8px; }
        .rule-card h3 {
            font-size: 0.95rem;
            color: #F77F00;
            margin: 0 0 6px;
        }
        .rule-card p { font-size: 0.88rem; color: #bbb; margin: 0; }

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

        /* ─── Table des matières ─── */
        .toc {
            background-color: #1e1e1e;
            border: 1px solid #2e2e2e;
            border-radius: 8px;
            padding: 20px 24px;
            margin-bottom: 40px;
        }
        .toc h3 { color: #F77F00; margin-bottom: 12px; font-size: 0.95rem; }
        .toc ol { padding-left: 20px; }
        .toc li { margin-bottom: 4px; }
        .toc a { color: #cccccc; text-decoration: none; font-size: 0.9rem; }
        .toc a:hover { color: #F77F00; }

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
        }
    </style>
</head>
<body>

<header>
    <a href="../index.php" class="site-title">Devine<span>Mot</span> CI</a>
    <nav class="legal-nav" aria-label="Pages légales">
        <a href="../index.php">🏠 Accueil</a>
        <a href="confidentialite.php">🔒 Confidentialité</a>
        <a href="conditions.php" class="active">📜 CGU</a>
        <a href="cookies.php">🍪 Cookies</a>
    </nav>
</header>

<main>
    <div class="page-badge">Légal</div>
    <h1>Conditions Générales d'Utilisation</h1>
    <p class="meta">
        <strong>Version :</strong> 1.0 &nbsp;|&nbsp;
        <strong>Date d'entrée en vigueur :</strong> 29 septembre 2026 &nbsp;|&nbsp;
        <strong>Jeu :</strong> iMots CI
    </p>

    <!-- Table des matières -->
    <nav class="toc" aria-label="Table des matières">
        <h3>📋 Sommaire</h3>
        <ol>
            <li><a href="#presentation">Présentation du jeu</a></li>
            <li><a href="#acces">Accès au jeu et inscription</a></li>
            <li><a href="#gratuite">Gratuité du service</a></li>
            <li><a href="#age">Âge minimum et responsabilité parentale</a></li>
            <li><a href="#contenu">Contenu culturel ivoirien</a></li>
            <li><a href="#regles">Règles de jeu et fair-play</a></li>
            <li><a href="#interdit">Comportements interdits</a></li>
            <li><a href="#compte">Gestion du compte</a></li>
            <li><a href="#donnees">Données personnelles</a></li>
            <li><a href="#propriete">Propriété intellectuelle</a></li>
            <li><a href="#responsabilite">Limitation de responsabilité</a></li>
            <li><a href="#modifications">Modifications des CGU</a></li>
            <li><a href="#droit">Droit applicable et litiges</a></li>
            <li><a href="#contact">Contact</a></li>
        </ol>
    </nav>

    <!-- ─── 1. Présentation ─── -->
    <section id="presentation">
        <h2>1. Présentation du jeu</h2>
        <p>
            <strong>iMots CI</strong> est un jeu de devinettes en ligne inspiré de la culture
            ivoirienne, développé et exploité depuis la Côte d'Ivoire. Le jeu propose aux joueurs
            de deviner des mots liés à la culture, aux langues, aux traditions et à la vie
            quotidienne en Côte d'Ivoire.
        </p>
        <p>
            Les présentes Conditions Générales d'Utilisation (CGU) régissent l'accès et l'utilisation
            du jeu iMots CI, accessible en ligne. En utilisant le jeu, vous acceptez sans réserve
            les présentes CGU.
        </p>
    </section>

    <!-- ─── 2. Accès et inscription ─── -->
    <section id="acces">
        <h2>2. Accès au jeu et inscription</h2>
        <p>
            Pour accéder aux fonctionnalités complètes du jeu (scores, classements, notifications),
            vous devez créer un compte en fournissant :
        </p>
        <ul>
            <li>Un <strong>pseudo</strong> unique (nom affiché publiquement)</li>
            <li>Une <strong>adresse e-mail</strong> valide</li>
            <li>Un <strong>mot de passe</strong> sécurisé (au moins 8 caractères)</li>
        </ul>
        <p>
            Vous êtes responsable de la confidentialité de vos identifiants. Toute utilisation
            effectuée depuis votre compte vous est attribuée. En cas de suspicion de compromission
            de votre compte, contactez-nous immédiatement.
        </p>
        <div class="info-box">
            <strong>✅ Accès partiel sans inscription :</strong> Certaines fonctionnalités du jeu
            peuvent être accessibles sans création de compte. Cependant, les scores et le classement
            nécessitent un compte enregistré.
        </div>
    </section>

    <!-- ─── 3. Gratuité ─── -->
    <section id="gratuite">
        <h2>3. Gratuité du service <span class="badge badge-green">100% Gratuit</span></h2>
        <p>
            iMots CI est un jeu <strong>entièrement gratuit</strong>. Aucun paiement n'est
            requis pour jouer, créer un compte ou accéder à l'ensemble des fonctionnalités
            actuellement disponibles.
        </p>
        <div class="info-box">
            <strong>💳 Aucune transaction financière :</strong> Nous ne collectons aucune
            information bancaire ou de paiement. Il n'existe aucun achat intégré (<em>in-app purchase</em>),
            abonnement payant, monnaie virtuelle payante ou contenu premium dans la version actuelle du jeu.
        </div>
        <p>
            Si des fonctionnalités payantes venaient à être introduites dans le futur, vous en seriez
            informés clairement et à l'avance, et ces CGU seraient mises à jour en conséquence.
        </p>
    </section>

    <!-- ─── 4. Âge minimum ─── -->
    <section id="age">
        <h2>4. Âge minimum et responsabilité parentale <span class="badge badge-orange">13 ans+</span></h2>
        <p>
            L'utilisation de iMots CI est <strong>recommandée aux personnes âgées de 13 ans
            et plus</strong>. Les enfants de moins de 13 ans peuvent jouer sous la supervision
            et avec l'accord de leurs parents ou tuteurs légaux.
        </p>
        <div class="warn-box">
            <strong>👨‍👩‍👧 Responsabilité parentale :</strong> Si votre enfant de moins de 13 ans
            utilise ce jeu, vous êtes responsable de superviser son usage et de vous assurer qu'il
            comprend et respecte les présentes CGU. Vous pouvez demander la suppression du compte
            d'un mineur à tout moment en nous contactant.
        </div>
    </section>

    <!-- ─── 5. Contenu culturel ─── -->
    <section id="contenu">
        <h2>5. Contenu culturel ivoirien 🇨🇮</h2>
        <p>
            iMots CI est un jeu ancré dans la <strong>richesse culturelle ivoirienne</strong>.
            Le contenu du jeu peut inclure des références à :
        </p>
        <ul>
            <li>Les langues et dialectes ivoiriens (Dioula, Baoulé, Bété, etc.)</li>
            <li>La gastronomie, les traditions et coutumes ivoiriennes</li>
            <li>L'histoire et la géographie de la Côte d'Ivoire</li>
            <li>La musique, les arts et la vie quotidienne ivoirienne</li>
        </ul>
        <p>
            Ce contenu est proposé dans un but éducatif, ludique et de valorisation de la culture
            ivoirienne. Tout utilisateur doit aborder ce contenu avec respect et bienveillance.
        </p>
    </section>

    <!-- ─── 6. Règles de jeu ─── -->
    <section id="regles">
        <h2>6. Règles de jeu et fair-play</h2>
        <div class="rules-grid">
            <div class="rule-card">
                <div class="rule-icon">🎮</div>
                <h3>Jouez honnêtement</h3>
                <p>Chaque partie doit être jouée de manière individuelle et honnête.</p>
            </div>
            <div class="rule-card">
                <div class="rule-icon">🏆</div>
                <h3>Classement équitable</h3>
                <p>Les scores affichés doivent refléter des performances réelles.</p>
            </div>
            <div class="rule-card">
                <div class="rule-icon">🤝</div>
                <h3>Respect mutuel</h3>
                <p>Respectez les autres joueurs et la communauté iMots CI.</p>
            </div>
            <div class="rule-card">
                <div class="rule-icon">🎓</div>
                <h3>Esprit éducatif</h3>
                <p>Ce jeu vise à apprendre en s'amusant. Profitez-en positivement.</p>
            </div>
        </div>
    </section>

    <!-- ─── 7. Comportements interdits ─── -->
    <section id="interdit">
        <h2>7. Comportements interdits <span class="badge badge-red">Interdit</span></h2>
        <div class="danger-box">
            <strong>🚫 Tolérance zéro :</strong> Toute violation grave des règles suivantes peut
            entraîner la suspension ou la suppression définitive de votre compte, sans préavis.
        </div>

        <h3>7.1 Triche et manipulation des scores</h3>
        <ul>
            <li>Utiliser des programmes ou scripts automatisés (<em>bots</em>) pour jouer à votre place</li>
            <li>Manipuler les requêtes HTTP pour falsifier des scores ou des réponses</li>
            <li>Exploiter des bugs ou failles du jeu pour obtenir un avantage déloyal</li>
            <li>Partager les réponses avec d'autres joueurs en cours de partie chronométrée</li>
        </ul>

        <h3>7.2 Attaques et tentatives de piratage</h3>
        <ul>
            <li>Tenter d'accéder à des comptes autres que le vôtre</li>
            <li>Effectuer des injections SQL, attaques XSS ou toute autre attaque informatique</li>
            <li>Procéder à des attaques par déni de service (DoS/DDoS)</li>
            <li>Tenter de décompiler, reverse-engineer ou accéder au code source sans autorisation</li>
            <li>Scanner les vulnérabilités du système sans autorisation écrite préalable</li>
        </ul>

        <h3>7.3 Usurpation d'identité et faux comptes</h3>
        <ul>
            <li>Créer plusieurs comptes pour contourner une sanction</li>
            <li>Usurper l'identité d'un autre joueur ou d'un membre de l'équipe</li>
            <li>Utiliser un pseudo offensant, discriminatoire ou contraire aux bonnes mœurs</li>
        </ul>

        <h3>7.4 Contenu inapproprié</h3>
        <ul>
            <li>Tout pseudo ou contenu à caractère raciste, sexiste, haineux ou discriminatoire</li>
            <li>Tout contenu portant atteinte à la dignité humaine ou à la culture ivoirienne</li>
        </ul>

        <p>
            Toute tentative d'attaque sur nos systèmes pourra faire l'objet d'un signalement
            aux autorités compétentes en Côte d'Ivoire, conformément à la législation en vigueur
            (Loi n°2013-451 relative à la cybercriminalité).
        </p>
    </section>

    <!-- ─── 8. Gestion du compte ─── -->
    <section id="compte">
        <h2>8. Gestion du compte</h2>
        <p>
            Vous pouvez <strong>supprimer votre compte</strong> à tout moment depuis les paramètres
            du jeu ou en nous contactant. La suppression entraîne l'effacement de vos données
            personnelles dans un délai raisonnable, à l'exception des données que nous sommes
            légalement tenus de conserver.
        </p>
        <p>
            Nous nous réservons le droit de <strong>suspendre ou supprimer tout compte</strong>
            en cas de violation des présentes CGU, sans obligation de remboursement (le jeu étant
            entièrement gratuit).
        </p>
    </section>

    <!-- ─── 9. Données personnelles ─── -->
    <section id="donnees">
        <h2>9. Données personnelles</h2>
        <p>
            La collecte et le traitement de vos données personnelles sont régis par notre
            <a href="confidentialite.php" style="color:#F77F00;">Politique de Confidentialité</a>,
            conforme à la Loi ivoirienne n°2013-450 du 19 juin 2013.
        </p>
    </section>

    <!-- ─── 10. Propriété intellectuelle ─── -->
    <section id="propriete">
        <h2>10. Propriété intellectuelle</h2>
        <p>
            L'ensemble des éléments composant iMots CI (code source, design, textes, bases
            de données de mots, logo) sont protégés par le droit de la propriété intellectuelle
            applicable en Côte d'Ivoire.
        </p>
        <p>
            Toute reproduction, représentation, modification, publication ou adaptation de tout
            ou partie des éléments du jeu, quel que soit le moyen ou le procédé utilisé, est
            interdite sans autorisation écrite préalable de l'équipe iMots CI.
        </p>
    </section>

    <!-- ─── 11. Responsabilité ─── -->
    <section id="responsabilite">
        <h2>11. Limitation de responsabilité</h2>
        <p>
            iMots CI est fourni <strong>"tel quel"</strong>, sans garantie d'aucune sorte.
            Nous ne pouvons être tenus responsables de :
        </p>
        <ul>
            <li>Toute interruption de service pour maintenance ou incident technique</li>
            <li>La perte de données liée à un dysfonctionnement technique imprévu</li>
            <li>Tout dommage indirect résultant de l'utilisation du jeu</li>
            <li>Le contenu de sites tiers vers lesquels des liens pourraient pointer</li>
        </ul>
        <p>
            Nous nous efforçons de maintenir le jeu disponible en permanence, mais nous ne pouvons
            garantir une disponibilité ininterrompue.
        </p>
    </section>

    <!-- ─── 12. Modifications CGU ─── -->
    <section id="modifications">
        <h2>12. Modifications des CGU</h2>
        <p>
            Nous nous réservons le droit de modifier les présentes CGU à tout moment. Les modifications
            entrent en vigueur dès leur publication sur cette page. En cas de modification substantielle,
            vous serez informé via une notification dans le jeu ou par e-mail.
        </p>
        <p>
            La poursuite de l'utilisation du jeu après notification des modifications vaut acceptation
            des nouvelles CGU.
        </p>
    </section>

    <!-- ─── 13. Droit applicable ─── -->
    <section id="droit">
        <h2>13. Droit applicable et règlement des litiges</h2>
        <p>
            Les présentes CGU sont régies par le <strong>droit ivoirien</strong>. En cas de litige
            relatif à l'interprétation ou à l'exécution des présentes, les parties s'engagent à
            chercher une solution amiable avant tout recours judiciaire.
        </p>
        <p>
            À défaut d'accord amiable, tout litige sera soumis à la compétence exclusive des
            <strong>juridictions ivoiriennes compétentes</strong>.
        </p>
    </section>

    <!-- ─── 14. Contact ─── -->
    <section id="contact">
        <h2>14. Contact</h2>
        <div class="contact-card">
            <div class="icon">✉️</div>
            <div>
                <h3>Une question sur ces CGU ?</h3>
                <p>Contactez-nous à l'adresse :</p>
                <p><a href="mailto:daatsey24@gmail.com">daatsey24@gmail.com</a></p>
                <p style="font-size:0.85rem;color:#888;margin-top:8px;">
                    Nous nous efforçons de répondre dans un délai de 7 jours ouvrés.
                </p>
            </div>
        </div>
    </section>
</main>

<footer>
    <p>
        &copy; <?php echo date('Y'); ?> iMots CI &mdash;
        <a href="confidentialite.php">Confidentialité</a> &bull;
        <a href="conditions.php">CGU</a> &bull;
        <a href="cookies.php">Cookies</a> &bull;
        <a href="../index.php">Accueil</a>
    </p>
    <p style="margin-top:8px;">Droit applicable : Loi ivoirienne &mdash; Juridictions de Côte d'Ivoire compétentes</p>
</footer>

</body>
</html>
