<?php
/**
 * 404.php — Page d'erreur personnalisée pour DevineMot CI.
 * Configurée via .htaccess ou Railway custom error handling.
 */
http_response_code(404);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page introuvable — DevineMot CI 🇨🇮</title>
    <link rel="icon" type="image/svg+xml" href="/assets/icons/icon-192.svg">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #0F0C07;
            color: #FDF8F0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 24px;
            background-image:
                repeating-linear-gradient(0deg, transparent, transparent 48px, rgba(247,127,0,0.04) 48px, rgba(247,127,0,0.04) 50px),
                repeating-linear-gradient(90deg, transparent, transparent 48px, rgba(0,158,96,0.04) 48px, rgba(0,158,96,0.04) 50px);
        }
        .card {
            background: rgba(253,248,240,0.05);
            border: 1px solid rgba(247,127,0,0.2);
            border-radius: 24px;
            padding: 48px 40px;
            max-width: 480px;
            width: 100%;
            box-shadow: 0 8px 40px rgba(0,0,0,0.5);
        }
        .emoji-404 {
            font-size: 72px;
            display: block;
            margin-bottom: 16px;
            animation: bounce 1.5s ease-in-out infinite;
        }
        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50%       { transform: translateY(-12px); }
        }
        h1 {
            font-size: 5rem;
            font-weight: 900;
            color: #F77F00;
            letter-spacing: -4px;
            line-height: 1;
            margin-bottom: 12px;
        }
        h2 {
            font-size: 1.2rem;
            font-weight: 700;
            color: #FDF8F0;
            margin-bottom: 10px;
        }
        p {
            color: rgba(253,248,240,0.6);
            font-size: 0.95rem;
            line-height: 1.6;
            margin-bottom: 32px;
        }
        .btn-home {
            display: inline-block;
            background: #F77F00;
            color: #0F0C07;
            font-weight: 800;
            font-size: 1rem;
            padding: 14px 32px;
            border-radius: 14px;
            text-decoration: none;
            transition: transform 0.15s, box-shadow 0.2s;
            box-shadow: 0 4px 20px rgba(247,127,0,0.35);
        }
        .btn-home:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 32px rgba(247,127,0,0.5);
        }
        .flag-stripe {
            height: 4px;
            background: linear-gradient(90deg, #F77F00 33%, #FFF 33% 66%, #009E60 66%);
            border-radius: 4px 4px 0 0;
            margin: -48px -40px 40px;
            border-radius: 24px 24px 0 0;
        }
        .hint {
            margin-top: 20px;
            font-size: 0.8rem;
            color: rgba(253,248,240,0.3);
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="flag-stripe"></div>
        <span class="emoji-404">🧊</span>
        <h1>404</h1>
        <h2>Cette page n'existe pas !</h2>
        <p>Tu t'es peut-être trompé de chemin...<br>
        Mais le mot du jour, lui, t'attend ! 🔥</p>
        <a href="/inscription/login.php" class="btn-home">🇨🇮 Retour au jeu</a>
        <p class="hint">Si tu penses que c'est une erreur, contacte-nous à daatsey24@gmail.com</p>
    </div>
</body>
</html>
