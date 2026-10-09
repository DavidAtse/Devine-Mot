# 🇨🇮 iMots CI (DevineMot CI)

> **Le premier jeu web de devinette de mots 100 % ancré dans la culture, le nouchi et le quotidien de la Côte d'Ivoire.**  
> 🌐 **Jouer en direct :** [https://imots.alwaysdata.net/](https://imots.alwaysdata.net/)

---

## 📌 Présentation du Projet

**iMots CI** est une plateforme web interactive et progressive (PWA) conçue pour célébrer la richesse linguistique et culturelle ivoirienne. Inspiré des mécaniques modernes de jeux de mots quotidiens, il propose une expérience dynamique rythmée par **4 créneaux horaires par jour**, un système de **proximité par thermomètre/emojis**, des **définitions culturelles**, et un **classement national en direct**.

---

## 📜 Règles Officielles du Jeu

### 1. 🎯 Objectif
Deviner le **mot secret ivoirien** du créneau horaire actif en un minimum de tentatives.  
Le lexique comprend :
* Le nouchi et l'argot abidjanais (*Gnan*, *Kpôkro*, *Dah*, *Zoblazo*, *Yako*...).
* Les communes, villes et quartiers ivoiriens (*Abobo*, *Yopougon*, *Cocody*, *Bassam*...).
* La gastronomie locale (*Attiéké*, *Alloco*, *Garba*, *Kédjénou*...).
* La faune, la flore et les personnalités marquantes de Côte d'Ivoire.

---

### 2. ⏰ 4 Créneaux Quotidiens (Un nouveau mot toutes les 6 heures)
Le jeu ne se limite pas à un seul mot par jour : il propose **4 défis quotidiens** calés sur le fuseau horaire d'Abidjan (GMT/UTC) :
* 🕛 **Créneau 1 : 00h - 06h** — La nuit & les couche-tard
* 🌅 **Créneau 2 : 06h - 12h** — Le matin & le réveil
* ☀️ **Créneau 3 : 12h - 18h** — L'après-midi & la pause déjeuner
* 🌙 **Créneau 4 : 18h - 00h** — La soirée & détente

> ⏳ Un **compte à rebours en temps réel** sur l'écran d'accueil indique précisément le temps restant avant l'arrivée du mot suivant.

---

### 3. 🧩 Mécanique de Jeu & Indices
1. **Longueur du mot :** Dès le chargement, le nombre exact de lettres est matérialisé par des **tuiles/cases**.
2. **Saisie libre :** Le joueur saisit n'importe quel mot de la bonne longueur. Toutes les variantes d'orthographe nouchi sont acceptées.
3. **Indices par couleurs (Tuiles) :**
   * 🟩 **Vert (Bien placé) :** La lettre est correcte et à la bonne position. Elle se verrouille en haut pour guider les essais suivants.
   * ⬛ **Gris (Absent) :** La lettre ne fait pas partie du mot secret.
4. **Thermomètre de Proximité (%) & Emojis :**  
   Chaque proposition est évaluée selon sa proximité avec le mot secret :
   * 🧊 **0 %** : Glace *(aucune lettre commune)*
   * 🥶 **1 % à 19 %** : Très froid
   * 😎 **20 % à 39 %** : Tiède
   * 🥵 **40 % à 59 %** : Chaud
   * 🔥 **60 % à 79 %** : Très chaud
   * 😮 **80 % à 99 %** : Brûlant
   * 🥳 **100 %** : Victoire (Mot trouvé !)

---

### 4. 📚 Victoire, Découverte Culturelle & Sponsoring
* **Définition culturelle :** Dès que le mot est trouvé, sa signification et son contexte d'utilisation ivoirien sont révélés et restent consultables jusqu'au prochain créneau.
* **Mots Partenaires (⭐) :** Des mots sponsorisés par des marques ou entreprises locales (ex: *ORANGE*, *WAVE*, etc.) peuvent être programmés par l'administration, affichant un badge officiel, leur slogan et des liens d'activation.
* **Partage sans spoiler :** Un bouton permet de copier un résumé graphique sous forme d'emojis pour défier ses amis sur WhatsApp et les réseaux sociaux sans dévoiler le mot.

---

### 5. 🏆 Compétition & Statistiques
* **Mon Profil :** Suivi du total de parties, taux de victoire, meilleur score (trouvé en 1 coup, 2 coups...) et série de victoires quotidiennes consécutives (*Streak*).
* **Classement National (Leaderboard) :** Top 10 des meilleurs joueurs classés par nombre de victoires (🥇, 🥈, 🥉) et départagés par l'ordre chronologique de découverte (les premiers à trouver sont en tête, sans pénalité sur le nombre d'essais).

---

### 6. 🔒 Sécurité & Fair-Play
* **Anti-triche côté serveur :** Le mot secret n'est jamais transmis au navigateur avant d'avoir été trouvé (inviolable via l'inspecteur d'éléments `F12`).
* **Synchronisation multi-appareils :** Une victoire sur smartphone est immédiatement reconnue sur ordinateur via le compte utilisateur.
* **Une seule victoire enregistrée par joueur et par créneau.**

---

## 🛠️ Technologies Utilisées

