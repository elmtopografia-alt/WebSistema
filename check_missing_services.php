<?php
require_once 'config.php';
require_once 'db.php';

$conn = Database::getProd();

echo "<h1>Verificação de Blocos por Serviço</h1>";

// 1. Get All Services
$services = [];
$res = $conn->query("SELECT id_servico, nome FROM Tipo_Servicos ORDER BY id_servico");
while ($row = $res->fetch_assoc()) {
    $services[$row['id_servico']] = $row['nome'];
}

// 2. Count Blocks per Service
$counts = [];
$res = $conn->query("SELECT service_type_id, COUNT(*) as qtd FROM service_type_blocks GROUP BY service_type_id");
while ($row = $res->fetch_assoc()) {
    $counts[$row['service_type_id']] = $row['qtd'];
}

echo "<table border='1' cellpadding='5'>";
echo "<tr><th>ID</th><th>Nome do Serviço</th><th>Blocos Configurados</th><th>Status</th></tr>";

foreach ($services as $id => $nome) {
    $qtd = $counts[$id] ?? 0;
    $status = ($qtd > 0) ? "<span style='color:green'>OK ($qtd)</span>" : "<span style='color:red'>FALTANDO</span>";
    echo "<tr><td>$id</td><td>$nome</td><td>$qtd</td><td>$status</td></tr>";
}
echo "</table>";
?>
