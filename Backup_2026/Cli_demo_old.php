<?php
/**
 * Cli_demo.php
 * Página Inicial do Cliente DEMO (Em teste)
 * Ambiente segregado - com CTAs para upgrade
 */

require_once 'session_validator.php';
require_once 'config.php';
require_once 'db.php';

// Verifica se é realmente um usuário DEMO
$ambiente = $_SESSION['ambiente'] ?? 'producao';
if ($ambiente !== 'demo') {
    header('Location: Cli_Pro.php');
    exit;
}

$id_usuario = $_SESSION['usuario_id'];
$nome_usuario = $_SESSION['usuario_nome'] ?? 'Usuário';
$primeiro_nome = explode(' ', trim($nome_usuario))[0];

// Calcula dias restantes
$validade_demo = $_SESSION['validade_demo'] ?? null;
$diasRestantes = 0;
$urgente = false;

if ($validade_demo) {
    $hoje = new DateTime();
    $validade = new DateTime($validade_demo);
    $diff = $hoje->diff($validade);
    $diasRestantes = $diff->invert ? 0 : $diff->days;
    $urgente = $diasRestantes <= 1;
}

// KPIs rápidos
try {
    $conn = Database::getDemo();
    
    $stmtKPI = $conn->prepare("SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN LOWER(status) LIKE '%aprov%' THEN 1 ELSE 0 END) as aprovadas
        FROM Propostas 
        WHERE id_criador = ? 
        AND MONTH(data_criacao) = MONTH(CURRENT_DATE()) 
        AND YEAR(data_criacao) = YEAR(CURRENT_DATE())");
    $stmtKPI->bind_param('i', $id_usuario);
    $stmtKPI->execute();
    $kpi = $stmtKPI->get_result()->fetch_assoc();
    
} catch (Exception $e) {
    $kpi = ['total' => 0, 'aprovadas' => 0];
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SGT Demo | Bem-vindo</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Exo+2:wght@400;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
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
                            demo: '#eab308', // Amarelo para DEMO
                        }
                    }
                }
            }
        }
    </script>

    <style>
        body {
            background: radial-gradient(circle at center, #0a2e5c 0%, #001224 100%);
            min-height: 100vh;
        }
        .glass-panel {
            background: rgba(10, 46, 92, 0.65);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.15);
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.3);
        }
        .glass-card {
            background: rgba(19, 47, 76, 0.6);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.12);
            transition: all 0.3s ease;
        }
        .glass-card:hover {
            background: rgba(10, 46, 92, 0.85);
            border-color: rgba(234, 179, 8, 0.6);
            transform: translateY(-5px);
            box-shadow: 0 12px 35px -10px rgba(234, 179, 8, 0.3);
        }
        .pulse-urgent {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }
    </style>
