<?php
require_once __DIR__ . '/ConnectionManager.php';
$conn = ConnectionManager::get();

$res = $conn->query("SHOW COLUMNS FROM Propostas");
$cols = [];
while($row = $res->fetch_assoc()) {
    $cols[] = $row['Field'];
}

file_put_contents(__DIR__ . '/colunas_reais.txt', implode("\n", $cols));
echo "Colunas listadas em colunas_reais.txt";
?>
