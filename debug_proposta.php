<?php
require_once 'config.php';
require_once 'db.php';

$conn = Database::getProd();

$numero = 'GEOMETRPOLE-2026-018';

echo "<h2>Debug Proposta: $numero</h2>";

// Busca proposta
$sql = "SELECT 
    numero_proposta,
    nome_cliente_salvo,
    total_custos_salarios,
    total_custos_estadia, 
    total_custos_consumos,
    total_custos_locacao,
    total_custos_admin,
    percentual_lucro,
    valor_lucro,
    subtotal_com_lucro,
    valor_desconto,
    valor_final_proposta
FROM Propostas 
WHERE numero_proposta = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $numero);
$stmt->execute();
$result = $stmt->get_result();
$prop = $result->fetch_assoc();

if (!$prop) {
    die("Proposta não encontrada!");
}

echo "<h3>Valores no Banco:</h3>";
echo "<table border='1' cellpadding='5'>";
foreach ($prop as $campo => $valor) {
    echo "<tr><td><strong>$campo</strong></td><td>$valor</td></tr>";
}
echo "</table>";

// Cálculo manual
$totalCustos = 
    $prop['total_custos_salarios'] + 
    $prop['total_custos_estadia'] + 
    $prop['total_custos_consumos'] + 
    $prop['total_custos_locacao'] + 
    $prop['total_custos_admin'];

$lucro = $totalCustos * ($prop['percentual_lucro'] / 100);
$subtotal = $totalCustos + $lucro;
$valorFinal = $subtotal - $prop['valor_desconto'];

echo "<h3>Cálculo Esperado:</h3>";
echo "<pre>";
echo "Total Custos: R$ " . number_format($totalCustos, 2, ',', '.') . "\n";
echo "Lucro ({$prop['percentual_lucro']}%): R$ " . number_format($lucro, 2, ',', '.') . "\n";
echo "Subtotal: R$ " . number_format($subtotal, 2, ',', '.') . "\n";
echo "Desconto: R$ " . number_format($prop['valor_desconto'], 2, ',', '.') . "\n";
echo "<strong>Valor Final: R$ " . number_format($valorFinal, 2, ',', '.') . "</strong>\n";
echo "</pre>";

if ($valorFinal != $prop['valor_final_proposta']) {
    echo "<div style='color: red; font-weight: bold;'>⚠️ DIVERGÊNCIA! Valor no banco não bate com cálculo!</div>";
}

// Busca itens de custo
echo "<h3>Itens de Custo:</h3>";
$tabelas = [
    'Proposta_Salarios' => 'Salários/Equipe',
    'Proposta_Estadia' => 'Estadia', 
    'Proposta_Consumos' => 'Combustível',
    'Proposta_Locacao' => 'Equipamentos',
    'Proposta_Custos_Administrativos' => 'Admin'
];

foreach ($tabelas as $tabela => $nome) {
    $sqlItens = "SELECT * FROM $tabela WHERE id_proposta = (SELECT id_proposta FROM Propostas WHERE numero_proposta = ?)";
    $stmtItens = $conn->prepare($sqlItens);
    $stmtItens->bind_param('s', $numero);
    $stmtItens->execute();
    $resultItens = $stmtItens->get_result();
    
    if ($resultItens->num_rows > 0) {
        echo "<h4>$nome:</h4>";
        echo "<table border='1' cellpadding='5'>";
        echo "<tr>";
        $first = true;
        while ($row = $resultItens->fetch_assoc()) {
            if ($first) {
                echo "<tr>";
                foreach (array_keys($row) as $col) {
                    echo "<th>$col</th>";
                }
                echo "</tr>";
                $first = false;
            }
            echo "<tr>";
            foreach ($row as $val) {
                echo "<td>$val</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p><em>Nenhum item em $nome</em></p>";
    }
}
?>
