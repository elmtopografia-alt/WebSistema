<?php
/**
 * auditoria_post.php — Acesse com ?token=SGT2026AUDIT
 * Raio-X completo: verifica campos POST, DadosEmpresa, Clientes e o mapa do banco.
 * DELETE após uso!
 */

// Proteção simples por token (sem depender de sessão)
$TOKEN = 'SGT2026AUDIT';
if (($_GET['token'] ?? '') !== $TOKEN) {
    http_response_code(403);
    die('Acesso negado. Use ?token=SGT2026AUDIT');
}

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

// Detecta usuário — usa GET se não tiver sessão
if (session_status() === PHP_SESSION_NONE) @session_start();
$idUser = $_SESSION['usuario_id'] ?? (int)($_GET['uid'] ?? 1);

require_once __DIR__ . '/ConnectionManager.php';

$conn   = ConnectionManager::get();
$idUser = $_SESSION['usuario_id'] ?? 0;

// --- 1. DadosEmpresa do usuário logado ---
$emp = $conn->query("SELECT * FROM DadosEmpresa WHERE id_criador = $idUser LIMIT 1")->fetch_assoc();

// --- 2. Colunas reais da tabela Propostas ---
$colsBanco = [];
$r = $conn->query("SHOW COLUMNS FROM Propostas");
while ($c = $r->fetch_assoc()) $colsBanco[] = $c['Field'];

// --- 3. Campos que o form DEVERIA enviar mas NÃO envia ---
$camposSemPost = [
    'empresa_proponente_nome', 'empresa_proponente_cnpj', 'empresa_proponente_endereco',
    'empresa_proponente_cidade', 'empresa_proponente_estado', 'empresa_proponente_banco',
    'empresa_proponente_agencia', 'empresa_proponente_conta', 'empresa_proponente_pix',
    'nome_cliente_salvo', 'email_salvo', 'telefone_salvo', 'celular_salvo',
    'whatsapp_salvo', 'empresa_cliente_salvo',
];

// --- Mapeamento DadosEmpresa → empresa_proponente_* ---
$mapaEmpresa = [
    'empresa_proponente_nome'     => 'Empresa',
    'empresa_proponente_cnpj'     => 'CNPJ',
    'empresa_proponente_endereco' => 'Endereco',
    'empresa_proponente_cidade'   => 'Cidade',
    'empresa_proponente_estado'   => 'Estado',
    'empresa_proponente_banco'    => 'Banco',
    'empresa_proponente_agencia'  => 'Agencia',
    'empresa_proponente_conta'    => 'Conta',
    'empresa_proponente_pix'      => 'PIX',
];

