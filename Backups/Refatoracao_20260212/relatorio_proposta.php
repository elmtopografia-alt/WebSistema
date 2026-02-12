<?php
/**
 * relatorio_proposta.php
 * Relatório Financeiro Detalhado - Design Otimizado (Alinhamento de Altura)
 * Tema: Aurora Premium (Dark/Glass)
 */

// 1. Carrega Lógica (Controller)
$report = require __DIR__ . '/core/proposal_report_controller.php';

$proposta = $report['proposta'];
$dados = $report['dados'];
$totais = $report['totais'];
$chart = $report['chart'];

$data = ['usuario' => ['perfil' => 'pro', 'nome_completo' => $_SESSION['usuario_nome'] ?? 'Usuário', 'id' => $_SESSION['usuario_id']]]; 
if(session_status() === PHP_SESSION_NONE) session_start();
?>
<!DOCTYPE html>
<html lang="pt-br" class="scroll-smooth">
<head>
    <?php include __DIR__ . '/components/dashboard/head.php'; ?>
    <title>Relatório | #<?= $proposta['numero_proposta'] ?></title>
    <style>
        .custom-scroll::-webkit-scrollbar { width: 6px; }
        .custom-scroll::-webkit-scrollbar-track { background: rgba(0,0,0,0.2); }
        .custom-scroll::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 10px; }
        .custom-scroll::-webkit-scrollbar-thumb:hover { background: rgba(255,255,255,0.2); }
    </style>
</head>
<body class="antialiased overflow-x-hidden min-h-screen">

    <!-- Background Effects -->
    <div class="fixed inset-0 grid-bg opacity-30 pointer-events-none z-0"></div>
    <div class="fixed top-0 right-0 w-[500px] h-[500px] bg-blue-600/10 rounded-full blur-[120px] pointer-events-none z-0"></div>
    <div class="fixed bottom-0 left-0 w-[500px] h-[500px] bg-orange-500/10 rounded-full blur-[120px] pointer-events-none z-0"></div>

    <!-- Navbar -->
    <?php include __DIR__ . '/components/dashboard/navbar.php'; ?>

<?php
// Define Navbar Extras
ob_start();
?>
<div class="hidden md:flex items-center gap-3 mr-2 border-r border-white/10 pr-4">
    <div class="text-right">
        <h1 class="font-display text-sm font-bold text-white tracking-tight leading-none"><?= htmlspecialchars($proposta['nome_cliente']) ?></h1>
        <div class="flex items-center justify-end gap-2 text-[10px] text-slate-400">
            <span>#<?= $proposta['numero_proposta'] ?></span>
            <span>•</span>
            <span><?= date('d/m/Y', strtotime($proposta['data_criacao'])) ?></span>
        </div>
    </div>
    <button onclick="window.print()" class="px-3 py-1.5 rounded-lg bg-white/5 hover:bg-white/10 text-white border border-white/10 transition-all flex items-center gap-2 text-xs font-medium cursor-pointer" title="Imprimir Relatório">
        <i class="ph ph-printer text-lg"></i>
    </button>
