<?php
/**
 * ARQUIVO: verificar_upgrade_v2.php
 * OBJETIVO: Verificar se o upgrade v2.0 foi aplicado corretamente
 * USO: Acesse via navegador após rodar a migration
 */

require_once __DIR__ . '/session_validator.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/ConnectionManager.php';

header('Content-Type: text/html; charset=utf-8');

echo "<!DOCTYPE html>
<html lang='pt-br'>
<head>
    <meta charset='UTF-8'>
    <title>Diagnostic SGT Upgrade v2.0</title>
    <style>
        body { font-family: 'Inter', sans-serif; margin: 40px; background: #0f172a; color: #e2e8f0; }
        .container { max-width: 1200px; margin: 0 auto; background: #1e293b; padding: 30px; border-radius: 12px; border: 1px solid #334155; }
        h1 { color: #f8fafc; border-bottom: 2px solid #3b82f6; padding-bottom: 15px; }
        .status { display: inline-block; padding: 6px 12px; border-radius: 20px; font-weight: 600; font-size: 0.85em; }
        .ok { background: #065f46; color: #34d399; }
        .error { background: #7f1d1d; color: #f87171; }
        table { width: 100%; border-collapse: collapse; margin: 15px 0; background: #0f172a; border-radius: 8px; overflow: hidden; }
        th { background: #334155; color: #f8fafc; padding: 12px; text-align: left; }
        td { padding: 10px; border-bottom: 1px solid #1e293b; }
        tr:hover { background: #1e293b; }
        .metric { display: inline-block; margin: 10px 15px 10px 0; padding: 15px 20px; background: #334155; border-radius: 8px; min-width: 150px; }
        .metric-value { font-size: 1.8em; font-weight: bold; color: #3b82f6; }
        pre { background: #0f172a; color: #34d399; padding: 15px; border-radius: 8px; overflow-x: auto; border: 1px solid #334155; }
    </style>
</head>
<body>
    <div class='container'>
        <h1>🔍 SGT Upgrade v2.0 - Diagnóstico</h1>
        <p>Verificação de integridade da tabela `Propostas`</p>
";

try {
    $conn = ConnectionManager::get();
    
    // 1. VERIFICA ESTRUTURA DO BANCO
    $res = $conn->query("SHOW COLUMNS FROM Propostas");
    $cols = [];
    while($row = $res->fetch_assoc()) $cols[] = $row['Field'];
    
    $checkGroups = [
        'Totais v2' => ['total_v2_adm', 'total_v2_fun', 'total_v2_est', 'total_v2_con', 'total_v2_loc'],
        'Admin (1-5)' => ['adm_1_id', 'adm_1_qtd', 'adm_1_valor', 'adm_1_subtotal'],
        'Funções (1-10)' => ['fun_1_id', 'fun_1_qtd', 'fun_1_dias', 'fun_1_valor', 'fun_1_subtotal'],
        'Estadia (1-5)' => ['est_1_id', 'est_1_qtd', 'est_1_dias', 'est_1_valor', 'est_1_subtotal'],
        'Consumo (1-20)' => ['con_1_id', 'con_1_qtd', 'con_1_km', 'con_20_subtotal'],
        'Locação (1-10)' => ['loc_1_id_tipo', 'loc_1_id_marca', 'loc_1_qtd', 'loc_10_subtotal']
    ];
    
    echo "<h2>📋 Verificação de Colunas</h2>";
    echo "<table><tr><th>Grupo</th><th>Status</th><th>Exemplos</th></tr>";
    
    foreach ($checkGroups as $group => $sampleCols) {
        $found = 0;
        foreach ($sampleCols as $sc) {
            if (in_array($sc, $cols)) $found++;
        }
        $status = ($found == count($sampleCols)) ? "<span class='status ok'>EXISTE</span>" : "<span class='status error'>FALHA</span>";
        echo "<tr><td>$group</td><td>$status</td><td><code>" . implode(', ', $sampleCols) . "</code></td></tr>";
    }
    echo "</table>";

    // 2. VERIFICA DADOS RECENTES
    echo "<h2>📊 Últimos Dados Salvos</h2>";
    $res = $conn->query("SELECT id_proposta, numero_proposta, data_criacao, total_custos_salarios, fun_1_subtotal, con_1_subtotal, loc_1_subtotal 
                         FROM Propostas ORDER BY id_proposta DESC LIMIT 3");
    
    echo "<table><tr><th>ID</th><th>Número</th><th>Total Fun (v1)</th><th>fun_1 (v2)</th><th>con_1 (v2)</th><th>loc_1 (v2)</th></tr>";
    while ($p = $res->fetch_assoc()) {
        echo "<tr>
            <td>{$p['id_proposta']}</td>
            <td>{$p['numero_proposta']}</td>
            <td>" . number_format($p['total_custos_salarios'], 2, ',', '.') . "</td>
            <td>" . number_format($p['fun_1_subtotal'] ?? 0, 2, ',', '.') . "</td>
            <td>" . number_format($p['con_1_subtotal'] ?? 0, 2, ',', '.') . "</td>
            <td>" . number_format($p['loc_1_subtotal'] ?? 0, 2, ',', '.') . "</td>
        </tr>";
    }
    echo "</table>";

    echo "<div style='margin-top:20px;'>
        <a href='https://elmtopografia.com.br/Orcamento/executar_migracao_v2.php' style='color:#3b82f6; font-weight:bold;'>&raquo; Rodar Migration SQL</a>
        &nbsp;&nbsp;|&nbsp;&nbsp;
        <a href='painel.php' style='color:#3b82f6; font-weight:bold;'>&raquo; Voltar ao Painel</a>
    </div>";

} catch (Exception $e) {
    echo "<p class='status error'>Erro: " . $e->getMessage() . "</p>";
}

echo "</div></body></html>";
