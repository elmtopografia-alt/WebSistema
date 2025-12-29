<?php
/**
 * painel.php
 * Dashboard Central do Cliente
 * 
 * Responsabilidade:
 * - Visão geral dos negócios (Gráficos e KPIs).
 * - Listagem de todas as propostas (Independente do Status).
 * - Acesso rápido às ações (Editar, PDF, Zap).
 */

session_start();
require_once 'config.php';
require_once 'db.php';

// 1. Segurança e Sessão
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

$id_usuario = $_SESSION['usuario_id'];
$nome_usuario = $_SESSION['usuario_nome'] ?? 'Cliente';

// Mensagens de Feedback
$msg = '';
if (isset($_GET['msg']) && $_GET['msg'] == 'sucesso') {
    $msg = '<div class="alert alert-success alert-dismissible fade show" role="alert">
                Operação realizada com sucesso!
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>';
}

try {
    // 2. Coleta de Dados para KPIs e Gráfico
    // Inicializa array zerado para garantir que o gráfico funcione mesmo sem dados
    $stats = [
        'elaborando' => ['qtd' => 0, 'valor' => 0],
        'enviada'    => ['qtd' => 0, 'valor' => 0],
        'aceita'     => ['qtd' => 0, 'valor' => 0],
        'cancelada'  => ['qtd' => 0, 'valor' => 0]
    ];

    $sql_kpi = "SELECT status, COUNT(*) as qtd, SUM(valor_final_proposta) as total 
                FROM Propostas 
                WHERE id_criador = ? 
                GROUP BY status";
    
    $stmt = $conn->prepare($sql_kpi);
    $stmt->bind_param('i', $id_usuario);
    $stmt->execute();
    $result_kpi = $stmt->get_result();

    $total_geral_valor = 0;
    $total_geral_qtd = 0;

    while ($row = $result_kpi->fetch_assoc()) {
        // Normaliza a chave para minúsculo para evitar erro (ex: 'Aprovada' vira 'aceita' se o banco não foi corrigido)
        $status_key = strtolower($row['status']);
        
        // Mapeamento de legado (caso o banco ainda tenha nomes antigos)
        if ($status_key == 'aprovada') $status_key = 'aceita';
        if ($status_key == 'em elaboração') $status_key = 'elaborando';
        if ($status_key == 'em elaboracao') $status_key = 'elaborando';

        if (isset($stats[$status_key])) {
            $stats[$status_key]['qtd'] = $row['qtd'];
            $stats[$status_key]['valor'] = $row['total'];
        }
        
        // Soma apenas o que não for cancelado para o KPI financeiro real
        if ($status_key !== 'cancelada') {
            $total_geral_valor += $row['total'];
        }
        $total_geral_qtd += $row['qtd'];
    }

    // Prepara JSON para o Chart.js
    $chart_data = [
        $stats['elaborando']['qtd'],
        $stats['enviada']['qtd'],
        $stats['aceita']['qtd'],
        $stats['cancelada']['qtd']
    ];
    $chart_json = json_encode($chart_data);

    // 3. Listagem Completa das Propostas (Recentes primeiro)
    $sql_lista = "SELECT p.*, c.nome_cliente 
                  FROM Propostas p
                  LEFT JOIN Clientes c ON p.id_cliente = c.id_cliente
                  WHERE p.id_criador = ? 
                  ORDER BY p.data_criacao DESC";
    
    $stmt_lista = $conn->prepare($sql_lista);
    $stmt_lista->bind_param('i', $id_usuario);
    $stmt_lista->execute();
    $result_lista = $stmt_lista->get_result();

} catch (Exception $e) {
    die("Erro ao carregar painel: " . $e->getMessage());
}

