<?php
require_once 'db.php';
$conn = Database::getProd();
$result = $conn->query("DESCRIBE Propostas");
echo "<pre>";
while($row = $result->fetch_assoc()) {
    if ($row['Field'] == 'numero_proposta') {
        echo "<b>" . $row['Field'] . "</b>: " . $row['Type'] . "\n";
    }
}
echo "</pre>";
?>
