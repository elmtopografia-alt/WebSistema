<?php

/**
 * ARQUIVO: criar_proposta.php (Refatorado v3.0)
 * OBJETIVO: Versão modular com CSS/JS externos e Partials
 */

require_once 'session_validator.php';
require_once 'config.php';
require_once 'db.php';
// require_once 'valida_demo.php'; // Temporariamente desativado para teste

$id_usuario = $_SESSION['usuario_id'] ?? 0;
if (!$id_usuario) {
    header('Location: login.php');
    exit;
}

// Debug temporário
ini_set('display_errors', 1);
error_reporting(E_ALL);

// ==================== LIMPEZA DE RASCUNHO (NOVA PROPOSTA) ====================
if (isset($_GET['nova']) && $_GET['nova'] == '1') {
    // Limpa dados de sessão relacionados à proposta
    unset($_SESSION['dados_proposta']);
    unset($_SESSION['id_proposta_edicao']);
    
    // Flag para o JavaScript limpar o localStorage
    $limpar_rascunho_js = true;
}
// =============================================================================

// ==================== CACHE E DADOS ====================
$cache_key = "proposta_dados_{$id_usuario}";
$dados_cache = false;

if (function_exists('apcu_fetch')) {
    $dados_cache = apcu_fetch($cache_key);
}

if ($dados_cache === false) {
    try {
        $ambiente = $_SESSION['ambiente'] ?? 'producao';
        $conn = ($ambiente === 'demo') ? Database::getDemo() : Database::getProd();

        // 1. Clientes
        $stmt = $conn->prepare("SELECT id_cliente, nome_cliente, telefone, celular FROM Clientes WHERE id_criador = ? ORDER BY nome_cliente ASC LIMIT 500");
        $stmt->bind_param('i', $id_usuario);
        $stmt->execute();
        $res = $stmt->get_result();
        $clientes = [];
        while ($row = $res->fetch_assoc()) $clientes[] = $row;

        // 2. Tabelas Auxiliares (Serviços, etc)
        $arrays_js = [];
        $tabelas = [
            'Tipo_Servicos' => ['id' => 'id_servico', 'nome' => 'nome', 'extra' => 'descricao'],
            'Tipo_Funcoes' => ['id' => 'id_funcao', 'nome' => 'nome', 'valor' => 'salario_base_default'],
            'Tipo_Estadia' => ['id' => 'id_estadia', 'nome' => 'nome', 'valor' => 'valor_unitario_default'],
            'Tipo_Consumo' => ['id' => 'id_consumo', 'nome' => 'nome', 'litro' => 'valor_litro_default', 'kml' => 'consumo_kml_default'],
            'Tipo_Locacao' => ['id' => 'id_locacao', 'nome' => 'nome', 'valor' => 'valor_mensal_default'],
            'Tipo_Custo_Admin' => ['id' => 'id_custo_admin', 'nome' => 'nome', 'valor' => 'valor_default']
        ];

        foreach ($tabelas as $tbl => $cols) {
            $r = $conn->query("SELECT * FROM {$tbl} ORDER BY nome ASC");
            $arrays_js[$tbl] = [];
            while ($row = $r->fetch_assoc()) {
                $item = ['id' => $row[$cols['id']], 'nome' => $row[$cols['nome']]];
                if (isset($cols['extra'])) $item['descricao'] = $row[$cols['extra']];
                if (isset($cols['valor'])) $item['valor'] = (float)$row[$cols['valor']];
                if (isset($cols['litro'])) $item['litro'] = (float)$row[$cols['litro']];
                if (isset($cols['kml'])) $item['kml'] = (float)$row[$cols['kml']];
                $arrays_js[$tbl][] = $item;
            }
        }

        // 3. Estados
        $estados = [];
        $r = $conn->query("SELECT sigla, nome FROM estados ORDER BY nome ASC");
        while ($row = $r->fetch_assoc()) $estados[] = $row;

        // 4. Marcas
        $marcas_por_tipo = [];
        $r = $conn->query("SELECT id_marca, id_locacao, nome_marca FROM Marcas ORDER BY nome_marca ASC");
        while ($row = $r->fetch_assoc()) {
            $marcas_por_tipo[$row['id_locacao']][] = ['id' => $row['id_marca'], 'nome' => $row['nome_marca']];
        }

        // 5. Empresa Endereço
        $empresa_endereco = '';
        $stmt_emp = $conn->prepare("SELECT Endereco, Cidade, Estado FROM DadosEmpresa WHERE id_criador = ? LIMIT 1");
        $stmt_emp->bind_param('i', $id_usuario);
        $stmt_emp->execute();
        $res_emp = $stmt_emp->get_result();
        if ($row = $res_emp->fetch_assoc()) {
            $empresa_endereco = implode(', ', array_filter([$row['Endereco'], $row['Cidade'], $row['Estado']]));
        }

        $dados_cache = [
            'clientes' => $clientes,
            'arrays_js' => $arrays_js,
            'estados' => $estados,
            'marcas' => $marcas_por_tipo,
            'empresa_endereco' => $empresa_endereco
        ];

        if (function_exists('apcu_store')) apcu_store($cache_key, $dados_cache, 300);

    } catch (Exception $e) {
        die("Erro ao carregar dados: " . $e->getMessage());
    }
} else {
    extract($dados_cache);
}

