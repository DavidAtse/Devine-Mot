<?php session_start(); ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, follow">
    <title>Politique de Confidentialité — iMots CI</title>
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

        /* ─── Navigation légale ─── */
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

        /* ─── Contenu principal ─── */
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

        /* ─── Sections ─── */
        section {
            margin-bottom: 40px;
        }

        h2 {
            font-size: 1.2rem;
            color: #F77F00;
            margin-bottom: 14px;
            padding-left: 12px;
            border-left: 3px solid #F77F00;
        }

        p { margin-bottom: 14px; }
        p:last-child { margin-bottom: 0; }

        ul, ol {
            padding-left: 22px;
            margin-bottom: 14px;
        }
        li { margin-bottom: 6px; }

        /* ─── Tableaux ─── */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin: 16px 0;
            font-size: 0.92rem;
        }
        .data-table th {
            background-color: #2a2a2a;
            color: #F77F00;
            text-align: left;
            padding: 10px 14px;
            border-bottom: 2px solid #F77F00;
        }
        .data-table td {
            padding: 10px 14px;
            border-bottom: 1px solid #2e2e2e;
            vertical-align: top;
        }
        .data-table tr:last-child td { border-bottom: none; }
        .data-table tr:hover td { background-color: #1f1f1f; }

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
        .contact-card .icon {
            font-size: 2rem;
            line-height: 1;
            flex-shrink: 0;
        }
        .contact-card h3 {
            color: #ffffff;
            margin-bottom: 6px;
            font-size: 1rem;
        }
        .contact-card a {
            color: #F77F00;
            text-decoration: none;
        }
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

        /* ─── Responsive ─── */
        @media (max-width: 600px) {
            h1 { font-size: 1.5rem; }
            .data-table { font-size: 0.8rem; }
            .data-table th, .data-table td { padding: 8px 10px; }
            header { flex-direction: column; align-items: flex-start; }
        }
    </style>
</head>
<body>

<header>
    <a href="../index.php" class="site-title">Devine<span>Mot</span> CI</a>
    <nav class="legal-nav" aria-label="Pages légales">
        <a href="../index.php">🏠 Accueil</a>
        <a href="confidentialite.php" class="active">🔒 Confidentialité</a>
        <a href="conditions.php">📜 CGU</a>
        <a href="cookies.php">🍪 Cookies</a>
    </nav>
</header>

<main>
    <div class="page-badge">Légal</div>
    <h1>Politique de Confidentialité</h1>
    <p class="meta">
        <strong>Version :</strong> 1.0 &nbsp;|&nbsp;
        <strong>Date d'entrée en vigueur :</strong> 29 septembre 2026 &nbsp;|&nbsp;
        <strong>Applicable à :</strong> iMots CI (jeu en ligne)
    </p>

    <!-- ─── 1. Introduction ─── -->
    <section>
        <h2>1. Introduction et cadre légal</h2>
        <p>
            La présente Politique de Confidentialité décrit la manière dont <strong>iMots CI</strong>
            collecte, utilise, stocke et protège vos données personnelles lorsque vous utilisez notre
            jeu de devinettes culturelles ivoirien.
        </p>
        <p>
            Elle est rédigée en conformité avec la <strong>Loi n°2013-450 du 19 juin 2013 relative à la
            protection des données à caractère personnel</strong> de la République de Côte d'Ivoire,
            ainsi que les recommandations de l'<strong>Autorité de Régulation des Télécommunications/TIC
            de Côte d'Ivoire (ARTCI)</strong>.
        </p>
        <div class="info-box">
            <strong>🇨🇮 Conformité ARTCI :</strong> iMots CI s'engage à respecter les droits des
            utilisateurs définis aux articles 8 à 13 de la Loi n°2013-450, notamment le droit d'accès,
            de rectification et de suppression de vos données personnelles.
        </div>
    </section>

    <!-- ─── 2. Responsable du traitement ─── -->
    <section>
        <h2>2. Responsable du traitement</h2>
        <div class="contact-card">
            <div class="icon">👤</div>
            <div>
                <h3>Responsable du traitement des données</h3>
                <p><strong>Projet :</strong> iMots CI</p>
                <p><strong>Pays :</strong> Côte d'Ivoire</p>
                <p><strong>Contact :</strong> <a href="mailto:daatsey24@gmail.com">daatsey24@gmail.com</a></p>
            </div>
        </div>
    </section>

    <!-- ─── 3. Données collectées ─── -->
    <section>
        <h2>3. Données personnelles collectées</h2>
        <p>
            Nous collectons uniquement les données <strong>strictement nécessaires</strong>
            au fonctionnement du jeu. Nous n'utilisons <strong>aucune publicité tierce</strong>
            et ne collectons <strong>aucune donnée de paiement</strong>.
        </p>

        <table class="data-table" aria-label="Tableau des données collectées">
            <thead>
                <tr>
                    <th>Catégorie</th>
                    <th>Données collectées</th>
                    <th>Finalité</th>
                    <th>Base légale</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong>Compte utilisateur</strong></td>
                    <td>Pseudo, adresse e-mail</td>
                    <td>Création et identification du compte de jeu</td>
                    <td>Exécution du contrat (art. 7 Loi 2013-450)</td>
                </tr>
                <tr>
                    <td><strong>Authentification</strong></td>
                    <td>Mot de passe (haché via bcrypt, jamais stocké en clair)</td>
                    <td>Sécurisation de l'accès au compte</td>
                    <td>Exécution du contrat</td>
                </tr>
                <tr>
                    <td><strong>Données de jeu</strong></td>
                    <td>Scores, niveaux atteints, historique de parties</td>
                    <td>Affichage du classement, progression du joueur</td>
                    <td>Intérêt légitime / exécution du contrat</td>
                </tr>
                <tr>
                    <td><strong>Notifications push</strong></td>
                    <td>Endpoint de notification, clé d'authentification (p256dh), clé secr&egrave;te (auth)</td>
                    <td>Envoi de notifications de jeu (si abonnement explicite)</td>
                    <td>Consentement explicite de l'utilisateur (art. 6 Loi 2013-450)</td>
                </tr>
                <tr>
                    <td><strong>Données de session</strong></td>
                    <td>Identifiant de session PHP, jeton CSRF</td>
                    <td>Sécurité de navigation, protection contre les attaques</td>
                    <td>Intérêt légitime (sécurité)</td>
                </tr>
            </tbody>
        </table>

        <div class="warn-box">
            <strong>⚠️ Ce que nous ne collectons PAS :</strong> Nous ne collectons aucune donnée
            bancaire, aucun numéro de carte, aucune donnée de localisation GPS précise, aucun
            identifiant publicitaire tiers, et nous n'utilisons aucun outil de tracking externe
            (Google Analytics, Facebook Pixel, etc.).
        </div>
    </section>

    <!-- ─── 4. Mot de passe & sécurité ─── -->
    <section>
        <h2>4. Sécurité de votre mot de passe</h2>
        <p>
            Votre mot de passe n'est <strong>jamais stocké en clair</strong> dans notre base de données.
            Nous utilisons l'algorithme de hachage <strong>bcrypt</strong> (conforme aux bonnes pratiques
            de l'OWASP), ce qui signifie que même notre équipe technique ne peut pas connaître votre
            mot de passe. En cas de perte, un mécanisme de réinitialisation vous est proposé.
        </p>
    </section>

    <!-- ─── 5. Notifications push ─── -->
    <section>
        <h2>5. Abonnement aux notifications push</h2>
        <p>
            Si vous choisissez de vous abonner aux notifications push, votre navigateur génère une
            souscription contenant un <em>endpoint</em> (URL du service de notification de votre
            navigateur), une clé publique (<em>p256dh</em>) et une clé d'authentification (<em>auth</em>).
            Ces données sont stockées dans notre base de données associées à votre compte.
        </p>
        <p>
            <strong>Vous pouvez vous désabonner à tout moment</strong> depuis les paramètres de votre
            compte ou depuis les réglages de notifications de votre navigateur. La suppression de votre
            abonnement entraîne la suppression immédiate de ces données dans nos systèmes.
        </p>
    </section>

    <!-- ─── 6. Durée de conservation ─── -->
    <section>
        <h2>6. Durée de conservation des données</h2>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Type de données</th>
                    <th>Durée de conservation</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Données de compte (pseudo, e-mail, mot de passe haché)</td>
                    <td>Jusqu'à suppression du compte par l'utilisateur ou après 3 ans d'inactivité</td>
                </tr>
                <tr>
                    <td>Scores et historique de jeu</td>
                    <td>Durée de vie du compte</td>
                </tr>
                <tr>
                    <td>Abonnements aux notifications push</td>
                    <td>Jusqu'au désabonnement ou suppression du compte</td>
                </tr>
                <tr>
                    <td>Données de session (cookies PHP)</td>
                    <td>Durée de la session (expiration à la fermeture du navigateur ou après inactivité)</td>
                </tr>
            </tbody>
        </table>
    </section>

    <!-- ─── 7. Partage des données ─── -->
    <section>
        <h2>7. Partage et transfert des données</h2>
        <p>
            Nous ne vendons, ne louons et ne partageons <strong>pas</strong> vos données personnelles
            à des tiers à des fins commerciales. Vos données peuvent être accessibles aux
            prestataires techniques hébergeant notre service (hébergeur web), lesquels sont soumis
            à des obligations de confidentialité.
        </p>
        <p>
            En cas d'obligation légale (décision de justice, demande des autorités ivoiriennes
            compétentes), nous pourrions être amenés à communiquer certaines données.
        </p>
    </section>

    <!-- ─── 8. Droits des utilisateurs ─── -->
    <section>
        <h2>8. Vos droits (Loi n°2013-450)</h2>
        <p>Conformément aux articles 8 à 13 de la Loi n°2013-450, vous disposez des droits suivants :</p>
        <ul>
            <li><strong>Droit d'accès (art. 8) :</strong> obtenir la confirmation que des données vous concernant sont traitées et en obtenir une copie.</li>
            <li><strong>Droit de rectification (art. 9) :</strong> faire corriger les données inexactes ou incomplètes.</li>
            <li><strong>Droit de suppression (art. 10) :</strong> demander l'effacement de vos données personnelles.</li>
            <li><strong>Droit d'opposition (art. 12) :</strong> vous opposer au traitement de vos données pour des motifs légitimes.</li>
            <li><strong>Droit au retrait du consentement :</strong> retirer à tout moment votre consentement aux notifications push, sans affecter la licéité des traitements antérieurs.</li>
        </ul>
        <p>
            Pour exercer ces droits, contactez-nous à l'adresse :
            <a href="mailto:daatsey24@gmail.com" style="color:#F77F00;">daatsey24@gmail.com</a>.
            Nous nous engageons à répondre dans un délai de <strong>30 jours</strong>.
        </p>
    </section>

    <!-- ─── 9. Sécurité ─── -->
    <section>
        <h2>9. Sécurité des données</h2>
        <p>
            Nous mettons en œuvre des mesures techniques et organisationnelles appropriées pour
            protéger vos données contre tout accès non autorisé, perte, altération ou divulgation :
        </p>
        <ul>
            <li>Hachage bcrypt des mots de passe</li>
            <li>Protection CSRF sur tous les formulaires</li>
            <li>Sessions PHP sécurisées (cookies <code>HttpOnly</code> et <code>SameSite</code>)</li>
            <li>Accès restreint à la base de données</li>
            <li>Aucun stockage de données sensibles côté client</li>
        </ul>
    </section>

    <!-- ─── 10. Mineurs ─── -->
    <section>
        <h2>10. Protection des mineurs</h2>
        <p>
            iMots CI est déconseillé aux enfants de moins de <strong>13 ans</strong> sans
            supervision parentale. Nous ne collectons pas sciemment de données personnelles
            provenant d'enfants de moins de 13 ans. Si vous pensez qu'un mineur nous a fourni
            des données sans consentement parental, contactez-nous immédiatement pour en demander
            la suppression.
        </p>
    </section>

    <!-- ─── 11. Modifications ─── -->
    <section>
        <h2>11. Modifications de cette politique</h2>
        <p>
            Nous pouvons mettre à jour cette politique à tout moment. Toute modification substantielle
            vous sera notifiée via une notification dans le jeu ou par e-mail. La date de dernière
            mise à jour figure en haut de cette page. L'utilisation continue du service après notification
            vaut acceptation de la politique révisée.
        </p>
    </section>

    <!-- ─── 12. Contact & réclamation ─── -->
    <section>
        <h2>12. Contact et dépôt de réclamation</h2>
        <div class="contact-card">
            <div class="icon">✉️</div>
            <div>
                <h3>Nous contacter</h3>
                <p>Pour toute question relative à cette politique ou pour exercer vos droits :</p>
                <p><a href="mailto:daatsey24@gmail.com">daatsey24@gmail.com</a></p>
            </div>
        </div>
        <p style="margin-top:20px;">
            Si vous estimez que le traitement de vos données n'est pas conforme à la loi, vous avez
            le droit de déposer une réclamation auprès de l'<strong>ARTCI</strong>
            (Autorité de Régulation des Télécommunications/TIC de Côte d'Ivoire) ou de saisir les
            juridictions ivoiriennes compétentes.
        </p>
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
    <p style="margin-top:8px;">Conforme à la Loi ivoirienne n°2013-450 du 19 juin 2013 &mdash; ARTCI</p>
</footer>

</body>
</html>
