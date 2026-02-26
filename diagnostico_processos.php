<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/ConnectionManager.php';
$conn = ConnectionManager::get();
$res = $conn->query("SHOW PROCESSLIST");
echo "<h1>MySQL Process List</h1><table border=1>";
while($row = $res->fetch_assoc()) {
    echo "<tr><td>" . implode("</td><td>", $row) . "</td></tr>";
}
echo "</table>";
