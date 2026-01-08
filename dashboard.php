<?php
// Nome do Arquivo: dashboard.php

// ==========================================
// 1. LÓGICA DE DADOS (BACKEND)
// ==========================================
session_start();

// Simulação de Usuário Logado
$id_criador_logado = $_SESSION['usuario_id'] ?? 1;

/**
 * ------------------------------------------------------------------
 * SIMULAÇÃO DE DADOS (Para testar o layout sem conexão com o BD)
 * Substitua este bloco pela conexão real abaixo quando implementar.
 * ------------------------------------------------------------------
 */

// Dados dos Cards (KPIs)
$kpis = [
    'vendas_totais' => 145890.50,
    'novos_clientes' => 24, // Interpretado como clientes criados pelo usuário
    'total_propostas' => 156,
    'receita_prevista' => 210500.00
];

// Dados para o Gráfico de Linha (Últimos 6 meses)
$grafico_linha = [
    'labels' => ['Ago', 'Set', 'Out', 'Nov', 'Dez', 'Jan'],
    'dados'  => [12500, 19000, 15400, 28900, 42000, 28090]
];

// Dados para o Gráfico de Pizza (Status)
$grafico_pizza = [
    'labels' => ['Aprovada', 'Enviada', 'Em Elaboração', 'Cancelada'],
    'dados'  => [45, 30, 15, 10], // Percentual ou Quantidade absoluta
    'cores'  => ['#10B981', '#3B82F6', '#F59E0B', '#EF4444'] // Verde, Azul, Laranja, Vermelho
];


/**
 * ------------------------------------------------------------------
 * CÓDIGO SQL REAL (Descomente e adapte ao conectar no banco)
 * ------------------------------------------------------------------
 */
/*
require_once 'db.php'; // Sua conexão
$conn = Database::getProd();

// 1. KPIs
$sqlKPI = "SELECT 
            SUM(valor_final_proposta) as vendas_totais,
            COUNT(id_proposta) as total_propostas,
            (SELECT COUNT(*) FROM Clientes WHERE id_criador = ?) as novos_clientes
           FROM Propostas 
           WHERE id_criador = ? AND data_criacao >= DATE_SUB(NOW(), INTERVAL 6 MONTH)";
// Execute e preencha o array $kpis...

// 2. Gráfico de Linha (Agrupado por mês)
$sqlLinha = "SELECT 
                DATE_FORMAT(data_criacao, '%b') as mes, 
                SUM(valor_final_proposta) as total 
             FROM Propostas 
             WHERE id_criador = ? AND data_criacao >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
             GROUP BY YEAR(data_criacao), MONTH(data_criacao)
             ORDER BY data_criacao ASC";
// Execute e preencha $grafico_linha...

// 3. Gráfico Pizza (Status)
$sqlPizza = "SELECT status, COUNT(*) as qtd FROM Propostas WHERE id_criador = ? GROUP BY status";
// Execute e preencha $grafico_pizza...
*/

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard SGT | Analytics</title>
    
    <!-- Tailwind CSS (CDN) -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Chart.js (CDN) -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        body { font-family: 'Inter', sans-serif; background-color: #F3F4F6; }
        .card-hover:hover { transform: translateY(-2px); box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); }
        .transition-all { transition: all 0.3s ease; }
    </style>
