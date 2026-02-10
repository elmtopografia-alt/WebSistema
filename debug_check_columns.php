<?php
require_once 'db.php';
$conn = Database::getProd();
$res = $conn->query("SHOW COLUMNS FROM Propostas");
while ($row = $res->fetch_assoc()) {
    echo $row['Field'] . "\n";
}
?>
