<?php
/**
 * Cli_demo.php
 * Página Inicial do Cliente DEMO (Em teste)
 * Visual atualizado "Clean" (Dark/Glass)
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
<html lang="pt-br" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SGT Demo | Dashboard</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Exo+2:wght@400;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    
    <!-- Phosphor Icons -->
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
                        background: '#0a0f1a',
                        surface: '#111827',
                        primary: '#f97316', // Orange 500
                        secondary: '#3b82f6', // Blue 500
                        demo: '#eab308', // Yellow 500
                    },
                    animation: {
                        'float': 'float 3s ease-in-out infinite',
                        'pulse-glow': 'pulse-glow 2s ease-in-out infinite',
                    },
                    keyframes: {
                        float: {
                            '0%, 100%': { transform: 'translateY(0px)' },
                            '50%': { transform: 'translateY(-10px)' },
                        },
                        'pulse-glow': {
                            '0%, 100%': { boxShadow: '0 0 20px rgba(234, 179, 8, 0.4)' },
                            '50%': { boxShadow: '0 0 40px rgba(234, 179, 8, 0.6)' },
                        }
                    }
                }
            }
        }
    </script>

    <style>
        body {
            background-color: #0a0f1a;
            color: #f8fafc;
        }
        
        /* Glass Effect */
        .glass {
            background: rgba(17, 24, 39, 0.7);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.3s ease;
        }

        .glass-card:hover {
            background: rgba(255, 255, 255, 0.08);
            border-color: rgba(234, 179, 8, 0.5);
            transform: translateY(-4px);
            box-shadow: 0 4px 20px -5px rgba(0, 0, 0, 0.3);
        }

        /* Text Gradients */
        .text-gradient {
            background: linear-gradient(135deg, #eab308 0%, #facc15 50%, #fef08a 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* Grid Background Pattern */
        .grid-bg {
            background-image: 
                linear-gradient(rgba(255, 255, 255, 0.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255, 255, 255, 0.03) 1px, transparent 1px);
            background-size: 50px 50px;
        }

        .pulse-urgent {
            animation: pulse-urgent 2s infinite;
        }
        @keyframes pulse-urgent {
            0% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.4); }
            70% { box-shadow: 0 0 0 10px rgba(239, 68, 68, 0); }
            100% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); }
        }
    </style>
