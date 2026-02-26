<?php
require_once 'db.php';

$conn = Database::getProd();
$result = $conn->query("SHOW COLUMNS FROM Propostas");

$columns = [];
while ($row = $result->fetch_assoc()) {
    $columns[] = $row['Field'];
}

$required = [
    'marca_veiculo', 'modelo_veiculo',
    'marca_estacao_total', 'modelo_estacao_total',
    'marca_gps', 'modelo_gps',
    'marca_drone', 'modelo_drone'
];

$missing = [];
foreach ($required as $req) {
    if (!in_array($req, $columns)) {
        $missing[] = $req;
    }
}

echo "Missing columns: " . implode(', ', $missing);
?>
