<?php
require_once __DIR__ . '/ConnectionManager.php';
$conn = ConnectionManager::get();

$cols_to_drop = ['marca_veiculo', 'marca_estacao_total', 'marca_gps', 'marca_drone'];

echo "=== REMOVENDO COLUNAS REDUNDANTES ===\n";

foreach ($cols_to_drop as $col) {
    echo "Limpando '$col'... ";
    if ($conn->query("ALTER TABLE Propostas DROP COLUMN $col")) {
        echo "OK\n";
    } else {
        echo "ERRO: " . $conn->error . "\n";
    }
}

echo "\nConcluído.";
?>
