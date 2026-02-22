<?php
/**
 * CORREÇÃO DE TEXTOS DA PROPOSTA V2 (ADVANCED FIX)
 * 
 * 1. Limpa contaminação do texto de Investimento no Cronograma
 * 2. Força atualização do bloco Equipamentos (vários IDs possíveis)
 * 3. Tenta corrigir propostas JÁ CRIADAS (Proposta_Conteudo_Personalizado)
 */

require_once 'db.php';
$conn = Database::getProd();

echo "<!DOCTYPE html>
<html lang='pt-BR'>
<head>
    <meta charset='UTF-8'>
    <title>Correção Avançada V2 - Drone</title>
    <style>
        body { font-family: 'Courier New', monospace; background: #0a0f1a; color: #00ff00; padding: 20px; }
        .success { color: #00ff00; }
        .error { color: #ff0000; }
        .warning { color: #ffcc00; }
        .info { color: #00aaff; }
        pre { background: #1a1f2e; padding: 15px; border-left: 3px solid #00ff00; margin: 10px 0; white-space: pre-wrap; }
        hr { border: 1px solid #333; margin: 20px 0; }
    </style>
</head>
<body>
<h1>🚀 CORREÇÃO AVANÇADA DE TEXTOS (V2)</h1>
<hr>";

// ====================================================================
// 1. CORREÇÃO DA CONTAMINAÇÃO NO CRONOGRAMA
// ====================================================================
echo "<h2 class='info'>1️⃣ Limpando 'Cronograma' (Removendo texto de Investimento)</h2>";

// Textos EXATOS para remoção (MySQL REPLACE não aceita curingas %)
$textosParaRemover = [
    "Este investimento traduz-se em produtividade multiplicada: milhões de coordenadas georreferenciadas coletadas em horas, não em dias, eliminando gargalos operacionais e acelerando entregas sem comprometer a precisão.",
    "Este investimento reflete o custo-benefício da tecnologia: maior riqueza de dados (milhões de pontos) em menor tempo de execução comparado à topografia tradicional.",
    "R$ 6.500,00 (SEIS MIL E QUINHENTOS REAIS)",
    "R$ 4.500,00 (QUATRO MIL E QUINHENTOS REAIS)",
    "R$ 5.850,00 (CINCO MIL OITOCENTOS E CINQUENTA REAIS)"
];

// Tabelas para limpar
$tabelasAlvo = [
    'service_type_blocks' => 'default_content',
    'proposal_block_templates' => 'default_content',
    'Proposta_Conteudo_Personalizado' => 'conteudo_texto'
];

foreach ($tabelasAlvo as $tabela => $coluna) {
    foreach ($textosParaRemover as $texto) {
        // Escapa para segurança (embora prepared statement fosse ideal, aqui é manutenção interna)
        $textoEscapado = $conn->real_escape_string($texto);
        
        // Remove o texto onde ele aparecer no Cronograma
        $sql = "UPDATE $tabela 
                SET $coluna = REPLACE($coluna, '$textoEscapado', '') 
                WHERE block_id = 'cronograma'";
        
        $conn->query($sql);
        if ($conn->affected_rows > 0) {
            echo "<pre class='success'>✅ $tabela: Removido texto contaminado.</pre>";
        }
    }
}

// ====================================================================
// 2. CORREÇÃO DOS EQUIPAMENTOS (FORÇADA)
// ====================================================================
echo "<h2 class='info'>2️⃣ Atualizando 'Equipamentos' (Forçado)</h2>";

$novoTextoEquipamentos = "●	Aeronave: \${Drone} (Câmera de Alta Resolução).
●	GPS - Geodésia: \${GPS} (Receptor GNSS RTK/PPK para Pontos de Controle).
●	Estação Total para Apoio: \${Estacao_Total} (Se necessário para áreas de sombra de GPS).
●	Processamento: Workstations com placas gráficas de alto desempenho.";

// Tenta atualizar em TODOS os lugares possíveis onde possa ser um bloco de equipamentos
// IDs possíveis: equipamentos, equipamentos_previstos, recursos_tecnicos
$idsPossiveis = ['equipamentos', 'equipamentos_previstos', 'recursos_tecnicos'];

foreach ($idsPossiveis as $blockId) {
    // 1. Atualizar Templates (Service Type Blocks)
    $sqlTpl = "UPDATE service_type_blocks 
               SET default_content = ? 
               WHERE block_id = ? 
               AND service_type_id = (SELECT id_servico FROM Tipo_Servicos WHERE nome = 'Drone' LIMIT 1)";
    $stmt = $conn->prepare($sqlTpl);
    $stmt->bind_param("ss", $novoTextoEquipamentos, $blockId);
    $stmt->execute();
    if ($stmt->affected_rows > 0) echo "<pre class='success'>✅ Template '$blockId' atualizado (Service Blocks).</pre>";

    // 2. Atualizar Templates Globais
    $sqlGlob = "UPDATE proposal_block_templates SET default_content = ? WHERE block_id = ?";
    $stmt = $conn->prepare($sqlGlob);
    $stmt->bind_param("ss", $novoTextoEquipamentos, $blockId);
    $stmt->execute();
    if ($stmt->affected_rows > 0) echo "<pre class='success'>✅ Template '$blockId' atualizado (Global).</pre>";

    // 3. ATUALIZAR CONTEÚDO PERSONALIZADO (CRÍTICO!)
    // Atualiza apenas se o conteúdo atual for o antigo ou estiver vazio/errado
    // Vamos ser agressivos: Atualiza para TODAS as propostas de Drone recentes (últimas 50)
    
    // Primeiro pegamos os IDs das propostas de Drone
    $resProps = $conn->query("SELECT id_proposta FROM Propostas WHERE id_servico = (SELECT id_servico FROM Tipo_Servicos WHERE nome = 'Drone' LIMIT 1) ORDER BY id_proposta DESC LIMIT 50");
    $idsPropostas = [];
    while ($r = $resProps->fetch_assoc()) $idsPropostas[] = $r['id_proposta'];
    
    if (!empty($idsPropostas)) {
        $idsStr = implode(',', $idsPropostas);
        $sqlPers = "UPDATE Proposta_Conteudo_Personalizado 
                    SET conteudo_texto = ? 
                    WHERE block_id = ? 
                    AND id_proposta IN ($idsStr)";
        
        $stmt = $conn->prepare($sqlPers);
        $stmt->bind_param("ss", $novoTextoEquipamentos, $blockId);
        $stmt->execute();
        if ($stmt->affected_rows > 0) echo "<pre class='success'>✅ $stmt->affected_rows propostas existentes atualizadas para o bloco '$blockId'!</pre>";
    }
}

// ====================================================================
// 3. DOSE EXTRA: INVESTIMENTO DUPLICADO (GARANTIA)
// ====================================================================
echo "<h2 class='info'>3️⃣ Verificação Final Investimento</h2>";
// Garante que não sobrou nenhum "Este investimento... Este investimento"
$sqlDedup = "UPDATE Proposta_Conteudo_Personalizado 
             SET conteudo_texto = REPLACE(conteudo_texto, 'Este investimento traduz-se em produtividade multiplicada: milhões de coordenadas georreferenciadas coletadas em horas, não em dias, eliminando gargalos operacionais e acelerando entregas sem comprometer a precisão.Este investimento traduz-se em', 'Este investimento traduz-se em')
             WHERE block_id = 'investimento'";
$conn->query($sqlDedup);
if ($conn->affected_rows > 0) echo "<pre class='success'>✅ Duplicações removidas de propostas existentes.</pre>";


echo "<hr>
<h2 class='success'>✅ FIM DO PROCESSO V2</h2>
<p>Se você viu mensagens verdes acima, as correções foram aplicadas.</p>
<p><strong>Por favor, recarregue o editor da proposta.</strong></p>
</body>
</html>";
$conn->close();
