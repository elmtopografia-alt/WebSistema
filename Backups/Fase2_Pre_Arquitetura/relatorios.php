<?php
/**
 * relatorios.php
 * Dashboard Financeiro e Relatórios (Refatorado)
 * Tema: Aurora Premium (Dark/Glass)
 */

// 1. Carrega Lógica (Validação + Export CSV)
$reportData = require __DIR__ . '/core/reports_controller.php';

// 2. Carrega Controller do Dashboard (para obter dados do usuário para o Navbar)
// Isso é necessário porque o navbar espera $data['usuario']
$dashboardData = require __DIR__ . '/core/dashboard_controller.php';
$data = array_merge($dashboardData, $reportData); // Merge para garantir compatibilidade
?>
<!DOCTYPE html>
<html lang="pt-br" class="scroll-smooth">
<head>
    <?php include __DIR__ . '/components/dashboard/head.php'; ?>
    <title>SGT Pro | Relatórios Financeiros</title>
</head>
<body class="antialiased overflow-x-hidden min-h-screen">

    <!-- Background Effects -->
    <div class="fixed inset-0 grid-bg opacity-30 pointer-events-none z-0"></div>
    <div class="fixed top-0 left-1/4 w-96 h-96 bg-green-600/10 rounded-full blur-[100px] pointer-events-none z-0"></div>
    <div class="fixed bottom-1/4 right-1/4 w-96 h-96 bg-blue-500/10 rounded-full blur-[100px] pointer-events-none z-0"></div>

    <!-- Navbar -->
    <?php include __DIR__ . '/components/dashboard/navbar.php'; ?>

    <!-- Main Content -->
    <main class="relative z-10 pt-28 pb-12 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Header da Página -->
        <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
            <div>
                <h1 class="text-3xl font-bold text-white mb-2">Dashboard Financeiro</h1>
                <p class="text-slate-400">Inteligência de negócio e acompanhamento de métricas.</p>
            </div>
            
            <div class="flex gap-3">
                <button onclick="if (!document.fullscreenElement) { document.documentElement.requestFullscreen(); } else { if (document.exitFullscreen) { document.exitFullscreen(); } }" 
                        class="px-4 py-2 rounded-lg border border-white/10 text-slate-300 hover:text-white hover:bg-white/5 transition-colors flex items-center gap-2">
                    <i class="ph ph-arrows-out-simple"></i>
                    <span class="hidden sm:inline">Tela Cheia</span>
                </button>
                
                <a href="relatorios.php?exportar=csv" 
                   class="px-4 py-2 rounded-lg bg-green-600/20 text-green-400 border border-green-600/30 hover:bg-green-600/30 transition-colors flex items-center gap-2 font-bold shadow-lg shadow-green-900/20">
                    <i class="ph ph-microsoft-excel-logo text-lg"></i>
                    Exportar CSV
                </a>
            </div>
        </div>

        <!-- KPIs Cards -->
        <?php include __DIR__ . '/components/reports/kpi_cards.php'; ?>

        <!-- Gráficos -->
        <?php include __DIR__ . '/components/reports/charts_layout.php'; ?>

    </main>

    <!-- Footer -->
    <?php include __DIR__ . '/components/dashboard/footer.php'; ?>

    <!-- Chart Logic (JS) -->
    <?php include __DIR__ . '/components/reports/charts_logic.php'; ?>

</body>
</html>