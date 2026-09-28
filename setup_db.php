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
if ($conn->multi_query($sql)) {
    do {
        // Vider les résultats pour passer à la requête suivante
        if ($result = $conn->store_result()) {
            $result->free();
        }
    } while ($conn->more_results() && $conn->next_result());
    echo "<p style='color:green;font-weight:bold;'>✅ Importation réussie ! La base de données est prête.</p>";
    echo "<p>Tu peux maintenant jouer au jeu.</p>";
} else {
    echo "<p style='color:red;'>Erreur lors de l'importation : " . $conn->error . "</p>";
}

$conn->close();
?>
