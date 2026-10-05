<?php
/**
 * php/export_bdd.php — Sauvegarde en direct de la base de données.
 */
require_once __DIR__ . '/config.php';

$key = $_GET['key'] ?? '';
if ($key !== 'imots2026' && $key !== 'imots_backup_2026') {
    http_response_code(403);
    die('Accès refusé.');
}

$conn = db_connect();

$tables = ['mots', 'mots_du_jour', 'push_log', 'push_subscriptions', 'scores', 'tentatives', 'users'];

header('Content-Type: text/plain; charset=utf-8');
header('Content-Disposition: attachment; filename="railway_backup_' . date('Y-m-d_His') . '.sql"');

echo "-- iMots CI - Export Live Base de Données\n";
echo "-- Date : " . date('Y-m-d H:i:s') . "\n\n";
echo "SET NAMES utf8mb4;\n";
echo "SET FOREIGN_KEY_CHECKS = 0;\n\n";

foreach ($tables as $table) {
    $res = $conn->query("SHOW CREATE TABLE `{$table}`");
    if ($res && $row = $res->fetch_row()) {
        echo "DROP TABLE IF EXISTS `{$table}`;\n";
        echo $row[1] . ";\n\n";
    }
    
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
