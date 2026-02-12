<?php
require_once 'config.php';
require_once 'db.php';

$conn = Database::getProd();

echo "<h2>📋 Últimas 10 Propostas Salvas</h2>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    table { border-collapse: collapse; width: 100%; margin-top: 20px; }
    th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
    th { background-color: #2563eb; color: white; }
    tr:nth-child(even) { background-color: #f2f2f2; }
    tr:hover { background-color: #e3f2fd; }
    .negativo { color: red; font-weight: bold; }
    .positivo { color: green; font-weight: bold; }
</style>";

$sql = "SELECT 
    id_proposta,
    numero_proposta,
    nome_cliente_salvo,
    valor_final_proposta,
    total_custos_salarios,
    total_custos_estadia,
    total_custos_consumos,
    total_custos_locacao,
    total_custos_admin,
    percentual_lucro,
    valor_desconto,
    status,
    data_criacao
FROM Propostas 
ORDER BY id_proposta DESC 
LIMIT 10";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo "<table>";
    echo "<tr>
        <th>ID</th>
        <th>Número</th>
        <th>Cliente</th>
        <th>Total Custos</th>
        <th>Lucro %</th>
        <th>Desconto</th>
        <th>Valor Final</th>
        <th>Status</th>
        <th>Data</th>
    </tr>";
    
    while ($row = $result->fetch_assoc()) {
        $totalCustos = 
            $row['total_custos_salarios'] + 
            $row['total_custos_estadia'] + 
            $row['total_custos_consumos'] + 
            $row['total_custos_locacao'] + 
            $row['total_custos_admin'];
        
        $classe = $row['valor_final_proposta'] < 0 ? 'negativo' : 'positivo';
        
        echo "<tr>";
        echo "<td>{$row['id_proposta']}</td>";
        echo "<td><strong>{$row['numero_proposta']}</strong></td>";
        echo "<td>{$row['nome_cliente_salvo']}</td>";
        echo "<td>R$ " . number_format($totalCustos, 2, ',', '.') . "</td>";
        echo "<td>{$row['percentual_lucro']}%</td>";
        echo "<td>R$ " . number_format($row['valor_desconto'], 2, ',', '.') . "</td>";
        echo "<td class='$classe'>R$ " . number_format($row['valor_final_proposta'], 2, ',', '.') . "</td>";
        echo "<td>{$row['status']}</td>";
        echo "<td>" . date('d/m/Y H:i', strtotime($row['data_criacao'])) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<br><p><strong>Total de propostas:</strong> " . $result->num_rows . "</p>";
} else {
    echo "<p>Nenhuma proposta encontrada no banco!</p>";
}

echo "<br><hr><br>";
echo "<h3>🔍 Verificar Proposta Específica</h3>";
echo "<form method='GET'>";
echo "Número da Proposta: <input type='text' name='numero' placeholder='GEOMETRPOLE-2026-018' value='" . ($_GET['numero'] ?? '') . "'>";
echo " <button type='submit'>Buscar</button>";
echo "</form>";

if (isset($_GET['numero']) && !empty($_GET['numero'])) {
    $numero = $_GET['numero'];
    
    $stmt = $conn->prepare("SELECT * FROM Propostas WHERE numero_proposta = ?");
    $stmt->bind_param('s', $numero);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $prop = $result->fetch_assoc();
        echo "<h4>Detalhes: {$prop['numero_proposta']}</h4>";
        echo "<table>";
        foreach ($prop as $campo => $valor) {
            echo "<tr><td><strong>$campo</strong></td><td>$valor</td></tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: red;'>Proposta não encontrada!</p>";
    }
}
?>
