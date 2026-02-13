<?php
require_once 'db.php';
header('Content-Type: text/plain; charset=utf-8');
$conn = Database::getProd();
$res = $conn->query("DESCRIBE proposal_content_variations");
while($row = $res->fetch_assoc()) {
    echo $row['Field'] . " (" . $row['Type'] . ")\n";
}
?>
