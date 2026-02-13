<?php
require_once 'db.php';
header('Content-Type: text/plain; charset=utf-8');
$conn = Database::getProd();
echo "=== CONTAGEM POR SERVIÇO EM service_type_blocks ===\n\n";
$res = $conn->query("SELECT service_type_id, COUNT(*) as qtd FROM service_type_blocks GROUP BY service_type_id");
while($row = $res->fetch_assoc()) {
    echo "ID " . $row['service_type_id'] . ": " . $row['qtd'] . " blocos\n";
}
?>
