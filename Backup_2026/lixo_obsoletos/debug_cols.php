<?php
require 'db.php';
$conn = Database::getProd();
$result = $conn->query("SHOW COLUMNS FROM Proposta_Cronograma");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        echo $row['Field'] . "<br>";
    }
} else {
    echo "Erro: " . $conn->error;
}
?>