// Função Auxiliar para Badge de Status
function getStatusBadge($status) {
    $status = strtolower($status);
    switch ($status) {
        case 'aceita':
        case 'aprovada': return '<span class="badge bg-success"><i class="bi bi-check-circle"></i> Aceita</span>';
        
        case 'enviada': return '<span class="badge bg-primary"><i class="bi bi-send"></i> Enviada</span>';
        
        case 'cancelada': return '<span class="badge bg-danger"><i class="bi bi-x-circle"></i> Cancelada</span>';
        
        default: return '<span class="badge bg-secondary text-light"><i class="bi bi-pencil"></i> Elaborando</span>';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | SGT</title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        body { background-color: #f4f6f9; font-family: 'Inter', sans-serif; }
        .card-kpi { border: none; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); transition: transform 0.2s; }
        .card-kpi:hover { transform: translateY(-5px); }
        .icon-box { width: 48px; height: 48px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; }
        .table-card { border-radius: 12px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.05); border: none; }
        .btn-action { width: 32px; height: 32px; padding: 0; display: inline-flex; align-items: center; justify-content: center; border-radius: 6px; }
    </style>
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
        <div class="container">
            <a class="navbar-brand fw-bold" href="#"><i class="bi bi-grid-fill me-2"></i>SGT Painel</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item me-3 text-white-50">
                        Olá, <span class="text-white fw-bold"><?= htmlspecialchars($nome_usuario) ?></span>
                    </li>
                    <li class="nav-item">
                        <a href="criar_proposta.php" class="btn btn-success btn-sm fw-bold">
                            <i class="bi bi-plus-lg"></i> Nova Proposta
                        </a>
                    </li>
                    <li class="nav-item ms-3">
                        <a href="logout.php" class="btn btn-outline-secondary btn-sm">Sair</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container py-4">
        
        <?= $msg ?>

        <!-- Seção 1: Indicadores (KPIs) -->
        <div class="row g-3 mb-4">
            <!-- Total Projetos -->
            <div class="col-md-3">
                <div class="card card-kpi bg-white p-3 h-100">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small fw-bold text-uppercase">Total Propostas</p>
                            <h3 class="mb-0 fw-bold"><?= $total_geral_qtd ?></h3>
                        </div>
                        <div class="icon-box bg-primary bg-opacity-10 text-primary">
                            <i class="bi bi-folder2-open"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Volume Financeiro -->
            <div class="col-md-3">
                <div class="card card-kpi bg-white p-3 h-100">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small fw-bold text-uppercase">Pipeline (Ativo)</p>
                            <h3 class="mb-0 fw-bold text-success">R$ <?= number_format($total_geral_valor, 2, ',', '.') ?></h3>
                        </div>
                        <div class="icon-box bg-success bg-opacity-10 text-success">
                            <i class="bi bi-currency-dollar"></i>
                        </div>
                    </div>
                    <small class="text-muted" style="font-size: 0.75rem">*Exclui canceladas</small>
                </div>
            </div>

            <!-- Fechadas/Aceitas -->
            <div class="col-md-3">
                <div class="card card-kpi bg-white p-3 h-100">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small fw-bold text-uppercase">Fechadas (Aceitas)</p>
                            <h3 class="mb-0 fw-bold"><?= $stats['aceita']['qtd'] ?></h3>
                            <small class="text-success fw-bold">R$ <?= number_format($stats['aceita']['valor'], 2, ',', '.') ?></small>
                        </div>
                        <div class="icon-box bg-success text-white">
                            <i class="bi bi-check-lg"></i>
                        </div>
                    </div>
                </div>
            </div>

             <!-- Em Negociação/Enviadas -->
             <div class="col-md-3">
                <div class="card card-kpi bg-white p-3 h-100">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small fw-bold text-uppercase">Enviadas (Negociação)</p>
                            <h3 class="mb-0 fw-bold"><?= $stats['enviada']['qtd'] ?></h3>
                            <small class="text-primary fw-bold">R$ <?= number_format($stats['enviada']['valor'], 2, ',', '.') ?></small>
                        </div>
                        <div class="icon-box bg-primary text-white">
                            <i class="bi bi-send"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            
            <!-- Seção 2: Tabela de Propostas -->
            <div class="col-lg-8">
                <div class="card table-card h-100">
                    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                        <h6 class="m-0 fw-bold text-dark"><i class="bi bi-list-task me-2"></i>Minhas Propostas</h6>
                        <input type="text" id="filtroTabela" class="form-control form-control-sm w-25" placeholder="Filtrar...">
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="tabelaPropostas">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-3">Ref/Data</th>
                                    <th>Cliente</th>
                                    <th>Status</th>
                                    <th class="text-end">Valor</th>
                                    <th class="text-center pe-3">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if($result_lista->num_rows > 0): ?>
                                    <?php while($p = $result_lista->fetch_assoc()): 
                                        $data = date('d/m/y', strtotime($p['data_criacao']));
                                        $badge = getStatusBadge($p['status']);
                                        $zap_msg = "Olá, segue o link da proposta " . $p['numero_proposta'] . ": [SEU_LINK_AQUI]";
                                        $zap_link = "https://wa.me/?text=" . urlencode($zap_msg);
                                        // Se tiver celular do cliente salvo, usa ele
                                        if(!empty($p['celular_salvo'])) {
                                            $nums = preg_replace("/[^0-9]/", "", $p['celular_salvo']);
                                            $zap_link = "https://wa.me/55{$nums}?text=" . urlencode("Olá, referente a proposta {$p['numero_proposta']}...");
                                        }
                                    ?>
                                    <tr>
                                        <td class="ps-3">
                                            <span class="d-block fw-bold text-dark small"><?= $p['numero_proposta'] ?></span>
                                            <span class="text-muted small"><?= $data ?></span>
                                        </td>
                                        <td>
                                            <span class="d-block fw-bold text-dark"><?= htmlspecialchars($p['nome_cliente_salvo'] ?? 'N/A') ?></span>
                                            <span class="text-muted small"><?= htmlspecialchars($p['tipo_levantamento'] ?? '') ?></span>
                                        </td>
                                        <td><?= $badge ?></td>
                                        <td class="text-end fw-bold text-dark">
                                            R$ <?= number_format($p['valor_final_proposta'], 2, ',', '.') ?>
                                        </td>
                                        <td class="text-center pe-3">
                                            <div class="btn-group">
                                                <a href="editar_proposta.php?id=<?= $p['id_proposta'] ?>" class="btn btn-outline-secondary btn-action" title="Editar Status/Dados"><i class="bi bi-pencil-square"></i></a>
                                                <a href="gerar_pdf.php?id=<?= $p['id_proposta'] ?>" target="_blank" class="btn btn-outline-danger btn-action" title="Ver PDF"><i class="bi bi-file-earmark-pdf"></i></a>
                                                <a href="<?= $zap_link ?>" target="_blank" class="btn btn-outline-success btn-action" title="Enviar WhatsApp"><i class="bi bi-whatsapp"></i></a>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr><td colspan="5" class="text-center py-4 text-muted">Nenhuma proposta encontrada.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Seção 3: Gráfico -->
            <div class="col-lg-4">
                <div class="card table-card h-100">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h6 class="m-0 fw-bold text-dark"><i class="bi bi-pie-chart-fill me-2"></i>Status Geral</h6>
                    </div>
                    <div class="card-body d-flex align-items-center justify-content-center">
                        <div style="width: 100%; max-width: 300px;">
                            <canvas id="chartStatus"></canvas>
                        </div>
                    </div>
                    <div class="card-footer bg-light text-center small text-muted">
                        Taxa de Conversão: 
                        <?php 
                            $conv = ($stats['aceita']['qtd'] > 0 && $total_geral_qtd > 0) 
                                    ? ($stats['aceita']['qtd'] / $total_geral_qtd) * 100 
                                    : 0; 
                            echo number_format($conv, 1) . '%';
                        ?>
                    </div>
                </div>
            </div>

        </div> <!-- Row -->
    </div> <!-- Container -->

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // 1. Configuração do Gráfico
        const ctx = document.getElementById('chartStatus').getContext('2d');
        const chartData = <?= $chart_json ?>; // [elaborando, enviada, aceita, cancelada]
        
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Elaborando', 'Enviada', 'Aceita', 'Cancelada'],
                datasets: [{
                    data: chartData,
                    backgroundColor: [
                        '#6c757d', // Elaborando (Cinza)
                        '#0d6efd', // Enviada (Azul)
                        '#198754', // Aceita (Verde)
                        '#dc3545'  // Cancelada (Vermelho)
                    ],
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'bottom', labels: { usePointStyle: true, font: { size: 11 } } }
                },
                cutout: '65%' // Rosca mais fina
            }
        });

        // 2. Filtro da Tabela (Javascript Simples)
        document.getElementById('filtroTabela').addEventListener('keyup', function() {
            let valor = this.value.toLowerCase();
            let linhas = document.querySelectorAll('#tabelaPropostas tbody tr');
            
            linhas.forEach(tr => {
                let texto = tr.innerText.toLowerCase();
                tr.style.display = texto.includes(valor) ? '' : 'none';
            });
        });
    </script>
</body>
</html>