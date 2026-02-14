<?php
/**
 * VERIFICAÇÃO FINAL - ÚLTIMA PROPOSTA DRONE
 * Mostra exatamente o que está salvo no banco para a última proposta
 */
require_once 'db.php';
$conn = Database::getProd();

echo "<!DOCTYPE html>
<html lang='pt-BR'>
<head>
    <meta charset='UTF-8'>
    <title>Status Final</title>
    <style>
        body { font-family: 'Courier New', monospace; background: #0a0f1a; color: #f0f0f0; padding: 20px; }
        h1, h2, h3 { color: #ebf8ff; }
        pre { background: #1a202c; padding: 15px; border: 1px solid #2d3748; border-radius: 8px; white-space: pre-wrap; color: #a0aec0; }
        .success { color: #48bb78; }
        .danger { color: #f56565; }
    </style>
</head>
<body>
<h1>🔍 Verificação de Status Real</h1>";

// 1. Pega a última proposta Drone criada/editada
$sql = "SELECT p.id_proposta, c.nome_cliente, ts.nome as servico, p.data_criacao 
        FROM Propostas p 
        JOIN Clientes c ON p.id_cliente = c.id_cliente
        JOIN Tipo_Servicos ts ON p.id_servico = ts.id_servico
        WHERE ts.nome = 'Drone'
        ORDER BY p.id_proposta DESC LIMIT 1";
$res = $conn->query($sql);
$prop = $res->fetch_assoc();

if (!$prop) {
    die("<h2 class='danger'>Nenhuma proposta Drone encontrada!</h2>");
}

$id = $prop['id_proposta'];
echo "<h2>Última Proposta: #$id - {$prop['nome_cliente']}</h2>
<p>Serviço: {$prop['servico']} | Data: {$prop['data_criacao']}</p>
<hr>";

// 2. Verifica CRONOGRAMA
echo "<h3>1. Estado do Bloco 'Cronograma'</h3>";
$resCrono = $conn->query("SELECT conteudo_texto FROM Proposta_Conteudo_Personalizado WHERE id_proposta = $id AND block_id = 'cronograma'");
if ($row = $resCrono->fetch_assoc()) {
    $content = $row['conteudo_texto'];
    echo "<p>Conteúdo Personalizado (O que o editor lê):</p>";
    echo "<pre>" . htmlspecialchars($content) . "</pre>";
    
    // Análise
    if (strpos($content, 'Este investimento') !== false || strpos($content, 'R$ 6.500,00') !== false) {
        echo "<h4 class='danger'>❌ O texto antigo AINDA ESTÁ AQUI!</h4>";
    } else {
        echo "<h4 class='success'>✅ Limpo! O texto antigo sumiu daqui.</h4>";
    }
} else {
    echo "<p>Esta proposta usa o conteúdo PADRÃO (não personalizado).</p>";
    // Verifica padrão
    $resDef = $conn->query("SELECT default_content FROM service_type_blocks WHERE block_id = 'cronograma' AND service_type_id = (SELECT id_servico FROM Tipo_Servicos WHERE nome = 'Drone' LIMIT 1)");
    $rowDef = $resDef->fetch_assoc();
    echo "<pre>" . htmlspecialchars($rowDef['default_content'] ?? 'N/A') . "</pre>";
}

// 3. Verifica EQUIPAMENTOS
echo "<hr><h3>2. Estado do Bloco 'Equipamentos'</h3>";
$foundEq = false;
$idsEq = ['equipamentos', 'equipamentos_previstos'];

foreach ($idsEq as $bId) {
    $resEq = $conn->query("SELECT conteudo_texto FROM Proposta_Conteudo_Personalizado WHERE id_proposta = $id AND block_id = '$bId'");
    if ($row = $resEq->fetch_assoc()) {
        $foundEq = true;
        echo "<p>Encontrado em <strong>$bId</strong>:</p>";
        $content = $row['conteudo_texto'];
        echo "<pre>" . htmlspecialchars($content) . "</pre>";
        
        if (strpos($content, 'Aeronave:') !== false) {
            echo "<h4 class='success'>✅ Atualizado! Aparece 'Aeronave:'.</h4>";
        } else {
            echo "<h4 class='danger'>❌ Ainda parece o antigo (não achei 'Aeronave:').</h4>";
        }
    }
}

if (!$foundEq) {
    echo "<p>Usa conteúdo PADRÃO.</p>";
    // Verifica padrão
    $resDef = $conn->query("SELECT default_content FROM service_type_blocks WHERE block_id = 'equipamentos_previstos' AND service_type_id = (SELECT id_servico FROM Tipo_Servicos WHERE nome = 'Drone' LIMIT 1)");
    $rowDef = $resDef->fetch_assoc();
    echo "<pre>" . htmlspecialchars($rowDef['default_content'] ?? 'N/A') . "</pre>";
}

echo "</body></html>";
