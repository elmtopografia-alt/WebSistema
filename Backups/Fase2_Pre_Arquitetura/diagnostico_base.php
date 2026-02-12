<?php
require_once 'db.php';

header('Content-Type: text/plain');

echo "=== DIAGNÓSTICO DO BANCO DE DADOS ===\n\n";

try {
    $conn = Database::getProd(); // Usando Produção por padrão

    // 1. Verificar Colunas das Tabelas Relevantes
    $tables = ['proposal_block_templates', 'proposal_content_variations', 'service_types'];
    
    foreach ($tables as $table) {
        echo ">>> Estrutura da tabela '$table':\n";
        try {
            $res = $conn->query("DESCRIBE $table");
            if ($res) {
                while ($row = $res->fetch_assoc()) {
                    echo " - " . $row['Field'] . " (" . $row['Type'] . ")\n";
                }
            } else {
                echo " [Tabela não encontrada ou erro: " . $conn->error . "]\n";
            }
        } catch (Exception $e) {
            echo " [Erro ao descrever tabela: " . $e->getMessage() . "]\n";
        }
        echo "\n";
    }

    // 2. Contar id_servico diferentes (Tabela Tipo_Servicos)
    echo ">>> Tipos de Serviço (Tipo_Servicos):\n";
    try {
        $res = $conn->query("SELECT * FROM Tipo_Servicos");
        if ($res && $res->num_rows > 0) {
            echo " TOTAL: " . $res->num_rows . "\n";
            while ($row = $res->fetch_assoc()) {
                echo " ID: " . ($row['id_servico'] ?? '?') . " | Nome: " . ($row['nome'] ?? '?') . "\n";
            }
        } else {
            echo " Tabela 'Tipo_Servicos' vazia ou erro.\n";
        }
    } catch (Exception $e) {
        echo " Erro ao ler Tipo_Servicos: " . $e->getMessage() . "\n";
    }
    echo "\n";

    // 3. Verificar distribuição em proposal_block_templates (CONFIRMADO: GENÉRICO)
    echo ">>> Distribuição de Blocos (proposal_block_templates):\n";
    $res = $conn->query("SELECT slug, name, category, `order` FROM proposal_block_templates ORDER BY `order`");
    if ($res) {
        $count = 0;
        echo " Lista de Blocos Cadastrados:\n";
        while ($row = $res->fetch_assoc()) {
            $count++;
            echo "  - [{$row['order']}] {$row['name']} (slug: {$row['slug']})\n";
        }
        echo " TOTAL BLOCOS: $count\n";
    }
    echo "\n";

    // 4. Verificar Variações de Conteúdo (proposal_content_variations)
    echo ">>> Variações de Conteúdo (proposal_content_variations):\n";
    echo " Aqui veremos se existe conteúdo específico (ex: Drone) ou apenas genérico.\n";
    
    $sql = "SELECT block_slug, variation_name, COUNT(*) as qtd, MIN(content_text) as exemplo 
            FROM proposal_content_variations 
            GROUP BY block_slug, variation_name";
            
    $res = $conn->query($sql);
    if ($res && $res->num_rows > 0) {
        while ($row = $res->fetch_assoc()) {
            $exemplo = mb_substr(strip_tags($row['exemplo']), 0, 60) . "...";
            echo "  Bloco: " . str_pad($row['block_slug'], 20) . 
                 " | Variação: " . str_pad($row['variation_name'], 20) . 
                 " | Qtd: " . $row['qtd'] . 
                 " | Ex: $exemplo\n";
        }
    } else {
        echo "  NENHUMA variação encontrada. Tabela vazia?\n";
    }
    
    // Checagem Específica para "Drone"
    echo "\n>>> Buscando explicitamente por conteúdo 'Drone' no texto:\n";
    $sqlDrone = "SELECT variation_name, block_slug FROM proposal_content_variations WHERE content_text LIKE '%Drone%' OR variation_name LIKE '%Drone%'";
    $resDrone = $conn->query($sqlDrone);
    if ($resDrone && $resDrone->num_rows > 0) {
        echo "  ENCONTRADO CONTEÚDO DRONE:\n";
        while($d = $resDrone->fetch_assoc()) {
            echo "   - No bloco '{$d['block_slug']}' (Variação: {$d['variation_name']})\n";
        }
    } else {
        echo "  NENHUM conteúdo com a palavra 'Drone' encontrado nos textos.\n";
    }

} catch (Exception $e) {
    echo "ERRO GERAL: " . $e->getMessage();
}