| Domaine | Technologies / Outils | Description |
| :--- | :--- | :--- |
| **Backend** | **PHP 8.x** | Architecture modulaire légère, API REST interne en JSON, gestion de sessions natives sécurisées. |
| **Base de Données** | **MySQL / MariaDB** | Encodage complet `utf8mb4_unicode_ci` (support des émojis et caractères accentués). Migrations de schéma automatiques et idempotentes. |
| **Frontend** | **HTML5 / CSS3 / Vanilla JS (ES6+)** | Aucun framework lourd (React/Vue) pour garantir un chargement ultra-rapide (moins de 1s) sur réseau mobile 3G/4G en Côte d'Ivoire. Dark mode natif (#1a1a1a / accent #F77F00). |
| **PWA & Mobile** | **Service Workers & Web App Manifest** | Application installable sur l'écran d'accueil (Android, iOS, PC), cache hors-ligne pour les assets statiques. |
| **Notifications** | **Web Push API / VAPID** | Notifications push natives navigateur pour alerter les joueurs au changement de créneau horaire. |
| **Paiements & Soutien** | **Jeko Africa API & Webhooks** | Passerelle de paiement sécurisée multi-opérateurs : **Wave**, **Orange Money**, **MTN MoMo**, **Moov Money** et **Cartes bancaires (Visa/Mastercard)**. |
| **Sécurité & Protection** | **CSRF, Bcrypt, Headers de sécurité** | Protection CSRF stricte par token de session sur tous les formulaires et requêtes AJAX, hashage de mot de passe `PASSWORD_BCRYPT`, en-têtes CSP/HSTS/X-Frame-Options. |
| **Conformité Légale** | **Loi ARTCI n°2013-450** | Respect de la réglementation ivoirienne relative à la protection des données à caractère personnel (pages légales dédiées : Confidentialité, CGU, Cookies). |
| **Hébergement & Déploiement** | **Alwaysdata & CI/CD Git Webhook** | Déploiement continu automatisé à chaque `git push` sur la branche `main` via webhook sécurisé. |

---

## 📂 Structure du Projet

```text
devine-mot/
├── admin/
│   └── mots.php                # Tableau de bord d'administration (gestion des mots, partenariats, revenus/dons)
├── assets/
│   ├── css/                    # Feuilles de style (index, dashboard, dark mode)
│   ├── icons/                  # Icônes de l'application et PWA
│   └── images/                 # Visuels et logos
├── dashboard/
│   ├── leaderboard.php         # Classement général des joueurs (Top 10)
│   └── profile.php             # Statistiques et profil joueur (Streak, taux de victoire)
├── inscription/
│   ├── login.php               # Connexion sécurisée
│   ├── register.php            # Inscription
│   └── logout.php              # Déconnexion
├── js/
│   ├── main.js                 # Logique frontend, saisie, tuiles, modales, paiement Jeko
│   └── push.js                 # Gestion de l'abonnement aux notifications Web Push
├── legal/
│   ├── confidentialite.php     # Politique de confidentialité conforme ARTCI
│   ├── conditions.php          # Conditions Générales d'Utilisation (CGU)
│   └── cookies.php             # Politique relative aux cookies
├── php/
│   ├── config.php              # Configuration DB, calcul des créneaux et migrations automatiques
│   ├── csrf.php                # Gestion et vérification des jetons CSRF
│   ├── security.php            # En-têtes de sécurité HTTP
│   ├── jouer.php               # Moteur de validation des propositions et calcul du score
│   ├── enregistrer-don.php     # Enregistrement des intentions de soutien (Jeko)
│   ├── jeko_webhook.php        # Webhook de validation automatique des paiements Jeko
│   └── push-send.php           # Envoi automatisé des rappels push
├── index.php                   # Page d'accueil et plateau de jeu principal
├── manifest.json               # Manifeste PWA
├── sw.js                       # Service Worker PWA (mise en cache et gestion push)
└── webhook_deploy.php          # Déploiement continu automatique GitHub -> Alwaysdata
```

---

## 💻 Installation en Local (Environnement XAMPP)

1. **Cloner le dépôt :**
   ```bash
   git clone https://github.com/DavidAtse/Devine-Mot.git
   cd Devine-Mot
   ```

2. **Configuration du serveur local :**
   * Placer le dossier dans votre répertoire web (ex: `C:/xampp/htdocs/devine-mot/`).
   * Démarrer les services **Apache** et **MySQL** dans XAMPP.

3. **Base de Données :**
   * Créer une base de données MySQL nommée `jeu_mot` (ou `imots_jeu`) avec l'interclassement `utf8mb4_unicode_ci`.
   * Les tables et migrations sont créées automatiquement dès la première connexion via `php/config.php`.

4. **Lancement :**
   * Ouvrir votre navigateur sur `http://localhost/devine-mot/`.

---

## 👨‍💻 Auteur

* **David ATSÉ** ([@DavidAtse](https://github.com/DavidAtse)) — *Concepteur & Développeur Full-Stack*  
* ✉️ Contact : `daatsey24@gmail.com`  
* 🇨🇮 *Fait avec fierté et passion en Côte d'Ivoire.*
