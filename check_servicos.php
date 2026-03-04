<?php
require_once 'ConnectionManager.php';
$conn = ConnectionManager::get();
$res = $conn->query("DESCRIBE Tipo_Servicos");
while($row = $res->fetch_assoc()) {
    echo $row['Field'] . " - " . $row['Type'] . "\n";
}
echo "----\n";
$res = $conn->query("SELECT * FROM Tipo_Servicos LIMIT 5");
while($row = $res->fetch_assoc()) {
    print_r($row);
}
