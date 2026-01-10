<?php
// ARQUIVO: dashboard_include.php
// (Não precisa de require 'db.php' se o painel.php já tiver carregado)

// GARANTIR ID DO CRIADOR (SEGURANÇA SAAS)
$id_logado_dash = isset($_SESSION['usuario_id']) ? $_SESSION['usuario_id'] : (isset($_SESSION['id_criador']) ? $_SESSION['id_criador'] : 0);

// ---------------------------------------------------------
// 1. QUERY GRÁFICO DE LINHA (EVOLUÇÃO 6 MESES - SAAS)
// ---------------------------------------------------------
$sql_linha = "SELECT 
            DATE_FORMAT(data_criacao, '%m/%Y') as mes_ano,
            SUM(CASE WHEN status = 'Em elaboração' THEN valor_final_proposta ELSE 0 END) as elaboracao,
            SUM(CASE WHEN status = 'Enviada' THEN valor_final_proposta ELSE 0 END) as enviada,
            SUM(CASE WHEN status = 'Aprovada' THEN valor_final_proposta ELSE 0 END) as aprovada,
            SUM(CASE WHEN status = 'Cancelada' THEN valor_final_proposta ELSE 0 END) as cancelada
        FROM Propostas 
        WHERE id_criador = $id_logado_dash
        AND data_criacao >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
        GROUP BY DATE_FORMAT(data_criacao, '%Y-%m') 
        ORDER BY data_criacao ASC";

$res_linha = $conn->query($sql_linha);

// Arrays JavaScript
$meses = []; $elab = []; $env = []; $aprov = []; $canc = [];

if ($res_linha) {
    while($row = $res_linha->fetch_assoc()) {
        $meses[] = $row['mes_ano'];
        $elab[]  = $row['elaboracao'];
        $env[]   = $row['enviada'];
        $aprov[] = $row['aprovada'];
        $canc[]  = $row['cancelada'];
    }
}

// ---------------------------------------------------------
// 2. QUERY GRÁFICO DE PIZZA (STATUS GERAL - SAAS)
// ---------------------------------------------------------
$sql_pizza = "SELECT status, COUNT(*) as total 
              FROM Propostas 
              WHERE id_criador = $id_logado_dash 
              GROUP BY status";
$res_pizza = $conn->query($sql_pizza);

$pizza_labels = [];
$pizza_valores = [];

if ($res_pizza) {
    while($row = $res_pizza->fetch_assoc()) {
        $pizza_labels[] = $row['status'];
        $pizza_valores[] = $row['total'];
    }
}
?>

