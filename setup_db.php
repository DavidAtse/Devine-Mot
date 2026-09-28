<?php
// Script temporaire pour importer la base de données sur Railway en interne
require_once __DIR__ . '/php/config.php';

echo "<h1>Importation de la Base de Données</h1>";

$conn = db_connect();

$sql_file = __DIR__ . '/railway_import.sql';
if (!file_exists($sql_file)) {
    die("Le fichier railway_import.sql est introuvable.");
}

$sql = file_get_contents($sql_file);

// On autorise l'exécution de requêtes multiples
try {
    if ($conn->multi_query($sql)) {
        do {
            if ($result = $conn->store_result()) {
                $result->free();
            }
        } while ($conn->more_results() && $conn->next_result());
        echo "<p style='color:green;font-weight:bold;'>✅ Importation réussie ! La base de données est prête.</p>";
        echo "<p><a href='index.php'>Aller jouer au jeu</a></p>";
    } else {
        echo "<p style='color:red;'>Erreur lors de l'importation : " . htmlspecialchars($conn->error) . "</p>";
    }
} catch (Exception $e) {
    // Si une exception est levée (par exemple si les tables existent déjà et qu'il y a un conflit)
    echo "<p style='color:orange;'>Info (ou erreur) pendant l'importation : " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><a href='index.php'>Retourner au jeu pour vérifier</a></p>";
}

$conn->close();
?>
