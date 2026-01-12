<?php
// ==========================================================
// ARQUIVO: painel.php (VERSÃO ESTÁVEL / BLINDADA)
// ==========================================================

// 1. FORÇA MOSTRAR ERROS (Para não dar tela branca nunca mais)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'config.php';
require_once 'db.php';

if (session_status() === PHP_SESSION_NONE) { session_start(); }

// Identifica o usuário (Fallback para evitar erro se a sessão cair)
$id_usuario = $_SESSION['usuario_id'] ?? $_SESSION['id_criador'] ?? 1;
$nome_usuario = $_SESSION['usuario_nome'] ?? 'Usuário';
$ambiente = $_SESSION['ambiente'] ?? 'producao';

// Lógica para Menu
$is_demo = ($ambiente === 'demo');
$modo_suporte = isset($_SESSION['admin_original_id']);
$primeiro_nome = explode(' ', trim($nome_usuario))[0];

// =========================================================================
// 2. MOTOR DE ATUALIZAÇÃO (Recebe o clique do botão)
// =========================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax_acao'])) {
    // Limpa buffer para garantir que só saia JSON
    while (ob_get_level()) { ob_end_clean(); }
    header('Content-Type: application/json');

    try {
        $conn = Database::getProd();
        $id = intval($_POST['id']);
        $status = $_POST['status'];
        
        // Tradução Visual -> Banco
        $status_banco = $status;
        if(strpos($status, 'Aceita') !== false) $status_banco = 'Aprovada';
        
        $sql = "UPDATE Propostas SET status = ? WHERE id_proposta = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $status_banco, $id);
        
        if ($stmt->execute()) {
            echo json_encode(['sucesso' => true]);
        } else {
            echo json_encode(['sucesso' => false, 'msg' => 'Erro SQL: ' . $conn->error]);
        }
    } catch (Exception $e) {
        echo json_encode(['sucesso' => false, 'msg' => 'Erro PHP: ' . $e->getMessage()]);
    }
    exit; // FIM DO AJAX
}

