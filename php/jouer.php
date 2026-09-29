<?php
/**
 * jouer.php — endpoint principal du jeu.
 *
 * POST mot + tentatives → validation, calcul, sauvegarde score.
 * GET (sans mot) → retourne uniquement la longueur du mot du jour.
 * Le mot du jour n'est JAMAIS transmis au client.
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/csrf.php';

session_start();
header('Content-Type: application/json');

// --- Authentification ---
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['erreur' => 'Non connecté.']);
    exit;
}

$conn       = db_connect();
$aujourdhui = date('Y-m-d');

// ============================================================
// GET : retourner uniquement la longueur du mot du jour
// ============================================================
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $motDuJour = _obtenir_mot_du_jour($conn, $aujourdhui);
    $longueur  = $motDuJour ? mb_strlen($motDuJour, 'UTF-8') : 0;

    // Vérifier si l'utilisateur a déjà gagné aujourd'hui (sync multi-appareils)
    $userId   = (int) $_SESSION['user_id'];
    $chkScore = $conn->prepare('SELECT tentatives FROM scores WHERE user_id = ? AND date_jour = ? AND trouve = 1');
    $chkScore->bind_param('is', $userId, $aujourdhui);
    $chkScore->execute();
    $scoreRow  = $chkScore->get_result()->fetch_assoc();
    $dejaGagne = !!$scoreRow;

    $definition = '';
    if ($dejaGagne && $motDuJour) {
        $defStmt = $conn->prepare('SELECT definition FROM mots WHERE UPPER(mot) = ?');
        $defStmt->bind_param('s', $motDuJour);
        $defStmt->execute();
        $defRow     = $defStmt->get_result()->fetch_assoc();
        $definition = $defRow ? ($defRow['definition'] ?? '') : '';
    }

    $conn->close();
    echo json_encode([
        'longueur'   => $longueur,
        'deja_gagne' => $dejaGagne,
        'tentatives' => $scoreRow ? (int)$scoreRow['tentatives'] : 0,
        'definition' => $definition,
    ]);
    exit;
}

// ============================================================
// POST : jouer
// ============================================================
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['erreur' => 'Méthode non autorisée.']);
    exit;
}

// --- CSRF ---
csrf_check_ajax();

// --- Entrée ---
$motPropose = mb_strtoupper(trim($_POST['mot'] ?? ''), 'UTF-8');

if ($motPropose === '' || !preg_match('/^[A-ZÀÂÄÉÈÊËÎÏÔÖÙÛÜÇ]{2,30}$/u', $motPropose)) {
    echo json_encode(['ok' => false, 'message' => 'Mot invalide.']);
    $conn->close();
    exit;
}

// --- Mot du jour ---
$motDuJour = _obtenir_mot_du_jour($conn, $aujourdhui);

if (!$motDuJour) {
    echo json_encode(['ok' => false, 'message' => 'Mot du jour indisponible.']);
    $conn->close();
    exit;
}

// Tous les mots sont acceptés — pas de validation dictionnaire
// ============================================================
// CALCULS (serveur uniquement)
// ============================================================
$lettresMDJ  = preg_split('//u', $motDuJour,  -1, PREG_SPLIT_NO_EMPTY);
$lettresMot  = preg_split('//u', $motPropose, -1, PREG_SPLIT_NO_EMPTY);
$longueurMDJ = count($lettresMDJ);
$longueurMot = count($lettresMot);

// 1. Initialiser le tableau des états (0 = gris, 1 = jaune, 2 = vert)
$positions = array_fill(0, $longueurMot, 0);
$lettresRestantesMDJ = [];

// Passe 1 : Trouver les lettres bien placées (Vert = 2)
for ($i = 0; $i < $longueurMot; $i++) {
    if (isset($lettresMDJ[$i]) && $lettresMot[$i] === $lettresMDJ[$i]) {
        $positions[$i] = 2;
    } else {
        if (isset($lettresMDJ[$i])) {
            $lettresRestantesMDJ[] = $lettresMDJ[$i];
        }
    }
}

// Passe 2 : Trouver les lettres mal placées (Jaune = 1)
for ($i = 0; $i < $longueurMot; $i++) {
    if ($positions[$i] !== 2 && in_array($lettresMot[$i], $lettresRestantesMDJ, true)) {
        $positions[$i] = 1;
        // Retirer la lettre utilisée pour gérer les doublons
        $idx = array_search($lettresMot[$i], $lettresRestantesMDJ, true);
        if ($idx !== false) {
            unset($lettresRestantesMDJ[$idx]);
            // Réindexer (optionnel mais propre)
            $lettresRestantesMDJ = array_values($lettresRestantesMDJ);
        }
    }
}

// 2. Score de proximité global (ancien système gardé pour la rétrocompatibilité des emojis)
$copie      = $lettresMDJ;
$communes   = 0;
$utilises   = [];

for ($i = 0; $i < $longueurMot; $i++) {
    $c = $lettresMot[$i];
    foreach ($copie as $j => $l) {
        if ($l === $c && !in_array($j, $utilises, true)) {
            $communes++;
            $utilises[] = $j;
            break;
        }
    }
}

$gagne      = ($motPropose === $motDuJour);
$score      = $gagne ? 100.0 : ($longueurMDJ > 0 ? round(($communes / $longueurMDJ) * 100, 2) : 0.0);

// 3. Emoji
if      ($gagne)        $emoji = '🥳';
elseif ($score >= 80)   $emoji = '😮';
elseif ($score >= 60)   $emoji = '🔥';
elseif ($score >= 40)   $emoji = '🥵';
elseif ($score >= 20)   $emoji = '😎';
elseif ($score > 0)     $emoji = '🥶';
else                    $emoji = '🧊';

// ============================================================
// SCORE EN BASE (uniquement si gagné, une fois par jour)
// ============================================================
$userId     = (int) $_SESSION['user_id'];
$tentatives = max(1, (int) ($_POST['tentatives'] ?? 1));

if ($gagne) {
    $chk = $conn->prepare('SELECT id FROM scores WHERE user_id = ? AND date_jour = ?');
    $chk->bind_param('is', $userId, $aujourdhui);
    $chk->execute();
    if ($chk->get_result()->num_rows === 0) {
        $ins = $conn->prepare('INSERT INTO scores (user_id, date_jour, tentatives, trouve) VALUES (?, ?, ?, 1)');
        $ins->bind_param('isi', $userId, $aujourdhui, $tentatives);
        $ins->execute();
    }
}

// Définition du mot (uniquement si gagné)
$definition = '';
if ($gagne) {
    $defStmt = $conn->prepare('SELECT definition FROM mots WHERE UPPER(mot) = ?');
    $defStmt->bind_param('s', $motDuJour);
    $defStmt->execute();
    $defRow = $defStmt->get_result()->fetch_assoc();
    $definition = $defRow ? ($defRow['definition'] ?? '') : '';
}

$conn->close();

echo json_encode([
    'ok'          => true,
    'valide'      => true,
    'positions'   => $positions,
    'score'       => $score,
    'emoji'       => $emoji,
    'gagne'       => $gagne,
    'longueurMDJ' => $longueurMDJ,
    'definition'  => $definition,  // vide si non gagné ou pas de définition
]);

// ============================================================
// Fonction interne : obtenir ou auto-assigner le mot du jour
// ============================================================
function _obtenir_mot_du_jour(mysqli $conn, string $date): string {
    // Chercher dans le cache
    $stmt = $conn->prepare('SELECT UPPER(mot) AS mot FROM mots_du_jour WHERE date_jour = ?');
    $stmt->bind_param('s', $date);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    if ($row) return trim($row['mot']);

    // Sélection déterministe
    $total = (int) $conn->query('SELECT COUNT(*) FROM mots WHERE ordre IS NOT NULL')->fetch_row()[0];
    if ($total === 0) return '';

    $index = jour_numero() % $total;
    $s2    = $conn->prepare('SELECT mot FROM mots WHERE ordre IS NOT NULL ORDER BY ordre ASC LIMIT 1 OFFSET ?');
    $s2->bind_param('i', $index);
    $s2->execute();
    $row2  = $s2->get_result()->fetch_assoc();
    if (!$row2) return '';

    $mot = strtoupper(trim($row2['mot']));

    $ins = $conn->prepare('INSERT IGNORE INTO mots_du_jour (date_jour, mot) VALUES (?, ?)');
    $ins->bind_param('ss', $date, $mot);
    $ins->execute();

    return $mot;
}
?>