</div>
<?php
$nav_extra_content = ob_get_clean();
?>

    <!-- Main Content -->
    <main class="relative z-10 pt-24 pb-16 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Header Moved to Navbar -->

        <!-- Grid Layout Refactored -->
        <div class="flex flex-col gap-6">
            
            <!-- Row 1: KPIs (Full Width) -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="glass-card p-4 rounded-xl border border-blue-500/10 bg-blue-500/5 relative overflow-hidden">
                    <div class="absolute right-0 top-0 p-3 opacity-10"><i class="ph ph-wallet text-4xl text-blue-400"></i></div>
                    <p class="text-[10px] text-blue-300 uppercase font-bold mb-1">Receita</p>
                    <h3 class="text-xl font-bold text-white">R$ <?= number_format($totais['receita'], 2, ',', '.') ?></h3>
                </div>
                
                <div class="glass-card p-4 rounded-xl border border-red-500/10 bg-red-500/5 relative overflow-hidden">
                    <div class="absolute right-0 top-0 p-3 opacity-10"><i class="ph ph-trend-down text-4xl text-red-400"></i></div>
                    <p class="text-[10px] text-red-300 uppercase font-bold mb-1">Custos</p>
                    <h3 class="text-xl font-bold text-white">R$ <?= number_format($totais['custos'], 2, ',', '.') ?></h3>
                </div>
                
                <div class="glass-card p-4 rounded-xl border border-orange-500/20 bg-orange-500/10 relative overflow-hidden shadow-[0_0_20px_rgba(249,115,22,0.1)]">
                    <div class="absolute right-0 top-0 p-3 opacity-20"><i class="ph ph-coins text-4xl text-orange-400"></i></div>
                    <p class="text-[10px] text-orange-300 uppercase font-bold mb-1">Lucro Real</p>
                    <div class="flex items-end gap-2">
                            <h3 class="text-xl font-bold text-white">R$ <?= number_format($totais['lucro'], 2, ',', '.') ?></h3>
                            <span class="text-[10px] font-bold text-orange-300 mb-1 bg-orange-500/20 px-1 rounded"><?= number_format($totais['margem'], 1, ',', '.') ?>%</span>
                    </div>
                </div>
            </div>

            <!-- Row 2: Split View (List + Chart) - Equal Height -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                
                <!-- Left: Cost List + Info -->
                <div class="flex flex-col gap-3">
                    <div class="glass-card rounded-xl border border-white/5 flex flex-col overflow-hidden h-[450px]">
                    <div class="p-4 border-b border-white/5 bg-surface/30 flex justify-between items-center shrink-0">
                        <h3 class="font-bold text-white text-sm flex items-center gap-2">
                            <i class="ph ph-list-dashes text-orange-400"></i> Composição de Custos
                        </h3>
                            <span class="text-[10px] text-slate-500 bg-white/5 px-2 py-1 rounded">5 Categorias</span>
                    </div>
                    
                    <div class="p-2 space-y-2 overflow-y-auto custom-scroll grow">
                            <?php 
                        function renderItem($id, $title, $total, $icon, $items, $colorClass) {
                            if ($total <= 0) return;
                            $totalF = number_format($total, 2, ',', '.');
                        ?>
                        <div class="bg-white/[0.02] border border-white/5 rounded-lg overflow-hidden group hover:bg-white/[0.04] transition-colors">
                            <button onclick="toggleAccordion('<?= $id ?>')" class="w-full px-4 py-3 flex items-center justify-between text-left">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded bg-<?= $colorClass ?>-500/10 flex items-center justify-center text-<?= $colorClass ?>-400 border border-<?= $colorClass ?>-500/20">
                                        <i class="ph <?= $icon ?> text-base"></i>
                                    </div>
                                    <span class="font-medium text-xs text-slate-200"><?= $title ?></span>
                                </div>
                                <span class="font-bold text-sm text-white">R$ <?= $totalF ?></span>
                            </button>
                            
                            <div id="<?= $id ?>" class="hidden border-t border-white/5 bg-black/20">
                                <table class="w-full text-[11px] text-left">
                                        <thead class="text-slate-500 bg-white/5">
                                        <tr>
                                            <th class="px-4 py-2 font-medium">Descrição</th>
                                            <th class="px-4 py-2 font-medium text-right">Qtd</th>
                                            <th class="px-4 py-2 font-medium text-right">Valor</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-white/5">
                                        <?php foreach($items as $item): 
                                            $nome = isset($item['funcao']) ? $item['funcao'] : (isset($item['nome_equipamento']) ? $item['nome_equipamento'] : $item['tipo']);
                                        ?>
                                        <tr>
                                            <td class="px-4 py-2 text-slate-300"><?= $nome ?></td>
                                            <td class="px-4 py-2 text-right text-slate-500"><?= $item['quantidade'] ?></td>
                                            <td class="px-4 py-2 text-right text-slate-300">R$ <?= number_format($item['custo_calculado'], 2, ',', '.') ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <?php } 
                        renderItem('acc-salarios', 'Equipe Técnica', $totais['salarios'], 'ph-users', $dados['salarios'], 'sky');
                        renderItem('acc-estadia', 'Estadia e Alimentação', $totais['estadia'], 'ph-bed', $dados['estadia'], 'violet');
                        renderItem('acc-consumos', 'Combustível e Logística', $totais['consumos'], 'ph-gas-pump', $dados['consumos'], 'orange');
                        renderItem('acc-locacao', 'Locação de Equipamentos', $totais['locacao'], 'ph-speaker-hifi', $dados['locacao'], 'yellow');
                        renderItem('acc-admin', 'Custos Administrativos', $totais['admin'], 'ph-files', $dados['admin'], 'slate');
                        ?>
                    </div>
                </div>

                <!-- Client Info (Outside Card, inside Flex Column) -->
                <div class="pl-2 flex items-baseline gap-3">
                     <h4 class="text-sm font-bold text-slate-300 uppercase tracking-wide"><?= htmlspecialchars($proposta['nome_cliente']) ?></h4>
                     <span class="text-xs text-slate-500">•</span>
                     <p class="text-xs text-slate-500">Data: <?= date('d/m/Y', strtotime($proposta['data_criacao'])) ?></p>
                </div>
            </div> <!-- Close Flex Column -->

            <!-- Right: Chart -->
                <div class="glass-card rounded-xl border border-white/10 bg-surface/40 p-5 h-[450px] flex flex-col justify-between relative overflow-visible">
                    <div>
                        <h3 class="font-bold text-white text-sm mb-2 text-center">Distribuição Financeira</h3>
                        <p class="text-[10px] text-slate-500 text-center mb-6">Custos proporcionais</p>
                    </div>
                    
                    <div class="relative w-full min-h-[300px] grow">
                        <canvas id="graficoCustos"></canvas>
                            <div class="absolute inset-0 flex items-center justify-center pointer-events-none pb-8"> 
                            <div class="text-center">
                                <p class="text-[10px] text-slate-500 uppercase tracking-widest mb-1">Total</p>
                                <p class="text-base font-bold text-white">R$ <?= number_format($totais['custos'], 2, ',', '.') ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="text-center px-4 mt-2"></div>
                </div>
            </div>

        </div>
    </main>

    <footer class="border-t border-white/5 py-6 text-center text-[10px] text-slate-600">
        &copy; <?= date('Y') ?> SGT Propostas.
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            window.toggleAccordion = function(id) {
                const el = document.getElementById(id);
                if(el.classList.contains('hidden')) {
                    el.classList.remove('hidden');
                    el.parentElement.classList.add('bg-white/[0.04]');
                } else {
                    el.classList.add('hidden');
                    el.parentElement.classList.remove('bg-white/[0.04]');
                }
            }

            const ctx = document.getElementById('graficoCustos');
            if (ctx) {
                const chartData = {
                    labels: <?= json_encode($chart['labels']) ?>,
                    data: <?= json_encode($chart['data']) ?>,
                    colors: <?= json_encode($chart['colors']) ?>,
                    total: <?= json_encode($totais['custos']) ?>
                };

                if (chartData.data && chartData.data.length > 0) {
                    new Chart(ctx, {
                        type: 'doughnut',
                        data: {
                            labels: chartData.labels,
                            datasets: [{
                                data: chartData.data,
                                backgroundColor: chartData.colors,
                                borderWidth: 0,
                                hoverOffset: 10
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            cutout: '75%',
                            layout: { padding: 10 },
                            plugins: {
                                legend: {
                                    display: true,
                                    position: 'bottom',
                                    labels: {
                                        color: '#94a3b8',
                                        font: { size: 10, family: "'Inter', sans-serif" },
                                        padding: 15,
                                        usePointStyle: true,
                                        pointStyle: 'circle'
                                    }
                                },
                                tooltip: {
                                    backgroundColor: 'rgba(15, 23, 42, 0.95)',
                                    padding: 12,
                                    cornerRadius: 8,
                                    displayColors: true,
                                    callbacks: {
                                        label: function(context) {
                                            let label = context.label || '';
                                            if (label) { label += ': '; }
                                            if (context.parsed !== null) {
                                                label += new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(context.parsed);
                                            }
                                            return label;
                                        }
                                    }
                                }
                            }
                        }
                    });
                }
            }
        });
    </script>
</body>
</html>
