<?php
require_once 'db.php';
$conn = Database::getProd();
$res = $conn->query("DESCRIBE Propostas");
echo "<pre>";
while ($row = $res->fetch_assoc()) {
    print_r($row);
}
echo "</pre>";
