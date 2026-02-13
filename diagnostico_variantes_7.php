<?php
require_once 'db.php';
header('Content-Type: text/plain; charset=utf-8');

$conn = Database::getProd();

echo "=== VARIANTES PARA ID_SERVICO 7 (Usucapião) ===\n\n";
$res = $conn->query("SELECT * FROM proposal_content_variations WHERE service_type_id = 7");
while ($row = $res->fetch_assoc()) {
    print_r($row);
}
if ($res->num_rows == 0) echo "NENHUMA VARIANTE ENCONTRADA\n";

echo "\n=== FIM ===\n";
?>
