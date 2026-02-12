<?php
// migrar_templates.php
// Este script lê os blocos do Código (CorporativoPremiumModel) e salva no Banco de Dados.
// Versão corrigida para PHP 5.6 (Sem Enums/Objetos complexos)

require_once 'vendor/autoload.php';

use ProposalArchitect\Models\CorporativoPremiumModel;
use ProposalArchitect\Models\BlockDefinition;

// --- Configuração Rápida de Conexão (Para o script funcionar isolado) ---
$host = 'localhost';
$db   = 'demanda';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

try {
    echo "<h1>Gerador de SQL para Carga Inicial</h1>";
    echo "<p>Copie e rode estes comandos no seu banco (phpMyAdmin ou WorkBench).</p>";
    echo "<textarea style='width:100%; height:400px; font-family:monospace;'>";

    $model = new CorporativoPremiumModel();

    // Acessar a propriedade protegida via Reflection (hack para migração)
    $reflection = new ReflectionClass($model);
    $property = $reflection->getProperty('structuralSequence');
    $property->setAccessible(true);
    $blocks = $property->getValue($model);

    echo "-- Carga Inicial de Blocos (Baseado no CorporativoPremiumModel)\n";
    echo "TRUNCATE TABLE proposal_block_templates;\n\n";

    foreach ($blocks as $block) {
        insertBlockRecursive($block);
    }

    echo "</textarea>";
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}

function insertBlockRecursive($block)
{
    // Escapar strings para SQL
    $slug = addslashes($block->id);
    $name = addslashes($block->name);

    // CORREÇÃO: category e level agora são strings, não objetos
    $category = $block->category;
    $levelName = $block->level;

    // Prepara o JSON padrão
    $defaultContent = [
        'is_required' => $block->isRequired,
        'level' => $levelName,
        'allowed_vars' => $block->requiredVars
    ];
    $json = addslashes(json_encode($defaultContent, JSON_UNESCAPED_UNICODE));

    $sql = "INSERT INTO proposal_block_templates (slug, name, category, default_content_json, is_active) ";
    $sql .= "VALUES ('$slug', '$name', '$category', '$json', 1);\n";

    echo $sql;

    // Processar filhos (recursive)
    if (!empty($block->children)) {
        foreach ($block->children as $child) {
            insertBlockRecursive($child);
        }
    }
}
