<?php
require_once 'db.php';
header('Content-Type: text/plain; charset=utf-8');

$conn = Database::getProd();

$tabelas = [
    'proposal_content_variations',
    'service_type_blocks',
    'proposal_structure_data'
];

foreach ($tabelas as $tabela) {
    echo "=== ESTRUTURA TABELA $tabela ===\n\n";
    $res = $conn->query("DESCRIBE $tabela");
    while ($row = $res->fetch_assoc()) {
        print_r($row);
    }
    echo "\n=== AMOSTRA DE DADOS (3 registros) ===\n\n";
    $res = $conn->query("SELECT * FROM $tabela LIMIT 3");
    while ($row = $res->fetch_assoc()) {
        print_r($row);
    }
    echo "\n\n";
}

echo "=== FIM ===\n";
?>
