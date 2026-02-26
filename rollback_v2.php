<?php
/**
 * ARQUIVO: rollback_v2.php
 * OBJETIVO: Desfazer upgrade v2.0 (remover colunas da planilha, manter dados básicos)
 * ATENÇÃO: Remove permanentemente as colunas da planilha. Os dados de custos serão perdidos!
 * BACKUP OBRIGATÓRIO antes de executar!
 */

require_once __DIR__ . '/session_validator.php';
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/ConnectionManager.php';

header('Content-Type: text/html; charset=utf-8');

// Configurações de segurança
$REQUER_CONFIRMACAO = true;
$MODO_SIMULACAO = ($_GET['modo'] ?? 'simulacao') === 'simulacao'; // true = só mostra, não executa
$TOKEN_SEGURANCA = $_GET['token'] ?? '';
$TOKEN_CORRETO = 'SGT_ROLLBACK_2025';

// Verificação de token (segurança básica)
if ($REQUER_CONFIRMACAO && $TOKEN_SEGURANCA !== $TOKEN_CORRETO && !$MODO_SIMULACAO) {
    http_response_code(403);
    die("
    <!DOCTYPE html>
    <html>
    <head>
        <style>
            body { font-family: 'Segoe UI', system-ui; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; background: #0f172a; color: #e2e8f0; }
            .box { background: rgba(30, 41, 59, 0.7); backdrop-filter: blur(10px); padding: 40px; border-radius: 20px; text-align: center; max-width: 500px; border: 1px solid rgba(255,0,0,0.3); box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5); }
            h1 { color: #ef4444; }
            code { background: rgba(234, 179, 8, 0.1); color: #fbbf24; padding: 4px 8px; border-radius: 4px; border: 1px solid rgba(234, 179, 8, 0.3); }
        </style>
    </head>
    <body>
        <div class='box'>
            <h1>⛔ Acesso Negado</h1>
            <p>Token de segurança inválido ou ausente.</p>
            <p>Para executar o rollback, adicione o token na URL:</p>
            <code>?token=SGT_ROLLBACK_2025</code>
            <p style='margin-top: 20px; color: #94a3b8; font-size: 0.9em;'>
                Ou execute em modo simulação:<br>
                <code>?modo=simulacao</code>
            </p>
        </div>
    </body>
    </html>
    ");
}

echo "<!DOCTYPE html>
<html lang='pt-br'>
<head>
    <meta charset='UTF-8'>
    <title>Rollback Upgrade v2.0 | SGT Propostas</title>
    <style>
        body { font-family: 'Segoe UI', system-ui, sans-serif; margin: 0; padding: 20px; background: #0f172a; color: #e2e8f0; }
        .container { max-width: 900px; margin: 0 auto; background: rgba(30, 41, 59, 0.7); backdrop-filter: blur(10px); padding: 30px; border-radius: 20px; border: 1px solid rgba(255,255,255,0.1); box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5); }
        h1 { color: #f87171; margin-bottom: 10px; font-weight: 800; }
        .subtitle { color: #94a3b8; margin-bottom: 30px; font-size: 0.95em; }
        
        .alerta-critical {
            background: rgba(239, 68, 68, 0.2);
            color: #f87171;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 25px;
            border: 1px solid rgba(239, 68, 68, 0.3);
        }
        .alerta-critical h3 { margin: 0 0 10px 0; font-size: 1.2em; }
        
        .modo-badge { 
            display: inline-block; 
            padding: 8px 16px; 
            border-radius: 20px; 
            font-size: 0.85em; 
            font-weight: 600;
            margin-bottom: 20px;
        }
        .simulacao { background: rgba(234, 179, 8, 0.2); color: #fbbf24; border: 1px solid rgba(234, 179, 8, 0.5); }
        .producao { background: rgba(239, 68, 68, 0.2); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.5); }
        
        .etapa {
            margin: 20px 0;
            padding: 20px;
            border-radius: 12px;
            border-left: 4px solid #334155;
            background: rgba(15, 23, 42, 0.5);
            transition: all 0.3s ease;
        }
        .etapa.pendente { opacity: 0.6; }
        .etapa.executando { border-left-color: #38bdf8; background: rgba(56, 189, 248, 0.05); }
        .etapa.sucesso { border-left-color: #22c55e; background: rgba(34, 197, 94, 0.05); }
        .etapa.erro { border-left-color: #ef4444; background: rgba(239, 68, 68, 0.05); }
        
        .etapa-header { display: flex; align-items: center; gap: 12px; margin-bottom: 10px; font-weight: 600; }
        .status-icon { width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.9em; }
        
        .sql-preview {
            background: rgba(0,0,0,0.3);
            color: #cbd5e1;
            padding: 15px;
            border-radius: 8px;
            font-family: 'Courier New', monospace;
            font-size: 0.85em;
            overflow-x: auto;
            margin: 10px 0;
            border: 1px solid rgba(255,255,255,0.05);
        }
        .sql-preview .comentario { color: #64748b; }
        .sql-preview .comando { color: #f87171; }
        .sql-preview .nome { color: #fbbf24; }
        
        .dados-afetados { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin: 20px 0; }
        .dado-card { background: rgba(30, 41, 59, 0.5); padding: 20px; border-radius: 10px; text-align: center; border: 1px solid rgba(255,255,255,0.05); border-top: 4px solid #334155; }
        .dado-card.perigo { border-top-color: #ef4444; }
        .dado-card.atencao { border-top-color: #f59e0b; }
        .dado-card.seguro { border-top-color: #22c55e; }
        .dado-valor { font-size: 2em; font-weight: bold; color: #f1f5f9; }
        .dado-label { font-size: 0.85em; color: #94a3b8; margin-top: 5px; }
        
        .btn {
            display: inline-block;
            padding: 12px 24px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.95em;
            border: none;
            cursor: pointer;
            margin: 5px;
        }
        .btn-danger { background: #ef4444; color: white; }
        .btn-danger:hover { background: #dc2626; }
        .btn-outline { background: transparent; border: 1px solid #334155; color: #94a3b8; }
        .btn-success { background: #22c55e; color: white; }
        
        .actions { margin-top: 30px; text-align: center; }
        
        .confirmacao-box {
            background: rgba(239, 68, 68, 0.05);
            border: 2px dashed rgba(239, 68, 68, 0.3);
            padding: 25px;
            border-radius: 12px;
            margin: 20px 0;
            text-align: center;
        }
        .confirmacao-box input {
            padding: 12px;
            font-size: 1.1em;
            border: 1px solid #334155;
            border-radius: 8px;
            width: 300px;
            text-align: center;
            margin: 10px 0;
            background: #1e293b;
            color: #f1f5f9;
        }
        
        .log-output {
            background: rgba(0,0,0,0.3);
            color: #cbd5e1;
            padding: 20px;
            border-radius: 8px;
            font-family: 'Courier New', monospace;
            font-size: 0.85em;
            max-height: 400px;
            overflow: auto;
            margin-top: 20px;
            border: 1px solid rgba(255,255,255,0.05);
        }
        .log-line { margin: 3px 0; }
        .log-time { color: #64748b; }
        .log-info { color: #38bdf8; }
        .log-success { color: #4ade80; }
        .log-error { color: #f87171; }
        .log-warning { color: #fbbf24; }
        
        .resumo-final { margin-top: 30px; padding: 25px; border-radius: 12px; text-align: center; border: 1px solid rgba(255,255,255,0.05); }
        .resumo-sucesso { background: rgba(34, 197, 94, 0.1); color: #4ade80; }
        .resumo-erro { background: rgba(239, 68, 68, 0.1); color: #f87171; }
        .resumo-simulacao { background: rgba(234, 179, 8, 0.1); color: #fbbf24; }
    </style>
</head>
<body>
    <div class='container'>
        <h1>🔄 Rollback Upgrade v2.0</h1>
        <p class='subtitle'>Desfazer upgrade e remover colunas da planilha de custos</p>
        
        <div class='alerta-critical'>
            <h3>⚠️ ATENÇÃO: AÇÃO IRREVERSÍVEL</h3>
            <p>Este script removerá todas as colunas de custos detalhados v2.0. Dados salvos nestas colunas serão deletados permanentemente.</p>
        </div>
        
        <div class='modo-badge " . ($MODO_SIMULACAO ? 'simulacao' : 'producao') . "'>
            " . ($MODO_SIMULACAO ? '🔒 MODO SIMULAÇÃO' : '⚡ MODO PRODUÇÃO') . "
        </div>
";

// Inicializa log
$log = [];
function addLog(&$log, $msg, $tipo = 'info') {
    $hora = date('H:i:s');
    $log[] = "<div class='log-line'><span class='log-time'>[{$hora}]</span> <span class='log-{$tipo}'>{$msg}</span></div>";
}

// Estatísticas
echo "<h3>📊 Impacto Estimado</h3>";
echo "<div class='dados-afetados'>";

try {
    $conn = ConnectionManager::get();
    
    $total = $conn->query("SELECT COUNT(*) FROM Propostas")->fetch_row()[0];
    
    // Verifica propostas v2.0 (que tem algum valor no total_v2_adm ou similar)
    $v2Count = $conn->query("SELECT COUNT(*) FROM Propostas WHERE (total_v2_adm > 0 OR total_v2_fun > 0 OR total_v2_est > 0 OR total_v2_con > 0 OR total_v2_loc > 0)")->fetch_row()[0];
    $v1Count = $total - $v2Count;
    
    echo "
        <div class='dado-card perigo'>
            <div class='dado-valor'>{$total}</div>
            <div class='dado-label'>Total de Propostas</div>
        </div>
        <div class='dado-card atencao'>
            <div class='dado-valor'>{$v2Count}</div>
            <div class='dado-label'>Com dados v2.0</div>
        </div>
        <div class='dado-card seguro'>
            <div class='dado-valor'>{$v1Count}</div>
            <div class='dado-label'>Apenas v1.0</div>
        </div>
    ";
} catch (Exception $e) {
    echo "<div class='dado-card perigo'><div class='dado-valor'>?</div><div class='dado-label'>Erro DB</div></div>";
}

echo "</div>";

// Lista de colunas (Sincronizada com migracao_v2.0.sql)
$colunasRemover = ['total_v2_adm', 'total_v2_fun', 'total_v2_est', 'total_v2_con', 'total_v2_loc'];
for ($i=1; $i<=5; $i++) { $colunasRemover[] = "adm_{$i}_id"; $colunasRemover[] = "adm_{$i}_qtd"; $colunasRemover[] = "adm_{$i}_valor"; $colunasRemover[] = "adm_{$i}_periodo"; $colunasRemover[] = "adm_{$i}_subtotal"; }
for ($i=1; $i<=10; $i++) { $colunasRemover[] = "fun_{$i}_id"; $colunasRemover[] = "fun_{$i}_qtd"; $colunasRemover[] = "fun_{$i}_dias"; $colunasRemover[] = "fun_{$i}_valor"; $colunasRemover[] = "fun_{$i}_encargos"; $colunasRemover[] = "fun_{$i}_subtotal"; }
for ($i=1; $i<=5; $i++) { $colunasRemover[] = "est_{$i}_id"; $colunasRemover[] = "est_{$i}_qtd"; $colunasRemover[] = "est_{$i}_dias"; $colunasRemover[] = "est_{$i}_valor"; $colunasRemover[] = "est_{$i}_subtotal"; }
for ($i=1; $i<=20; $i++) { $colunasRemover[] = "con_{$i}_id"; $colunasRemover[] = "con_{$i}_qtd"; $colunasRemover[] = "con_{$i}_kml"; $colunasRemover[] = "con_{$i}_litro"; $colunasRemover[] = "con_{$i}_km"; $colunasRemover[] = "con_{$i}_subtotal"; }
for ($i=1; $i<=10; $i++) { $colunasRemover[] = "loc_{$i}_id_tipo"; $colunasRemover[] = "loc_{$i}_id_marca"; $colunasRemover[] = "loc_{$i}_qtd"; $colunasRemover[] = "loc_{$i}_valor"; $colunasRemover[] = "loc_{$i}_dias"; $colunasRemover[] = "loc_{$i}_subtotal"; }

echo "<h3>📜 Preview da Limpeza</h3>";
echo "
    <div class='etapa executando'>
        <p><strong>" . count($colunasRemover) . " colunas</strong> foram identificadas para remoção.</p>
        <div class='sql-preview'>
            <span class='comentario'>-- Exemplo de Cleanup:</span><br>
            <span class='comando'>ALTER TABLE</span> <span class='nome'>Propostas</span> <span class='comando'>DROP COLUMN</span> total_v2_adm, ...;
        </div>
    </div>
";

if (!$MODO_SIMULACAO && $_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['confirmar'] ?? '') === 'REMOVER_V2') {
    echo "<div class='log-output'>";
    try {
        $conn->begin_transaction();
        addLog($log, "Iniciando processo de remoção...", "info");
        
        // Remove em lotes para evitar erro de tamanho de query se o DB for restrito
        $chunks = array_chunk($colunasRemover, 30);
        foreach ($chunks as $chunk) {
            $drops = array_map(fn($c) => "DROP COLUMN IF EXISTS $c", $chunk);
            $sql = "ALTER TABLE Propostas " . implode(", ", $drops);
            if ($conn->query($sql)) {
                addLog($log, "Lote de " . count($chunk) . " colunas removido.", "success");
            }
        }
        
        $conn->commit();
        $executado = true;
    } catch (Exception $e) {
        $conn->rollback();
        addLog($log, "ERRO: " . $e->getMessage(), "error");
        $executado = false;
    }
    echo "</div>";
} else {
    $executado = false;
}

echo "<div class='resumo-final " . ($executado ? 'resumo-sucesso' : ($MODO_SIMULACAO ? 'resumo-simulacao' : '')) . "'>";
if ($executado) {
    echo "<h2>✅ Rollback Efetuado</h2><p>As colunas v2.0 foram removidas. O sistema retornou ao estado v1.0.</p>";
} elseif ($MODO_SIMULACAO) {
    echo "<h2>🔒 Simulação Pronta</h2><p>Revise os dados acima. Para executar de verdade, use o formulário abaixo.</p>";
}

if (!$executado) {
    echo "
        <div class='confirmacao-box'>
            <h3>Confirmar Remoção Permanente</h3>
            <form method='POST'>
                <input type='text' name='confirmar' placeholder='Digite REMOVER_V2' required pattern='REMOVER_V2'>
                <br>
                <button type='submit' class='btn btn-danger'>🗑️ Executar Rollback em Produção</button>
            </form>
        </div>
    ";
}
echo "</div>";

echo "<div class='actions'>
    <a href='rollback_v2.php?modo=simulacao' class='btn btn-outline'>🔁 Resetar Página</a>
    <a href='painel.php' class='btn btn-primary'>🏠 Voltar ao Painel</a>
</div>";

echo "</div></body></html>";
