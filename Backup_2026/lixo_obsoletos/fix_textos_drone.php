<?php
/**
 * CORREÇÃO DE TEXTOS - BLOCOS DRONE
 * Atualiza os textos específicos solicitados pelo usuário
 */

require_once 'db.php';
$conn = Database::getProd();

echo "<!DOCTYPE html>
<html lang='pt-BR'>
<head>
    <meta charset='UTF-8'>
    <title>Correção de Textos - Drone</title>
    <style>
        body { font-family: 'Courier New', monospace; background: #0a0f1a; color: #00ff00; padding: 20px; }
        .success { color: #00ff00; }
        .error { color: #ff0000; }
        .info { color: #00aaff; }
        pre { background: #1a1f2e; padding: 15px; border-left: 3px solid #00ff00; margin: 10px 0; }
    </style>
</head>
<body>
<h1>🔧 CORREÇÃO DE TEXTOS - BLOCOS DRONE</h1>
<hr>
";

// ============================================
// 1. ATUALIZAÇÃO DO BLOCO "INVESTIMENTO"
// ============================================
echo "<h2 class='info'>1️⃣ Atualizando Bloco 'Investimento'</h2>";

// Novo texto limpo (sem valor, pois será substituído dinamicamente)
$textoNovoInvestimento = "Este investimento traduz-se em produtividade multiplicada: milhões de coordenadas georreferenciadas coletadas em horas, não em dias, eliminando gargalos operacionais e acelerando entregas sem comprometer a precisão.";

// ESTRATÉGIA: Limpar TUDO e inserir apenas o novo texto
// Padrão: Qualquer texto que contenha "investimento" + "tecnologia" ou "custo-benefício"

// 1. Remove textos antigos que contenham a frase antiga
$sqlClean1 = "UPDATE service_type_blocks 
              SET default_content = ?
              WHERE block_id = 'investimento' 
              AND service_type_id = (SELECT id_servico FROM Tipo_Servicos WHERE nome = 'Drone' LIMIT 1)
              AND (default_content LIKE '%custo-benefício da tecnologia%' 
                   OR default_content LIKE '%maior riqueza de dados%'
                   OR default_content LIKE '%milhões de pontos%')";

$stmt1 = $conn->prepare($sqlClean1);
$stmt1->bind_param("s", $textoNovoInvestimento);
$result1 = $stmt1->execute();
$affected1 = $stmt1->affected_rows;

echo "<pre class='success'>✅ service_type_blocks: $affected1 linha(s) limpa(s) e atualizada(s)</pre>";

// 2. Fallback: proposal_block_templates
$sqlClean2 = "UPDATE proposal_block_templates 
              SET default_content = ?
              WHERE block_id = 'investimento'
              AND (default_content LIKE '%custo-benefício da tecnologia%' 
                   OR default_content LIKE '%maior riqueza de dados%'
                   OR default_content LIKE '%milhões de pontos%')";

$stmt2 = $conn->prepare($sqlClean2);
$stmt2->bind_param("s", $textoNovoInvestimento);
$result2 = $stmt2->execute();
$affected2 = $stmt2->affected_rows;

echo "<pre class='success'>✅ proposal_block_templates: $affected2 linha(s) limpa(s) e atualizada(s)</pre>";

// 3. LIMPEZA ADICIONAL: Remove qualquer duplicação residual
echo "<h3 class='info'>🧹 Limpando duplicações residuais...</h3>";

$sqlCleanDuplicates = "UPDATE service_type_blocks 
                       SET default_content = ?
                       WHERE block_id = 'investimento' 
                       AND service_type_id = (SELECT id_servico FROM Tipo_Servicos WHERE nome = 'Drone' LIMIT 1)
                       AND default_content LIKE '%Este investimento%Este investimento%'";

$stmt3 = $conn->prepare($sqlCleanDuplicates);
$stmt3->bind_param("s", $textoNovoInvestimento);
$result3 = $stmt3->execute();
$affected3 = $stmt3->affected_rows;

echo "<pre class='success'>✅ Duplicações removidas: $affected3 linha(s)</pre>";

// ============================================
// 2. ATUALIZAÇÃO DO BLOCO "EQUIPAMENTOS PREVISTOS"
// ============================================
echo "<h2 class='info'>2️⃣ Atualizando Bloco 'Equipamentos Previstos'</h2>";

$textoAntigoEquipamentos = "%Receptor GNSS: Par de Receptores GNSS RTK de Dupla Frequência%";
$textoNovoEquipamentos = "●	Aeronave: \${Drone} (Câmera de Alta Resolução).
●	GPS - Geodésia: \${GPS} (Receptor GNSS RTK/PPK para Pontos de Controle).
●	Estação Total para Apoio: \${Estacao_Total} (Se necessário para áreas de sombra de GPS).
●	Processamento: Workstations com placas gráficas de alto desempenho.";

// Busca na tabela service_type_blocks
$sqlUpdate4 = "UPDATE service_type_blocks 
               SET default_content = ?
               WHERE block_id = 'equipamentos_previstos' 
               AND service_type_id = (SELECT id_servico FROM Tipo_Servicos WHERE nome = 'Drone' LIMIT 1)
               AND default_content LIKE ?";

$stmt4 = $conn->prepare($sqlUpdate4);
$stmt4->bind_param("ss", $textoNovoEquipamentos, $textoAntigoEquipamentos);
$result4 = $stmt4->execute();
$affected4 = $stmt4->affected_rows;

echo "<pre class='success'>✅ service_type_blocks: $affected4 linha(s) atualizada(s)</pre>";

// Fallback: proposal_block_templates
$sqlUpdate5 = "UPDATE proposal_block_templates 
               SET default_content = ?
               WHERE block_id = 'equipamentos_previstos'
               AND default_content LIKE ?";

$stmt5 = $conn->prepare($sqlUpdate5);
$stmt5->bind_param("ss", $textoNovoEquipamentos, $textoAntigoEquipamentos);
$result5 = $stmt5->execute();
$affected5 = $stmt5->affected_rows;

echo "<pre class='success'>✅ proposal_block_templates: $affected5 linha(s) atualizada(s)</pre>";

// ============================================
// 4. LIMPEZA DO BLOCO "CRONOGRAMA" (Remove texto de investimento perdido lá)
// ============================================
echo "<h2 class='info'>4️⃣ Limpando Bloco 'Cronograma'</h2>";

$padroesRemover = [
    '%R$ %',
    '%Este investimento reflete%',
    '%custo-benefício da tecnologia%',
    '%VALOR TOTAL DA PROPOSTA%'
];

// Monta query dinâmica para remover texto
// Nota: Em SQL puro é difícil fazer replace complexo, vamos zerar se for apenas isso, 
// ou tentar um UPDATE genérico se o texto for EXATAMENTE o que queremos remover.
// Assumindo que o texto de investimento está no final ou é o conteúdo todo.

// Vou usar uma abordagem mais segura: Ler, limpar via PHP, salvar.
echo "<p>🔄 Processando limpeza inteligente do cronograma...</p>";

$afetadosCrono1 = 0;
// Loop em service_type_blocks
$sqlScan1 = "SELECT id, default_content FROM service_type_blocks 
             WHERE block_id = 'cronograma' 
             AND (default_content LIKE '%R$ %' OR default_content LIKE '%Este investimento%')";
$resScan1 = $conn->query($sqlScan1);
while($row = $resScan1->fetch_assoc()) {
    $novoConteudo = preg_replace('/R\$\s?[\d\.,]+\s*\([^)]*reais[^)]*\).*?(\.|$)/iu', '', $row['default_content']);
    $novoConteudo = preg_replace('/Este investimento.*?(\.|$)/iu', '', $novoConteudo);
    $novoConteudo = preg_replace('/custo-benef[ií]cio.*?(\.|$)/iu', '', $novoConteudo);
    $novoConteudo = preg_replace('/VALOR TOTAL DA PROPOSTA.*?(\.|$)/iu', '', $novoConteudo);
    $novoConteudo = trim($novoConteudo);
    
    if ($novoConteudo !== $row['default_content']) {
        $stmtUp = $conn->prepare("UPDATE service_type_blocks SET default_content = ? WHERE id = ?");
        $stmtUp->bind_param("si", $novoConteudo, $row['id']);
        $stmtUp->execute();
        $afetadosCrono1++;
    }
}
$affected6 = $afetadosCrono1;
echo "<pre class='success'>✅ service_type_blocks (Cronograma): $affected6 linha(s) processada(s)</pre>";

// Loop em proposal_block_templates
$afetadosCrono2 = 0;
$sqlScan2 = "SELECT id, default_content FROM proposal_block_templates 
             WHERE block_id = 'cronograma' 
             AND (default_content LIKE '%R$ %' OR default_content LIKE '%Este investimento%')";
$resScan2 = $conn->query($sqlScan2);
while($row = $resScan2->fetch_assoc()) {
    $novoConteudo = preg_replace('/R\$\s?[\d\.,]+\s*\([^)]*reais[^)]*\).*?(\.|$)/iu', '', $row['default_content']);
    $novoConteudo = preg_replace('/Este investimento.*?(\.|$)/iu', '', $novoConteudo);
    $novoConteudo = preg_replace('/custo-benef[ií]cio.*?(\.|$)/iu', '', $novoConteudo);
    $novoConteudo = preg_replace('/VALOR TOTAL DA PROPOSTA.*?(\.|$)/iu', '', $novoConteudo);
    $novoConteudo = trim($novoConteudo);
    
    if ($novoConteudo !== $row['default_content']) {
        $stmtUp = $conn->prepare("UPDATE proposal_block_templates SET default_content = ? WHERE id = ?");
        $stmtUp->bind_param("si", $novoConteudo, $row['id']);
        $stmtUp->execute();
        $afetadosCrono2++;
    }
}
$affected7 = $afetadosCrono2;
echo "<pre class='success'>✅ proposal_block_templates (Cronograma): $affected7 linha(s) processada(s)</pre>";

// ============================================
// 3. VERIFICAÇÃO FINAL
// ============================================
echo "<h2 class='info'>3️⃣ Verificação Final</h2>";

// Verifica conteúdo atual do bloco investimento
$sqlCheck1 = "SELECT default_content FROM service_type_blocks 
              WHERE block_id = 'investimento' 
              AND service_type_id = (SELECT id_servico FROM Tipo_Servicos WHERE nome = 'Drone' LIMIT 1)
              LIMIT 1";
$resCheck1 = $conn->query($sqlCheck1);
if ($rowCheck1 = $resCheck1->fetch_assoc()) {
    echo "<h3>Bloco 'Investimento' (service_type_blocks):</h3>";
    echo "<pre>" . htmlspecialchars($rowCheck1['default_content']) . "</pre>";
} else {
    echo "<pre class='error'>❌ Bloco 'investimento' não encontrado em service_type_blocks</pre>";
}

// Verifica conteúdo atual do bloco equipamentos
$sqlCheck2 = "SELECT default_content FROM service_type_blocks 
              WHERE block_id = 'equipamentos_previstos' 
              AND service_type_id = (SELECT id_servico FROM Tipo_Servicos WHERE nome = 'Drone' LIMIT 1)
              LIMIT 1";
$resCheck2 = $conn->query($sqlCheck2);
if ($rowCheck2 = $resCheck2->fetch_assoc()) {
    echo "<h3>Bloco 'Equipamentos Previstos' (service_type_blocks):</h3>";
    echo "<pre>" . htmlspecialchars($rowCheck2['default_content']) . "</pre>";
} else {
    echo "<pre class='error'>❌ Bloco 'equipamentos_previstos' não encontrado em service_type_blocks</pre>";
}

// ============================================
// 5. LIMPEZA DE PROPOSTAS SALVAS (Proposta_Conteudo_Personalizado)
// ============================================
echo "<h2 class='info'>5️⃣ Atualizando Propostas Salvas (Rascunhos)</h2>";

// A. Limpa Investmento duplicado
$sqlCleanSaved1 = "UPDATE Proposta_Conteudo_Personalizado 
                   SET conteudo_texto = ?
                   WHERE block_id = 'investimento' 
                   AND (conteudo_texto LIKE '%custo-benefício da tecnologia%' 
                        OR conteudo_texto LIKE '%maior riqueza de dados%'
                        OR conteudo_texto LIKE '%milhões de pontos%')";
$stmtSaved1 = $conn->prepare($sqlCleanSaved1);
$stmtSaved1->bind_param("s", $textoNovoInvestimento);
$stmtSaved1->execute();
$savedInvest = $stmtSaved1->affected_rows;

// B. Limpa Cronograma sujo
$savedCrono = 0;
// Aqui precisamos ser cirúrgicos para não perder o cronograma real do usuário
$sqlScanSaved2 = "SELECT id, conteudo_texto FROM Proposta_Conteudo_Personalizado 
                  WHERE block_id = 'cronograma' 
                  AND (conteudo_texto LIKE '%R$ %' OR conteudo_texto LIKE '%Este investimento%')";
$resScanSaved2 = $conn->query($sqlScanSaved2);
while($row = $resScanSaved2->fetch_assoc()) {
    $novoConteudo = preg_replace('/R\$\s?[\d\.,]+\s*\([^)]*reais[^)]*\).*?(\.|$)/iu', '', $row['conteudo_texto']);
    $novoConteudo = preg_replace('/Este investimento.*?(\.|$)/iu', '', $novoConteudo);
    $novoConteudo = preg_replace('/custo-benef[ií]cio.*?(\.|$)/iu', '', $novoConteudo);
    $novoConteudo = preg_replace('/VALOR TOTAL DA PROPOSTA.*?(\.|$)/iu', '', $novoConteudo);
    $novoConteudo = trim($novoConteudo);
    
    if ($novoConteudo !== $row['conteudo_texto']) {
        $stmtUp = $conn->prepare("UPDATE Proposta_Conteudo_Personalizado SET conteudo_texto = ? WHERE id = ?");
        $stmtUp->bind_param("si", $novoConteudo, $row['id']);
        $stmtUp->execute();
        $savedCrono++;
    }
}

echo "<pre class='success'>✅ Propostas Salvas atualizadas:
- Investimento corrigido: $savedInvest
- Cronograma limpo: $savedCrono</pre>";

echo "<hr>
<h2 class='success'>✅ PROCESSO CONCLUÍDO!</h2>
<p>Total de atualizações:</p>
<ul>
    <li>Investimento (service_type_blocks): $affected1</li>
    <li>Investimento (proposal_block_templates): $affected2</li>
    <li>Duplicações removidas: $affected3</li>
    <li>Equipamentos (service_type_blocks): $affected4</li>
    <li>Equipamentos (proposal_block_templates): $affected5</li>
    <li>Cronograma limpo (service_type_blocks): $affected6</li>
    <li>Cronograma (proposal_block_templates): $affected7</li>
    <li><strong>Rascunhos Salvos (Invest.): $savedInvest</strong></li>
    <li><strong>Rascunhos Salvos (Crono.): $savedCrono</strong></li>
</ul>
<p><strong>⚠️ IMPORTANTE:</strong> Se algum valor for 0, significa que o texto já estava correto ou não foi encontrado.</p>
<p><strong>Próximo passo:</strong> Acesse o editor de uma proposta Drone para verificar as mudanças.</p>
</body>
</html>";

$conn->close();
