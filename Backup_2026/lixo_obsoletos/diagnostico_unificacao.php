<?php
require 'db.php';
header('Content-Type: text/plain; charset=utf-8');

$conn = Database::getProd();

echo "=== DIAGNÓSTICO DE UNIFICAÇÃO (Demanda vs Proposta) ===\n\n";

// Listar todas as tabelas para conferência
echo "--- Tabelas Existentes no Banco ---\n";
$resTables = $conn->query("SHOW TABLES");
$todas_tabelas = [];
while ($row = $resTables->fetch_array()) {
    $todas_tabelas[] = $row[0];
    echo " - " . $row[0] . "\n";
}
echo "\n";

function getColumns($conn, $table) {
    $columns = [];
    $res = $conn->query("SHOW COLUMNS FROM `$table`");
    if (!$res) return [];
    while ($row = $res->fetch_assoc()) {
        $columns[$row['Field']] = $row;
    }
    return $columns;
}

// Tentar identificar os nomes corretos (plural/singular/caixa)
$tabDemanda = in_array('Demandas', $todas_tabelas) ? 'Demandas' : (in_array('demanda', $todas_tabelas) ? 'demanda' : null);
$tabProposta = in_array('Propostas', $todas_tabelas) ? 'Propostas' : (in_array('proposta', $todas_tabelas) ? 'proposta' : null);

if (!$tabDemanda || !$tabProposta) {
    echo "ERRO: Não foi possível identificar as tabelas de Demanda ou Proposta.\n";
    echo "Por favor, verifique a lista acima.\n";
    exit;
}

echo "Usando tabelas: '$tabDemanda' e '$tabProposta'\n\n";

$estruturas = [
    'demanda' => getColumns($conn, $tabDemanda),
    'proposta' => getColumns($conn, $tabProposta)
];

echo "Tabela '$tabDemanda': " . count($estruturas['demanda']) . " colunas.\n";
echo "Tabela '$tabProposta': " . count($estruturas['proposta']) . " colunas.\n\n";

echo "--- Colunas em '$tabDemanda' que NÃO estão em '$tabProposta' ---\n";
$faltando_proposta = 0;
foreach ($estruturas['demanda'] as $col => $info) {
    if (!isset($estruturas['proposta'][$col])) {
        echo " - $col ({$info['Type']})\n";
        $faltando_proposta++;
    }
}
if ($faltando_proposta == 0) echo " (Nenhuma)\n";

echo "\n--- Colunas em '$tabProposta' que NÃO estão em '$tabDemanda' ---\n";
$faltando_demanda = 0;
foreach ($estruturas['proposta'] as $col => $info) {
    if (!isset($estruturas['demanda'][$col])) {
        echo " - $col ({$info['Type']})\n";
        $faltando_demanda++;
    }
}
if ($faltando_demanda == 0) echo " (Nenhuma)\n";

echo "\n=== FIM DO DIAGNÓSTICO ===\n";
?>
