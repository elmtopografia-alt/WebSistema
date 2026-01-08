<?php
// Inicio: painel.php
// Função: Dashboard Principal
// Atualização: Adicionado botões de acesso rápido ao Cadastro de Clientes.

session_start();
require_once 'config.php';
require_once 'db.php';

// 1. Verificação de Segurança
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

$id_usuario = $_SESSION['usuario_id'];
$nome_usuario = $_SESSION['usuario_nome'] ?? 'Usuário';
$ambiente = $_SESSION['ambiente'] ?? 'producao';
$perfil_usuario = $_SESSION['perfil'] ?? 'cliente';
$conn = ($ambiente === 'demo') ? Database::getDemo() : Database::getProd();

// 1.1 Verificação de Validade (Apenas Demo)
if ($ambiente === 'demo') {
    $stmtVal = $conn->prepare("SELECT validade_acesso FROM Usuarios WHERE id_usuario = ?");
    $stmtVal->bind_param('i', $id_usuario);
    $stmtVal->execute();
    $resVal = $stmtVal->get_result()->fetch_assoc();
    
    if ($resVal) {
        $agora = new DateTime();
        $validade = new DateTime($resVal['validade_acesso']);
        if ($agora > $validade) {
            header("Location: bloqueio_demo.php");
            exit;
        }
    }
}

function limparStringArquivo($string) {
    return preg_replace('/[^a-zA-Z0-9]/', '', iconv('UTF-8', 'ASCII//TRANSLIT', $string));
}

// 2. Dados Iniciais
try {
    $sqlKPI = "SELECT 
                COUNT(*) as qtd_total,
                SUM(CASE WHEN status LIKE '%aprov%' OR status LIKE '%conclu%' OR status LIKE '%aceita%' THEN 1 ELSE 0 END) as qtd_aprovada,
                SUM(CASE WHEN status LIKE '%envia%' THEN 1 ELSE 0 END) as qtd_enviada,
                SUM(CASE WHEN status LIKE '%cancel%' THEN 1 ELSE 0 END) as qtd_cancelada,
                SUM(CASE WHEN status LIKE '%elabora%' OR status LIKE '%rascunho%' THEN 1 ELSE 0 END) as qtd_elaborada
               FROM Propostas WHERE id_criador = ?";
    $stmtKPI = $conn->prepare($sqlKPI);
    $stmtKPI->bind_param('i', $id_usuario);
    $stmtKPI->execute();
    $kpi = $stmtKPI->get_result()->fetch_assoc();

    $sqlLista = "SELECT p.*, c.nome_cliente 
                 FROM Propostas p 
                 LEFT JOIN Clientes c ON p.id_cliente = c.id_cliente 
                 WHERE p.id_criador = ? 
                 ORDER BY p.data_criacao DESC LIMIT 50";
    $stmtLista = $conn->prepare($sqlLista);
    $stmtLista->bind_param('i', $id_usuario);
    $stmtLista->execute();
    $resultLista = $stmtLista->get_result();

} catch (Exception $e) { die("Erro SQL: " . $e->getMessage()); }

