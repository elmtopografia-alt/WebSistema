<?php
require_once 'db.php';
header('Content-Type: text/plain; charset=utf-8');

$conn = Database::getProd();

echo "=== MAPA DE SERVIÇOS ===\n\n";
$res = $conn->query("SELECT id, nome FROM Tipo_Servicos ORDER BY id");
while ($row = $res->fetch_assoc()) {
    echo $row['id'] . " - " . $row['nome'] . "\n";
}

echo "\n=== FIM ===\n";
?>
