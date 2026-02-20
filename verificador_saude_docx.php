<?php
/**
 * verificador_saude_docx.php
 * Executa verificações e gera relatório HTML/JSON
 */

require_once __DIR__ . '/session_validator.php';
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/ConnectionManager.php';

$conn = ConnectionManager::get();

header('Content-Type: text/html; charset=utf-8');

echo "<h1>🔍 Verificação de Saúde - Sistema DOCX SGT</h1>";
echo "<pre>";

$checks = [];
$score = 0;
$maxScore = 100;

try {
    // 1. Verifica estrutura
    $res = $conn->query("
        SELECT COLUMN_NAME 
        FROM INFORMATION_SCHEMA.COLUMNS 
        WHERE TABLE_SCHEMA = DATABASE()
        AND TABLE_NAME = 'Propostas'
        AND COLUMN_NAME IN ('modelo_docx','docx_conteudo','docx_blocos_count','docx_ultima_edicao')
    ");
    
    $cols = [];
    if($res) {
        while($r = $res->fetch_assoc()) {
            $cols[] = $r['COLUMN_NAME'];
        }
    }
    
    $estruturaOk = count($cols) === 4;
    $checks[] = ["Estrutura (4 colunas)", $estruturaOk ? '✅' : '❌', $estruturaOk ? 40 : count($cols) * 10];
    
    // 2. Verifica índice
    $resIdx = $conn->query("
        SELECT COUNT(*) as qtd FROM INFORMATION_SCHEMA.STATISTICS 
        WHERE TABLE_SCHEMA = DATABASE()
        AND TABLE_NAME = 'Propostas' 
        AND INDEX_NAME = 'idx_modelo_docx'
    ");
    $idx = $resIdx ? (int)$resIdx->fetch_assoc()['qtd'] : 0;
    
    $indiceOk = $idx > 0;
    $checks[] = ["Índice modelo_docx", $indiceOk ? '✅' : '❌', $indiceOk ? 20 : 0];
    
    // 3. Verifica integridade
    $resAnomalias = $conn->query("
        SELECT COUNT(*) as qtd FROM Propostas 
        WHERE (modelo_docx IS NOT NULL AND docx_conteudo IS NULL)
           OR (docx_conteudo IS NOT NULL AND JSON_VALID(docx_conteudo) = 0)
    ");
    $anomalias = $resAnomalias ? (int)$resAnomalias->fetch_assoc()['qtd'] : 0;
    
    $integridadeOk = $anomalias === 0;
    $checks[] = ["Integridade de dados", $integridadeOk ? '✅' : "⚠️ ($anomalias anomalias)", $integridadeOk ? 40 : max(0, 40 - $anomalias * 10)];
    
    // 4. Estatísticas
    $resStats = $conn->query("
        SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN modelo_docx IS NOT NULL THEN 1 ELSE 0 END) as docx
        FROM Propostas
    ");
    $stats = $resStats ? $resStats->fetch_assoc() : ['total' => 0, 'docx' => 0];
    
    // Exibe resultados
    echo "\n<b>CHECKLIST:</b>\n";
    foreach ($checks as $check) {
        echo sprintf("  %s %s (%d pts)\n", $check[1], $check[0], $check[2]);
        $score += $check[2];
    }
    
    echo "\n<b>SCORE DE SAÚDE: $score/$maxScore</b>\n";
    echo "Status: " . ($score >= 90 ? '🟢 EXCELENTE' : ($score >= 70 ? '🟡 BOM' : ($score >= 50 ? '🟠 ATENÇÃO' : '🔴 CRÍTICO'))) . "\n";
    
    echo "\n<b>ESTATÍSTICAS:</b>\n";
    echo "  Total propostas: {$stats['total']}\n";
    echo "  Com modelo DOCX: {$stats['docx']}\n";
    $perc = $stats['total'] > 0 ? round($stats['docx'] / $stats['total'] * 100, 2) : 0;
    echo "  Percentual: " . $perc . "%\n";
    
    // Recomendações
    echo "\n<b>RECOMENDAÇÕES:</b>\n";
    if (!$estruturaOk) echo "  → Completar migração SQL (faltam colunas)\n";
    if (!$indiceOk) echo "  → Criar índice idx_modelo_docx\n";
    if ($anomalias > 0) echo "  → Corrigir $anomalias registros com anomalias\n";
    if ($score >= 90) echo "  → Sistema pronto para produção!\n";
    
} catch (Exception $e) {
    echo "❌ ERRO: " . $e->getMessage();
}

echo "</pre>";
echo "<hr><small>Executado em: " . date('d/m/Y H:i:s') . "</small>";
