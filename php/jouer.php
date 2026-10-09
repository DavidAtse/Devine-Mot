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
$creneau    = creneau_actuel();

// ============================================================
// GET : retourner uniquement la longueur du mot du jour
// ============================================================
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $motDuJour = _obtenir_mot_du_jour($conn, $aujourdhui, $creneau);
    $longueur  = $motDuJour ? mb_strlen($motDuJour, 'UTF-8') : 0;

    // Vérifier si l'utilisateur a déjà gagné aujourd'hui (sync multi-appareils)
    $userId   = (int) $_SESSION['user_id'];
    $chkScore = $conn->prepare('SELECT tentatives FROM scores WHERE user_id = ? AND date_jour = ? AND creneau = ? AND trouve = 1');
    $chkScore->bind_param('isi', $userId, $aujourdhui, $creneau);
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

    // Mot partenaire ?
    $stmtP = $conn->prepare("SELECT est_partenaire FROM mots_du_jour WHERE date_jour = ? AND creneau = ?");
    $stmtP->bind_param('si', $aujourdhui, $creneau);
    $stmtP->execute();
    $pRow = $stmtP->get_result()->fetch_assoc();
    $estPartenaire = $pRow ? (bool)$pRow['est_partenaire'] : false;

    $conn->close();
    echo json_encode([
        'longueur'       => $longueur,
        'deja_gagne'     => $dejaGagne,
        'tentatives'     => $scoreRow ? (int)$scoreRow['tentatives'] : 0,
        'mot'            => $dejaGagne ? $motDuJour : null,
        'definition'     => $definition,
        'creneau'        => $creneau,
        'fin_dans'       => creneau_secondes_restantes(),
        'est_partenaire' => $estPartenaire,
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
$motDuJour = _obtenir_mot_du_jour($conn, $aujourdhui, $creneau);

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
if ($gagne) {
    $score = 100.0;
} else {
    // Si ce n'est pas le mot exact, le score prend en compte les lettres en trop
    // et ne peut JAMAIS atteindre 100.00%
    $denominateur = max($longueurMDJ, $longueurMot);
    $rawScore     = ($denominateur > 0) ? round(($communes / $denominateur) * 100, 2) : 0.0;
    $score        = min(99.0, $rawScore);
}

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
    $chk = $conn->prepare('SELECT id FROM scores WHERE user_id = ? AND date_jour = ? AND creneau = ?');
    $chk->bind_param('isi', $userId, $aujourdhui, $creneau);
    $chk->execute();
    if ($chk->get_result()->num_rows === 0) {
        $maintenant = date('Y-m-d H:i:s');
        $ins = $conn->prepare('INSERT INTO scores (user_id, date_jour, creneau, tentatives, trouve, created_at) VALUES (?, ?, ?, ?, 1, ?)');
        $ins->bind_param('isiis', $userId, $aujourdhui, $creneau, $tentatives, $maintenant);
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

// Mot partenaire ?
$stmtP2 = $conn->prepare("SELECT est_partenaire FROM mots_du_jour WHERE date_jour = ? AND creneau = ?");
$stmtP2->bind_param('si', $aujourdhui, $creneau);
$stmtP2->execute();
$pRow2 = $stmtP2->get_result()->fetch_assoc();
$estPartenairePost = $pRow2 ? (bool)$pRow2['est_partenaire'] : false;

$conn->close();

echo json_encode([
    'ok'             => true,
    'valide'         => true,
    'positions'      => $positions,
    'score'          => $score,
    'emoji'          => $emoji,
    'gagne'          => $gagne,
    'longueurMDJ'    => $longueurMDJ,
    'definition'     => $definition,
    'est_partenaire' => $estPartenairePost,
]);

// ============================================================
// Fonction interne : obtenir ou auto-assigner le mot du jour
// ============================================================
function _obtenir_mot_du_jour(mysqli $conn, string $date, int $creneau): string {
    return assigner_mot_creneau($conn, $date, $creneau);
}
?>
