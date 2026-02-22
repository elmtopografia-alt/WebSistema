<?php
/**
 * DIAGNÓSTICO EM MASSA DE PROPOSTAS
 * Acesse: /Orcamento/diag_massa.php?de=113
 * Identifica quais campos estão consistentemente vazios nas propostas
 */
require_once __DIR__ . '/session_validator.php';
require_once __DIR__ . '/ConnectionManager.php';

$de  = max(1, (int)($_GET['de'] ?? 113));
$ate = (int)($_GET['ate'] ?? 9999);

$conn = ConnectionManager::get();

// Busca propostas no range
$sql = "SELECT id_proposta, numero_proposta, 
               nome_cliente_salvo, email_salvo, telefone_salvo,
               endereco_obra, cidade_obra, bairro_obra, estado_obra,
               area_obra, unidade_area, tipo_levantamento, finalidade,
               tipo_terreno, cobertura_vegetal, acesso_local, restricoes_aereas,
               id_cliente, id_servico, modelo_docx,
               valor_final_proposta, mobilizacao_valor,
               total_custos_salarios, total_custos_estadia,
               created_at
        FROM Propostas 
        WHERE id_proposta >= $de AND id_proposta <= $ate
        ORDER BY id_proposta ASC";

$result = $conn->query($sql);
if (!$result) die("Erro: " . $conn->error);

$propostas = [];
while ($row = $result->fetch_assoc()) {
    $propostas[] = $row;
}

// Campos a analisar (exceto IDs numéricos)
$campos_texto = [
    'nome_cliente_salvo', 'email_salvo', 'telefone_salvo',
    'endereco_obra', 'cidade_obra', 'bairro_obra',
    'area_obra', 'tipo_levantamento', 'finalidade',
    'tipo_terreno', 'cobertura_vegetal', 'acesso_local', 'restricoes_aereas',
    'modelo_docx',
    'valor_final_proposta', 'total_custos_salarios'
];

// Calcula score de completude
$scores = [];
foreach ($propostas as $p) {
    $total  = count($campos_texto);
    $cheios = 0;
    foreach ($campos_texto as $c) {
        $val = $p[$c] ?? null;
        if ($val !== null && $val !== '' && $val !== '0' && $val !== 0 && $val !== '0.00') {
            $cheios++;
        }
    }
    $scores[$p['id_proposta']] = round(($cheios / $total) * 100);
}

// CSS
echo '<!DOCTYPE html><html><head><meta charset="UTF-8">
<title>Diagnóstico Propostas</title>
<style>
body{font-family:monospace;background:#0f172a;color:#e2e8f0;padding:20px;font-size:12px;}
h2{color:#38bdf8;}
table{border-collapse:collapse;width:100%;margin-top:10px;}
th{background:#1e293b;color:#94a3b8;padding:6px 10px;text-align:left;font-size:11px;white-space:nowrap;}
td{padding:5px 10px;border-bottom:1px solid #1e293b;white-space:nowrap;}
.ok{color:#10b981;} .vazio{color:#f97316;} .null{color:#ef4444;}
.score-high{background:rgba(16,185,129,.15);}
.score-med{background:rgba(249,115,22,.1);}
.score-low{background:rgba(239,68,68,.15);}
tr:hover{background:#1e293b;}
.filter{margin-bottom:10px;}
</style></head><body>';

echo "<h2>🔍 Diagnóstico em Massa — Propostas #{$de} a #" . ($ate < 9999 ? $ate : 'todas') . "</h2>";
echo "<p>Total: " . count($propostas) . " propostas encontradas</p>";

// Resumo: campos mais vazios
echo "<h3 style='color:#fbbf24'>📊 Campos Mais Problemáticos</h3>";
$vazio_count = [];
foreach ($campos_texto as $c) {
    $vazio_count[$c] = 0;
    foreach ($propostas as $p) {
        $val = $p[$c] ?? null;
        if ($val === null || $val === '' || $val === '0' || $val === '0.00') {
            $vazio_count[$c]++;
        }
    }
}
arsort($vazio_count);
echo "<table><tr><th>Campo</th><th>Vazios</th><th>%</th></tr>";
foreach ($vazio_count as $campo => $cnt) {
    $pct = count($propostas) ? round($cnt / count($propostas) * 100) : 0;
    $cor = $pct > 70 ? '#ef4444' : ($pct > 30 ? '#f97316' : '#10b981');
    echo "<tr><td>$campo</td><td style='color:$cor'>$cnt</td><td style='color:$cor'>{$pct}%</td></tr>";
}
echo "</table>";

// Tabela principal
echo "<h3 style='color:#fbbf24;margin-top:20px'>📋 Detalhe por Proposta</h3>";
echo "<table><tr><th>ID</th><th>Número</th><th>Score</th><th>Cliente</th><th>Cidade</th><th>Área</th><th>Tipo Terreno</th><th>Acesso</th><th>Modelo DOCX</th><th>Valor Final</th><th>Custo Equipe</th><th>Data</th></tr>";

foreach ($propostas as $p) {
    $score = $scores[$p['id_proposta']];
    $cls   = $score >= 70 ? 'score-high' : ($score >= 40 ? 'score-med' : 'score-low');
    
    $fmt = function($v) {
        if ($v === null || $v === '') return "<span class='null'>∅ NULL</span>";
        if ($v === '0' || $v === '0.00' || $v === 0) return "<span class='vazio'>0</span>";
        return "<span class='ok'>" . htmlspecialchars(substr($v, 0, 25)) . "</span>";
    };
    
    echo "<tr class='$cls'>";
    echo "<td>{$p['id_proposta']}</td>";
    echo "<td style='color:#94a3b8'>{$p['numero_proposta']}</td>";
    echo "<td style='font-weight:bold;color:" . ($score>=70?'#10b981':($score>=40?'#f97316':'#ef4444')) . "'>{$score}%</td>";
    echo "<td>{$fmt($p['nome_cliente_salvo'])}</td>";
    echo "<td>{$fmt($p['cidade_obra'])}</td>";
    echo "<td>{$fmt($p['area_obra'])}</td>";
    echo "<td>{$fmt($p['tipo_terreno'])}</td>";
    echo "<td>{$fmt($p['acesso_local'])}</td>";
    echo "<td>{$fmt($p['modelo_docx'])}</td>";
    echo "<td>{$fmt($p['valor_final_proposta'])}</td>";
    echo "<td>{$fmt($p['total_custos_salarios'])}</td>";
    echo "<td style='color:#64748b'>" . substr($p['created_at'] ?? '', 0, 10) . "</td>";
    echo "</tr>";
}
echo "</table>";
echo "<p style='color:#475569;margin-top:20px;font-size:11px;'>⚠️ Deletar este arquivo após uso!</p>";
echo "</body></html>";
