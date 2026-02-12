<?php
/**
 * Cli_Pro.php
 * Página Inicial do Cliente PRO (Refatorada)
 * Estrutura Modular: Controller + Components
 */

// 1. Carrega Lógica do Dashboard (Controller)
$data = require __DIR__ . '/core/dashboard_controller.php';
?>
<!DOCTYPE html>
<html lang="pt-br" class="scroll-smooth">
<head>
    <?php include __DIR__ . '/components/dashboard/head.php'; ?>
</head>
<body class="antialiased overflow-x-hidden min-h-screen">

    <!-- Background Effects -->
    <div class="fixed inset-0 grid-bg opacity-30 pointer-events-none z-0"></div>
    <div class="fixed top-0 left-1/4 w-96 h-96 bg-blue-600/10 rounded-full blur-[100px] pointer-events-none z-0"></div>
    <div class="fixed bottom-1/4 right-1/4 w-96 h-96 bg-orange-500/10 rounded-full blur-[100px] pointer-events-none z-0"></div>

    <!-- Navbar -->
    <?php include __DIR__ . '/components/dashboard/navbar.php'; ?>

    <!-- Main Content -->
    <main class="relative z-10 pt-28 pb-12 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Welcome Section -->
        <?php include __DIR__ . '/components/dashboard/welcome_card.php'; ?>

        <!-- KPIs / Stats Grid -->
        <?php include __DIR__ . '/components/dashboard/stats_grid.php'; ?>

        <!-- Quick Actions -->
        <?php include __DIR__ . '/components/dashboard/quick_actions.php'; ?>

    </main>

    <!-- Footer -->
    <?php include __DIR__ . '/components/dashboard/footer.php'; ?>

</body>
</html>
