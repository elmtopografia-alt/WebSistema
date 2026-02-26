<?php
require_once __DIR__ . '/db.php';
$conn = Database::getProd();

function checkTable($conn, $table) {
    echo "<h3>Estrutura da tabela: $table</h3>";
    $res = $conn->query("SHOW COLUMNS FROM $table");
    if (!$res) {
        echo "Erro ao ler tabela $table: " . $conn->error . "<br>";
        return;
    }
    echo "<table border='1'><tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    while ($row = $res->fetch_assoc()) {
        echo "<tr>";
        foreach ($row as $val) echo "<td>$val</td>";
        echo "</tr>";
    }
    echo "</table>";
}

checkTable($conn, 'Proposta_Locacao');
checkTable($conn, 'Marcas');
checkTable($conn, 'Tipo_Locacao');
?>
