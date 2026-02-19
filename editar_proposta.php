<?php

/**
 * ARQUIVO: editar_proposta.php (Refatorado v3.0)
 * OBJETIVO: Versão modular com CSS/JS externos e Partials (IGUAL AO CRIAR), mas carregando dados.
 */

require_once 'session_validator.php';
require_once 'config.php';
require_once 'ConnectionManager.php';
require_once 'PropostaRepository.php';

$id_usuario = $_SESSION['usuario_id'] ?? 0;
if (!$id_usuario) {
    header('Location: login.php');
    exit;
}

if (!isset($_GET['id'])) { header("Location: painel.php"); exit; }
$id_proposta = intval($_GET['id']);

// ==================== CARREGAMENTO DE DADOS (Refatorado via Repo) ====================
try {
    $repo = new PropostaRepository();
    
    // 1. Dados de Apoio (Lookup)
    $dados_lookup = $repo->getAllLookupData($id_usuario);
    
    // 2. Dados da Proposta Principal + Itens
    $proposta_atual = $repo->buscarPorId($id_proposta);
    
    if (!$proposta_atual || $proposta_atual['id_criador'] != $id_usuario) {
        die("<div class='alert alert-danger'>Proposta não encontrada ou acesso negado.</div>");
    }

    // Variáveis para a View (Compatibilidade com Partials)
    $clientes = $dados_lookup['clientes'] ?? [];
    $servicos = $dados_lookup['arrays_js']['Tipo_Servicos'] ?? [];
    $estados = $dados_lookup['estados'] ?? [];
    $tipos_funcao = $dados_lookup['arrays_js']['Tipo_Funcoes'] ?? [];
    $tipos_estadia = $dados_lookup['arrays_js']['Tipo_Estadia'] ?? [];
    $tipos_consumo = $dados_lookup['arrays_js']['Tipo_Consumo'] ?? [];
    $tipos_locacao = $dados_lookup['arrays_js']['Tipo_Locacao'] ?? [];
    $tipos_admin = $dados_lookup['arrays_js']['Tipo_Custo_Admin'] ?? [];
    $marcas_por_tipo = $dados_lookup['marcas'] ?? [];
    $empresa_endereco = $dados_lookup['empresa_endereco'] ?? '';
    
    // Itens Populados
    $itens_atuais = $proposta_atual['itens'];

} catch (Exception $e) {
    die("Erro ao carregar dados: " . $e->getMessage());
}

// CSRF
if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

