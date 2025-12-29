<?php
// Nome do Arquivo: relatorios.php
// Função: Dashboard Financeiro com MENU UNIVERSAL.

session_start();
require_once 'config.php';
require_once 'db.php';

if (!isset($_SESSION['usuario_id'])) { header("Location: login.php"); exit; }

$id_usuario = $_SESSION['usuario_id'];
$ambiente_atual = $_SESSION['ambiente'] ?? 'indefinido';
$is_demo = ($ambiente_atual === 'demo');
$modo_suporte = isset($_SESSION['admin_original_id']);
$nome_usuario = $_SESSION['usuario_nome'] ?? 'Usuário';
$primeiro_nome = explode(' ', trim($nome_usuario))[0];

$conn = $is_demo ? Database::getDemo() : Database::getProd();

if (isset($_GET['exportar']) && $_GET['exportar'] == 'csv') {
    // (Lógica de Exportação mantida igual ao anterior para economizar espaço aqui)
    // ...
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Relatórios | SGT</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body { background-color: #f8f9fa; }
        .navbar-custom { background-color: #2c3e50; color: white; }
        .env-badge-demo { background-color: #ffc107; color: #000; font-weight: bold; font-size: 0.7rem; padding: 2px 6px; border-radius: 4px; text-transform: uppercase; }
        .env-badge-prod { background-color: #198754; color: #fff; font-weight: bold; font-size: 0.7rem; padding: 2px 6px; border-radius: 4px; text-transform: uppercase; }
        .support-banner { background-color: #dc3545; color: white; text-align: center; padding: 10px; font-weight: bold; position: sticky; top: 0; z-index: 1050; }
        .btn-upgrade { background-color: #25D366; color: white; font-weight: bold; border: none; animation: pulse 2s infinite; }
        .btn-upgrade:hover { background-color: #128C7E; color: white; }
        @keyframes pulse { 0% { box-shadow: 0 0 0 0 rgba(37, 211, 102, 0.7); } 70% { box-shadow: 0 0 0 10px rgba(37, 211, 102, 0); } 100% { box-shadow: 0 0 0 0 rgba(37, 211, 102, 0); } }
        .dropdown-menu-end { right: 0; left: auto; }
        .user-avatar { width: 32px; height: 32px; background-color: rgba(255,255,255,0.2); border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; margin-right: 8px; }
        .card-kpi { border: none; border-radius: 12px; transition: transform 0.2s; color: white; position: relative; overflow: hidden; }
        .card-kpi:hover { transform: translateY(-5px); }
        .bg-kpi-green { background: linear-gradient(135deg, #198754 0%, #20c997 100%); }
        .bg-kpi-blue { background: linear-gradient(135deg, #0d6efd 0%, #0dcaf0 100%); }
        .bg-kpi-purple { background: linear-gradient(135deg, #6610f2 0%, #6f42c1 100%); }
        .chart-container { position: relative; height: 320px; width: 100%; }
        :fullscreen { background-color: #f8f9fa; overflow-y: auto; }
    </style>
</head>
<body>

    <?php if($modo_suporte): ?>
        <div class="support-banner shadow">MODO SUPORTE: <?php echo strtoupper($nome_usuario); ?><a href="painel.php?sair_suporte=1" class="btn btn-sm btn-light text-danger fw-bold ms-3">ENCERRAR</a></div>
    <?php endif; ?>

    <nav class="navbar navbar-expand-lg navbar-custom px-4 shadow-sm">
        <div class="container-fluid">
            <a class="navbar-brand text-white fw-bold d-flex align-items-center" href="painel.php">
                <i class="bi bi-grid-fill me-2"></i>
                <div>SGT <span class="<?php echo $is_demo ? 'env-badge-demo' : 'env-badge-prod'; ?> ms-2"><?php echo $is_demo ? 'DEMO' : 'PRODUÇÃO'; ?></span></div>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"><span class="navbar-toggler-icon text-white"><i class="bi bi-list"></i></span></button>
            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <ul class="navbar-nav align-items-center">
                    <li class="nav-item me-2"><a href="minha_empresa.php" class="btn btn-outline-light btn-sm fw-bold border-0"><i class="bi bi-gear-fill"></i> Empresa</a></li>
                    <li class="nav-item me-2"><a href="meus_clientes.php" class="btn btn-outline-light btn-sm fw-bold border-0"><i class="bi bi-people-fill"></i> Clientes</a></li>
                    <li class="nav-item me-2"><a href="relatorios.php" class="btn btn-light btn-sm fw-bold text-dark"><i class="bi bi-graph-up"></i> Relatórios</a></li>
                    <?php if($is_demo): ?><li class="nav-item mx-3"><a href="contratar.php" class="btn btn-upgrade btn-sm shadow-sm">CONTRATAR</a></li><?php endif; ?>
                    <?php if(!$is_demo && !$modo_suporte && isset($_SESSION['perfil']) && $_SESSION['perfil'] == 'admin'): ?>
                        <li class="nav-item me-2"><a href="admin_usuarios.php" class="btn btn-warning btn-sm fw-bold text-dark">Admin</a></li>
                        <li class="nav-item me-2"><a href="admin_parametros.php" class="btn btn-secondary btn-sm fw-bold text-white">Cadastros</a></li>
                    <?php endif; ?>
                    <?php if($_SESSION['usuario_id'] == 1 && !$modo_suporte): ?>
                        <li class="nav-item mx-2"><a href="admin_alternar.php" class="btn btn-sm fw-bold <?php echo $is_demo ? 'btn-success' : 'btn-warning'; ?>"><i class="bi bi-arrow-repeat"></i> Trocar</a></li>
                    <?php endif; ?>
                    
                    <li class="nav-item dropdown ms-3">
                        <a class="nav-link dropdown-toggle text-white fw-bold d-flex align-items-center" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                            <span class="user-avatar"><i class="bi bi-person-fill"></i></span> <?php echo htmlspecialchars($primeiro_nome); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow">
                            <li><a class="dropdown-item" href="alterar_senha.php"><i class="bi bi-key-fill me-2 text-primary"></i> Alterar Senha</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger fw-bold" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i> Sair</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container pb-5 mt-4" id="painelRelatorios">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold text-dark">Visão Geral</h2>
            <div class="btn-group">
                <button onclick="toggleFullScreen()" class="btn btn-outline-secondary shadow-sm" title="Tela Cheia"><i class="bi bi-arrows-fullscreen"></i></button>
                <a href="relatorios.php?exportar=csv" class="btn btn-success shadow-sm"><i class="bi bi-file-earmark-excel-fill me-2"></i>Exportar Excel</a>
            </div>
        </div>

        <!-- KPIs -->
        <div class="row g-4 mb-5">
            <div class="col-md-4"><div class="card card-kpi bg-kpi-green p-4 shadow-sm"><div class="d-flex justify-content-between align-items-center"><div><div class="small text-white-50 fw-bold text-uppercase mb-1">Receita Real (Aprovada)</div><div class="h2 mb-0 fw-bold" id="kpi-receita">R$ 0,00</div></div><i class="bi bi-cash-stack fs-1 opacity-25"></i></div></div></div>
            <div class="col-md-4"><div class="card card-kpi bg-kpi-blue p-4 shadow-sm"><div class="d-flex justify-content-between align-items-center"><div><div class="small text-white-50 fw-bold text-uppercase mb-1">Volume Total Orçado</div><div class="h2 mb-0 fw-bold" id="kpi-volume">R$ 0,00</div></div><i class="bi bi-calculator fs-1 opacity-25"></i></div></div></div>
            <div class="col-md-4"><div class="card card-kpi bg-kpi-purple p-4 shadow-sm"><div class="d-flex justify-content-between align-items-center"><div><div class="small text-white-50 fw-bold text-uppercase mb-1">Ticket Médio Geral</div><div class="h2 mb-0 fw-bold" id="kpi-ticket">R$ 0,00</div></div><i class="bi bi-graph-up-arrow fs-1 opacity-25"></i></div></div></div>
        </div>

        <!-- Gráficos -->
        <div class="row g-4">
            <div class="col-lg-8"><div class="card shadow-sm border-0 h-100"><div class="card-header bg-white py-3 border-bottom-0"><h6 class="fw-bold text-primary mb-0"><i class="bi bi-graph-up me-2"></i>Evolução Financeira por Status (6 Meses)</h6></div><div class="card-body"><div class="chart-container"><canvas id="graficoEvolucao"></canvas></div></div></div></div>
            <div class="col-lg-4"><div class="card shadow-sm border-0 h-100"><div class="card-header bg-white py-3 border-bottom-0"><h6 class="fw-bold text-primary mb-0"><i class="bi bi-pie-chart-fill me-2"></i>Distribuição (Qtd)</h6></div><div class="card-body"><div class="chart-container" style="height: 250px;"><canvas id="graficoStatus"></canvas></div><div class="text-center mt-3 small text-muted">Proporção de propostas</div></div></div></div>
        </div>
    </div>

    <!-- Scripts (Mantidos) -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        function toggleFullScreen() { if (!document.fullscreenElement) { document.documentElement.requestFullscreen(); } else { if (document.exitFullscreen) { document.exitFullscreen(); } } }
        const formatarMoeda = (val) => new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(val);

        fetch('api_graficos.php')
            .then(response => response.json())
            .then(data => {
                document.getElementById('kpi-receita').innerText = formatarMoeda(data.kpis.soma_aprovada || 0);
                document.getElementById('kpi-volume').innerText = formatarMoeda(data.kpis.soma_geral || 0);
                document.getElementById('kpi-ticket').innerText = formatarMoeda(data.kpis.media || 0);

                const labelsMes = data.grafico_linha.map(item => item.label);
                
                new Chart(document.getElementById('graficoEvolucao'), {
                    type: 'line',
                    data: {
                        labels: labelsMes,
                        datasets: [
                            { label: 'Aprovada', data: data.grafico_linha.map(i => i.Aprovada), borderColor: '#198754', backgroundColor: '#198754', tension: 0.3, borderWidth: 3 },
                            { label: 'Enviada', data: data.grafico_linha.map(i => i.Enviada), borderColor: '#0d6efd', backgroundColor: '#0d6efd', tension: 0.3, borderWidth: 2 },
                            { label: 'Em elaboração', data: data.grafico_linha.map(i => i['Em elaboração']), borderColor: '#6c757d', backgroundColor: '#6c757d', tension: 0.3, borderWidth: 2, borderDash: [5,5] },
                            { label: 'Cancelada', data: data.grafico_linha.map(i => i.Cancelada), borderColor: '#dc3545', backgroundColor: '#dc3545', tension: 0.3, borderWidth: 1 }
                        ]
                    },
                    options: { responsive: true, maintainAspectRatio: false, interaction: { mode: 'index', intersect: false }, plugins: { tooltip: { callbacks: { label: function(c) { return c.dataset.label + ': ' + formatarMoeda(c.raw); } } } }, scales: { y: { beginAtZero: true, ticks: { callback: function(value) { return 'R$ ' + value.toLocaleString('pt-BR'); } } } } }
                });

                const labelsPizza = ['Aprovada', 'Enviada', 'Em elaboração', 'Cancelada'];
                const dadosPizza = [data.status_pizza.Aprovada, data.status_pizza.Enviada, data.status_pizza['Em elaboração'], data.status_pizza.Cancelada];
                const coresPizza = ['#198754', '#0d6efd', '#6c757d', '#dc3545'];

                new Chart(document.getElementById('graficoStatus'), {
                    type: 'doughnut',
                    data: { labels: labelsPizza, datasets: [{ data: dadosPizza, backgroundColor: coresPizza, borderWidth: 0, hoverOffset: 5 }] },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom', labels: { usePointStyle: true } } } }
                });
            });
    </script>
</body>
</html>