<div class="row">
    <div class="col-lg-8 mb-4">
        <div class="card shadow h-100">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="m-0 text-primary fw-bold"><i class="fas fa-chart-line me-2"></i>Evolução Financeira</h5>
                <span class="badge bg-light text-dark border">Últimos 6 meses</span>
            </div>
            <div class="card-body">
                <div style="height: 300px;">
                    <canvas id="chartLinha"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4 mb-4">
        <div class="card shadow h-100">
            <div class="card-header bg-white">
                <h5 class="m-0 text-primary fw-bold"><i class="fas fa-chart-pie me-2"></i>Status das Propostas</h5>
            </div>
            <div class="card-body">
                <div style="height: 250px; position: relative;">
                    <canvas id="chartPizza"></canvas>
                </div>
                <div class="text-center mt-3 text-muted small">
                    Quantidade total de propostas por status
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-12">
        <div class="card shadow border-top border-primary border-3">
            <div class="card-header bg-white py-3">
                <h5 id="tituloTabela" class="m-0 text-secondary"><i class="fas fa-list-ul me-2"></i> Detalhes da Seleção</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Proposta / Cliente</th>
                                <th>Data Criação</th>
                                <th class="text-end">Valor Final</th>
                                <th class="text-end">Ações</th>
                            </tr>
                        </thead>
                        <tbody id="corpoTabela">
                            <tr>
                                <td colspan="4" class="text-center py-5 text-muted">
                                    <i class="fas fa-hand-pointer fa-2x mb-3"></i><br>
                                    Clique em uma bolinha no <b>Gráfico de Evolução</b> para ver as propostas aqui.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // --- CORES OFICIAIS SGT ---
    const CORES = {
        'Em elaboração': '#ffc107', // Amarelo
        'Enviada':       '#0d6efd', // Azul
        'Aprovada':      '#198754', // Verde
        'Cancelada':     '#dc3545'  // Vermelho
    };

    // 1. GRÁFICO DE LINHA
    const ctxLinha = document.getElementById('chartLinha').getContext('2d');
    const chartLinha = new Chart(ctxLinha, {
        type: 'line',
        data: {
            labels: <?php echo json_encode($meses); ?>,
            datasets: [
                { label: 'Aprovada', data: <?php echo json_encode($aprov); ?>, borderColor: CORES['Aprovada'], backgroundColor: CORES['Aprovada'], tension: 0.3, borderWidth: 3 },
                { label: 'Enviada', data: <?php echo json_encode($env); ?>, borderColor: CORES['Enviada'], backgroundColor: CORES['Enviada'], tension: 0.3, borderWidth: 3 },
                { label: 'Em elaboração', data: <?php echo json_encode($elab); ?>, borderColor: CORES['Em elaboração'], backgroundColor: CORES['Em elaboração'], tension: 0.3, borderWidth: 3 },
                { label: 'Cancelada', data: <?php echo json_encode($canc); ?>, borderColor: CORES['Cancelada'], backgroundColor: CORES['Cancelada'], borderDash: [5,5], tension: 0.3, borderWidth: 2 }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            scales: { 
                y: { 
                    beginAtZero: true,
                    ticks: { callback: function(value) { return 'R$ ' + value.toLocaleString('pt-BR'); } }
                } 
            },
            onClick: (e) => {
                const points = chartLinha.getElementsAtEventForMode(e, 'nearest', { intersect: true }, true);
                if (points.length) {
                    const mes = chartLinha.data.labels[points[0].index];
                    const status = chartLinha.data.datasets[points[0].datasetIndex].label;
                    carregarDetalhes(mes, status);
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) { label += ': '; }
                            label += parseFloat(context.raw).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
                            return label;
                        }
                    }
                }
            }
        }
    });

    // 2. GRÁFICO DE PIZZA
    const labelsPizza = <?php echo json_encode($pizza_labels); ?>;
    const valoresPizza = <?php echo json_encode($pizza_valores); ?>;
    const coresPizza = labelsPizza.map(status => CORES[status] || '#6c757d');

    const ctxPizza = document.getElementById('chartPizza').getContext('2d');
    new Chart(ctxPizza, {
        type: 'doughnut',
        data: {
            labels: labelsPizza,
            datasets: [{
                data: valoresPizza,
                backgroundColor: coresPizza,
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'right' },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.label || '';
                            let value = context.raw;
                            let total = context.chart._metasets[context.datasetIndex].total;
                            let percentage = Math.round((value / total) * 100) + '%';
                            return ` ${label}: ${percentage} (${value})`;
                        }
                    }
                }
            }
        }
    });

    // 3. AJAX
    function carregarDetalhes(mes, status) {
        document.getElementById('tituloTabela').innerText = `Propostas: ${status} em ${mes}`;
        document.getElementById('corpoTabela').innerHTML = '<tr><td colspan="4" class="text-center py-4"><div class="spinner-border text-primary"></div><div class="mt-2 text-muted">Carregando dados...</div></td></tr>';
        
        // Chama a API dashboard
        fetch(`api_dashboard.php?acao=detalhes&mes=${mes}&status=${status}`)
            .then(res => res.text())
            .then(html => {
                document.getElementById('corpoTabela').innerHTML = html;
            })
            .catch(err => {
                console.error(err);
                document.getElementById('corpoTabela').innerHTML = '<tr><td colspan="4" class="text-center text-danger">Erro ao comunicar com o servidor.</td></tr>';
            });
    }
</script>