<?php
// Nome do Arquivo: relatorio_9_16.php
// Função: Versão 9:16 (Story/Mobile) do Relatório Financeiro para Exportação PNG

session_start();
require_once 'config.php';
require_once 'db.php';

if (!isset($_SESSION['usuario_id'])) { header("Location: login.php"); exit; }
if (!isset($_GET['id'])) { die("ID da proposta não fornecido."); }

$id_proposta = intval($_GET['id']);
$id_usuario = $_SESSION['usuario_id'];
$is_demo = ($_SESSION['ambiente'] === 'demo');
$conn = $is_demo ? Database::getDemo() : Database::getProd();

// 1. Busca Dados da Proposta
$sql = "SELECT p.*, c.nome_cliente, s.nome as nome_servico 
        FROM Propostas p 
        LEFT JOIN Clientes c ON p.id_cliente = c.id_cliente 
        LEFT JOIN Tipo_Servicos s ON p.id_servico = s.id_servico 
        WHERE p.id_proposta = ? AND p.id_criador = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('ii', $id_proposta, $id_usuario);
$stmt->execute();
$proposta = $stmt->get_result()->fetch_assoc();

if (!$proposta) { die("Proposta não encontrada ou acesso negado."); }

// 2. Busca Custos Detalhados
function getCustos($conn, $tabela, $id_proposta) {
    $sql = "SELECT * FROM $tabela WHERE id_proposta = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $id_proposta);
    $stmt->execute();
    return $stmt->get_result();
}

$salarios = getCustos($conn, 'Proposta_Salarios', $id_proposta);
$estadia = getCustos($conn, 'Proposta_Estadia', $id_proposta);
$consumos = getCustos($conn, 'Proposta_Consumos', $id_proposta);

// Locação precisa de JOIN para pegar o nome
$sql_loc = "SELECT pl.*, tl.nome as nome_equipamento, m.nome_marca 
            FROM Proposta_Locacao pl 
            LEFT JOIN Tipo_Locacao tl ON pl.id_locacao = tl.id_locacao 
            LEFT JOIN Marcas m ON pl.id_marca = m.id_marca 
            WHERE pl.id_proposta = ?";
$stmt_loc = $conn->prepare($sql_loc);
$stmt_loc->bind_param('i', $id_proposta);
$stmt_loc->execute();
$locacao = $stmt_loc->get_result();

$admin = getCustos($conn, 'Proposta_Custos_Administrativos', $id_proposta);

// 3. Totais e Cálculos
$total_salarios = 0; 
$dados_salarios = [];
while($r = $salarios->fetch_assoc()) {
    $custo = ($r['quantidade'] * $r['salario_base'] * $r['fator_encargos'] / 30) * $r['dias'];
    $r['custo_calculado'] = $custo;
    $dados_salarios[] = $r;
    $total_salarios += $custo;
}

$total_estadia = 0;
$dados_estadia = [];
while($r = $estadia->fetch_assoc()) {
    $custo = $r['quantidade'] * $r['valor_unitario'] * $r['dias'];
    $r['custo_calculado'] = $custo;
    $dados_estadia[] = $r;
    $total_estadia += $custo;
}

$total_consumos = 0;
$dados_consumos = [];
while($r = $consumos->fetch_assoc()) {
    $kml = $r['consumo_kml'] > 0 ? $r['consumo_kml'] : 1;
    $custo = ($r['km_total'] * $r['valor_litro'] / $kml) * $r['quantidade'];
    $r['custo_calculado'] = $custo;
    $dados_consumos[] = $r;
    $total_consumos += $custo;
}

$total_locacao = 0;
$dados_locacao = [];
while($r = $locacao->fetch_assoc()) {
    $custo = ($r['quantidade'] * $r['valor_mensal'] / 30) * $r['dias'];
    $r['custo_calculado'] = $custo;
    $dados_locacao[] = $r;
    $total_locacao += $custo;
}

$total_admin = 0;
$dados_admin = [];
while($r = $admin->fetch_assoc()) {
    $custo = $r['quantidade'] * $r['valor'];
    $r['custo_calculado'] = $custo;
    $dados_admin[] = $r;
    $total_admin += $custo;
}

$total_custos = $total_salarios + $total_estadia + $total_consumos + $total_locacao + $total_admin;
$receita_bruta = $proposta['valor_final_proposta'];
$lucro_real = $receita_bruta - $total_custos;
$margem_real = ($receita_bruta > 0) ? ($lucro_real / $receita_bruta) * 100 : 0;

