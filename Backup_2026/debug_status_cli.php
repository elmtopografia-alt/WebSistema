<?php
// debug_status_cli.php
require_once 'config.php';
require_once 'db.php';

// Mock session for DB connection if needed, though DB class usually handles it.
// Assuming Database::getProd() works without session if config is right.

$conn = Database::getProd();

echo "--- DIAGNÓSTICO DE STATUS NO BANCO ---\n";

$sql = "SELECT status, COUNT(*) as qtd, HEX(status) as hex_val FROM Propostas GROUP BY status";
$result = $conn->query($sql);

if ($result) {
    while ($row = $result->fetch_assoc()) {
        echo "Status: [" . $row['status'] . "] | Qtd: " . $row['qtd'] . " | Hex: " . $row['hex_val'] . "\n";
    }
} else {
    echo "Erro na query: " . $conn->error . "\n";
}
echo "--------------------------------------\n";
?>
