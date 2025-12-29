<?php
// Nome do Arquivo: relatorios.php
// Função: Dashboard Financeiro (Front-end).

session_start();
require_once 'config.php';
require_once 'db.php';

if (!isset($_SESSION['usuario_id'])) { header("Location: login.php"); exit; }
$id_usuario = $_SESSION['usuario_id'];

// Export CSV
if (isset($_GET['exportar']) && $_GET['exportar'] == 'csv') {
    $filename = "Relatorio_SGT_" . date('Y-m-d') . ".csv";
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=' . $filename);
    $output = fopen('php://output', 'w');
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    fputcsv($output, ['Numero', 'Cliente', 'Servico', 'Data', 'Status', 'Valor (R$)', 'Cidade'], ';');
    
    $is_demo = ($_SESSION['ambiente'] === 'demo');
    $conn = $is_demo ? Database::getDemo() : Database::getProd();
    $sql = "SELECT p.numero_proposta, p.nome_cliente_salvo, s.nome as servico, p.data_criacao, p.status, p.valor_final_proposta, p.cidade_obra FROM Propostas p LEFT JOIN Tipo_Servicos s ON p.id_servico = s.id_servico WHERE p.id_criador = ? ORDER BY p.data_criacao DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $id_usuario);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) { fputcsv($output, [$row['numero_proposta'], $row['nome_cliente_salvo'], $row['servico'], date('d/m/Y', strtotime($row['data_criacao'])), $row['status'], number_format($row['valor_final_proposta'], 2, ',', ''), $row['cidade_obra']], ';'); }
    fclose($output);
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8"><title>Relatórios | SGT</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body { background-color: #f8f9fa; }
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
    <nav class="navbar navbar-dark bg-dark mb-4 shadow-sm"><div class="container"><a class="navbar-brand fw-bold" href="painel.php"><i class="bi bi-arrow-left me-2"></i>Voltar ao Painel</a><span class="navbar-text text-white small">Inteligência de Negócio</span></div></nav>
    <div class="container pb-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="fw-bold text-dark mb-0">Dashboard Financeiro</h3>
            <div class="btn-group">
                <button onclick="toggleFullScreen()" class="btn btn-outline-secondary shadow-sm" title="Tela Cheia"><i class="bi bi-arrows-fullscreen"></i></button>
                <a href="relatorios.php?exportar=csv" class="btn btn-success shadow-sm fw-bold"><i class="bi bi-file-earmark-excel-fill me-2"></i>Excel</a>
            </div>
        </div>
        <div class="row g-3 mb-4">
            <div class="col-md-4"><div class="card card-kpi bg-kpi-green p-4 shadow-sm"><div class="d-flex justify-content-between align-items-center"><div><div class="small text-white-50 fw-bold text-uppercase mb-1">Receita Real (Aprovada)</div><div class="h2 mb-0 fw-bold" id="kpi-receita">R$ 0,00</div></div><i class="bi bi-cash-stack fs-1 opacity-25"></i></div></div></div>
            <div class="col-md-4"><div class="card card-kpi bg-kpi-blue p-4 shadow-sm"><div class="d-flex justify-content-between align-items-center"><div><div class="small text-white-50 fw-bold text-uppercase mb-1">Volume Total Orçado</div><div class="h2 mb-0 fw-bold" id="kpi-volume">R$ 0,00</div></div><i class="bi bi-calculator fs-1 opacity-25"></i></div></div></div>
            <div class="col-md-4"><div class="card card-kpi bg-kpi-purple p-4 shadow-sm"><div class="d-flex justify-content-between align-items-center"><div><div class="small text-white-50 fw-bold text-uppercase mb-1">Ticket Médio Geral</div><div class="h2 mb-0 fw-bold" id="kpi-ticket">R$ 0,00</div></div><i class="bi bi-graph-up-arrow fs-1 opacity-25"></i></div></div></div>
        </div>
        <div class="row g-4">
            <div class="col-lg-8"><div class="card shadow-sm border-0 h-100"><div class="card-header bg-white py-3 border-bottom-0"><h6 class="fw-bold text-primary mb-0"><i class="bi bi-graph-up me-2"></i>Evolução Financeira (Orçado vs Aprovado)</h6></div><div class="card-body"><div class="chart-container"><canvas id="graficoEvolucao"></canvas></div></div></div></div>
            <div class="col-lg-4"><div class="card shadow-sm border-0 h-100"><div class="card-header bg-white py-3 border-bottom-0"><h6 class="fw-bold text-primary mb-0"><i class="bi bi-pie-chart-fill me-2"></i>Funil de Status</h6></div><div class="card-body"><div class="chart-container" style="height: 250px;"><canvas id="graficoStatus"></canvas></div><div class="text-center mt-3 small text-muted">Proporção de propostas</div></div></div></div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        function toggleFullScreen() { if (!document.fullscreenElement) { document.documentElement.requestFullscreen(); } else { if (document.exitFullscreen) { document.exitFullscreen(); } } }
        const formatarMoeda = (val) => new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(val);

        // Fetch seguro com tratamento de erro
        fetch('api_graficos.php')
            .then(response => response.json())
            .then(data => {
                if(data.erro) { console.error(data.erro); return; }

                // KPIs
                document.getElementById('kpi-receita').innerText = formatarMoeda(data.kpis.receita_real || 0);
                document.getElementById('kpi-volume').innerText = formatarMoeda(data.kpis.volume_orcado || 0);
                document.getElementById('kpi-ticket').innerText = formatarMoeda(data.kpis.ticket_medio || 0);

                // Gráfico Linha
                const labelsMes = data.grafico_linha.map(i => i.label);
                const valOrcado = data.grafico_linha.map(i => i.total_orcado);
                const valAprovado = data.grafico_linha.map(i => i.total_aprovado);

                new Chart(document.getElementById('graficoEvolucao'), {
                    type: 'line',
                    data: {
                        labels: labelsMes,
                        datasets: [
                            {
                                label: 'Receita (Aprovada)',
                                data: valAprovado,
                                borderColor: '#198754',
                                backgroundColor: 'rgba(25, 135, 84, 0.1)',
                                tension: 0.3,
                                fill: true,
                                borderWidth: 3
                            },
                            {
                                label: 'Volume (Orçado)',
                                data: valOrcado,
                                borderColor: '#0d6efd',
                                backgroundColor: 'transparent',
                                tension: 0.3,
                                borderWidth: 2,
                                borderDash: [5, 5]
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { tooltip: { callbacks: { label: function(c) { return c.dataset.label + ': ' + formatarMoeda(c.raw); } } } },
                        scales: { y: { beginAtZero: true, ticks: { callback: function(v) { return 'R$ ' + v.toLocaleString('pt-BR'); } } } }
                    }
                });

                // Gráfico Pizza
                new Chart(document.getElementById('graficoStatus'), {
                    type: 'doughnut',
                    data: {
                        labels: ['Aprovada', 'Enviada', 'Em elaboração', 'Cancelada'],
                        datasets: [{
                            data: [
                                data.status_pizza['Aprovada'] || 0,
                                data.status_pizza['Enviada'] || 0,
                                data.status_pizza['Em elaboração'] || 0,
                                data.status_pizza['Cancelada'] || 0
                            ],
                            backgroundColor: ['#198754', '#0d6efd', '#6c757d', '#dc3545'],
                            borderWidth: 0,
                            hoverOffset: 5
                        }]
                    },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom', labels: { usePointStyle: true } } } }
                });
            })
            .catch(err => console.error("Erro na API:", err));
    </script>
</body>
</html>