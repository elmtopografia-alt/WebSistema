<?php
require_once 'db.php';
header('Content-Type: text/plain; charset=utf-8');

$id = 117;
$conn = Database::getProd();

echo "=== DIAGNÓSTICO PROPOSTA #$id ===\n\n";

// 1. Tabela Principal Propostas
echo "[1] Tabela Propostas:\n";
$res = $conn->query("SELECT * FROM Propostas WHERE id_proposta = $id");
if ($row = $res->fetch_assoc()) {
    print_r($row);
} else {
    echo "NÃO ENCONTRADA\n";
}
echo "\n";

// 2. Tabelas de Itens
$tabelas = [
    'Proposta_Salarios' => 'id_proposta',
    'Proposta_Estadia' => 'id_proposta',
    'Proposta_Consumos' => 'id_proposta',
    'Proposta_Locacao' => 'id_proposta',
    'Proposta_Custos_Administrativos' => 'id_proposta',
    'Proposta_Conteudo_Personalizado' => 'id_proposta'
];

foreach ($tabelas as $tabela => $coluna) {
    echo "[$tabela]:\n";
    $res = $conn->query("SELECT * FROM $tabela WHERE $coluna = $id");
    while ($row = $res->fetch_assoc()) {
        print_r($row);
    }
    if ($res->num_rows == 0) echo "SEM REGISTROS\n";
    echo "\n";
}

echo "=== FIM DO DIAGNÓSTICO ===\n";
?>
