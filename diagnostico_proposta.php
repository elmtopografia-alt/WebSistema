<?php
/**
 * DIAGNÓSTICO: Inspeciona o que foi salvo no banco para uma proposta específica
 * Acesse: /Orcamento/diagnostico_proposta.php?id=215
 */
require_once __DIR__ . '/session_validator.php';
require_once __DIR__ . '/ConnectionManager.php';

$id = (int)($_GET['id'] ?? 0);
if (!$id) die("Informe ?id=XXX");

$conn = ConnectionManager::get();
$row = $conn->query("SELECT * FROM Propostas WHERE id_proposta = $id")->fetch_assoc();

if (!$row) die("Proposta #$id não encontrada");

// Campos críticos para o template
$campos = [
    'id_proposta', 'numero_proposta', 'status',
    'nome_cliente_salvo', 'email_salvo', 'telefone_salvo', 'celular_salvo',
    'endereco_obra', 'bairro_obra', 'cidade_obra', 'estado_obra',
    'area_obra', 'unidade_area', 'tipo_levantamento', 'finalidade',
    'tipo_terreno', 'cobertura_vegetal', 'acesso_local', 'restricoes_aereas',
    'dias_campo', 'dias_escritorio', 'prazo_execucao',
    'valor_final_proposta', 'mobilizacao_percentual', 'mobilizacao_valor',
    'id_cliente', 'id_servico', 'modelo_docx'
];

echo '<style>body{font-family:monospace;background:#0f172a;color:#e2e8f0;padding:20px;} 
table{border-collapse:collapse;width:100%;} 
th,td{padding:8px 12px;border:1px solid #334155;text-align:left;}
th{background:#1e293b;color:#94a3b8;font-size:11px;text-transform:uppercase;}
.ok{color:#10b981;} .vazio{color:#f97316;font-style:italic;} .null{color:#ef4444;}
h2{color:#38bdf8;}</style>';
echo "<h2>🔍 Diagnóstico Proposta #{$row['id_proposta']} — {$row['numero_proposta']}</h2>";
echo "<table><tr><th>Campo</th><th>Valor</th><th>Status</th></tr>";

foreach ($campos as $campo) {
    $val = $row[$campo] ?? '-- COLUNA NÃO EXISTE --';
    if (!array_key_exists($campo, $row)) {
        $status = "<span class='null'>❌ Coluna ausente no DB</span>";
        $display = "<span class='null'>N/A</span>";
    } elseif ($val === null) {
        $status = "<span class='null'>❌ NULL</span>";
        $display = "<span class='null'>NULL</span>";
    } elseif ($val === '' || $val === '0' || $val === 0) {
        $status = "<span class='vazio'>⚠️ Vazio/Zero</span>";
        $display = "<span class='vazio'>" . htmlspecialchars($val) . "</span>";
    } else {
        $status = "<span class='ok'>✅ OK</span>";
        $display = "<span class='ok'>" . htmlspecialchars(substr($val, 0, 100)) . "</span>";
    }
    echo "<tr><td>$campo</td><td>$display</td><td>$status</td></tr>";
}
echo "</table>";
echo "<p style='color:#475569;margin-top:20px;font-size:11px;'>⚠️ Deletar este arquivo após o diagnóstico!</p>";