// Variáveis para a View
$clientes = $dados_cache['clientes'] ?? [];
$servicos = $dados_cache['arrays_js']['Tipo_Servicos'] ?? [];
$estados = $dados_cache['estados'] ?? [];

// ✅ EXTRAÇÃO DAS VARIÁVEIS DE CUSTOS (necessário para window.SGT_DATA)
$tipos_funcao = $dados_cache['arrays_js']['Tipo_Funcoes'] ?? [];
$tipos_estadia = $dados_cache['arrays_js']['Tipo_Estadia'] ?? [];
$tipos_consumo = $dados_cache['arrays_js']['Tipo_Consumo'] ?? [];
$tipos_locacao = $dados_cache['arrays_js']['Tipo_Locacao'] ?? [];
$tipos_admin = $dados_cache['arrays_js']['Tipo_Custo_Admin'] ?? [];
$marcas_por_tipo = $dados_cache['marcas'] ?? [];
$empresa_endereco = $dados_cache['empresa_endereco'] ?? '';

// CSRF
if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

?>
<!DOCTYPE html>
<html lang="pt-br" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <meta name="theme-color" content="#2563eb">
    <title>Nova Proposta | SGT Premium</title>
    
    <?php
    // Flag para limpar localStorage (Nova Proposta)
    // Se NÃO vier id_proposta na URL, é nova proposta = limpa cache
    $is_nova_proposta = !isset($_GET['id_proposta']) && !isset($_POST['id_proposta']);
    ?>
    


    <!-- Preconnect -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <!-- CSS Crítico -->
    <style>
        :root{--primary:#2563eb;--bg:#fafbfc;--card:#fff;--border:#94a3b8}
        body{margin:0;font-family:Inter,sans-serif;background:var(--bg)}
        .wizard-wrapper{background:var(--card);min-height:100vh}
        @media(min-width:768px){.wizard-wrapper{max-width:900px;margin:1rem auto;border-radius:16px;box-shadow:0 4px 6px -1px rgba(0,0,0,.05);border:1px solid var(--border)}}
        /* Skeleton Loading Inicial */
        .step-panel { display: none; }
        .step-panel.active { display: block; animation: fadeIn 0.3s ease; }
        @keyframes fadeIn { from { opacity:0; transform:translateY(10px); } to { opacity:1; transform:translateY(0); } }
    </style>

    <!-- CSS Externo (Async) -->
    <link rel="stylesheet" href="assets/css/proposta.css?v=<?= time() ?>_fix" media="print" onload="this.media='all'">
    <noscript><link rel="stylesheet" href="assets/css/proposta.css?v=<?= time() ?>"></noscript>

    <!-- Libs CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" media="print" onload="this.media='all'">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" media="print" onload="this.media='all'">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="ambient-glow"></div>
    <div class="toast-container" id="toast-container"></div>

    <!-- NAVBAR -->
    <nav class="navbar">
        <div style="display:flex; justify-content:space-between; align-items:center; width:100%;">
            <a class="navbar-brand" href="painel.php">
                <i class="bi bi-lightning-charge-fill text-warning"></i> SGT Propostas
                <?php if (($_SESSION['ambiente'] ?? '') === 'demo'): ?>
                    <span class="badge-demo">DEMO</span>
                <?php endif; ?>
            </a>
            <a href="painel.php" class="btn btn-outline" style="min-height: 36px; padding: 0.5rem 1rem;">
                <i class="bi bi-x-lg"></i>
            </a>
        </div>
    </nav>

    <!-- FORM WIZARD -->
    <form action="salvar_proposta.php" method="POST" id="form-proposta" novalidate>
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
        <input type="hidden" name="form_complete" value="1">

        <div class="wizard-wrapper">
            <!-- PROGRESS -->
            <div class="wizard-progress">
                <div class="steps-container">
                    <div class="step active" data-step="1"><div class="step-number">1</div><span class="step-label">Cliente</span></div>
                    <div class="step" data-step="2"><div class="step-number">2</div><span class="step-label">Escopo</span></div>
                    <div class="step" data-step="3"><div class="step-number">3</div><span class="step-label">Custos</span></div>
                    <div class="step" data-step="4"><div class="step-number">4</div><span class="step-label">Fechamento</span></div>
                </div>
            </div>

            <!-- CONTENT -->
            <div class="wizard-content">
                <?php include 'partials/proposta_step1.php'; ?>
                <?php include 'partials/proposta_step2.php'; ?>
                <?php include 'partials/proposta_step3.php'; ?>
                <?php include 'partials/proposta_step4.php'; ?>
            </div>

            <!-- FOOTER NAVIGATION -->
            <div class="wizard-footer">
                <button type="button" class="btn btn-outline hidden" id="btn-prev">
                    <i class="bi bi-arrow-left"></i> <span class="hidden sm:inline">Voltar</span>
                </button>
                <div style="flex:1"></div>
                <!-- Status Autosave Indicator -->
                <div id="status-autosave" style="font-size:0.8rem; color:#64748b; margin-right:1rem; display:none;"></div>

                <button type="button" class="btn btn-primary" id="btn-next">
                    Próximo <i class="bi bi-arrow-right"></i>
                </button>
                <button type="submit" class="btn btn-primary hidden" id="btn-legacy" name="acao" value="gerar_doc" style="margin-right: 10px;">
                    <i class="bi bi-file-earmark-word"></i> Gerar Proposta
                </button>
                <button type="button" class="btn btn-success hidden" id="btn-finish" onclick="irParaEditor()">
                    <i class="bi bi-magic"></i> Editor Avançado ✨
                </button>
            </div>
        </div>

        <?php include 'partials/proposta_hiddens.php'; ?>
    </form>

    <?php include 'partials/proposta_modais.php'; ?>

    <!-- SCRIPTS -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <!-- DATA JSON -->
    <script>
        // Dados PHP para JS (JSON seguro)
        window.SGT_DATA = {
            opcoesFuncao: <?= json_encode($tipos_funcao, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
            opcoesEstadia: <?= json_encode($tipos_estadia, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
            opcoesConsumo: <?= json_encode($tipos_consumo, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
            opcoesLocacao: <?= json_encode($tipos_locacao, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
            opcoesAdmin: <?= json_encode($tipos_admin, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
            marcasPorTipo: <?= json_encode($marcas_por_tipo, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
            enderecoEmpresa: <?= json_encode($empresa_endereco, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>
        };
        
        // Debug
        console.log('📊 SGT_DATA carregado:', window.SGT_DATA);
    </script>

    <!-- MODULAR JS -->
    <script src="assets/js/utils.js?v=<?= time() ?>"></script>
    <script src="assets/js/calculator.js?v=<?= time() ?>"></script>
    <script src="assets/js/wizard.js?v=<?= time() ?>"></script>
    <script>
        // Flag para limpar rascunho (no caso de nova proposta)
        <?php if($is_nova_proposta): ?>
        window.SGT_CLEAR_STORAGE = true;
        <?php endif; ?>
    </script>
    <script src="assets/js/cliente-modal.js?v=<?= time() ?>"></script>
    <script src="assets/js/costs-manager.js?v=<?= time() ?>"></script>
    <script src="assets/js/autosave.js?v=<?= time() ?>"></script>
    <script src="assets/js/proposta.js?v=<?= time() ?>"></script>

</body>
</html>