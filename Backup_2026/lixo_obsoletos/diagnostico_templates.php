<?php
require_once 'db.php';
header('Content-Type: text/plain; charset=utf-8');

$conn = Database::getProd();

echo "=== ESTRUTURA TABELA proposal_block_templates ===\n\n";
$res = $conn->query("DESCRIBE proposal_block_templates");
while ($row = $res->fetch_assoc()) {
    print_r($row);
}
echo "\n";

echo "=== AMOSTRA DE DADOS (3 registros) ===\n\n";
$res = $conn->query("SELECT * FROM proposal_block_templates LIMIT 3");
while ($row = $res->fetch_assoc()) {
    print_r($row);
}

echo "\n=== FIM ===\n";
?>