// Preparar dados para o gráfico
$chart_labels = [];
$chart_data = [];
$chart_colors = [];

if ($total_salarios > 0) { $chart_labels[] = 'Equipe'; $chart_data[] = $total_salarios; $chart_colors[] = '#0d6efd'; }
if ($total_estadia > 0) { $chart_labels[] = 'Estadia'; $chart_data[] = $total_estadia; $chart_colors[] = '#6610f2'; }
if ($total_consumos > 0) { $chart_labels[] = 'Combustível'; $chart_data[] = $total_consumos; $chart_colors[] = '#fd7e14'; }
if ($total_locacao > 0) { $chart_labels[] = 'Equipamentos'; $chart_data[] = $total_locacao; $chart_colors[] = '#ffc107'; }
if ($total_admin > 0) { $chart_labels[] = 'Admin'; $chart_data[] = $total_admin; $chart_colors[] = '#6c757d'; }

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatório Vertical 9:16 | Proposta #<?= $proposta['numero_proposta'] ?></title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Exo+2:wght@400;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <script src="https://unpkg.com/@phosphor-icons/web"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        display: ['Exo 2', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            dark: '#001e3c',
                            primary: '#0a2e5c',
                            surface: '#132f4c',
                            accent: '#FF7518',
                            action: '#EA580C',
                            glow: '#4fc3f7',
                        }
                    }
                }
            }
        }
    </script>

    <style>
        .glass-panel {
            background: rgba(10, 46, 92, 0.65);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.15);
        }
        .glass-card {
            background: rgba(19, 47, 76, 0.6);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.12);
        }
        body {
            background: radial-gradient(circle at center, #0a2e5c 0%, #001224 100%);
            min-height: 100vh;
        }
        /* Hide scrollbar for cleaner screenshot if needed */
        ::-webkit-scrollbar { display: none; }
    </style>
</head>
<body class="text-slate-200 font-sans antialiased overflow-x-hidden">

    <!-- Container otimizado para 9:16 -->
    <div class="max-w-[1080px] mx-auto px-6 py-10 relative">
        
        <!-- Header Compacto -->
        <div class="text-center mb-10">
            <div class="inline-flex items-center gap-3 mb-4">
                <img src="<?= BASE_URL ?>/assets/img/logo_sgt.png" alt="SGT" class="h-16">
            </div>
            <h1 class="font-display text-4xl font-bold text-white leading-tight">
                <?= htmlspecialchars($proposta['nome_cliente']) ?>
            </h1>
            <p class="text-blue-200/80 text-xl mt-2">
                <?= htmlspecialchars($proposta['nome_servico']) ?>
            </p>
            <div class="flex justify-center gap-2 mt-4">
                <span class="px-3 py-1 rounded-full bg-brand-accent/20 text-brand-accent text-sm font-bold border border-brand-accent/30 tracking-wider">
                    #<?= $proposta['numero_proposta'] ?>
                </span>
                <span class="px-3 py-1 rounded-full bg-white/10 text-slate-300 text-sm font-bold border border-white/10 tracking-wider">
                    <?= date('d/m/Y', strtotime($proposta['data_criacao'])) ?>
                </span>
            </div>
        </div>

        <!-- KPIs Vertical Stack -->
        <div class="grid grid-cols-1 gap-4 mb-10">
            <!-- Receita -->
            <div class="glass-card p-6 rounded-2xl flex justify-between items-center relative overflow-hidden">
                <div class="absolute right-0 top-0 h-full w-24 bg-gradient-to-l from-blue-500/10 to-transparent"></div>
                <div>
                    <p class="text-blue-300 text-sm font-bold uppercase tracking-wider mb-1">Receita Final</p>
                    <div class="font-display text-4xl font-bold text-white">
                        R$ <?= number_format($receita_bruta, 2, ',', '.') ?>
                    </div>
                </div>
                <div class="h-12 w-12 rounded-full bg-blue-500/20 flex items-center justify-center text-blue-400">
                    <i class="ph ph-wallet text-2xl"></i>
                </div>
            </div>

            <!-- Custos -->
            <div class="glass-card p-6 rounded-2xl flex justify-between items-center relative overflow-hidden border-red-500/20">
                <div class="absolute right-0 top-0 h-full w-24 bg-gradient-to-l from-red-500/10 to-transparent"></div>
                <div>
                    <p class="text-red-300 text-sm font-bold uppercase tracking-wider mb-1">Total de Custos</p>
                    <div class="font-display text-4xl font-bold text-white">
                        R$ <?= number_format($total_custos, 2, ',', '.') ?>
                    </div>
                </div>
                <div class="h-12 w-12 rounded-full bg-red-500/20 flex items-center justify-center text-red-400">
                    <i class="ph ph-trend-down text-2xl"></i>
                </div>
            </div>

            <!-- Lucro (Destaque) -->
            <div class="glass-card p-8 rounded-2xl flex flex-col items-center text-center relative overflow-hidden border-brand-accent/40 bg-brand-accent/10">
                <div class="absolute inset-0 bg-gradient-to-b from-brand-accent/5 to-transparent"></div>
                <p class="text-brand-accent text-lg font-bold uppercase tracking-wider mb-2 relative z-10">Lucro Real</p>
                <div class="font-display text-6xl font-bold text-white drop-shadow-[0_0_15px_rgba(255,117,24,0.4)] relative z-10">
                    R$ <?= number_format($lucro_real, 2, ',', '.') ?>
                </div>
                <div class="mt-4 px-4 py-1.5 rounded-full bg-brand-accent text-brand-dark font-bold text-lg relative z-10">
                    Margem: <?= number_format($margem_real, 1, ',', '.') ?>%
                </div>
            </div>
        </div>

        <!-- Gráfico -->
        <div class="mb-10 p-4 glass-panel rounded-3xl">
            <h3 class="font-display text-xl font-bold text-white mb-6 text-center">Distribuição Financeira</h3>
            <div class="relative w-full aspect-square max-w-[500px] mx-auto">
                <canvas id="graficoCustos"></canvas>
                <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                    <div class="text-center">
                        <span class="text-sm text-slate-400 uppercase">Total Despesas</span>
                        <div class="font-bold text-white text-2xl">R$ <?= number_format($total_custos, 2, ',', '.') ?></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Lista de Custos Simplificada (Apenas Totais) -->
        <div class="space-y-3">
             <h3 class="font-display text-xl font-bold text-white mb-4 flex items-center gap-2">
                <i class="ph ph-list-dashes text-brand-accent"></i> Resumo por Categoria
            </h3>
            
            <?php 
            $categorias = [
                ['Equipe Técnica', $total_salarios, 'ph-users', 'bg-blue-500'],
                ['Estadia & Alimentação', $total_estadia, 'ph-bed', 'bg-violet-500'],
                ['Combustível & Logística', $total_consumos, 'ph-gas-pump', 'bg-orange-500'],
                ['Locação de Equipamentos', $total_locacao, 'ph-speaker-hifi', 'bg-yellow-500'],
                ['Custos Administrativos', $total_admin, 'ph-files', 'bg-slate-500'],
            ];

            foreach($categorias as $cat): 
                if($cat[1] <= 0) continue;
            ?>
            <div class="glass-panel p-4 rounded-xl flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 rounded-lg <?= $cat[3] ?>/20 flex items-center justify-center text-white">
                        <i class="ph <?= $cat[2] ?> text-xl"></i>
                    </div>
                    <span class="font-medium text-lg text-slate-200"><?= $cat[0] ?></span>
                </div>
                <span class="font-bold text-xl text-white">R$ <?= number_format($cat[1], 2, ',', '.') ?></span>
            </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Rodapé -->
        <div class="mt-12 text-center text-slate-500 text-sm">
            <p>Gerado via SGT Propostas em <?= date('d/m/Y H:i') ?></p>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const ctx = document.getElementById('graficoCustos');
        const chartData = {
            labels: <?= json_encode($chart_labels) ?>,
            datasets: [{
                data: <?= json_encode($chart_data) ?>,
                backgroundColor: [
                    '#0ea5e9', // Sky 500
                    '#8b5cf6', // Violet 500
                    '#f97316', // Orange 500
                    '#eab308', // Yellow 500
                    '#64748b'  // Slate 500
                ],
                borderWidth: 0,
            }]
        };

        new Chart(ctx, {
            type: 'doughnut',
            data: chartData,
            options: {
                responsive: true,
                cutout: '75%',
                plugins: {
                    legend: { 
                        position: 'bottom',
                        labels: {
                            color: '#cbd5e1',
                            font: { family: 'Inter', size: 14 },
                            padding: 20,
                            usePointStyle: true,
                        }
                    },
                    tooltip: { enabled: false } // Cleaner look for image
                }
            }
        });
    </script>
</body>
</html>