function getStatusClass($status) {
    $s = mb_strtolower($status);
    if (strpos($s, 'aprov')!==false) return 'btn-success';
    if (strpos($s, 'envia')!==false) return 'btn-primary';
    if (strpos($s, 'cancel')!==false) return 'btn-danger';
    return 'btn-warning text-dark';
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SGT | Painel</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        body { background-color: #f0f2f5; font-family: 'Inter', sans-serif; color: #444; }
        
        /* Header */
        .header-painel { 
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); 
            color: white; padding: 30px; border-radius: 16px; margin-bottom: 30px; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.1); 
            display: flex; justify-content: space-between; align-items: center; 
        }

        /* Cards KPI */
        .card-kpi { 
            background: white; border: none; border-radius: 16px; padding: 25px; height: 100%; 
            position: relative; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.05); 
            transition: all 0.3s ease; 
        }
        .card-kpi:hover { transform: translateY(-5px); box-shadow: 0 8px 25px rgba(0,0,0,0.1); }
        
        .icon-sq { 
            width: 56px; height: 56px; border-radius: 14px; display: flex; align-items: center; justify-content: center; 
            font-size: 1.5rem; margin-right: 20px; color: white; flex-shrink: 0; box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        .bg-icon-yellow { background: linear-gradient(135deg, #ffc107, #ffca2c); } 
        .bg-icon-blue { background: linear-gradient(135deg, #0d6efd, #0b5ed7); } 
        .bg-icon-green { background: linear-gradient(135deg, #198754, #157347); } 
        .bg-icon-red { background: linear-gradient(135deg, #dc3545, #bb2d3b); }
        
        .kpi-value { font-size: 2.2rem; font-weight: 800; margin-bottom: 0; line-height: 1.1; color: #1e293b; }
        .kpi-label { font-size: 0.8rem; color: #64748b; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 5px; }
        .kpi-sub { font-size: 0.85rem; font-weight: 500; }

        /* Charts */
        .chart-box { 
            background: white; border-radius: 16px; padding: 30px; 
            box-shadow: 0 4px 20px rgba(0,0,0,0.05); height: 100%; 
            display: flex; flex-direction: column; border: 1px solid rgba(0,0,0,0.02);
        }
        .chart-title { font-size: 1.1rem; font-weight: 700; margin-bottom: 20px; color: #1e293b; }
        .chart-container { position: relative; flex-grow: 1; min-height: 300px; width: 100%; }

        /* Table */
        .card-table { border-radius: 16px; overflow: hidden; border: none; box-shadow: 0 4px 20px rgba(0,0,0,0.05); }
        .table-custom th { font-size: 0.75rem; text-transform: uppercase; color: #64748b; font-weight: 700; background-color: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 15px 25px; }
        .table-custom td { font-size: 0.9rem; color: #334155; vertical-align: middle; padding: 15px 25px; border-bottom: 1px solid #f1f5f9; }
        .table-custom tr:last-child td { border-bottom: none; }
        
        .status-dropdown .btn { font-size: 0.75rem; font-weight: 700; padding: 6px 15px; border-radius: 30px; text-transform: uppercase; width: 130px; letter-spacing: 0.5px; }
        
        /* CTA Demo */
        .cta-demo {
            position: fixed; bottom: 30px; right: 30px; z-index: 9999;
            background: linear-gradient(135deg, #4154f1, #2a3ecc);
            color: white; padding: 20px; border-radius: 16px;
            box-shadow: 0 10px 40px rgba(65, 84, 241, 0.3);
            display: flex; align-items: center; gap: 20px;
            animation: slideIn 0.5s ease-out;
            max-width: 400px;
        }
        .cta-content strong { display: block; font-size: 1.1rem; margin-bottom: 4px; }
        .cta-content p { margin: 0; font-size: 0.9rem; opacity: 0.9; }
        .cta-btn { background: white; color: #4154f1; border: none; padding: 10px 20px; border-radius: 8px; font-weight: 700; text-decoration: none; transition: transform 0.2s; white-space: nowrap; }
        .cta-btn:hover { transform: scale(1.05); background: #f8f9fa; color: #2a3ecc; }
        
        @keyframes slideIn { from { transform: translateY(100px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
        
        @media (max-width: 768px) {
            .cta-demo { left: 20px; right: 20px; bottom: 20px; flex-direction: column; text-align: center; }
            .cta-btn { width: 100%; }
        }
    </style>
</head>
<body>

    <!-- AVISO DEMO -->
    <?php if($ambiente == 'demo'): ?>
    <div class="bg-warning text-dark text-center py-2 fw-bold small">
        <i class="bi bi-exclamation-triangle-fill me-1"></i> AMBIENTE DE DEMONSTRAÇÃO - Dados fictícios e temporários. <a href="contratar.php" class="text-dark text-decoration-underline">Contratar Versão Completa</a>
    </div>
    <?php endif; ?>

    <!-- NAVEGAÇÃO SUPERIOR -->
    <nav class="navbar navbar-expand-lg bg-white shadow-sm py-2 mb-4 sticky-top">
        <div class="container-fluid px-4">
            <a class="navbar-brand fw-bold text-dark d-flex align-items-center" href="#">
                <i class="bi bi-grid-fill me-2 text-primary"></i> SGT 
                <?php if($ambiente == 'demo'): ?><span class="badge bg-warning text-dark ms-2" style="font-size: 0.6rem;">DEMO</span><?php endif; ?>
            </a>
            
            <div class="ms-auto d-flex align-items-center gap-3">
                <?php if($ambiente == 'demo'): ?>
                    <a href="setup_empresa_demo.php" class="btn btn-warning btn-sm fw-bold shadow-sm" onclick="return confirm('Confirmar configuração automática?')"><i class="bi bi-lightning-fill"></i> Configurar Empresa Demo</a>
                <?php endif; ?>

                <span class="text-muted small d-none d-md-inline">Olá, <strong><?= htmlspecialchars($nome_usuario) ?></strong></span>
                
                <?php if($ambiente !== 'demo'): ?>
                    <a href="minha_empresa.php" class="btn btn-sm btn-outline-dark"><i class="bi bi-building"></i> Minha Empresa</a>
                    <a href="admin_parametros.php" class="btn btn-sm btn-outline-secondary"><i class="bi bi-gear-fill"></i> Cadastros</a>
                    <?php if($perfil_usuario === 'admin'): ?>
                        <a href="painel_financeiro.php" class="btn btn-sm btn-outline-success"><i class="bi bi-cash-coin"></i> Financeiro</a>
                    <?php endif; ?>
                <?php endif; ?>

                <!-- BOTÃO DE CLIENTES (NOVO) -->
                <a href="meus_clientes.php" class="btn btn-outline-primary btn-sm fw-bold">
                    <i class="bi bi-people-fill"></i> Clientes
                </a>
                
                <a href="criar_proposta.php" class="btn btn-success btn-sm fw-bold shadow-sm px-3"><i class="bi bi-plus-lg me-1"></i> Nova Proposta</a>
                <a href="logout.php" class="btn btn-sm btn-outline-danger">Sair</a>
            </div>
        </div>
    </nav>

    <div class="container-fluid px-4">
        
        <?php if(isset($_GET['msg'])): ?>
            <div class="alert alert-info alert-dismissible fade show small mb-4" role="alert">
                <?php 
                    if($_GET['msg']=='demo_configurada') echo '✅ Dados da empresa fictícia preenchidos!';
                    if($_GET['msg']=='status_atualizado') echo '✅ Status atualizado e gráficos recalculados!';
                    if($_GET['msg']=='sucesso') echo '✅ Proposta salva com sucesso.';
                    else echo 'ℹ️ Operação concluída.';
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- HEADER -->
        <div class="header-painel">
            <div class="d-flex align-items-center">
                <i class="bi bi-bar-chart-line-fill me-3 fs-3 text-warning"></i>
                <div><h4 class="mb-0 fw-bold">Evolução Comercial</h4><small class="opacity-75">Visão Geral</small></div>
            </div>
            <div class="d-none d-md-block text-end"><small class="d-block opacity-50 text-uppercase">Hoje</small><span class="fw-bold fs-5"><?php echo date('d/m/Y'); ?></span></div>
        </div>

        <!-- CARDS KPI -->
        <div class="row g-4 mb-4">
            <div class="col-xl-3 col-md-6"><div class="card-kpi d-flex flex-row align-items-center"><div class="icon-sq bg-icon-yellow"><i class="bi bi-file-text"></i></div><div><div class="kpi-label">Propostas Elaboradas</div><div class="kpi-value" id="kpi-elaborada"><?= $kpi['qtd_elaborada'] ?></div><div class="kpi-sub text-warning">Rascunhos</div></div></div></div>
            <div class="col-xl-3 col-md-6"><div class="card-kpi d-flex flex-row align-items-center"><div class="icon-sq bg-icon-blue"><i class="bi bi-send-fill"></i></div><div><div class="kpi-label">Propostas Enviadas</div><div class="kpi-value" id="kpi-enviada"><?= $kpi['qtd_enviada'] ?></div><div class="kpi-sub text-primary">Aguardando</div></div></div></div>
            <div class="col-xl-3 col-md-6"><div class="card-kpi d-flex flex-row align-items-center"><div class="icon-sq bg-icon-green"><i class="bi bi-check-lg"></i></div><div><div class="kpi-label">Propostas Aceitas</div><div class="kpi-value" id="kpi-aprovada"><?= $kpi['qtd_aprovada'] ?></div><div class="kpi-sub text-success fw-bold">Sucesso!</div></div></div></div>
            <div class="col-xl-3 col-md-6"><div class="card-kpi d-flex flex-row align-items-center"><div class="icon-sq bg-icon-red"><i class="bi bi-x-lg"></i></div><div><div class="kpi-label">Propostas Canceladas</div><div class="kpi-value" id="kpi-cancelada"><?= $kpi['qtd_cancelada'] ?></div><div class="kpi-sub text-danger">Perdidas</div></div></div></div>
        </div>

        <!-- GRÁFICOS -->
        <div class="row g-4 mb-4">
            <div class="col-lg-8"><div class="chart-box"><div class="chart-title">Evolução Financeira</div><div class="chart-container"><canvas id="graficoLinha"></canvas></div></div></div>
            <div class="col-lg-4"><div class="chart-box"><div class="chart-title">Status</div><div class="chart-container"><canvas id="graficoPizza"></canvas></div></div></div>
        </div>

        <!-- LISTA -->
        <div class="card shadow-sm border-0 mb-5">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <h6 class="m-0 fw-bold text-dark">Últimas Propostas</h6>
                <div class="d-flex gap-2">
                    <input type="text" id="filtroTabela" class="form-control form-control-sm" placeholder="🔍 Buscar..." style="width: 200px;">
                    
                    <!-- BOTÃO DE CADASTRO RÁPIDO -->
                    <a href="form_cliente.php" class="btn btn-outline-primary btn-sm" title="Cadastrar Novo Cliente">
                        <i class="bi bi-person-plus-fill"></i> Novo Cliente
                    </a>
                    
                    <a href="ajustar_datas.php" class="btn btn-outline-warning btn-sm" title="Ajustar Datas"><i class="bi bi-calendar-event"></i></a>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-hover table-custom mb-0" id="tabelaPropostas">
                    <thead><tr><th class="ps-4">Data</th><th>Número</th><th>Cliente</th><th>Status</th><th class="text-end">Valor</th><th class="text-center pe-4">Ações</th></tr></thead>
                    <tbody>
                        <?php if($resultLista->num_rows > 0): while($row = $resultLista->fetch_assoc()): 
                            $is_rv = (strpos($row['numero_proposta'], '-Rv') !== false);
                            $nomeArquivo = limparStringArquivo($row['empresa_proponente_nome']) . '-' . $row['numero_proposta'] . '.docx';
                            $arquivoExiste = file_exists(__DIR__ . '/propostas_emitidas/' . $nomeArquivo);
                            if (!$arquivoExiste) { // Fallback antigo
                                $partes = explode('-', $row['numero_proposta']);
                                if(count($partes) >= 3) { $nomeArquivo = limparStringArquivo($row['empresa_proponente_nome']) . '-' . $partes[1] . '-' . end($partes) . '.docx'; $arquivoExiste = file_exists(__DIR__ . '/propostas_emitidas/' . $nomeArquivo); }
                            }
                            $statusDisplay = $row['status'];
                            if($row['status'] == 'Aprovada' || $row['status'] == 'Concluída') $statusDisplay = 'Aceita';
                            $statusClass = getStatusClass($row['status']); $idp = $row['id_proposta'];
                        ?>
                        <tr>
                            <td class="ps-4 fw-bold text-secondary"><?php echo date('d/m/Y', strtotime($row['data_criacao'])); ?></td>
                            <td><div class="fw-bold text-dark"><?php echo $row['numero_proposta']; ?></div><?php if($is_rv): ?><span class="badge bg-info text-dark" style="font-size:0.6rem;">REVISÃO</span><?php endif; ?></td>
                            <td><div class="fw-bold text-secondary"><?php echo htmlspecialchars($row['nome_cliente_salvo']); ?></div></td>
                            <td>
                                <div class="dropdown status-dropdown">
                                    <button id="btn-status-<?= $idp ?>" class="btn <?php echo $statusClass; ?> dropdown-toggle shadow-sm" type="button" data-bs-toggle="dropdown"><?php echo htmlspecialchars($statusDisplay); ?></button>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="#" onclick="mudarStatusAJAX(<?= $idp ?>, 'Em elaboração')">✏️ Em elaboração</a></li>
                                        <li><a class="dropdown-item" href="#" onclick="mudarStatusAJAX(<?= $idp ?>, 'Enviada')">📨 Enviada</a></li>
                                        <li><a class="dropdown-item" href="#" onclick="mudarStatusAJAX(<?= $idp ?>, 'Aprovada')">✅ Aceita</a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item text-danger" href="#" onclick="mudarStatusAJAX(<?= $idp ?>, 'Cancelada')">❌ Cancelada</a></li>
                                    </ul>
                                </div>
                            </td>
                            <td class="text-end fw-bold text-dark">R$ <?php echo number_format($row['valor_final_proposta'], 2, ',', '.'); ?></td>
                            <td class="text-center pe-4">
                                <div class="btn-group">
                                    <a href="editar_proposta.php?id=<?= $idp ?>" class="btn btn-outline-secondary btn-action" title="Editar"><i class="bi bi-pencil-square"></i></a>
                                    <?php if($arquivoExiste): ?><a href="propostas_emitidas/<?php echo $nomeArquivo; ?>" class="btn btn-outline-primary btn-action" title="Baixar" download><i class="bi bi-download"></i></a><?php else: ?><button class="btn btn-outline-secondary btn-action" disabled><i class="bi bi-x-lg"></i></button><?php endif; ?>
                                    <a href="gerar_link_whatsapp.php?id=<?= $idp ?>" target="_blank" class="btn btn-outline-success btn-action" title="WhatsApp"><i class="bi bi-whatsapp"></i></a>
                                    <a href="excluir_proposta.php?id=<?= $idp ?>" class="btn btn-outline-danger btn-action" onclick="return confirm('Excluir?');" title="Excluir"><i class="bi bi-trash"></i></a>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        // JS igual ao anterior, sem mudanças na lógica de gráficos
        function mudarStatusAJAX(id, novoStatus) {
            if(!confirm('Mudar para: ' + novoStatus + '?')) return;
            const btn = document.getElementById('btn-status-' + id);
            const originalText = btn.innerText; btn.innerText = '...';
            const formData = new FormData(); formData.append('is_ajax', '1'); formData.append('id_proposta', id); formData.append('novo_status', novoStatus);
            fetch('mudar_status.php', { method: 'POST', body: formData }).then(r => r.json()).then(res => {
                if(res.success) { 
                    let displayStatus = novoStatus;
                    if(novoStatus === 'Aprovada') displayStatus = 'Aceita';
                    btn.innerText = displayStatus; 
                    btn.className = 'btn dropdown-toggle shadow-sm ' + getBtnClass(novoStatus); 
                    carregarDadosDashboard(); 
                } else { alert('Erro: ' + res.msg); btn.innerText = originalText; }
            }).catch(err => { console.error(err); alert('Erro conexão'); btn.innerText = originalText; });
        }
        function getBtnClass(status) {
            status = status.toLowerCase();
            if(status.includes('aprov')) return 'btn-success'; if(status.includes('envia')) return 'btn-primary'; if(status.includes('cancel')) return 'btn-danger'; return 'btn-warning text-dark';
        }
        function carregarDadosDashboard() {
            fetch('api_graficos.php').then(r => r.json()).then(data => {
                if(data.erro) return;
                const ctxLinha = document.getElementById('graficoLinha').getContext('2d');
                if(window.chartLinhaInstance) window.chartLinhaInstance.destroy();
                window.chartLinhaInstance = new Chart(ctxLinha, { 
                    type: 'line', 
                    data: { 
                        labels: data.grafico_linha.labels, 
                        datasets: [ 
                            { label: 'Aceitas', data: data.grafico_linha.series['Aprovada'], borderColor: '#198754', backgroundColor: '#198754', tension: 0.3, borderWidth: 3, pointRadius: 4, fill: false }, 
                            { label: 'Enviadas', data: data.grafico_linha.series['Enviada'], borderColor: '#0d6efd', backgroundColor: '#0d6efd', tension: 0.3, borderWidth: 3, pointRadius: 4, fill: false }, 
                            { label: 'Elaboradas', data: data.grafico_linha.series['Em elaboração'], borderColor: '#ffc107', backgroundColor: '#ffc107', tension: 0.3, borderWidth: 2, borderDash: [5, 5], fill: false } 
                        ] 
                    }, 
                    options: { 
                        responsive: true, 
                        maintainAspectRatio: false, 
                        interaction: { mode: 'index', intersect: false }, 
                        plugins: { 
                            legend: { position: 'top' },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        let label = context.dataset.label || '';
                                        if (label) { label += ': '; }
                                        if (context.parsed.y !== null) { label += new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(context.parsed.y); }
                                        return label;
                                    }
                                }
                            }
                        }, 
                        scales: { 
                            y: { 
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value) { return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL', maximumSignificantDigits: 3 }).format(value); }
                                }
                            } 
                        } 
                    } 
                });
                const ctxPizza = document.getElementById('graficoPizza').getContext('2d');
                if(window.chartPizzaInstance) window.chartPizzaInstance.destroy();
                window.chartPizzaInstance = new Chart(ctxPizza, { type: 'doughnut', data: { labels: ['Elaboradas', 'Enviadas', 'Aceitas', 'Canceladas'], datasets: [{ data: [ data.status_pizza['Em elaboração']||0, data.status_pizza['Enviada']||0, data.status_pizza['Aprovada']||0, data.status_pizza['Cancelada']||0 ], backgroundColor: ['#ffc107', '#0d6efd', '#198754', '#dc3545'], borderWidth: 2, borderColor: '#ffffff' }] }, options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'right' } }, cutout: '70%' } });
            });
        }
        document.addEventListener("DOMContentLoaded", function() {
            carregarDadosDashboard();
            document.getElementById('filtroTabela').addEventListener('keyup', function() {
                let valor = this.value.toLowerCase();
                document.querySelectorAll('#tabelaPropostas tbody tr').forEach(tr => { tr.style.display = tr.innerText.toLowerCase().includes(valor) ? '' : 'none'; });
            });
        });
    </script>

    <!-- CTA DEMO -->
    <?php if($ambiente == 'demo'): ?>
    <div class="cta-demo" id="ctaDemo">
        <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-2" onclick="fecharCTA()" aria-label="Close"></button>
        <div class="cta-content">
            <strong>Modo Demonstração</strong>
            <p>Aproveite seus 5 dias de teste grátis. Migre para Produção!</p>
        </div>
        <a href="contratar.php" class="cta-btn">Assinar Agora</a>
    </div>

    <script>
        function fecharCTA() {
            document.getElementById('ctaDemo').style.display = 'none';
            sessionStorage.setItem('cta_closed', 'true');
        }
        if(sessionStorage.getItem('cta_closed') === 'true') {
            document.getElementById('ctaDemo').style.display = 'none';
        }
    </script>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php 
// Fim: painel.php
?>