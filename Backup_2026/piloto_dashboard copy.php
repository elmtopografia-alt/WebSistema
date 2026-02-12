<?php
// ARQUIVO: piloto_dashboard.php
require_once 'db.php'; 

// ---------------------------------------------------------
// 1. GRÁFICO DE LINHA (EVOLUÇÃO 6 MESES) - COM OS 4 STATUS
// ---------------------------------------------------------
$sql_linha = "SELECT 
            DATE_FORMAT(data_criacao, '%m/%Y') as mes_ano,
            SUM(CASE WHEN status = 'Em elaboração' THEN valor ELSE 0 END) as elaboracao,
            SUM(CASE WHEN status = 'Enviada' THEN valor ELSE 0 END) as enviada,
            SUM(CASE WHEN status = 'Aprovada' THEN valor ELSE 0 END) as aprovada,
            SUM(CASE WHEN status = 'Cancelada' THEN valor ELSE 0 END) as cancelada
        FROM sgt_piloto_teste 
        WHERE data_criacao >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
        GROUP BY DATE_FORMAT(data_criacao, '%Y-%m') 
        ORDER BY data_criacao ASC";

$res_linha = $conn->query($sql_linha);

// Arrays para o JS
$meses = []; 
$elab = []; $env = []; $aprov = []; $canc = [];

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
// 2. GRÁFICO DE PIZZA (TOTAL POR STATUS)
// ---------------------------------------------------------
$sql_pizza = "SELECT status, COUNT(*) as total FROM sgt_piloto_teste GROUP BY status";
$res_pizza = $conn->query($sql_pizza);

$pizza_labels = [];
$pizza_valores = [];

if ($res_pizza) {
    while($row = $res_pizza->fetch_assoc()) {
        $pizza_labels[] = $row['status']; // Ex: "Em elaboração"
        $pizza_valores[] = $row['total'];
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Piloto SGT - Oficial</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-light p-4">

<div class="container-fluid">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="text-secondary"><i class="fas fa-chart-line"></i> Dashboard Financeiro</h3>
        <span class="badge bg-secondary">Versão Piloto</span>
    </div>

    <div class="row">
        
        <div class="col-lg-8 mb-4">
            <div class="card shadow h-100">
                <div class="card-header bg-white">
                    <h5 class="m-0 text-primary">Evolução Financeira (R$)</h5>
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
                    <h5 class="m-0 text-primary">Status (%)</h5>
                </div>
                <div class="card-body">
                    <div style="height: 250px; position: relative;">
                        <canvas id="chartPizza"></canvas>
                    </div>
                    <div class="text-center mt-3 small text-muted">
                        Distribuição do volume de propostas
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
                    <table class="table table-striped table-hover mb-0">
                        <thead class="table-light">
                            <tr><th>Cliente</th><th>Data</th><th class="text-end">Valor</th></tr>
                        </thead>
                        <tbody id="corpoTabela">
                            <tr><td colspan="3" class="text-center py-4 text-muted">Clique no gráfico de linha para ver detalhes.</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // --- DEFINIÇÃO DE CORES OFICIAIS (BOOTSTRAP) ---
    const CORES = {
        'Em elaboração': '#ffc107', // Amarelo (Warning)
        'Enviada':       '#0d6efd', // Azul (Primary)
        'Aprovada':      '#198754', // Verde (Success)
        'Cancelada':     '#dc3545'  // Vermelho (Danger)
    };

    // --- 1. GRÁFICO DE LINHA ---
    const ctxLinha = document.getElementById('chartLinha').getContext('2d');
    const chartLinha = new Chart(ctxLinha, {
        type: 'line',
        data: {
            labels: <?php echo json_encode($meses); ?>,
            datasets: [
                { 
                    label: 'Aprovada', 
                    data: <?php echo json_encode($aprov); ?>, 
                    borderColor: CORES['Aprovada'], 
                    backgroundColor: CORES['Aprovada'],
                    tension: 0.3, borderWidth: 3 
                },
                { 
                    label: 'Enviada', 
                    data: <?php echo json_encode($env); ?>, 
                    borderColor: CORES['Enviada'], 
                    backgroundColor: CORES['Enviada'],
                    tension: 0.3, borderWidth: 3 
                },
                { 
                    label: 'Em elaboração', 
                    data: <?php echo json_encode($elab); ?>, 
                    borderColor: CORES['Em elaboração'], 
                    backgroundColor: CORES['Em elaboração'],
                    tension: 0.3, borderWidth: 3 
                },
                { 
                    label: 'Cancelada', 
                    data: <?php echo json_encode($canc); ?>, 
                    borderColor: CORES['Cancelada'], 
                    backgroundColor: CORES['Cancelada'],
                    borderDash: [5,5], // Tracejado para destacar que é perda
                    tension: 0.3, borderWidth: 2 
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            scales: { y: { beginAtZero: true } },
            onClick: (e) => {
                const points = chartLinha.getElementsAtEventForMode(e, 'nearest', { intersect: true }, true);
                if (points.length) {
                    const mes = chartLinha.data.labels[points[0].index];
                    const status = chartLinha.data.datasets[points[0].datasetIndex].label;
                    carregarDetalhes(mes, status);
                }
            }
        }
    });

    // --- 2. GRÁFICO DE PIZZA (COM CORES DINÂMICAS) ---
    const labelsPizza = <?php echo json_encode($pizza_labels); ?>;
    const valoresPizza = <?php echo json_encode($pizza_valores); ?>;
    
    // Mapeia as cores na mesma ordem dos labels
    const coresPizza = labelsPizza.map(status => CORES[status] || '#999'); // #999 se não achar a cor

    const ctxPizza = document.getElementById('chartPizza').getContext('2d');
    const chartPizza = new Chart(ctxPizza, {
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

    // --- 3. FUNÇÃO AJAX ---
    function carregarDetalhes(mes, status) {
        document.getElementById('tituloTabela').innerText = `Filtrando: ${status} em ${mes}`;
        document.getElementById('corpoTabela').innerHTML = '<tr><td colspan="3" class="text-center py-4"><div class="spinner-border text-primary"></div></td></tr>';
        
        fetch(`piloto_api.php?acao=detalhes&mes=${mes}&status=${status}`)
            .then(res => res.text())
            .then(html => {
                document.getElementById('corpoTabela').innerHTML = html;
            });
    }
</script>

</body>
</html>