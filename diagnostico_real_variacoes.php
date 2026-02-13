<?php
require_once 'db.php';
header('Content-Type: text/plain; charset=utf-8');

$conn = Database::getProd();

echo "=== CONTAGEM POR SERVIÇO NA TABELA proposal_content_variations ===\n\n";
$res = $conn->query("SELECT service_type_id, COUNT(*) as qtd FROM proposal_content_variations GROUP BY service_type_id");
while ($row = $res->fetch_assoc()) {
    print_r($row);
}
if ($res->num_rows == 0) echo "TABELA COMPLETAMENTE VAZIA\n";

echo "\n=== AMOSTRA DE DADOS (se houver) ===\n\n";
$res = $conn->query("SELECT * FROM proposal_content_variations LIMIT 5");
while ($row = $res->fetch_assoc()) {
    print_r($row);
}

echo "\n=== FIM ===\n";
?>
