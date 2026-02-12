<?php
require_once 'config.php';
require_once 'db.php';

$conn = Database::getProd();

// Check if column exists
$check = $conn->query("SHOW COLUMNS FROM Propostas LIKE 'empresa_cliente_salvo'");

if ($check->num_rows == 0) {
    $sql = "ALTER TABLE Propostas ADD COLUMN empresa_cliente_salvo VARCHAR(255) DEFAULT NULL AFTER nome_cliente_salvo";
    if ($conn->query($sql)) {
        echo "<div style='color:green; font-weight:bold;'>Sucesso: Coluna 'empresa_cliente_salvo' adicionada!</div>";
    } else {
        echo "<div style='color:red;'>Erro ao adicionar coluna: " . $conn->error . "</div>";
    }
} else {
    echo "<div style='color:blue;'>Aviso: Coluna 'empresa_cliente_salvo' já existe.</div>";
}
?>
