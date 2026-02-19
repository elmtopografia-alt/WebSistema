<?php
require_once 'db.php';
header('Content-Type: text/plain; charset=utf-8');

$conn = Database::getProd();

echo "=== LISTA DE TABELAS (PRODUÇÃO) ===\n\n";
$res = $conn->query("SHOW TABLES");
while ($row = $res->fetch_array()) {
    echo " - " . $row[0] . "\n";
}
?>
