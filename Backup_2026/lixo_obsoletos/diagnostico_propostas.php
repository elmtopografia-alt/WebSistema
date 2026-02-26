<?php
require_once 'db.php';
header('Content-Type: text/plain; charset=utf-8');
$conn = Database::getProd();

echo "=== ÚLTIMAS 5 PROPOSTAS ===\n";
$res = $conn->query("SELECT id_proposta, id_servico, numero_proposta, data_criacao FROM Propostas ORDER BY id_proposta DESC LIMIT 5");
while($row = $res->fetch_assoc()) {
    $id_serv = $row['id_servico'];
    echo "ID: {$row['id_proposta']} | Serviço: {$id_serv} | Número: {$row['numero_proposta']} | Data: {$row['data_criacao']}\n";
    
    // Verifica blocos para este serviço
    $resB = $conn->query("SELECT COUNT(*) as qtd FROM service_type_blocks WHERE service_type_id = $id_serv");
    $qtd = $resB ? $resB->fetch_assoc()['qtd'] : 0;
    echo "  -> Blocos em service_type_blocks: $qtd\n";
}

echo "\n=== CONTEÚDO GENÉRICO (Fallback) ===\n";
$resF = $conn->query("SELECT COUNT(*) as qtd FROM proposal_block_templates");
$qtdF = $resF ? $resF->fetch_assoc()['qtd'] : 0;
echo "Total em proposal_block_templates: $qtdF\n";

if ($qtdF > 0) {
    echo "Exemplo de blocos (Fallack):\n";
    $resEx = $conn->query("SELECT slug, category FROM proposal_block_templates LIMIT 3");
    while($ex = $resEx->fetch_assoc()) echo "  - {$ex['slug']} ({$ex['category']})\n";
}
