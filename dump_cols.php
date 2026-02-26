<?php
require_once __DIR__ . '/ConnectionManager.php';
$conn = ConnectionManager::get();
$res = $conn->query("SHOW COLUMNS FROM Propostas");
while($row = $res->fetch_assoc()) {
    echo $row['Field'] . "\n";
}
?>
