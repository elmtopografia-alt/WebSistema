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

<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    
    <!-- Gráfico de Linha (2/3) -->
    <div class="md:col-span-2 glass-panel rounded-2xl p-6 flex flex-col h-full">
        <div class="flex justify-between items-center mb-6">
            <h5 class="font-display text-lg font-bold text-white flex items-center gap-2">
                <i class="ph ph-chart-line-up text-brand-accent"></i> Evolução Financeira
            </h5>
            <span class="px-3 py-1 rounded-full bg-white/5 text-slate-400 text-xs font-bold border border-white/10">
                Últimos 6 meses
            </span>
        </div>
        <div class="flex-1 relative min-h-[300px]">
            <canvas id="chartLinha"></canvas>
        </div>
    </div>

    <!-- Gráfico de Pizza (1/3) -->
    <div class="glass-panel rounded-2xl p-6 flex flex-col h-full">
        <div class="mb-6">
            <h5 class="font-display text-lg font-bold text-white flex items-center gap-2">
                <i class="ph ph-chart-pie-slice text-brand-accent"></i> Status Geral
            </h5>
        </div>
        <div class="flex-1 relative min-h-[250px] flex items-center justify-center">
            <canvas id="chartPizza"></canvas>
        </div>
        <div class="text-center mt-4 text-xs text-slate-500">
            Quantidade total de propostas por status
        </div>
    </div>

</div>

<!-- Detalhes da Seleção -->
<div class="glass-panel rounded-2xl overflow-hidden mb-8">
    <div class="p-4 border-b border-white/5 bg-white/5">
        <h5 id="tituloTabela" class="font-display text-sm font-bold text-slate-300 flex items-center gap-2">
            <i class="ph ph-list text-brand-accent"></i> Detalhes da Seleção
        </h5>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead class="bg-black/20 text-xs text-slate-500 uppercase">
                <tr>
                    <th class="px-6 py-3 font-semibold">Proposta / Cliente</th>
                    <th class="px-6 py-3 font-semibold">Data Criação</th>
                    <th class="px-6 py-3 font-semibold text-right">Valor Final</th>
                    <th class="px-6 py-3 font-semibold text-right">Ações</th>
                </tr>
            </thead>
            <tbody id="corpoTabela" class="divide-y divide-white/5">
                <tr>
                    <td colspan="4" class="text-center py-8 text-slate-500">
                        <i class="ph ph-hand-tap text-3xl mb-2 block opacity-50"></i>
                        Clique em uma bolinha no <b>Gráfico de Evolução</b> para ver as propostas aqui.
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // --- CORES OFICIAIS SGT (Adaptadas para Dark Mode) ---
    const CORES = {
        'Em elaboração': '#fbbf24', // Amber 400
        'Enviada':       '#3b82f6', // Blue 500
        'Aprovada':      '#22c55e', // Green 500
        'Cancelada':     '#ef4444'  // Red 500
    };

    // Configuração Comum
    Chart.defaults.color = '#94a3b8'; // Slate 400
    Chart.defaults.borderColor = '#1e293b'; // Slate 800

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
                    grid: { color: 'rgba(255, 255, 255, 0.05)' },
                    ticks: { callback: function(value) { return 'R$ ' + value.toLocaleString('pt-BR'); } }
                },
                x: {
                    grid: { display: false }
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
                legend: { labels: { usePointStyle: true, padding: 20 } },
                tooltip: {
                    backgroundColor: 'rgba(15, 23, 42, 0.9)',
                    titleColor: '#fff',
                    bodyColor: '#cbd5e1',
                    padding: 12,
                    cornerRadius: 8,
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
                borderWidth: 0,
                hoverOffset: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '65%',
            plugins: {
                legend: { position: 'right', labels: { usePointStyle: true, padding: 20 } },
                tooltip: {
                    backgroundColor: 'rgba(15, 23, 42, 0.9)',
                    titleColor: '#fff',
                    bodyColor: '#cbd5e1',
                    padding: 12,
                    cornerRadius: 8,
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
        document.getElementById('corpoTabela').innerHTML = '<tr><td colspan="4" class="text-center py-8"><div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-brand-accent"></div><div class="mt-2 text-slate-400 text-xs">Carregando dados...</div></td></tr>';
        
        // Chama a API dashboard
        fetch(`api_dashboard.php?acao=detalhes&mes=${mes}&status=${status}`)
            .then(res => res.text())
            .then(html => {
                document.getElementById('corpoTabela').innerHTML = html;
            })
            .catch(err => {
                console.error(err);
                document.getElementById('corpoTabela').innerHTML = '<tr><td colspan="4" class="text-center text-red-400 py-4">Erro ao comunicar com o servidor.</td></tr>';
            });
    }
</script>