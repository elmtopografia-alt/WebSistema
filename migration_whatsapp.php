<?php
require 'conexao.php';
require 'db.php'; // Ensure Database class is loaded

$conn = Database::getProd();

// Check if column exists
$check = $conn->query("SHOW COLUMNS FROM Clientes LIKE 'whatsapp_handle'");
if ($check->num_rows === 0) {
    echo "Adding whatsapp_handle column... ";
    if ($conn->query("ALTER TABLE Clientes ADD COLUMN whatsapp_handle VARCHAR(100) AFTER whatsapp")) {
        echo "SUCCESS!";
    } else {
        echo "ERROR: " . $conn->error;
    }
} else {
    echo "Column whatsapp_handle already exists.";
}
?>