</head>
<body class="text-gray-800">

    <!-- Layout Wrapper -->
    <div class="min-h-screen flex flex-col">

        <!-- Header -->
        <header class="bg-white shadow-sm sticky top-0 z-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex justify-between items-center">
                <div class="flex items-center gap-3">
                    <div class="bg-blue-600 text-white p-2 rounded-lg">
                        <i class="fa-solid fa-chart-line"></i>
                    </div>
                    <h1 class="text-xl font-bold text-gray-800">SGT Dashboard</h1>
                </div>
                <div class="flex items-center gap-4">
                    <span class="text-sm text-gray-500 hidden sm:block">Última atualização: Hoje, 14:30</span>
                    <div class="h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-bold border border-blue-200">
                        U
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="flex-grow p-4 sm:p-6 lg:p-8 max-w-7xl mx-auto w-full">
            
            <!-- Welcome Section -->
            <div class="mb-8">
                <h2 class="text-2xl font-bold text-gray-800">Visão Geral</h2>
                <p class="text-gray-500">Acompanhe o desempenho das suas propostas nos últimos 6 meses.</p>
            </div>

            <!-- 1. Cards de Métricas (KPIs) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                
                <!-- Card 1: Vendas Totais -->
                <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100 card-hover transition-all">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Vendas Totais</p>
                            <h3 class="text-2xl font-bold text-gray-800 mt-1">
                                R$ <?php echo number_format($kpis['vendas_totais'], 2, ',', '.'); ?>
                            </h3>
                            <span class="text-xs text-emerald-500 font-semibold bg-emerald-50 px-2 py-1 rounded-full mt-2 inline-block">
                                <i class="fa-solid fa-arrow-up"></i> +12.5%
                            </span>
                        </div>
                        <div class="p-3 bg-blue-50 rounded-lg text-blue-600">
                            <i class="fa-solid fa-wallet text-xl"></i>
                        </div>
                    </div>
                </div>

                <!-- Card 2: Propostas -->
                <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100 card-hover transition-all">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Total Propostas</p>
                            <h3 class="text-2xl font-bold text-gray-800 mt-1">
                                <?php echo $kpis['total_propostas']; ?>
                            </h3>
                            <span class="text-xs text-gray-400 mt-2 inline-block">No período</span>
                        </div>
                        <div class="p-3 bg-orange-50 rounded-lg text-orange-500">
                            <i class="fa-solid fa-file-invoice text-xl"></i>
                        </div>
                    </div>
                </div>

                <!-- Card 3: Receita Prevista -->
                <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100 card-hover transition-all">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Receita Prevista</p>
                            <h3 class="text-2xl font-bold text-gray-800 mt-1">
                                R$ <?php echo number_format($kpis['receita_prevista'], 2, ',', '.'); ?>
                            </h3>
                            <span class="text-xs text-blue-500 font-semibold bg-blue-50 px-2 py-1 rounded-full mt-2 inline-block">
                                Pipeline Ativo
                            </span>
                        </div>
                        <div class="p-3 bg-emerald-50 rounded-lg text-emerald-500">
                            <i class="fa-solid fa-chart-line text-xl"></i>
                        </div>
                    </div>
                </div>

                <!-- Card 4: Novos Clientes/Criadores -->
                <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100 card-hover transition-all">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Novos Clientes</p>
                            <h3 class="text-2xl font-bold text-gray-800 mt-1">
                                <?php echo $kpis['novos_clientes']; ?>
                            </h3>
                            <span class="text-xs text-emerald-500 mt-2 inline-block">+4 esta semana</span>
                        </div>
                        <div class="p-3 bg-indigo-50 rounded-lg text-indigo-500">
                            <i class="fa-solid fa-users text-xl"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. Área de Gráficos -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                <!-- Gráfico Principal (Linha) - Ocupa 2 colunas -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 lg:col-span-2">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-bold text-gray-800">Evolução Financeira</h3>
                        <button class="text-sm text-gray-400 hover:text-blue-600"><i class="fa-solid fa-ellipsis"></i></button>
                    </div>
                    <div class="relative h-72 w-full">
                        <canvas id="lineChart"></canvas>
                    </div>
                </div>

                <!-- Gráfico Secundário (Pizza/Donut) - Ocupa 1 coluna -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-bold text-gray-800">Status das Propostas</h3>
                        <button class="text-sm text-gray-400 hover:text-blue-600"><i class="fa-solid fa-filter"></i></button>
                    </div>
                    <div class="relative h-60 w-full flex justify-center">
                        <canvas id="doughnutChart"></canvas>
                    </div>
                    <!-- Legenda Customizada (Opcional, pois o ChartJS já gera) -->
                    <div class="mt-6 space-y-3">
                        <?php 
                        foreach($grafico_pizza['labels'] as $index => $label): 
                            $cor = $grafico_pizza['cores'][$index];
                            $val = $grafico_pizza['dados'][$index];
                        ?>
                        <div class="flex justify-between items-center text-sm">
                            <div class="flex items-center gap-2">
                                <span class="w-3 h-3 rounded-full" style="background-color: <?php echo $cor; ?>"></span>
                                <span class="text-gray-600"><?php echo $label; ?></span>
                            </div>
                            <span class="font-semibold text-gray-800"><?php echo $val; ?>%</span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

            </div>

        </main>
    </div>

    <!-- ==========================================
       3. CONFIGURAÇÃO DOS GRÁFICOS (JS)
    ========================================== -->
    <script>
        // Configuração Comum
        Chart.defaults.font.family = "'Inter', sans-serif";
        Chart.defaults.color = '#6B7280';

        // 1. Gráfico de Linha (Evolução Financeira)
        const ctxLine = document.getElementById('lineChart').getContext('2d');
        
        // Criando gradiente para o fundo da linha
        const gradient = ctxLine.createLinearGradient(0, 0, 0, 300);
        gradient.addColorStop(0, 'rgba(59, 130, 246, 0.15)'); // Azul suave
        gradient.addColorStop(1, 'rgba(59, 130, 246, 0)');

        new Chart(ctxLine, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($grafico_linha['labels']); ?>,
                datasets: [{
                    label: 'Faturamento (R$)',
                    data: <?php echo json_encode($grafico_linha['dados']); ?>,
                    borderColor: '#2563EB', // Azul Royal
                    backgroundColor: gradient,
                    borderWidth: 3,
                    pointBackgroundColor: '#ffffff',
                    pointBorderColor: '#2563EB',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    fill: true,
                    tension: 0.4 // Curva suave (Spline)
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#1E293B',
                        padding: 12,
                        titleFont: { size: 13 },
                        bodyFont: { size: 14, weight: 'bold' },
                        callbacks: {
                            label: function(context) {
                                return 'R$ ' + context.parsed.y.toLocaleString('pt-BR');
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { borderDash: [4, 4], color: '#E5E7EB' },
                        ticks: {
                            callback: function(value) { return 'R$ ' + value/1000 + 'k'; }
                        }
                    },
                    x: {
                        grid: { display: false }
                    }
                }
            }
        });

        // 2. Gráfico de Pizza/Donut (Status)
        const ctxDoughnut = document.getElementById('doughnutChart').getContext('2d');
        new Chart(ctxDoughnut, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode($grafico_pizza['labels']); ?>,
                datasets: [{
                    data: <?php echo json_encode($grafico_pizza['dados']); ?>,
                    backgroundColor: <?php echo json_encode($grafico_pizza['cores']); ?>,
                    borderWidth: 0,
                    hoverOffset: 10
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '75%', // Espessura do Donut
                plugins: {
                    legend: { display: false }, // Usamos a legenda HTML customizada
                    tooltip: {
                        backgroundColor: '#1E293B',
                        callbacks: {
                            label: function(context) {
                                return ' ' + context.label + ': ' + context.parsed + '%';
                            }
                        }
                    }
                }
            }
        });
    </script>

</body>
</html>