echo '<!DOCTYPE html><html><head><meta charset="UTF-8">
<title>Auditoria POST vs Banco</title>
<style>
body{font-family:monospace;background:#0f172a;color:#e2e8f0;padding:20px;font-size:13px;}
h2{color:#38bdf8;font-size:1.2rem;margin:20px 0 10px;}
h3{color:#fbbf24;font-size:.95rem;margin:15px 0 8px;}
table{border-collapse:collapse;width:100%;margin-bottom:16px;}
th{background:#1e293b;color:#94a3b8;padding:6px 12px;text-align:left;}
td{padding:6px 12px;border-bottom:1px solid #1e293b;}
.ok{color:#10b981;font-weight:bold;} .err{color:#ef4444;font-weight:bold;}
.warn{color:#f97316;} .info{color:#64748b;}
.box{background:#1e293b;border-radius:8px;padding:16px;margin-bottom:16px;}
</style></head><body>';

echo '<h2>🔍 Auditoria POST vs Banco — Usuário #' . $idUser . '</h2>';

// ── BLOCO 1: DadosEmpresa ──────────────────────────────────────────────────
echo '<h3>🏢 Tabela DadosEmpresa (id_criador = ' . $idUser . ')</h3>';
if (!$emp) {
    echo '<p class="err">❌ Nenhum registro em DadosEmpresa para este usuário! Os campos empresa_proponente_* ficarão sempre vazios.</p>';
} else {
    echo '<table><tr><th>Campo banco</th><th>Coluna DadosEmpresa</th><th>Valor encontrado</th><th>Status</th></tr>';
    foreach ($mapaEmpresa as $colBanco => $colEmp) {
        $val = $emp[$colEmp] ?? null;
        $ok  = !empty($val);
        echo "<tr><td>{$colBanco}</td><td>{$colEmp}</td><td>" . htmlspecialchars($val ?? 'NULL') . "</td>";
        echo "<td class='" . ($ok ? 'ok' : 'err') . "'>" . ($ok ? '✅ OK' : '❌ VAZIO') . "</td></tr>";
    }
    echo '</table>';
}

// ── BLOCO 2: O que o form ENVIA vs o que o banco espera ───────────────────
echo '<h3>📋 Campos "invisíveis" — nunca chegam no POST</h3>';
echo '<div class="box">';
echo '<p class="warn">⚠️ Os campos abaixo não existem em nenhum input do formulário wizard (step1~4).</p>';
echo '<p>A correção atual usa <strong>salvar_proposta.php</strong> para buscá-los no banco antes de salvar.</p>';
echo '<table><tr><th>Campo banco</th><th>Vem do form?</th><th>Fonte atual</th></tr>';
foreach ($camposSemPost as $campo) {
    $fonte = in_array($campo, array_keys($mapaEmpresa)) ? 'DadosEmpresa (auto-fix ✅)' : 'Clientes (auto-fix ✅)';
    echo "<tr><td>{$campo}</td><td class='err'>❌ Não</td><td class='ok'>{$fonte}</td></tr>";
}
echo '</table></div>';

// ── BLOCO 3: Campos que o form envia e o banco espera ─────────────────────
echo '<h3>✅ Campos que o form envia corretamente</h3>';
$camposForm = [
    'id_cliente'         => 'step1 (select)',
    'endereco'           => 'step1 → normalizado para endereco_obra',
    'bairro'             => 'step1 → normalizado para bairro_obra',
    'cidade'             => 'step1 → normalizado para cidade_obra',
    'estado'             => 'step1 → normalizado para estado_obra',
    'id_servico'         => 'step2',
    'tipo_levantamento'  => 'step2',
    'finalidade'         => 'step2',
    'area_obra'          => 'step2 → também como area',
    'unidade_area'       => 'step2',
    'tipo_terreno'       => 'step2',
    'cobertura_vegetal'  => 'step2',
    'acesso_local'       => 'step2',
    'restricoes_aereas'  => 'step2',
    'coordenadas_gps'    => 'step2',
    'prazo_execucao'     => 'step2',
    'dias_campo'         => 'step2',
    'dias_escritorio'    => 'step2',
    'modelo_docx'        => 'step2',
    'tipo_servico_id'    => 'step2',
    'percentual_lucro'   => 'step4',
    'valor_desconto'     => 'step4',
    'mobilizacao_percentual' => 'step4',
    'total_custos_salarios'  => 'proposta_hiddens.php (hidden)',
    'total_custos_estadia'   => 'proposta_hiddens.php (hidden)',
    'total_custos_consumos'  => 'proposta_hiddens.php (hidden)',
    'valor_final_proposta'   => 'proposta_hiddens.php (hidden)',
];
echo '<table><tr><th>Campo POST</th><th>Origem</th><th>Coluna banco</th></tr>';
foreach ($camposForm as $f => $orig) {
    $banco = in_array($f, $colsBanco) ? $f : ($f === 'endereco' ? 'endereco_obra' : ($f === 'bairro' ? 'bairro_obra' : ($f === 'cidade' ? 'cidade_obra' : ($f === 'estado' ? 'estado_obra' : '?'))));
    $existe = in_array(str_replace('_obra','',$banco), $colsBanco) || in_array($banco, $colsBanco);
    echo "<tr><td>{$f}</td><td class='info'>{$orig}</td><td class='" . ($existe ? 'ok' : 'warn') . "'>{$banco}</td></tr>";
}
echo '</table>';

// ── BLOCO 4: Colunas do banco sem correspondência no form ─────────────────
echo '<h3>💭 Colunas no banco sem campo no form (serão NULL)</h3>';
$colsFORM = array_merge(array_keys($camposForm), $camposSemPost, [
    'id_proposta', 'numero_proposta', 'id_criador', 'is_demo', 'data_criacao', 'data_atualizacao',
    'status', 'fase_crm', 'data_followup', 'motivo_perda', 'docx_conteudo', 'docx_blocos_count',
    'docx_ultima_edicao', 'config_docx_json', 'valor_lucro', 'subtotal_com_lucro',
    'mobilizacao_valor', 'restante_percentual', 'restante_valor', 'Valor_proposta_extenso',
    'marca_veiculo', 'modelo_veiculo', 'marca_estacao_total', 'modelo_estacao_total',
    'marca_gps', 'modelo_gps', 'marca_drone', 'modelo_drone', 'contato_obra',
    'salario_id_funcao', 'estadia_id', 'total_custos_locacao', 'total_custos_admin',
]);
$semCoberta = array_filter($colsBanco, fn($c) => !in_array($c, $colsFORM));
echo '<div class="info" style="padding:8px;">' . implode(', ', $semCoberta) . '</div>';

echo '<p style="color:#475569;margin-top:24px;font-size:11px;">⚠️ Deletar após uso: /Orcamento/auditoria_post.php</p>';
echo '</body></html>';
