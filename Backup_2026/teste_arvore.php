<?php
// teste_arvore.php
header('Content-Type: text/plain; charset=utf-8');
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once 'vendor/autoload.php';
require_once 'db.php'; // Conexão Oficial

use ProposalArchitect\Infrastructure\DatabaseStructureLoader;
use ProposalArchitect\Infrastructure\HierarchyTreeBuilder;

try {
    echo "=== Teste de Árvore (HierarchyTreeBuilder) ===\n\n";

    if (!isset($conn) || !($conn instanceof mysqli)) {
        $conn = Database::getProd();
    }

    // 1. Carregar Blocos
    echo "1. Carregando blocos...\n";
    $loader = new DatabaseStructureLoader($conn);
    $model = $loader->getVirtualModel();
    $blocks = $model->getOrderedBlocks();
    echo "Blocos carregados: " . count($blocks) . "\n";

    // 2. Construir Árvore
    echo "2. Construindo árvore...\n";
    $builder = new HierarchyTreeBuilder();
    $tree = $builder->build($model);

    echo "3. Árvore Construída com Sucesso!\n";
    echo "Total de nós raiz: " . count($tree) . "\n\n";

    function printTree($nodes, $depth = 0)
    {
        foreach ($nodes as $node) {
            echo str_repeat("  ", $depth) . "- " . $node['title'] . " (" . $node['level'] . ")\n";
            if (!empty($node['children'])) {
                printTree($node['children'], $depth + 1);
            }
        }
    }

    echo "--- Estrutura Visual ---\n";
    printTree($tree);
} catch (Exception $e) {
    echo "\n[ERRO CRÍTICO] " . $e->getMessage();
    echo "\n" . $e->getTraceAsString();
} catch (Error $e) {
    echo "\n[ERRO FATAL PHP 7+] " . $e->getMessage();
    echo "\n" . $e->getTraceAsString();
}
