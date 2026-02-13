<?php
require_once 'db.php';
header('Content-Type: text/plain; charset=utf-8');
$conn = Database::getProd();
$res = $conn->query("DESCRIBE service_type_blocks");
while($row = $res->fetch_assoc()) {
    echo $row['Field'] . " (" . $row['Type'] . ")\n";
}
echo "\n=== DADOS ===\n";
$res = $conn->query("SELECT * FROM service_type_blocks LIMIT 10");
while($row = $res->fetch_assoc()) {
    print_r($row);
}
?>
