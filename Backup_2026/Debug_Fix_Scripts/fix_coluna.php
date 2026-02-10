<?php
require_once 'db.php';
$conn = Database::getProd();

try {
    $sql = "ALTER TABLE Propostas MODIFY COLUMN numero_proposta VARCHAR(50) NOT NULL";
    if ($conn->query($sql) === TRUE) {
        echo "<h1>Sucesso!</h1><p>Coluna 'numero_proposta' aumentada para VARCHAR(50).</p>";
    } else {
        echo "<h1>Erro</h1><p>" . $conn->error . "</p>";
    }
} catch (Exception $e) {
    echo "<h1>Erro Fatal</h1><p>" . $e->getMessage() . "</p>";
}
?>
