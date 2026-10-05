<?php
/**
 * export_railway.php — Exporte la base de données active en direct (depuis Railway).
 * Protégé par une clé secrète.
 */
session_start();
require_once __DIR__ . '/php/config.php';

$secret = $_GET['key'] ?? '';
if ($secret !== 'imots_backup_2026') {
    http_response_code(403);
    die('Accès refusé. Clé de sécurité manquante ou incorrecte.');
}

$conn = db_connect();

$tables = ['mots', 'mots_du_jour', 'push_log', 'push_subscriptions', 'scores', 'tentatives', 'users'];

$filename = 'imots_railway_backup_' . date('Y-m-d_His') . '.sql';

header('Content-Type: text/plain; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

echo "-- iMots CI - Export Live Base de Données\n";
echo "-- Date : " . date('Y-m-d H:i:s') . "\n\n";
echo "SET NAMES utf8mb4;\n";
echo "SET FOREIGN_KEY_CHECKS = 0;\n\n";

foreach ($tables as $table) {
    // Structure
    $res = $conn->query("SHOW CREATE TABLE `{$table}`");
    if ($res && $row = $res->fetch_row()) {
        echo "DROP TABLE IF EXISTS `{$table}`;\n";
        echo $row[1] . ";\n\n";
    }
    
    // Données
    $resData = $conn->query("SELECT * FROM `{$table}`");
    if ($resData && $resData->num_rows > 0) {
        $cols = [];
        $fields = $resData->fetch_fields();
        foreach ($fields as $f) {
            $cols[] = '`' . $f->name . '`';
        }
        $colsStr = implode(', ', $cols);
        
        echo "INSERT INTO `{$table}` ({$colsStr}) VALUES\n";
        $rows = [];
        while ($r = $resData->fetch_row()) {
            $vals = [];
            foreach ($r as $val) {
                if ($val === null) {
                    $vals[] = 'NULL';
                } else {
                    $vals[] = "'" . $conn->real_escape_string($val) . "'";
                }
            }
            $rows[] = "(" . implode(', ', $vals) . ")";
        }
        echo implode(",\n", $rows) . ";\n\n";
    }
}

echo "SET FOREIGN_KEY_CHECKS = 1;\n";
$conn->close();
exit;
