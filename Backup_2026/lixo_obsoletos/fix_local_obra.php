<?php
/**
 * fix_local_obra.php - Restaura o bloco "Local da Obra" no editor dinâmico
 * 
 * Problema: O bloco 'local_obra' foi deletado da tabela service_type_blocks,
 * fazendo com que o editor dinâmico não mostre os campos de endereço.
 * 
 * Ação 1: Reinsere o bloco 'local_obra' para TODOS os service_type_ids ativos.
 * Ação 2: Atualiza o texto da Metodologia (Etapa 1) para Drone (id=19), 
 *          corrigindo as variáveis ${endereco_obra}, ${cidade_obra}, etc.
 * 
 * EXECUTAR UMA VEZ e depois APAGAR este arquivo.
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once 'db.php';
$conn = Database::getProd();

echo "<h1 style='font-family:monospace;'>Fix Local da Obra</h1>";
echo "<pre>";

// =====================================================
// AÇÃO 1: Restaurar bloco 'local_obra' para todos os serviços
// =====================================================
echo "\n=== AÇÃO 1: Restaurar bloco 'local_obra' ===\n\n";

// Verifica se já existe
$check = $conn->query("SELECT id, service_type_id FROM service_type_blocks WHERE block_slug = 'local_obra'");
if ($check && $check->num_rows > 0) {
    echo "⚠️ Bloco 'local_obra' já existe para os seguintes service_type_ids:\n";
    while ($r = $check->fetch_assoc()) {
        echo "   - service_type_id = {$r['service_type_id']} (id={$r['id']})\n";
    }
    echo "\nNenhuma inserção necessária para estes.\n";
} else {
    echo "❌ Bloco 'local_obra' NÃO encontrado. Inserindo...\n\n";
}

// Pega todos os service_type_ids ativos
$res = $conn->query("SELECT DISTINCT service_type_id FROM service_type_blocks WHERE is_active = 1 ORDER BY service_type_id");
$serviceIds = [];
while ($row = $res->fetch_assoc()) {
    $serviceIds[] = (int)$row['service_type_id'];
}

echo "Service IDs ativos encontrados: " . implode(', ', $serviceIds) . "\n\n";

$allowedVars = json_encode(['endereco_obra', 'bairro_obra', 'cidade_obra', 'estado_obra', 'area_obra', 'unidade_area']);

$inseridos = 0;
$jaExiste = 0;

foreach ($serviceIds as $sid) {
    // Verifica se já existe para este service_type_id
    $exists = $conn->query("SELECT id FROM service_type_blocks WHERE block_slug = 'local_obra' AND service_type_id = $sid");
    if ($exists && $exists->num_rows > 0) {
        $jaExiste++;
        continue;
    }

    // Pega o display_order do bloco 'dados_cliente' para inserir logo depois
    $orderRes = $conn->query("SELECT display_order FROM service_type_blocks WHERE block_slug = 'dados_cliente' AND service_type_id = $sid LIMIT 1");
    $orderRow = $orderRes ? $orderRes->fetch_assoc() : null;
    $displayOrder = $orderRow ? ((int)$orderRow['display_order'] + 1) : 2;

    // Ajusta a ordem dos blocos posteriores para abrir espaço
    $conn->query("UPDATE service_type_blocks SET display_order = display_order + 1 WHERE service_type_id = $sid AND display_order >= $displayOrder");

    $stmt = $conn->prepare("INSERT INTO service_type_blocks 
        (service_type_id, block_slug, block_title, category, display_order, is_required, default_content, allowed_vars, is_active) 
        VALUES (?, 'local_obra', 'Local da Obra', 'presentation', ?, 1, '', ?, 1)");
    $stmt->bind_param('iis', $sid, $displayOrder, $allowedVars);
    
    if ($stmt->execute()) {
        echo "✅ Inserido local_obra para service_type_id = $sid (order=$displayOrder)\n";
        $inseridos++;
    } else {
        echo "❌ ERRO ao inserir para service_type_id = $sid: {$conn->error}\n";
    }
    $stmt->close();
}

echo "\nResumo: $inseridos inseridos, $jaExiste já existiam.\n";

// =====================================================
// AÇÃO 2: Atualizar Metodologia Etapa 1 do Drone (id=19)
// =====================================================
echo "\n\n=== AÇÃO 2: Atualizar Metodologia Drone (Etapa 1) ===\n\n";

// Verifica o conteúdo atual
$res = $conn->query("SELECT id, block_slug, default_content FROM service_type_blocks WHERE service_type_id = 19 AND block_slug = 'metodologia'");

if ($res && $res->num_rows > 0) {
    $row = $res->fetch_assoc();
    echo "Bloco encontrado: id={$row['id']}, slug={$row['block_slug']}\n";
    echo "Conteúdo atual (primeiros 200 chars):\n";
    echo substr($row['default_content'], 0, 200) . "...\n\n";

    // Verifica se o texto da Etapa 1 tem as variáveis sem preenchimento correto
    // Corrige o texto para incluir ${endereco_obra} na análise de viabilidade
    $conteudo = $row['default_content'];
    
    // Padrão problemático: "Análise do local em , ${cidade_obra}" (faltando endereço)
    // Corrige para: "Análise do local em ${endereco_obra}, ${bairro_obra}, ${cidade_obra} - ${estado_obra}"
    $conteudo = preg_replace(
        '/Análise do local em\s*,?\s*\$\{cidade_obra\}\s*-\s*\$\{estado_obra\}/',
        'Análise do local em ${endereco_obra}, ${bairro_obra}, ${cidade_obra} - ${estado_obra}',
        $conteudo
    );
    
    // Também corrige se estiver como "Análise do local em , ${cidade_obra} - ${estado_obra},"
    $conteudo = preg_replace(
        '/Análise do local em\s*,\s*\$\{cidade_obra\}\s*-\s*\$\{estado_obra\}\s*,\s*-/',
        'Análise do local em ${endereco_obra}, ${bairro_obra}, ${cidade_obra} - ${estado_obra} -',
        $conteudo
    );

    $stmt = $conn->prepare("UPDATE service_type_blocks SET default_content = ? WHERE id = ?");
    $stmt->bind_param('si', $conteudo, $row['id']);
    
    if ($stmt->execute()) {
        echo "✅ Metodologia do Drone atualizada!\n";
        echo "Novo conteúdo (primeiros 300 chars):\n";
        echo substr($conteudo, 0, 300) . "...\n";
    } else {
        echo "❌ ERRO ao atualizar: {$conn->error}\n";
    }
    $stmt->close();
} else {
    echo "⚠️ Bloco 'metodologia' não encontrado para service_type_id=19 (Drone).\n";
    echo "Verifique se o Drone está cadastrado com id_servico=19.\n";
}

// =====================================================
// VERIFICAÇÃO FINAL
// =====================================================
echo "\n\n=== VERIFICAÇÃO FINAL ===\n\n";

$verify = $conn->query("SELECT service_type_id, block_slug, block_title, display_order FROM service_type_blocks WHERE block_slug = 'local_obra' ORDER BY service_type_id");
if ($verify && $verify->num_rows > 0) {
    echo "Blocos 'local_obra' ativos:\n";
    while ($v = $verify->fetch_assoc()) {
        echo "  ✅ Service ID {$v['service_type_id']}: \"{$v['block_title']}\" (order={$v['display_order']})\n";
    }
} else {
    echo "❌ NENHUM bloco 'local_obra' encontrado após a correção!\n";
}

echo "\n\n=== CONCLUÍDO ===\n";
echo "Acesse o editor_dinamico.php para verificar se o bloco 'Local da Obra' aparece.\n";
echo "⚠️ APAGUE ESTE ARQUIVO após confirmar o funcionamento.\n";
echo "</pre>";
?>
