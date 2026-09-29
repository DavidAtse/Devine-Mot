<?php
/**
 * setup_db.php — Import de la base de données (accès admin uniquement).
 * PROTÉGÉ : accessible uniquement par l'administrateur connecté.
 */
session_start();
require_once __DIR__ . '/php/config.php';

// Protection : uniquement accessible par un administrateur connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: inscription/login.php');
    exit;
}

// Vérifier que l'utilisateur est admin
$conn  = db_connect();
$stmt  = $conn->prepare('SELECT is_admin FROM users WHERE id = ?');
$uid   = (int) $_SESSION['user_id'];
$stmt->bind_param('i', $uid);
$stmt->execute();
$row   = $stmt->get_result()->fetch_assoc();
if (!$row || !$row['is_admin']) {
    http_response_code(403);
    echo '<h2>403 — Accès refusé</h2><p>Réservé à l\'administrateur.</p>';
    $conn->close();
    exit;
}

echo "<!DOCTYPE html><html lang='fr'><head><meta charset='UTF-8'><title>Setup DB</title>
<style>body{font-family:sans-serif;background:#1a1a1a;color:#fff;text-align:center;padding:60px 20px;}
a{color:#F77F00;}p{max-width:600px;margin:auto;}</style></head><body>";
echo "<h1>🗄️ Importation de la Base de Données</h1>";

$sql_file = __DIR__ . '/railway_import.sql';
if (!file_exists($sql_file)) {
    echo "<p style='color:red;'>❌ Le fichier railway_import.sql est introuvable.</p>";
    $conn->close();
    exit;
}

$sql = file_get_contents($sql_file);

// Supprime le BOM UTF-8 ajouté par PowerShell lors de l'export
if (strpos($sql, "\xEF\xBB\xBF") === 0) {
    $sql = substr($sql, 3);
}
if (strpos($sql, "\xFF\xFE") === 0) {
    $sql = mb_convert_encoding(substr($sql, 2), 'UTF-8', 'UTF-16LE');
}

try {
    if ($conn->multi_query($sql)) {
        do {
            if ($result = $conn->store_result()) {
                $result->free();
            }
        } while ($conn->more_results() && $conn->next_result());
        echo "<p style='color:#22c55e;font-weight:bold;font-size:18px;'>✅ Importation réussie !</p>";
        echo "<p><a href='index.php'>← Retour au jeu</a></p>";
    } else {
        echo "<p style='color:red;'>❌ Erreur : " . htmlspecialchars($conn->error) . "</p>";
    }
} catch (Exception $e) {
    echo "<p style='color:orange;'>⚠️ Info : " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><a href='index.php'>← Retour au jeu</a></p>";
}

$conn->close();
echo "</body></html>";
?>
