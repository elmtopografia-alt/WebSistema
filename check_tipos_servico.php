<?php
require_once 'ConnectionManager.php';
$conn = ConnectionManager::get();
$res = $conn->query("DESCRIBE tipos_servico");
if ($res) {
    while($row = $res->fetch_assoc()) {
        echo $row['Field'] . " - " . $row['Type'] . "\n";
    }
    echo "----\n";
    $res = $conn->query("SELECT * FROM tipos_servico LIMIT 5");
    while($row = $res->fetch_assoc()) {
        print_r($row);
    }
} else {
    echo "Tabela tipos_servico não encontrada.\n";
}
