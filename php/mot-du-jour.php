<?php
/**
 * mot-du-jour.php
 *
 * Sélection déterministe : dayNumber % total_mots → index dans la liste ordonnée.
 * Fonctionne indéfiniment sans intervention manuelle.
 * Le résultat est mis en cache dans mots_du_jour pour l'historique.
 */
require_once __DIR__ . '/config.php';

// Cet endpoint n'est plus utilisé directement par le client (cf. jouer.php).
// Accès limité : session ou appel interne seulement.
session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    exit;
}

$conn       = db_connect();
$aujourdhui = date('Y-m-d');

// 1. Mot déjà assigné ?
$stmt = $conn->prepare('SELECT UPPER(mot) AS mot FROM mots_du_jour WHERE date_jour = ?');
$stmt->bind_param('s', $aujourdhui);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();

if ($row) {
    $conn->close();
    echo $row['mot'];
    exit;
}

// 2. Sélection déterministe
$total = (int) $conn->query('SELECT COUNT(*) FROM mots')->fetch_row()[0];

if ($total === 0) {
    $conn->close();
    echo '';
    exit;
}

$jourNum = jour_numero();
$index   = $jourNum % $total;

$stmt2 = $conn->prepare('SELECT mot FROM mots ORDER BY ordre ASC LIMIT 1 OFFSET ?');
$stmt2->bind_param('i', $index);
$stmt2->execute();
$row2 = $stmt2->get_result()->fetch_assoc();

if (!$row2) {
    $conn->close();
    echo '';
    exit;
}

$mot = strtoupper(trim($row2['mot']));

// 3. Mise en cache historique
$ins = $conn->prepare('INSERT IGNORE INTO mots_du_jour (date_jour, mot) VALUES (?, ?)');
$ins->bind_param('ss', $aujourdhui, $mot);
$ins->execute();

$conn->close();
echo $mot;
?>
