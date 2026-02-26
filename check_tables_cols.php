<?php
require_once __DIR__ . '/ConnectionManager.php';
$conn = ConnectionManager::get();

function checkCols($conn, $table) {
    echo "<h3>Tabela: $table</h3>";
    $res = $conn->query("SHOW COLUMNS FROM $table");
    if (!$res) {
        echo "Erro ao ler $table: " . $conn->error;
        return;
    }
    echo "<ul>";
    while($row = $res->fetch_assoc()) {
        echo "<li>{$row['Field']}</li>";
    }
    echo "</ul>";
}

checkCols($conn, 'Clientes');
checkCols($conn, 'Tipo_Servicos');
checkCols($conn, 'DadosEmpresa');
checkCols($conn, 'Propostas');
checkCols($conn, 'Proposta_Locacao');
checkCols($conn, 'Marcas');
checkCols($conn, 'Tipo_Locacao');
?>
