<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/PropostaRepository.php';

$id_prop = (int)($_GET['id'] ?? 4); // Use a default ID for testing if needed

try {
    $repo = new PropostaRepository();
    $data = $repo->buscarPorId($id_prop);
    
    if (!$data) {
        die("Proposta $id_prop não encontrada.");
    }

    $serviceTypeId = (int)$data['id_servico'];
    echo "ID Proposta: $id_prop\n";
    echo "ID Serviço: $serviceTypeId\n";
    echo "Nome Serviço: " . ($data['nome_servico'] ?? 'N/A') . "\n";

    $loader = new ProposalArchitect\Infrastructure\DatabaseStructureLoader($repo->getConn());
    $model = $loader->getVirtualModel($serviceTypeId);
    
    $blocks = $model->getOrderedBlocks();
    echo "Total de Blocos (linear): " . count($blocks) . "\n";
    foreach ($blocks as $b) {
        echo "- [{$b->category}] {$b->id}: {$b->name} (Level: {$b->level})\n";
    }

    $treeBuilder = new ProposalArchitect\Infrastructure\HierarchyTreeBuilder();
    $structure = $treeBuilder->build($model);
    echo "Total de Nós Raiz na Árvore: " . count($structure) . "\n";

} catch (Exception $e) {
    echo "ERRO: " . $e->getMessage() . "\n";
}