</head>
<body class="antialiased overflow-x-hidden min-h-screen">

    <!-- Background Effects -->
    <div class="fixed inset-0 grid-bg opacity-30 pointer-events-none z-0"></div>
    <div class="fixed top-0 left-1/4 w-96 h-96 bg-yellow-600/10 rounded-full blur-[100px] pointer-events-none z-0"></div>
    <div class="fixed bottom-1/4 right-1/4 w-96 h-96 bg-blue-500/10 rounded-full blur-[100px] pointer-events-none z-0"></div>

    <!-- Navbar -->
    <nav class="fixed top-0 w-full glass z-50 transition-all duration-300">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-20">
                <!-- Logo -->
                <div class="flex items-center gap-3">
                    <?php include 'components/logo_svg.php'; ?>
                </div>

                <!-- Desktop Menu -->
                <div class="hidden md:flex items-center gap-4">
                    <a href="painel.php" class="text-sm font-medium text-slate-300 hover:text-white transition-colors flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-white/5">
                        <i class="ph ph-chart-line-up"></i> Dashboard
                    </a>
                    <a href="minha_empresa.php" class="text-sm font-medium text-slate-300 hover:text-white transition-colors flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-white/5">
                        <i class="ph ph-buildings"></i> Empresa
                    </a>
                    <a href="meus_clientes.php" class="text-sm font-medium text-slate-300 hover:text-white transition-colors flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-white/5">
                        <i class="ph ph-users"></i> Clientes
                    </a>
                    
                    <!-- CTA Upgrade -->
                    <a href="contratar.php" class="px-4 py-2 bg-gradient-to-r from-green-600 to-green-700 hover:from-green-500 hover:to-green-600 text-white text-sm font-bold rounded-lg transition-all shadow-lg shadow-green-900/40 flex items-center gap-2 transform hover:-translate-y-0.5 <?= $urgente ? 'pulse-urgent' : '' ?>">
                        <i class="ph ph-rocket-launch"></i> Contratar PRO
                    </a>

                    <a href="criar_proposta_dinamica.php?nova=1" class="ml-2 px-4 py-2 bg-gradient-to-r from-yellow-500 to-yellow-600 hover:from-yellow-600 hover:to-yellow-700 text-white text-sm font-bold rounded-lg transition-all shadow-lg shadow-yellow-500/25 flex items-center gap-2 transform hover:-translate-y-0.5">
                        <i class="ph ph-plus-bold"></i> Nova Proposta
                    </a>

                    <!-- User Menu Dropdown -->
                    <div class="relative group ml-2">
                        <button class="flex items-center gap-3 text-white font-medium pl-3 border-l border-white/10">
                            <div class="w-9 h-9 rounded-full bg-surface border border-white/10 flex items-center justify-center text-yellow-400 ring-2 ring-transparent group-hover:ring-yellow-500/30 transition-all">
                                <i class="ph ph-user"></i>
                            </div>
                        </button>
                        <!-- Dropdown -->
                        <div class="absolute right-0 mt-4 w-56 glass rounded-xl shadow-2xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 transform origin-top-right z-50">
                            <div class="p-3 border-b border-white/5 mb-1">
                                <p class="text-sm font-bold text-white"><?= htmlspecialchars($nome_usuario) ?></p>
                                <p class="text-xs text-yellow-500">Modo Demonstração</p>
                            </div>
                            <div class="p-1">
                                <a href="alterar_senha.php" class="flex items-center w-full px-3 py-2 text-sm text-slate-300 hover:bg-white/5 hover:text-white rounded-lg transition-colors">
                                    <i class="ph ph-key mr-2 text-slate-400"></i> Alterar Senha
                                </a>
                                <a href="logout.php" class="flex items-center w-full px-3 py-2 text-sm text-red-400 hover:bg-red-500/10 hover:text-red-300 rounded-lg transition-colors">
                                    <i class="ph ph-sign-out mr-2"></i> Sair
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Mobile Menu Button -->
                <button class="md:hidden p-2 text-slate-300 hover:text-white" onclick="document.getElementById('mobile-menu').classList.toggle('hidden')">
                    <i class="ph ph-list text-2xl"></i>
                </button>
            </div>
        </div>

        <!-- Mobile Menu -->
        <div id="mobile-menu" class="hidden md:hidden glass border-t border-white/10 absolute w-full left-0">
            <div class="p-4 space-y-2">
                <a href="painel.php" class="block px-4 py-3 rounded-lg hover:bg-white/5 text-slate-300">Dashboard</a>
                <a href="contratar.php" class="block px-4 py-3 rounded-lg bg-green-600/20 text-green-400 font-bold">Contratar PRO</a>
                <a href="criar_proposta_dinamica.php?nova=1" class="block px-4 py-3 rounded-lg bg-yellow-500/20 text-yellow-400 font-bold">Nova Proposta</a>
                <a href="meus_clientes.php" class="block px-4 py-3 rounded-lg hover:bg-white/5 text-slate-300">Clientes</a>
                <a href="logout.php" class="block px-4 py-3 rounded-lg hover:bg-red-500/10 text-red-400">Sair</a>
            </div>
        </div>
    </nav>

    <!-- Alert Demo / Urgency -->
    <?php if($diasRestantes <= 5): 
        $corBg = $urgente ? 'border-red-500/30 bg-red-500/10' : 'border-yellow-500/30 bg-yellow-500/10';
        $corTexto = $urgente ? 'text-red-200' : 'text-yellow-200';
        $icon = $urgente ? 'ph-warning' : 'ph-clock';
    ?>
    <div class="fixed bottom-4 right-4 z-[60] max-w-sm animate-pulse-glow">
        <div class="glass p-4 rounded-xl border <?= $corBg ?> flex items-start gap-3 shadow-2xl">
            <i class="ph <?= $icon ?> text-xl mt-0.5 <?= $urgente ? 'text-red-400' : 'text-yellow-400' ?>"></i>
            <div>
                <strong class="block text-sm text-white mb-1">
                    <?php if($urgente): ?>
                        Atenção! Seu teste acaba em breve.
                    <?php else: ?>
                        Faltam <?= $diasRestantes ?> dias de teste.
                    <?php endif; ?>
                </strong>
                <p class="text-xs <?= $corTexto ?> mb-3">Não perca seus dados e propostas criadas.</p>
                <a href="contratar.php" class="block text-center w-full px-3 py-1.5 bg-white/10 hover:bg-white/20 text-white text-xs font-bold rounded-lg transition-colors border border-white/20">
                    CONTRATAR AGORA
                </a>
            </div>
            <button onclick="this.parentElement.parentElement.remove()" class="text-slate-500 hover:text-white">
                <i class="ph ph-x"></i>
            </button>
        </div>
    </div>
    <?php endif; ?>


    <!-- Main Content -->
    <main class="relative z-10 pt-28 pb-12 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Welcome Section -->
        <div class="glass rounded-2xl p-8 mb-8 text-center border border-white/5 relative overflow-hidden group">
            <div class="absolute inset-0 bg-gradient-to-r from-yellow-500/5 to-blue-500/5 opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
            
            <div class="relative z-10">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-yellow-500/10 border border-yellow-500/20 text-yellow-400 text-xs font-bold mb-6 tracking-wide uppercase">
                    <i class="ph ph-test-tube text-lg"></i> Modo Demonstração
                </div>
                
                <h1 class="font-display text-4xl md:text-5xl font-bold text-white mb-4">
                    Olá, <span class="text-gradient"><?= htmlspecialchars($primeiro_nome) ?></span>!
                </h1>
                
                <p class="text-slate-400 text-lg max-w-2xl mx-auto mb-6">
                    Explore todo o potencial do SGT e veja como é fácil organizar suas propostas.
                </p>
                
                <div class="inline-flex items-center justify-center gap-4">
                    <span class="px-4 py-2 rounded-lg bg-surface/50 border border-white/5 text-sm text-yellow-400 font-bold flex items-center gap-2">
                        <i class="ph ph-hourglass-medium"></i>
                        <?= $diasRestantes ?> dias restantes
                    </span>
                    <a href="contratar.php" class="px-6 py-2 bg-gradient-to-r from-green-600 to-emerald-600 text-white font-bold rounded-lg shadow-lg hover:shadow-green-500/25 transition-all transform hover:-translate-y-0.5 flex items-center gap-2">
                        <i class="ph ph-rocket-launch"></i> Virar PRO
                    </a>
                </div>
            </div>
        </div>

        <!-- Stats Grid (Do Mês) -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-12">
            <!-- Total Propostas -->
            <div class="glass-card p-6 rounded-2xl relative overflow-hidden group">
                <div class="absolute top-0 right-0 p-4 opacity-50">
                    <i class="ph ph-file-text text-6xl text-slate-800 group-hover:text-slate-700 transition-colors"></i>
                </div>
                <div class="relative z-10">
                    <div class="w-12 h-12 rounded-xl bg-blue-500/10 flex items-center justify-center text-blue-400 mb-4 border border-blue-500/20">
                        <i class="ph ph-files text-2xl"></i>
                    </div>
                    <p class="text-slate-400 text-sm font-medium mb-1">Propostas Criadas</p>
                    <h3 class="text-3xl font-bold text-white"><?= $kpi['total'] ?? 0 ?></h3>
                </div>
            </div>

            <!-- Aprovadas -->
            <div class="glass-card p-6 rounded-2xl relative overflow-hidden group">
                <div class="absolute top-0 right-0 p-4 opacity-50">
                    <i class="ph ph-check-circle text-6xl text-slate-800 group-hover:text-slate-700 transition-colors"></i>
                </div>
                <div class="relative z-10">
                    <div class="w-12 h-12 rounded-xl bg-green-500/10 flex items-center justify-center text-green-400 mb-4 border border-green-500/20">
                        <i class="ph ph-thumbs-up text-2xl"></i>
                    </div>
                    <p class="text-slate-400 text-sm font-medium mb-1">Aprovadas</p>
                    <h3 class="text-3xl font-bold text-white"><?= $kpi['aprovadas'] ?? 0 ?></h3>
                </div>
            </div>
        </div>

        <!-- Quick Actions Grid -->
        <h2 class="text-xl font-bold text-white mb-6 flex items-center gap-2">
            <i class="ph ph-lightning text-yellow-400"></i> Acesso Rápido
        </h2>
        
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
            <!-- Criar Nova -->
            <a href="criar_proposta_dinamica.php?nova=1" class="glass-card p-6 rounded-2xl flex flex-col items-center text-center hover:bg-surface/80 group border-dashed border-2 border-white/10 hover:border-yellow-500/50">
                <div class="w-16 h-16 rounded-full bg-yellow-500/10 flex items-center justify-center text-yellow-400 mb-4 group-hover:scale-110 transition-transform">
                    <i class="ph ph-plus text-3xl"></i>
                </div>
                <h3 class="text-white font-bold mb-1">Nova Proposta</h3>
                <p class="text-xs text-slate-500">Testar criação</p>
            </a>

            <!-- Painel -->
            <a href="painel.php" class="glass-card p-6 rounded-2xl flex flex-col items-center text-center group">
                <div class="w-16 h-16 rounded-full bg-blue-500/10 flex items-center justify-center text-blue-400 mb-4 group-hover:scale-110 transition-transform">
                    <i class="ph ph-chart-line-up text-3xl"></i>
                </div>
                <h3 class="text-white font-bold mb-1">Dashboard</h3>
                <p class="text-xs text-slate-500">Ver estatísticas</p>
            </a>

            <!-- Clientes -->
            <a href="meus_clientes.php" class="glass-card p-6 rounded-2xl flex flex-col items-center text-center group">
                <div class="w-16 h-16 rounded-full bg-purple-500/10 flex items-center justify-center text-purple-400 mb-4 group-hover:scale-110 transition-transform">
                    <i class="ph ph-users text-3xl"></i>
                </div>
                <h3 class="text-white font-bold mb-1">Meus Clientes</h3>
                <p class="text-xs text-slate-500">Cadastrar clientes</p>
            </a>

            <!-- Configurações -->
            <a href="minha_empresa.php" class="glass-card p-6 rounded-2xl flex flex-col items-center text-center group">
                <div class="w-16 h-16 rounded-full bg-slate-500/10 flex items-center justify-center text-slate-400 mb-4 group-hover:scale-110 transition-transform">
                    <i class="ph ph-buildings text-3xl"></i>
                </div>
                <h3 class="text-white font-bold mb-1">Configurações</h3>
                <p class="text-xs text-slate-500">Dados da empresa</p>
            </a>
        </div>

        <!-- CTA Final -->
        <div class="glass p-8 rounded-3xl border border-green-500/20 relative overflow-hidden text-center">
            <div class="absolute inset-0 bg-green-500/5 z-0"></div>
            <div class="relative z-10">
                <h2 class="font-display text-2xl font-bold text-white mb-3">
                    Gostou da experiência?
                </h2>
                <p class="text-slate-400 mb-8 max-w-lg mx-auto">
                    Não perca suas propostas criadas. Migre para o plano PRO agora mesmo e drible a concorrência.
                </p>
                
                <div class="flex flex-col sm:flex-row justify-center gap-4">
                    <a href="contratar.php" class="px-8 py-3 bg-gradient-to-r from-green-600 to-emerald-600 text-white font-bold rounded-xl shadow-lg shadow-green-900/40 hover:shadow-green-500/30 transition-all transform hover:-translate-y-1 flex items-center justify-center gap-2">
                        <i class="ph ph-crown text-xl"></i> Assinar Plano PRO
                    </a>
                    <a href="https://api.whatsapp.com/send?phone=5531971875928" target="_blank" class="px-8 py-3 glass hover:bg-white/10 text-white font-bold rounded-xl transition-all flex items-center justify-center gap-2">
                        <i class="ph ph-whatsapp-logo text-xl text-green-400"></i> Falar com Consultor
                    </a>
                </div>
            </div>
        </div>

    </main>

    <!-- Footer -->
    <footer class="relative z-10 border-t border-white/5 mt-12 py-8">
        <div class="max-w-7xl mx-auto px-4 text-center">
            <p class="text-slate-500 text-sm mb-2">© <?= date('Y') ?> SGT-Propostas. Todos os direitos reservados.</p>
            <div class="flex items-center justify-center gap-2 text-xs text-yellow-600">
                <i class="ph ph-test-tube"></i> Ambiente de Demonstração
            </div>
        </div>
    </footer>

</body>
</html>
