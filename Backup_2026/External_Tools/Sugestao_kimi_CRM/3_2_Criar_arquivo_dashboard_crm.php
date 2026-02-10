<?php
//Página completa de analytics
// dashboard_crm.php - Dashboard de Analytics SGT CRM
require_once 'db.php';
require_once 'session_validator.php';

$id_usuario = $_SESSION['usuario_id'];
$conn = Database::getProd();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | SGT CRM Analytics</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <style>
        body { background: #0a0f1a; color: #e2e8f0; font-family: 'Inter', sans-serif; }
        .glass { background: rgba(31, 41, 55, 0.7); backdrop-filter: blur(12px); border: 1px solid rgba(255,255,255,0.05); }
        .gradient-text { background: linear-gradient(135deg, #f97316, #ec4899); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
    </style>
</head>
<body class="min-h-screen pb-10">

    <!-- Navbar -->
    <nav class="glass sticky top-0 z-50 h-16 flex items-center justify-between px-6 mb-6">
        <div class="flex items-center gap-4">
            <a href="painel_crm.php" class="p-2 hover:bg-white/5 rounded-lg transition-colors">
                <i class="ph ph-arrow-left text-xl"></i>
            </a>
            <h1 class="font-bold text-xl"><span class="gradient-text">Analytics</span> CRM</h1>
        </div>
        
        <!-- Seletor de Período -->
        <div class="flex bg-black/30 rounded-lg p-1 border border-white/10">
            <?php $periodos = ['hoje' => 'Hoje', 'semana' => '7 dias', 'mes' => '30 dias', 'trimestre' => '90 dias']; 
            foreach ($periodos as $val => $label): ?>
            <button onclick="mudarPeriodo('<?= $val ?>')" 
                    class="periodo-btn px-4 py-1.5 rounded-md text-sm font-medium transition-all <?= $val === 'mes' ? 'bg-orange-600 text-white' : 'text-slate-400 hover:text-white' ?>"
                    data-periodo="<?= $val ?>">
                <?= $label ?>
            </button>
            <?php endforeach; ?>
        </div>
    </nav>

    <main class="px-6 max-w-7xl mx-auto space-y-6">

        <!-- KPIs Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4" id="kpi-container">
            <!-- Preenchido via JS -->
        </div>

        <!-- Gráficos Principais -->
        <div class="grid lg:grid-cols-3 gap-6">
            <!-- Funil de Vendas -->
            <div class="lg:col-span-1 glass rounded-xl p-6">
                <h3 class="font-bold text-lg mb-4 flex items-center gap-2">
                    <i class="ph ph-funnel text-orange-500"></i>
                    Funil de Vendas
                </h3>
                <div class="space-y-3" id="funil-container">
                    <!-- Preenchido via JS -->
                </div>
            </div>

            <!-- Evolução Temporal -->
            <div class="lg:col-span-2 glass rounded-xl p-6">
                <h3 class="font-bold text-lg mb-4 flex items-center gap-2">
                    <i class="ph ph-trend-up text-blue-500"></i>
                    Evolução de Propostas
                </h3>
                <canvas id="chart-evolucao" height="100"></canvas>
            </div>
        </div>

        <!-- Segunda Linha -->
        <div class="grid lg:grid-cols-2 gap-6">
            <!-- Top Clientes -->
            <div class="glass rounded-xl p-6">
                <h3 class="font-bold text-lg mb-4 flex items-center gap-2">
                    <i class="ph ph-trophy text-yellow-500"></i>
                    Top Clientes
                </h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-slate-400 border-b border-white/10">
                                <th class="text-left py-3">Cliente</th>
                                <th class="text-center py-3">Propostas</th>
                                <th class="text-right py-3">Valor em Vendas</th>
                                <th class="text-right py-3">Conversão</th>
                            </tr>
                        </thead>
                        <tbody id="top-clientes-container">
                            <!-- Preenchido via JS -->
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Produtividade -->
            <div class="glass rounded-xl p-6">
                <h3 class="font-bold text-lg mb-4 flex items-center gap-2">
                    <i class="ph ph-check-circle text-green-500"></i>
                    Produtividade (Tarefas)
                </h3>
                <div class="grid grid-cols-3 gap-4 mb-6">
                    <div class="text-center p-4 bg-white/5 rounded-lg">
                        <div class="text-2xl font-bold text-white" id="prod-total">-</div>
                        <div class="text-xs text-slate-400 uppercase mt-1">Total</div>
                    </div>
                    <div class="text-center p-4 bg-green-500/10 rounded-lg border border-green-500/20">
                        <div class="text-2xl font-bold text-green-400" id="prod-concluidas">-</div>
                        <div class="text-xs text-green-400 uppercase mt-1">Concluídas</div>
                    </div>
                    <div class="text-center p-4 bg-orange-500/10 rounded-lg border border-orange-500/20">
                        <div class="text-2xl font-bold text-orange-400" id="prod-pendentes">-</div>
                        <div class="text-xs text-orange-400 uppercase mt-1">Pendentes</div>
                    </div>
                </div>
                <canvas id="chart-produtividade" height="80"></canvas>
            </div>
        </div>

        <!-- Comparativo -->
        <div class="glass rounded-xl p-6">
            <h3 class="font-bold text-lg mb-4">Comparativo vs Período Anterior</h3>
            <div class="grid md:grid-cols-3 gap-6" id="comparativo-container">
                <!-- Preenchido via JS -->
            </div>
        </div>

    </main>

    <script>
    let periodoAtual = 'mes';
    let charts = {};

    document.addEventListener('DOMContentLoaded', () => {
        carregarDashboard();
    });

    function mudarPeriodo(novo) {
        periodoAtual = novo;
        
        // Atualiza UI dos botões
        document.querySelectorAll('.periodo-btn').forEach(btn => {
            if (btn.dataset.periodo === novo) {
                btn.classList.remove('text-slate-400');
                btn.classList.add('bg-orange-600', 'text-white');
            } else {
                btn.classList.remove('bg-orange-600', 'text-white');
                btn.classList.add('text-slate-400');
            }
        });
        
        carregarDashboard();
    }

    function carregarDashboard() {
        // Carrega dados principais
        fetch(`api/relatorios_api.php?acao=dashboard&periodo=${periodoAtual}`)
            .then(r => r.json())
            .then(data => {
                if (data.sucesso) {
                    renderizarKPIs(data.kpis);
                    renderizarFunil(data.funil);
                    renderizarEvolucao(data.evolucao);
                    renderizarTopClientes(data.top_clientes);
                    renderizarProdutividade(data.produtividade);
                }
            });

        // Carrega comparativo
        fetch(`api/relatorios_api.php?acao=comparativo&dias=${periodoAtual === 'hoje' ? 1 : periodoAtual === 'semana' ? 7 : periodoAtual === 'mes' ? 30 : 90}`)
            .then(r => r.json())
            .then(data => {
                if (data.sucesso) {
                    renderizarComparativo(data.variacoes);
                }
            });
    }

    function renderizarKPIs(kpis) {
        const cards = [
            {
                titulo: 'Propostas Criadas',
                valor: kpis.propostas_criadas.total,
                sub: `R$ ${parseFloat(kpis.propostas_criadas.valor_total || 0).toLocaleString('pt-BR', {minimumFractionDigits: 2})}`,
                icone: 'ph-file-text',
                cor: 'blue'
            },
            {
                titulo: 'Taxa de Conversão',
                valor: kpis.conversao.taxa + '%',
                sub: `${kpis.conversao.aprovadas} de ${kpis.conversao.total} propostas`,
                icone: 'ph-chart-pie',
                cor: 'green'
            },
            {
                titulo: 'Receita Ganha',
                valor: 'R$ ' + parseFloat(kpis.conversao.valor_ganho || 0).toLocaleString('pt-BR', {minimumFractionDigits: 2}),
                sub: `${kpis.conversao.perdidas} propostas perdidas`,
                icone: 'ph-currency-dollar',
                cor: 'orange'
            },
            {
                titulo: 'Tempo Médio Conversão',
                valor: Math.round(kpis.tempo_conversao || 0) + ' dias',
                sub: 'Da criação à aprovação',
                icone: 'ph-clock',
                cor: 'purple'
            }
        ];

        document.getElementById('kpi-container').innerHTML = cards.map(card => `
            <div class="glass rounded-xl p-5 border-l-4 border-${card.cor}-500 hover:bg-white/5 transition-colors">
                <div class="flex justify-between items-start mb-2">
                    <span class="text-slate-400 text-sm font-medium">${card.titulo}</span>
                    <div class="p-2 bg-${card.cor}-500/20 rounded-lg">
                        <i class="ph ${card.icone} text-${card.cor}-400 text-xl"></i>
                    </div>
                </div>
                <div class="text-2xl font-bold text-white mb-1">${card.valor}</div>
                <div class="text-xs text-slate-500">${card.sub}</div>
            </div>
        `).join('');
    }

    function renderizarFunil(funil) {
        const total = funil.reduce((acc, f) => acc + parseInt(f.quantidade), 0);
        
        document.getElementById('funil-container').innerHTML = funil.map((f, index) => {
            const percentual = total > 0 ? (f.quantidade / total * 100).toFixed(1) : 0;
            const cores = ['yellow', 'blue', 'green', 'red', 'purple'];
            const cor = cores[index] || 'gray';
            
            return `
            <div class="relative">
                <div class="flex justify-between text-sm mb-1">
                    <span class="text-slate-300 font-medium">${f.status}</span>
                    <span class="text-slate-400">${f.quantidade} (${percentual}%)</span>
                </div>
                <div class="h-8 bg-black/30 rounded-lg overflow-hidden relative">
                    <div class="h-full bg-${cor}-500/30 border-r-2 border-${cor}-500 flex items-center justify-end px-3 transition-all duration-500" 
                         style="width: ${percentual}%">
                        <span class="text-${cor}-400 font-bold text-sm">R$ ${parseFloat(f.valor || 0).toLocaleString('pt-BR', {notation: 'compact'})}</span>
                    </div>
                </div>
            </div>
            `;
        }).join('');
    }

    function renderizarEvolucao(evolucao) {
        const ctx = document.getElementById('chart-evolucao').getContext('2d');
        
        if (charts.evolucao) charts.evolucao.destroy();
        
        charts.evolucao = new Chart(ctx, {
            type: 'line',
            data: {
                labels: evolucao.map(e => {
                    const d = new Date(e.data);
                    return d.toLocaleDateString('pt-BR', {day: '2-digit', month: 'short'});
                }),
                datasets: [{
                    label: 'Propostas Criadas',
                    data: evolucao.map(e => e.propostas),
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    fill: true,
                    tension: 0.4
                }, {
                    label: 'Aprovadas',
                    data: evolucao.map(e => e.aprovadas),
                    borderColor: '#22c55e',
                    backgroundColor: 'rgba(34, 197, 94, 0.1)',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { labels: { color: '#94a3b8' } }
                },
                scales: {
                    y: { 
                        beginAtZero: true,
                        grid: { color: 'rgba(255,255,255,0.05)' },
                        ticks: { color: '#64748b' }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { color: '#64748b' }
                    }
                }
            }
        });
    }

    function renderizarTopClientes(clientes) {
        document.getElementById('top-clientes-container').innerHTML = clientes.map((c, i) => {
            const taxa = c.total_propostas > 0 ? Math.round((parseInt(c.valor_comprado > 0 ? 1 : 0) / c.total_propostas) * 100) : 0;
            
            return `
            <tr class="border-b border-white/5 hover:bg-white/5 transition-colors">
                <td class="py-3">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white font-bold text-xs">
                            ${c.nome_cliente.charAt(0).toUpperCase()}
                        </div>
                        <div>
                            <div class="font-medium text-white">${c.nome_cliente}</div>
                            <div class="text-xs text-slate-500">${c.empresa || 'Pessoa Física'}</div>
                        </div>
                    </div>
                </td>
                <td class="text-center py-3 text-slate-300">${c.total_propostas}</td>
                <td class="text-right py-3 font-medium text-green-400">
                    R$ ${parseFloat(c.valor_comprado || 0).toLocaleString('pt-BR', {minimumFractionDigits: 2})}
                </td>
                <td class="text-right py-3">
                    <span class="px-2 py-1 rounded-full text-xs ${taxa >= 50 ? 'bg-green-500/20 text-green-400' : 'bg-yellow-500/20 text-yellow-400'}">
                        ${taxa}%
                    </span>
                </td>
            </tr>
            `;
        }).join('');
    }

    function renderizarProdutividade(prod) {
        document.getElementById('prod-total').textContent = prod.total_tarefas || 0;
        document.getElementById('prod-concluidas').textContent = prod.concluidas || 0;
        document.getElementById('prod-pendentes').textContent = prod.pendentes || 0;

        const ctx = document.getElementById('chart-produtividade').getContext('2d');
        
        if (charts.produtividade) charts.produtividade.destroy();
        
        charts.produtividade = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Concluídas', 'Pendentes'],
                datasets: [{
                    data: [prod.concluidas || 0, prod.pendentes || 0],
                    backgroundColor: ['#22c55e', '#f97316'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: {
                    legend: { 
                        position: 'bottom',
                        labels: { color: '#94a3b8', padding: 20 }
                    }
                }
            }
        });
    }

    function renderizarComparativo(vars) {
        const items = [
            { label: 'Propostas', valor: vars.propostas, icone: 'ph-file-text', cor: 'blue' },
            { label: 'Vendas Fechadas', valor: vars.vendas, icone: 'ph-check-circle', cor: 'green' },
            { label: 'Receita', valor: vars.receita, icone: 'ph-currency-dollar', cor: 'orange', prefixo: 'R$ ' }
        ];

        document.getElementById('comparativo-container').innerHTML = items.map(item => {
            const positivo = item.valor >= 0;
            const cor = positivo ? 'green' : 'red';
            const iconeSeta = positivo ? 'ph-trend-up' : 'ph-trend-down';
            
            return `
            <div class="flex items-center gap-4 p-4 bg-white/5 rounded-lg border border-white/5">
                <div class="w-12 h-12 rounded-full bg-${item.cor}-500/20 flex items-center justify-center">
                    <i class="ph ${item.icone} text-${item.cor}-400 text-2xl"></i>
                </div>
                <div>
                    <div class="text-sm text-slate-400 mb-1">${item.label}</div>
                    <div class="flex items-center gap-2">
                        <span class="text-2xl font-bold text-white">${Math.abs(item.valor)}%</span>
                        <span class="flex items-center text-sm ${positivo ? 'text-green-400' : 'text-red-400'}">
                            <i class="ph ${iconeSeta} mr-1"></i>
                            vs período anterior
                        </span>
                    </div>
                </div>
            </div>
            `;
        }).join('');
    }
    </script>
</body>
</html>