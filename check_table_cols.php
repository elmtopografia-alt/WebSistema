<?php
require_once 'db.php';
$conn = Database::getProd();
$res = $conn->query("SHOW COLUMNS FROM service_type_blocks");
echo "<h1>Estrutura de service_type_blocks</h1><ul>";
while($row = $res->fetch_assoc()) {
    echo "<li>" . $row['Field'] . " (" . $row['Type'] . ")</li>";
}
echo "</ul>";
?>