// =========================================================================
// 3. CARREGAMENTO DA TELA
// =========================================================================
try {
    $conn = ($ambiente === 'demo') ? Database::getDemo() : Database::getProd();

    // A. KPIs (Contadores)
    $kpi = ['elaborada'=>0, 'enviada'=>0, 'aprovada'=>0, 'cancelada'=>0];
    $sqlKPI = "SELECT status, count(*) as qtd FROM Propostas WHERE id_criador = ? GROUP BY status";
    $stmtKPI = $conn->prepare($sqlKPI);
    $stmtKPI->bind_param('i', $id_usuario);
    $stmtKPI->execute();
    $resKPI = $stmtKPI->get_result();

    while($row = $resKPI->fetch_assoc()) {
        $st = mb_strtolower($row['status']);
        if (strpos($st, 'aprov')!==false || strpos($st, 'conclu')!==false || strpos($st, 'aceit')!==false) $kpi['aprovada'] += $row['qtd'];
        elseif (strpos($st, 'envia')!==false) $kpi['enviada'] += $row['qtd'];
        elseif (strpos($st, 'cancel')!==false || strpos($st, 'perdid')!==false) $kpi['cancelada'] += $row['qtd'];
        else $kpi['elaborada'] += $row['qtd'];
    }

    // B. Lista de Propostas
    // B. Lista de Propostas
    $sqlLista = "SELECT p.*, c.nome_cliente 
                 FROM Propostas p 
                 LEFT JOIN Clientes c ON p.id_cliente = c.id_cliente 
                 WHERE p.id_criador = ? 
                 ORDER BY p.data_criacao DESC LIMIT 50";
    $stmtLista = $conn->prepare($sqlLista);
    $stmtLista->bind_param('i', $id_usuario);
    $stmtLista->execute();
    $resultLista = $stmtLista->get_result();

} catch (Exception $e) {
    die("<h1>Erro Crítico de Banco:</h1> " . $e->getMessage());
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
    <style>
        body { background-color: #f1f5f9; font-family: 'Inter', sans-serif; }
        .header-painel { background-color: #1e293b; color: white; padding: 25px 30px; border-radius: 12px; margin-bottom: 30px; box-shadow: 0 4px 20px rgba(0,0,0,0.15); display: flex; justify-content: space-between; align-items: center; }
        .card-kpi { background: white; border: none; border-radius: 12px; padding: 24px; height: 100%; position: relative; overflow: hidden; box-shadow: 0 2px 12px rgba(0,0,0,0.03); transition: transform 0.2s; }
        .card-kpi:hover { transform: translateY(-4px); }
        .icon-sq { width: 50px; height: 50px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.6rem; margin-right: 18px; color: white; flex-shrink: 0; }
        .bg-icon-yellow { background-color: #ffc107; } .bg-icon-blue { background-color: #0d6efd; } .bg-icon-green { background-color: #198754; } .bg-icon-red { background-color: #dc3545; }
        .kpi-value { font-size: 2rem; font-weight: 700; margin-bottom: 2px; line-height: 1; color: #212529; }
        .kpi-label { font-size: 0.85rem; color: #64748b; font-weight: 600; text-transform: uppercase; }
        .table-custom th { font-size: 0.75rem; text-transform: uppercase; color: #64748b; font-weight: 700; background-color: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 12px 20px; }
        .table-custom td { font-size: 0.9rem; color: #334155; vertical-align: middle; padding: 12px 20px; border-bottom: 1px solid #f1f5f9; }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg bg-white shadow-sm py-2 mb-4 sticky-top">
        <div class="container-fluid px-4">
            <a class="navbar-brand fw-bold text-dark d-flex align-items-center" href="painel.php">
                <img src="<?= BASE_URL ?>/assets/img/logo_sgt.png" alt="SGT" style="height: 40px;">
                <?php if($is_demo): ?><span class="badge bg-warning text-dark ms-2" style="font-size: 0.6rem;">DEMO</span><?php endif; ?>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <ul class="navbar-nav align-items-center gap-2">
                    <li class="nav-item"><a href="minha_empresa.php" class="btn btn-outline-secondary btn-sm fw-bold border-0"><i class="bi bi-gear-fill"></i> Empresa</a></li>
                    <li class="nav-item"><a href="meus_clientes.php" class="btn btn-outline-secondary btn-sm fw-bold border-0"><i class="bi bi-people-fill"></i> Clientes</a></li>
                    <li class="nav-item"><a href="admin_parametros.php" class="btn btn-outline-secondary btn-sm fw-bold border-0"><i class="bi bi-list-check"></i> Cadastro</a></li>
                    
                    <?php if($is_demo): ?>
                        <li class="nav-item"><a href="contratar.php" class="btn btn-success btn-sm fw-bold shadow-sm text-white">CONTRATAR</a></li>
                    <?php endif; ?>

                    <?php if(!$is_demo && !$modo_suporte && isset($_SESSION['perfil']) && $_SESSION['perfil'] == 'admin'): ?>
                        <li class="nav-item"><a href="admin_usuarios.php" class="btn btn-warning btn-sm fw-bold text-dark">Admin</a></li>
                    <?php endif; ?>

                    <li class="nav-item ms-2">
                        <a href="criar_proposta.php" class="btn btn-success btn-sm fw-bold shadow-sm px-3"><i class="bi bi-plus-lg me-1"></i> Nova Proposta</a>
                    </li>

                    <li class="nav-item dropdown ms-2">
                        <a class="nav-link dropdown-toggle text-dark fw-bold d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle fs-5 me-1 text-secondary"></i> <?= htmlspecialchars($primeiro_nome) ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                            <li><a class="dropdown-item" href="alterar_senha.php"><i class="bi bi-key-fill me-2 text-primary"></i> Alterar Senha</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger fw-bold" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i> Sair</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid px-4">
        
        <div class="header-painel">
            <div class="d-flex align-items-center">
                <i class="bi bi-bar-chart-line-fill me-3 fs-3 text-warning"></i>
                <div><h4 class="mb-0 fw-bold">Evolução Comercial</h4><small class="opacity-75">Visão Geral</small></div>
            </div>
            <div class="d-none d-md-block text-end"><small class="d-block opacity-50 text-uppercase">Hoje</small><span class="fw-bold fs-5"><?php echo date('d/m/Y'); ?></span></div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-xl-3 col-md-6"><div class="card-kpi d-flex flex-row align-items-center"><div class="icon-sq bg-icon-yellow"><i class="bi bi-file-text"></i></div><div><div class="kpi-label">Propostas Elaboradas</div><div class="kpi-value"><?= $kpi['elaborada'] ?></div><div class="kpi-sub text-warning">Rascunhos</div></div></div></div>
            <div class="col-xl-3 col-md-6"><div class="card-kpi d-flex flex-row align-items-center"><div class="icon-sq bg-icon-blue"><i class="bi bi-send-fill"></i></div><div><div class="kpi-label">Propostas Enviadas</div><div class="kpi-value"><?= $kpi['enviada'] ?></div><div class="kpi-sub text-primary">Aguardando</div></div></div></div>
            <div class="col-xl-3 col-md-6"><div class="card-kpi d-flex flex-row align-items-center"><div class="icon-sq bg-icon-green"><i class="bi bi-check-lg"></i></div><div><div class="kpi-label">Propostas Aceitas</div><div class="kpi-value"><?= $kpi['aprovada'] ?></div><div class="kpi-sub text-success fw-bold">Sucesso!</div></div></div></div>
            <div class="col-xl-3 col-md-6"><div class="card-kpi d-flex flex-row align-items-center"><div class="icon-sq bg-icon-red"><i class="bi bi-x-lg"></i></div><div><div class="kpi-label">Propostas Canceladas</div><div class="kpi-value"><?= $kpi['cancelada'] ?></div><div class="kpi-sub text-danger">Perdidas</div></div></div></div>
        </div>

        <!-- Novo Dashboard (Gemini 3) -->
        <?php include 'dashboard_include.php'; ?>

        <div class="card shadow-sm border-0 mb-5 mt-4">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <h6 class="m-0 fw-bold text-dark">Últimas Propostas</h6>
                <div class="d-flex gap-2">
                    <input type="text" id="filtroTabela" class="form-control form-control-sm" placeholder="🔍 Buscar..." style="width: 200px;">
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-hover table-custom mb-0" id="tabelaPrincipal">
                    <thead>
                        <tr>
                            <th class="ps-4">Data</th>
                            <th>Número</th>
                            <th>Cliente</th>
                            <th>Status (Clique para Alterar)</th>
                            <th class="text-end">Valor</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $resultLista->fetch_assoc()): 
                             $id = $row['id_proposta'];
                             // Define cor do botão
                             $st = mb_strtolower($row['status']);
                             $corBtn = 'btn-warning text-dark';
                             if(strpos($st, 'aprov')!==false) $corBtn = 'btn-success';
                             elseif(strpos($st, 'envia')!==false) $corBtn = 'btn-primary';
                             elseif(strpos($st, 'cancel')!==false) $corBtn = 'btn-danger';
                        ?>
                        <tr>
                            <td class="ps-4 fw-bold text-secondary"><?= date('d/m/Y', strtotime($row['data_criacao'])); ?></td>
                            <td><div class="fw-bold text-dark"><?= $row['numero_proposta']; ?></div></td>
                            <td><div class="fw-bold text-secondary"><?= htmlspecialchars($row['nome_cliente_salvo']); ?></div></td>
                            
                            <td>
                                <div class="dropdown">
                                    <button class="btn <?= $corBtn ?> btn-sm dropdown-toggle fw-bold shadow-sm" 
                                            type="button" 
                                            data-bs-toggle="dropdown"
                                            id="btn-<?= $id ?>"
                                            style="width: 140px; border-radius: 20px;">
                                        <?= $row['status'] ?>
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="#" onclick="trocarStatus(<?= $id ?>, 'Em Elaboração')">🟡 Em Elaboração</a></li>
                                        <li><a class="dropdown-item" href="#" onclick="trocarStatus(<?= $id ?>, 'Enviada')">🔵 Enviada</a></li>
                                        <li><a class="dropdown-item" href="#" onclick="trocarStatus(<?= $id ?>, 'Aprovada')">🟢 Aprovada</a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item text-danger" href="#" onclick="trocarStatus(<?= $id ?>, 'Cancelada')">🔴 Cancelada</a></li>
                                    </ul>
                                </div>
                            </td>

                            <td class="text-end fw-bold text-dark">
                                <div>R$ <?= number_format($row['valor_final_proposta'], 2, ',', '.'); ?></div>
                                <a href="relatorio_proposta.php?id=<?= $id ?>" class="btn btn-outline-secondary btn-sm border-0 mt-1" title="Ver Relatório Financeiro">
                                    <i class="bi bi-file-earmark-spreadsheet"></i> Relatório
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        function trocarStatus(id, novoStatus) {
            let btn = document.getElementById('btn-' + id);
            let textoAntigo = btn.innerText;
            btn.innerText = '...';
            btn.classList.add('disabled');
            document.body.style.cursor = 'wait';

            let dados = new FormData();
            dados.append('ajax_acao', 'mudar_status');
            dados.append('id', id);
            dados.append('status', novoStatus);

            fetch('painel.php', { method: 'POST', body: dados })
            .then(r => r.json())
            .then(res => {
                if (res.sucesso) {
                    window.location.reload(); 
                } else {
                    alert('Erro: ' + res.msg);
                    btn.innerText = textoAntigo;
                    btn.classList.remove('disabled');
                }
            })
            .catch(e => {
                console.error(e);
                alert('Erro de conexão.');
                btn.innerText = textoAntigo;
                btn.classList.remove('disabled');
            })
            .finally(() => document.body.style.cursor = 'default');
        }

        // Filtro de Busca
        const filtro = document.getElementById('filtroTabela');
        if(filtro){
            filtro.addEventListener('keyup', function() {
                let valor = this.value.toLowerCase();
                let linhas = document.querySelectorAll('#tabelaPrincipal tbody tr');
                linhas.forEach(tr => {
                    let texto = tr.innerText.toLowerCase();
                    tr.style.display = texto.includes(valor) ? '' : 'none';
                });
            });
        }
    </script>
</body>
</html>