?>
<!DOCTYPE html>
<html lang="pt-br" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <meta name="theme-color" content="#2563eb">
    <title>Editar Proposta #<?= $proposta_atual['numero_proposta'] ?> | SGT Premium</title>
    
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
        
        .badge-edicao { background: rgba(245, 158, 11, 0.1); color: #f59e0b; padding: 0.25rem 0.75rem; border-radius: 999px; font-size: 0.75rem; font-weight: 600; border: 1px solid rgba(245, 158, 11, 0.2); display: flex; align-items: center; gap: 0.35rem; }
    </style>

    <!-- CSS Externo (Async) -->
    <link rel="stylesheet" href="assets/css/proposta.css?v=<?= time() ?>" media="print" onload="this.media='all'">
    <noscript><link rel="stylesheet" href="assets/css/proposta.css?v=<?= time() ?>"></noscript>

    <!-- Libs CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" media="print" onload="this.media='all'">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" media="print" onload="this.media='all'">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/sgt_mobile.css?v=<?= time() ?>">
</head>
<body>
    <div class="ambient-glow"></div>
    <div class="toast-container" id="toast-container"></div>

    <!-- NAVBAR -->
    <nav class="navbar">
        <div style="display:flex; justify-content:space-between; align-items:center; width:100%;">
            <a class="navbar-brand" href="painel.php">
                <i class="bi bi-lightning-charge-fill text-warning"></i> SGT Propostas
                <span class="badge-edicao">
                    <i class="bi bi-pencil-square"></i> Modo de Edição
                </span>
            </a>
            <a href="painel.php" class="btn btn-outline" style="min-height: 36px; padding: 0.5rem 1rem;">
                <i class="bi bi-x-lg"></i>
            </a>
        </div>
    </nav>

    <!-- FORM WIZARD -->
    <form action="salvar_proposta.php" method="POST" id="form-proposta" novalidate>
        <input type="hidden" name="id_proposta_original" value="<?= $id_proposta ?>">
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

            <!-- CONTENT (Reutiliza os mesmos partials de criar_proposta) -->
            <div class="wizard-content">
                <?php include 'partials/proposta_step1.php'; ?>
                <?php include 'partials/proposta_step2.php'; ?>
                <?php include 'partials/proposta_step3.php'; ?>
                <?php include 'partials/proposta_step4.php'; ?>
            </div>

            <!-- FOOTER NAVIGATION -->
            <div class="wizard-footer">
                <div class="footer-left">
                    <button type="button" class="btn btn-outline hidden" id="btn-prev">
                        <i class="bi bi-arrow-left"></i> <span class="hidden sm:inline">Voltar</span>
                    </button>
                    <!-- BOTÃO EDITOR AVANÇADO (Lado Esquerdo inferior se solicitado, ou mantendo padrão footer) -->
                    <!-- Mantendo no padrão mas garantindo funcionalidade -->
                </div>
                
                <div style="flex:1"></div>
                
                <button type="button" class="btn btn-primary" id="btn-next">
                    Próximo <i class="bi bi-arrow-right"></i>
                </button>
                
                <!-- Botão Legado (Salvar Dados Apenas) -->
                <button type="submit" class="btn btn-secondary hidden" id="btn-legacy" name="acao" value="salvar_edicao" style="margin-right: 10px;" title="Apenas Salvar Dados">
                    <i class="bi bi-save"></i> Salvar Dados
                </button>

                <!-- Botão Principal: Editor Avançado (Target: editor_dinamico.php) -->
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
        // Dados PHP para LEITURA (Opções)
        window.SGT_DATA = {
            opcoesFuncao: <?= json_encode($tipos_funcao, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
            opcoesEstadia: <?= json_encode($tipos_estadia, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
            opcoesConsumo: <?= json_encode($tipos_consumo, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
            opcoesLocacao: <?= json_encode($tipos_locacao, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
            opcoesAdmin: <?= json_encode($tipos_admin, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
            marcasPorTipo: <?= json_encode($marcas_por_tipo, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
            enderecoEmpresa: <?= json_encode($empresa_endereco, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>
        };

        // Dados PHP da PROPOSTA ATUAL (Para popular os campos)
        window.SGT_EDIT_DATA = {
            proposta: <?= json_encode($proposta_atual, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
            itens: <?= json_encode($itens_atuais, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>
        };
       
    </script>

    <!-- MODULAR JS -->
    <script src="assets/js/utils.js?v=<?= time() ?>"></script>
    <script src="assets/js/calculator.js?v=<?= time() ?>"></script>
    <script src="assets/js/wizard.js?v=<?= time() ?>"></script>
    <script src="assets/js/cliente-modal.js?v=<?= time() ?>"></script>
    <script src="assets/js/costs-manager.js?v=<?= time() ?>"></script>
    <!-- Substitui autosave por script de população -->
    <!-- <script src="assets/js/autosave.js?v=<?= time() ?>"></script> -->
    <script src="assets/js/proposta.js?v=<?= time() ?>"></script>
    
    <!-- Script Específico de Edição (Popula Campos) -->
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        
        // 1. Função de espera (para garantir que Select2 e outros scripts carregaram)
        const waitForLib = (check, callback, totalTime=0) => {
            if(totalTime > 5000) return; // Timeout 5s
            if(check()) callback();
            else setTimeout(() => waitForLib(check, callback, totalTime+100), 100);
        };

        waitForLib(() => window.CostsManager && window.Calculator, () => {
            console.log('🔄 Iniciando população de dados...');
            const data = window.SGT_EDIT_DATA;
            if(!data || !data.proposta) return;

            const p = data.proposta;
            const itens = data.itens;

            // --- CAMPOS SIMPLES ---
            const fields = [
                // Step 1
                'contato_obra', 'endereco', 'bairro', 'cidade', 'estado', 
                // Step 2
                'finalidade', 'tipo_levantamento', 'area', 'prazo_execucao',
                'dias_campo', 'dias_escritorio', 
                // Step 4
                'mobilizacao_percentual', 'percentual_lucro', 'valor_desconto'
            ];

            const fieldMap = {
                'endereco': 'endereco_obra',
                'bairro': 'bairro_obra',
                'cidade': 'cidade_obra',
                'estado': 'estado_obra',
                'area': 'area_obra'
            };

            fields.forEach(f => {
                const dbField = fieldMap[f] || f;
                if(p[dbField] !== undefined && p[dbField] !== null) {
                    const el = document.getElementsByName(f)[0] || document.getElementById(f);
                    if(el) {
                        el.value = p[dbField];
                        // Disparar evento para inputs que dependem disto (recalc)
                        el.dispatchEvent(new Event('input', {bubbles:true}));
                    }
                }
            });

            // --- SELECTS ---
            if(p.id_cliente) {
                $('#id_cliente').val(p.id_cliente).trigger('change');
            }
            if(p.id_servico) {
                $('#id_servico').val(p.id_servico).trigger('change');
            }

            // --- TABELAS (Complexo) ---
            
            // Função Hack para adicionar linha via Click no botão
            const addItem = (type, item) => {
                const btnId = `btn-add-${type.replace(/s$/, '')}`; // btn-add-salario
                // Mapeamento manual de IDs se necessário
                 const btnMap = {
                    'salarios': 'btn-add-salario',
                    'estadia': 'btn-add-estadia',
                    'consumos': 'btn-add-consumo',
                    'locacao': 'btn-add-locacao',
                    'admin': 'btn-add-admin'
                };
                const btn = document.getElementById(btnMap[type]);
                if(!btn) return console.error('Botão não achado:', btnMap[type]);

                // Clica para adicionar linha vazia
                btn.click();

                // Pega a última linha adicionada
                const tbody = document.getElementById(`tbody-${type}`);
                const rows = tbody.querySelectorAll('tr');
                const row = rows[rows.length - 1];

                if(!row) return;

                // Preenche Select Principal
                const select = row.querySelector('select:not(.marca-selector)'); // Exclui marca se houver
                
                let idVal;
                if(type == 'salarios') idVal = item.id_funcao;
                if(type == 'estadia') idVal = item.id_estadia;
                if(type == 'consumos') idVal = item.id_consumo;
                if(type == 'locacao') idVal = item.id_locacao;
                if(type == 'admin') idVal = item.id_custo_admin;

                if(select && idVal) {
                    select.value = idVal;
                    // Trigger change para atualizar data-attributes (valor default) e liberar outros campos
                    // IMPORTANTE: jQuery trigger para o Select2 ou JS puro para nativo?
                    // CostsManager usa vanilla JS 'change' listener, mas se for stylizado..
                    // Vamos tentar vanilla primeiro
                    select.dispatchEvent(new Event('change', {bubbles:true}));
                }

                // Preenche Inputs (Qtd, Valor, etc)
                // É mais seguro buscar por atributo 'name' parcial porque as classes variam
                const inputs = Array.from(row.querySelectorAll('input'));
                
                const setInput = (idx, val) => {
                    if(inputs[idx] && val !== undefined) {
                        inputs[idx].value = val;
                        // Dispara input para recálculos
                        inputs[idx].dispatchEvent(new Event('input', {bubbles:true}));
                    }
                };

               if(type == 'salarios') {
                   // Item: quantidade, salario_base, encargos, dias
                   // Busca por name pattern é mais seguro
                   const find = (n) => inputs.find(i => i.name.includes(n));
                   
                   const iQtd = find('[qtd]'); if(iQtd) iQtd.value = item.quantidade;
                   const iVal = find('[valor]'); if(iVal) iVal.value = item.salario_base;
                   const iEnc = find('encargos'); if(iEnc) iEnc.value = item.encargos || ((item.fator_encargos - 1)*100).toFixed(2);
                   const iDias = find('[dias]'); if(iDias) iDias.value = item.dias;
               }
               
               else if(type == 'estadia') {
                   const find = (n) => inputs.find(i => i.name.includes(n));
                   if(find('[qtd]')) find('[qtd]').value = item.quantidade;
                   if(find('[valor]')) find('[valor]').value = item.valor_unitario;
                   if(find('[dias]')) find('[dias]').value = item.dias;
               }
               
               else if(type == 'consumos') {
                   const find = (n) => inputs.find(i => i.name.includes(n));
                   if(find('[qtd]')) find('[qtd]').value = item.quantidade;
                   if(find('kml')) find('kml').value = item.consumo_kml;
                   if(find('litro')) find('litro').value = item.valor_litro;
                   // km_total é calculado
               }

               else if(type == 'locacao') {
                   const find = (n) => inputs.find(i => i.name.includes(n));
                   if(find('[qtd]')) find('[qtd]').value = item.quantidade;
                   if(find('[valor]')) find('[valor]').value = item.valor_mensal;
                   if(find('[dias]')) find('[dias]').value = item.dias;
                   
                   // Marca
                   if(item.id_marca) {
                       // Marca geralmente é carregada via AJAX ou JS local após select change
                       // Vamos tentar setar com delay
                       setTimeout(() => {
                           const selMarca = row.querySelector('.marca-selector') || row.querySelector('select[name*="marca"]');
                           if(selMarca) {
                               selMarca.value = item.id_marca;
                           }
                       }, 500);
                   }
               }

               else if(type == 'admin') {
                   const find = (n) => inputs.find(i => i.name.includes(n));
                   if(find('[qtd]')) find('[qtd]').value = item.quantidade;
                   if(find('[valor]')) find('[valor]').value = item.valor;
               }
               
               // Dispara input final em todos para garantir consistencia visual
               inputs.forEach(i => i.dispatchEvent(new Event('input', {bubbles:true})));
            };

            // Loop e adiciona
            if(itens.salarios) itens.salarios.forEach(i => addItem('salarios', i));
            if(itens.estadia) itens.estadia.forEach(i => addItem('estadia', i));
            if(itens.consumo) itens.consumo.forEach(i => addItem('consumos', i));
            if(itens.locacao) itens.locacao.forEach(i => addItem('locacao', i));
            if(itens.admin) itens.admin.forEach(i => addItem('admin', i));

            // Atualiza totais após o "caos" de eventos
            setTimeout(() => {
                if(window.Calculator) window.Calculator.updateTotals();
                console.log('✅ Dados populados.');
            }, 1000);

        }); // Fim waitForLib
    });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            setTimeout(() => {
                if (typeof SGTUtils !== 'undefined') {
                    SGTUtils.showToast('Modo de Edição ativado. Suas alterações gerarão uma nova revisão desta proposta.', 'info');
                }
            }, 1000);
        });
    </script>
</body>
</html>