</head>
<body class="text-slate-200 font-sans antialiased">

    <!-- Navbar DEMO -->
    <nav class="w-full glass-panel sticky top-0 z-50 border-b border-white/10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center gap-4">
                    <img src="<?= BASE_URL ?>/assets/img/logo_sgt.png" alt="SGT" class="h-10">
                    <span class="px-2 py-0.5 rounded bg-yellow-500/20 text-yellow-400 text-[10px] font-bold border border-yellow-500/30 uppercase tracking-wider">DEMO</span>
                </div>

                <div class="hidden md:flex items-center gap-4">
                    <a href="painel.php" class="text-sm font-medium text-slate-300 hover:text-white transition-colors flex items-center gap-2">
                        <i class="ph ph-chart-line-up"></i> Dashboard
                    </a>
                    <a href="minha_empresa.php" class="text-sm font-medium text-slate-300 hover:text-white transition-colors flex items-center gap-2">
                        <i class="ph ph-gear"></i> Empresa
                    </a>
                    <a href="meus_clientes.php" class="text-sm font-medium text-slate-300 hover:text-white transition-colors flex items-center gap-2">
                        <i class="ph ph-users"></i> Clientes
                    </a>
                    
                    <!-- CTA Contratar destacado -->
                    <a href="contratar.php" class="px-4 py-2 bg-green-600 hover:bg-green-500 text-white text-sm font-bold rounded-lg transition-colors shadow-lg shadow-green-900/30 flex items-center gap-2 <?= $urgente ? 'pulse-urgent' : '' ?>">
                        <i class="ph ph-rocket"></i> Contratar PRO
                    </a>

                    <a href="criar_proposta_dinamica.php" class="px-4 py-2 bg-brand-accent hover:bg-brand-action text-white text-sm font-bold rounded-lg transition-colors shadow-lg shadow-brand-accent/20 flex items-center gap-2">
                        <i class="ph ph-plus-bold"></i> Nova Proposta
                    </a>

                    <!-- User Menu -->
                    <div class="relative group ml-2">
                        <button class="flex items-center gap-2 text-white font-medium hover:text-yellow-400 transition-colors">
                            <div class="w-8 h-8 rounded-full bg-yellow-500/20 border border-yellow-500/30 flex items-center justify-center text-yellow-400">
                                <i class="ph ph-user"></i>
                            </div>
                            <span><?= htmlspecialchars($primeiro_nome) ?></span>
                            <i class="ph ph-caret-down text-xs text-slate-500"></i>
                        </button>
                        <div class="absolute right-0 mt-2 w-48 glass-panel rounded-xl shadow-xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50">
                            <div class="py-1">
                                <a href="alterar_senha.php" class="block px-4 py-2 text-sm text-slate-300 hover:bg-white/5 hover:text-white">
                                    <i class="ph ph-key mr-2"></i> Alterar Senha
                                </a>
                                <div class="h-px bg-white/10 my-1"></div>
                                <a href="logout.php" class="block px-4 py-2 text-sm text-red-400 hover:bg-red-500/10 hover:text-red-300">
                                    <i class="ph ph-sign-out mr-2"></i> Sair
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Alert Demo -->
    <?php if($diasRestantes <= 3): 
        $corBg = $urgente ? 'border-red-500/50 bg-red-500/10' : 'border-yellow-500/50 bg-yellow-500/10';
        $corTexto = $urgente ? 'text-red-200' : 'text-yellow-200';
    ?>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-6">
        <div class="glass-panel rounded-xl p-4 border <?= $corBg ?> <?= $corTexto ?> flex flex-col sm:flex-row justify-between items-center gap-4 <?= $urgente ? 'pulse-urgent' : '' ?>">
            <div>
                <strong class="block sm:inline">
                    <?php if($urgente): ?>
                        ⚠️ SEU TESTE ACABA EM BREVE! Seus dados serão apagados em menos de 24h.
                    <?php else: ?>
                        ⏳ Restam <?= $diasRestantes ?> dias de teste.
                    <?php endif; ?>
                </strong>
                <span class="text-sm opacity-80 hidden sm:inline ml-2">Contrate agora para manter seu acesso.</span>
            </div>
            <a href="contratar.php" class="px-4 py-2 bg-white/10 hover:bg-white/20 text-white text-xs font-bold rounded-lg transition-colors uppercase tracking-wider border border-white/20">
                Contratar Agora
            </a>
        </div>
    </div>
    <?php endif; ?>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        
        <!-- Welcome Section -->
        <div class="glass-panel rounded-2xl p-8 mb-8 text-center">
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-yellow-500/10 border border-yellow-500/20 text-yellow-400 text-sm font-bold mb-4">
                <i class="ph ph-test-tube"></i> Modo Demonstração
            </div>
            <h1 class="font-display text-4xl font-bold text-white mb-2">
                Olá, <?= htmlspecialchars($primeiro_nome) ?>!
            </h1>
            <p class="text-slate-400 text-lg mb-4">
                Explore o SGT-Propostas e veja como ele pode transformar seu negócio.
            </p>
            
            <div class="inline-flex items-center gap-4 text-sm">
                <span class="text-yellow-400">
                    <i class="ph ph-hourglass-medium"></i>
                    <?= $diasRestantes ?> dias restantes
                </span>
                <a href="contratar.php" class="px-4 py-2 bg-green-600 hover:bg-green-500 text-white font-bold rounded-lg transition-colors shadow-lg">
                    <i class="ph ph-rocket mr-1"></i> Virar PRO
                </a>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <div class="glass-card p-6 rounded-2xl text-center">
                <div class="w-16 h-16 mx-auto mb-4 rounded-xl bg-brand-accent/10 flex items-center justify-center text-brand-accent border border-brand-accent/20">
                    <i class="ph ph-file-text text-3xl"></i>
                </div>
                <h3 class="text-3xl font-bold text-white"><?= $kpi['total'] ?? 0 ?></h3>
                <p class="text-slate-400 text-sm">Propostas de Teste (Mês)</p>
            </div>

            <div class="glass-card p-6 rounded-2xl text-center">
                <div class="w-16 h-16 mx-auto mb-4 rounded-xl bg-emerald-500/10 flex items-center justify-center text-emerald-400 border border-emerald-500/20">
                    <i class="ph ph-check-circle text-3xl"></i>
                </div>
                <h3 class="text-3xl font-bold text-white"><?= $kpi['aprovadas'] ?? 0 ?></h3>
                <p class="text-slate-400 text-sm">Aprovadas (Mês)</p>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <a href="criar_proposta_dinamica.php" class="glass-card p-6 rounded-2xl flex flex-col items-center text-center group">
                <div class="w-14 h-14 mb-4 rounded-xl bg-brand-accent/10 flex items-center justify-center text-brand-accent border border-brand-accent/20 group-hover:scale-110 transition-transform">
                    <i class="ph ph-plus-circle text-3xl"></i>
                </div>
                <h4 class="font-bold text-white mb-1">Nova Proposta</h4>
                <p class="text-slate-500 text-xs">Testar criação de proposta</p>
            </a>

            <a href="painel.php" class="glass-card p-6 rounded-2xl flex flex-col items-center text-center group">
                <div class="w-14 h-14 mb-4 rounded-xl bg-blue-500/10 flex items-center justify-center text-blue-400 border border-blue-500/20 group-hover:scale-110 transition-transform">
                    <i class="ph ph-chart-line-up text-3xl"></i>
                </div>
                <h4 class="font-bold text-white mb-1">Dashboard</h4>
                <p class="text-slate-500 text-xs">Ver estatísticas</p>
            </a>

            <a href="meus_clientes.php" class="glass-card p-6 rounded-2xl flex flex-col items-center text-center group">
                <div class="w-14 h-14 mb-4 rounded-xl bg-purple-500/10 flex items-center justify-center text-purple-400 border border-purple-500/20 group-hover:scale-110 transition-transform">
                    <i class="ph ph-users text-3xl"></i>
                </div>
                <h4 class="font-bold text-white mb-1">Clientes</h4>
                <p class="text-slate-500 text-xs">Cadastrar clientes</p>
            </a>

            <a href="minha_empresa.php" class="glass-card p-6 rounded-2xl flex flex-col items-center text-center group">
                <div class="w-14 h-14 mb-4 rounded-xl bg-slate-500/10 flex items-center justify-center text-slate-400 border border-slate-500/20 group-hover:scale-110 transition-transform">
                    <i class="ph ph-buildings text-3xl"></i>
                </div>
                <h4 class="font-bold text-white mb-1">Minha Empresa</h4>
                <p class="text-slate-500 text-xs">Configurar dados</p>
            </a>
        </div>

        <!-- CTA Final -->
        <div class="glass-panel rounded-2xl p-8 text-center border border-green-500/20 bg-green-500/5">
            <h2 class="font-display text-2xl font-bold text-white mb-2">
                Gostou do que viu?
            </h2>
            <p class="text-slate-400 mb-6">
                Contrate agora e mantenha todos os seus dados. Sem perder nada!
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="contratar.php" class="px-8 py-4 bg-gradient-to-r from-green-600 to-emerald-600 text-white font-bold rounded-xl shadow-lg shadow-green-900/30 hover:shadow-green-600/40 transition-all transform hover:scale-105 flex items-center justify-center gap-2">
                    <i class="ph ph-crown text-xl"></i> Contratar Plano PRO
                </a>
                <a href="https://api.whatsapp.com/send?phone=5531971875928&text=Tenho%20dúvidas%20sobre%20o%20SGT" target="_blank" class="px-6 py-4 glass-panel text-white font-semibold rounded-xl hover:bg-white/10 transition-all flex items-center justify-center gap-2">
                    <i class="ph ph-whatsapp-logo text-xl text-green-400"></i> Falar com Consultor
                </a>
            </div>
        </div>

    </main>

    <!-- Footer -->
    <footer class="border-t border-white/5 mt-12 py-6">
        <div class="max-w-7xl mx-auto px-4 text-center text-slate-500 text-sm">
            © <?= date('Y') ?> SGT-Propostas | Modo Demonstração
        </div>
    </footer>

</body>
</html>
