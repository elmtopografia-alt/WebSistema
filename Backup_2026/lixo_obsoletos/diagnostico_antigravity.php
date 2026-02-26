<?php
require_once 'db.php';
header('Content-Type: text/plain; charset=utf-8');

function getColumns($conn, $table) {
    $columns = [];
    $res = $conn->query("SHOW COLUMNS FROM `$table`");
    while ($row = $res->fetch_assoc()) {
        $columns[$row['Field']] = $row;
    }
    return $columns;
}

$conn = Database::getProd();

echo "=== DIAGNÓSTICO DE PARIDADE (PROD vs DEMO) ===\n\n";

$tabelas = ['Propostas', 'Clientes', 'Tipo_Servicos', 'DadosEmpresa'];
$connProd = Database::getProd();
$connDemo = Database::getDemo();

foreach ($tabelas as $tab) {
    echo "--- Tabela: $tab ---\n";
    
    try {
        $colsProd = getColumns($connProd, $tab);
        $colsDemo = getColumns($connDemo, $tab);
        
        echo "Produção: " . count($colsProd) . " colunas.\n";
        echo "Demo:     " . count($colsDemo) . " colunas.\n";
        
        // 1. O que tem na Demo que NÃO tem na Produção?
        $faltandoNoProd = array_diff(array_keys($colsDemo), array_keys($colsProd));
        if (!empty($faltandoNoProd)) {
            echo "\n[ERRO] Colunas na Demo que FALTAM na Produção:\n";
            foreach ($faltandoNoProd as $c) {
                echo " - $c ({$colsDemo[$c]['Type']})\n";
            }
        } else {
            echo "\n[OK] Nenhuma coluna faltante na Produção.\n";
        }
        
        // 2. O que tem na Produção que NÃO tem na Demo?
        $faltandoNoDemo = array_diff(array_keys($colsProd), array_keys($colsDemo));
        if (!empty($faltandoNoDemo)) {
            echo "\n[AVISO] Colunas na Produção que não estão na Demo:\n";
            foreach ($faltandoNoDemo as $c) {
                echo " - $c ({$colsProd[$c]['Type']})\n";
            }
        }
        
        // 3. Tipos diferentes
        echo "\nVerificando tipos de dados...\n";
        foreach ($colsDemo as $col => $info) {
            if (isset($colsProd[$col])) {
                if ($info['Type'] !== $colsProd[$col]['Type']) {
                    echo " - Corrigir $col: Demo({$info['Type']}) vs Prod({$colsProd[$col]['Type']})\n";
                }
            }
        }
        
    } catch (Exception $e) {
        echo "Erro ao analisar '$tab': " . $e->getMessage() . "\n";
    }
    echo "\n----------------------------------------\n\n";
}

echo "=== FIM DO DIAGNÓSTICO ===";
?>
