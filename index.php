<?php
/**
 * index.php
 * Landing Page Premium - SGT (Dark Mode)
 * Integrada com Login e Segurança
 */

// Configura parâmetros da sessão antes de iniciar
if (session_status() === PHP_SESSION_NONE) {
    $cookieParams = session_get_cookie_params();
    session_set_cookie_params([
        'lifetime' => $cookieParams['lifetime'],
        'path' => '/',
        'domain' => $cookieParams['domain'],
        'secure' => isset($_SERVER['HTTPS']),
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
}
require_once 'config.php';
require_once 'db.php';

// Se já está logado, redireciona para a página correta
if (isset($_SESSION['usuario_id'])) {
    // Verifica se há um redirecionamento pendente
    if(isset($_SESSION['redirect_after_login'])) {
        $dest = $_SESSION['redirect_after_login'];
        unset($_SESSION['redirect_after_login']);
        header("Location: $dest");
    } else {
        // ROTEAMENTO UNIFICADO: TODOS VÃO PARA PAINEL.PHP
        header('Location: painel.php');
    }
    exit;
}

$erro_login = '';
$modal_aberto = false;

// Captura intenção de redirecionamento via GET
if (isset($_GET['redirect'])) {
    $_SESSION['redirect_after_login'] = $_GET['redirect'];
    $modal_aberto = true; // Abre o modal para facilitar
} elseif (isset($_GET['id'])) {
    $modal_aberto = true;
}

// Lógica de Login (Teste de Upload FTP - 21)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = filter_input(INPUT_POST, 'usuario', FILTER_SANITIZE_SPECIAL_CHARS);
    $senha   = $_POST['senha'];

    if (empty($usuario) || empty($senha)) {
        $erro_login = "Preencha usuário e senha.";
        $modal_aberto = true;
    } else {
        try {
            $conn = Database::getProd();
            
            // Busca por usuario (Email é armazenado no campo usuario)
            $stmt = $conn->prepare("SELECT id_usuario, usuario, senha, nome_completo, tipo_perfil, validade_acesso FROM Usuarios WHERE usuario = ? LIMIT 1");
            $stmt->bind_param('s', $usuario);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();

            if ($user) {
                $senha_valida = false;
                $precisa_migrar = false;

                if (password_verify($senha, $user['senha'])) {
                    $senha_valida = true;
                } elseif ($user['senha'] === $senha) {
                    $senha_valida = true;
                    $precisa_migrar = true;
                }

                if ($senha_valida) {
                        // VERIFICA VALIDADE
                        $hoje = new DateTime();
                        $val = new DateTime($user['validade_acesso'] ? $user['validade_acesso'] : '2000-01-01');
                        
                        if ($hoje > $val && $user['tipo_perfil'] !== 'admin') { 
                            $erro_login = "Sua assinatura venceu. Entre em contato.";
                            $modal_aberto = true;
                        } else {
                            if ($precisa_migrar) {
                                $novo_hash = password_hash($senha, PASSWORD_DEFAULT);
                                $upd = $conn->prepare("UPDATE Usuarios SET senha = ?, ultimo_acesso = NOW() WHERE id_usuario = ?");
                                $upd->bind_param('si', $novo_hash, $user['id_usuario']);
                                $upd->execute();
                            } else {
                                $upd = $conn->prepare("UPDATE Usuarios SET ultimo_acesso = NOW() WHERE id_usuario = ?");
                                $upd->bind_param('i', $user['id_usuario']);
                                $upd->execute();
                            }

                            // Regenera Sessão
                            session_regenerate_id(true);
                            $_SESSION['usuario_id']    = $user['id_usuario'];
                            $_SESSION['usuario_nome']  = $user['nome_completo'];
                            $_SESSION['perfil']        = $user['tipo_perfil'];
                            $_SESSION['ambiente']      = ($user['usuario'] == 'demo' || $user['tipo_perfil'] == 'demo') ? 'demo' : 'producao'; 
                            $_SESSION['origem_login']  = ($user['tipo_perfil'] === 'admin') ? 'admin' : 'cliente';
                            
                            session_write_close();

                            // REDIRECIONAMENTO INTELIGENTE
                            if(isset($_SESSION['redirect_after_login'])) {
                                $dest = $_SESSION['redirect_after_login'];
                                unset($_SESSION['redirect_after_login']);
                                header("Location: $dest");
                            } else {
                                header("Location: painel.php");
                            }
                            exit;
                        }

                } else {
                    $erro_login = "Senha incorreta.";
                    $modal_aberto = true;
                }
            } else {
                $erro_login = "Usuário não encontrado.";
                $modal_aberto = true;
            }
        } catch (Exception $e) { 
            $erro_login = "Erro técnico no sistema."; 
            $modal_aberto = true;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SGT-Propostas | Sistema de Gestão SaaS</title>
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyMDAgNjAiIHdpZHRoPSIyMDAiIGhlaWdodD0iNjAiPjxkZWZzPjxsaW5lYXJHcmFkaWVudCBpZD0iZ3JhZEljb24iIHgxPSIwJSIgeTE9IjAlIiB4Mj0iMTAwJSIgeTI9IjEwMCUiPjxzdG9wIG9mZnNldD0iMCUiIHN0eWxlPSJzdG9wLWNvbG9yOiNmOTczMTY7c3RvcC1vcGFjaXR5OjEiIC8+PHN0b3Agb2Zmc2V0PSIxMDAlIiBzdHlsZT0ic3RvcC1jb2xvcjojZWE1ODBjO3N0b3Atb3BhY2l0eToxIiAvPjwvbGluZWFyR3JhZGllbnQ+PC9kZWZzPjxyZWN0IHg9IjUiIHk9IjUiIHdpZHRoPSI1MCIgaGVpZ2h0PSI1MCIgcng9IjEwIiBmaWxsPSJ1cmwoI2dyYWRJY29uKSIvPjxwYXRoIGQ9Ik0yOCAxNSBMMjIgMjggTDMwIDI4IEwyNiA0NSBMMzggMzAgTDMwIDMwIEwzNCAxNSBaIiBmaWxsPSJ3aGl0ZSIvPjx0ZXh0IHg9IjY1IiB5PSIzNSIgZm9udC1mYW1pbHk9IkFyaWFsLCBzYW5zLXNlcmlmIiBmb250LXdlaWdodD0iYm9sZCIgZm9udC1zaXplPSIyNCIgZmlsbD0id2hpdGUiPlNHVDwvdGV4dD48dGV4dCB4PSI2NSIgeT0iNTAiIGZvbnQtZmFtaWx5PSJBcmlhbCwgc2Fucy1zZXJpZiIgZm9udC1zaXplPSIxMiIgZmlsbD0iI2ZiOTIzYyI+UHJvcG9zdGFzPC90ZXh0Pjwvc3ZnPg==">




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
                    },
                    animation: {
                        'float': 'float 3s ease-in-out infinite',
                        'pulse-glow': 'pulse-glow 2s ease-in-out infinite',
                        'gradient': 'gradient-shift 8s ease infinite',
                    },
                    keyframes: {
                        float: {
                            '0%, 100%': { transform: 'translateY(0px)' },
                            '50%': { transform: 'translateY(-10px)' },
                        },
                        'pulse-glow': {
                            '0%, 100%': { boxShadow: '0 0 20px rgba(249, 115, 22, 0.4)' },
                            '50%': { boxShadow: '0 0 40px rgba(249, 115, 22, 0.6)' },
                        },
                        'gradient-shift': {
                            '0%, 100%': { backgroundPosition: '0% 50%' },
                            '50%': { backgroundPosition: '100% 50%' },
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

        /* Text Gradients */
        .text-gradient {
            background: linear-gradient(135deg, #f97316 0%, #fb923c 50%, #fbbf24 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .text-gradient-blue {
            background: linear-gradient(135deg, #3b82f6 0%, #60a5fa 50%, #93c5fd 100%);
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

        /* Button Shine Effect */
        .btn-shine {
            position: relative;
            overflow: hidden;
        }
        .btn-shine::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }
        .btn-shine:hover::before {
            left: 100%;
        }

        /* Card Hover */
        .card-hover {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .card-hover:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
        }

        /* Scrollbar */
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #0a0f1a; }
        ::-webkit-scrollbar-thumb { background: #334155; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #475569; }
    </style>
</head>
<body class="antialiased overflow-x-hidden">

    <!-- Navbar -->
    <header id="navbar" class="fixed top-0 left-0 right-0 z-50 transition-all duration-300 bg-transparent">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-20">
                <!-- Logo -->
                <a href="#inicio" class="flex items-center gap-2 group transform hover:scale-105 transition-transform duration-200">
                    <?php include 'components/logo_svg.php'; ?>

                </a>

                <!-- Desktop Nav -->
                <nav class="hidden md:flex items-center gap-1">
                    <a href="#inicio" class="px-4 py-2 text-sm font-medium text-slate-300 hover:text-white rounded-lg hover:bg-white/5 transition-colors">Início</a>
                    <a href="#recursos" class="px-4 py-2 text-sm font-medium text-slate-300 hover:text-white rounded-lg hover:bg-white/5 transition-colors">Recursos</a>
                    <a href="#dashboard" class="px-4 py-2 text-sm font-medium text-slate-300 hover:text-white rounded-lg hover:bg-white/5 transition-colors">Dashboard</a>
                    <a href="#planos" class="px-4 py-2 text-sm font-medium text-slate-300 hover:text-white rounded-lg hover:bg-white/5 transition-colors">Planos</a>
                </nav>

                <!-- CTA -->
                <div class="hidden md:flex items-center gap-3">
                    <button onclick="toggleLoginModal()" class="px-4 py-2 text-sm font-medium text-slate-300 hover:text-white hover:bg-white/5 rounded-lg transition-colors">
                        Login
                    </button>
                    <a href="#planos" class="btn-shine px-4 py-2 bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white text-sm font-bold rounded-lg shadow-lg shadow-orange-500/25 transition-all">
                        Começar Agora
                    </a>
                </div>

                <!-- Mobile Menu Button -->
                <button onclick="toggleMobileMenu()" class="md:hidden p-2 rounded-lg text-slate-300 hover:text-white hover:bg-white/5">
                    <i class="ph ph-list text-2xl"></i>
                </button>
            </div>
        </div>

        <!-- Mobile Menu -->
        <div id="mobile-menu" class="hidden md:hidden glass border-t border-white/10 absolute w-full">
            <div class="px-4 py-4 space-y-2">
                <a href="#inicio" onclick="toggleMobileMenu()" class="block px-4 py-3 text-slate-300 hover:text-white hover:bg-white/5 rounded-lg">Início</a>
                <a href="#recursos" onclick="toggleMobileMenu()" class="block px-4 py-3 text-slate-300 hover:text-white hover:bg-white/5 rounded-lg">Recursos</a>
                <a href="#dashboard" onclick="toggleMobileMenu()" class="block px-4 py-3 text-slate-300 hover:text-white hover:bg-white/5 rounded-lg">Dashboard</a>
                <a href="#planos" onclick="toggleMobileMenu()" class="block px-4 py-3 text-slate-300 hover:text-white hover:bg-white/5 rounded-lg">Planos</a>
                <div class="pt-4 space-y-2 border-t border-white/10">
                    <button onclick="toggleLoginModal(); toggleMobileMenu()" class="w-full px-4 py-2 border border-white/20 text-slate-300 hover:text-white hover:bg-white/5 rounded-lg">
                        Login
                    </button>
                    <a href="#planos" onclick="toggleMobileMenu()" class="block text-center w-full px-4 py-2 bg-gradient-to-r from-orange-500 to-orange-600 text-white font-bold rounded-lg">
                        Começar Agora
                    </a>
                </div>
            </div>
        </div>
    </header>

    <main class="relative z-10">
        
        <!-- HERO SECTION -->
        <section id="inicio" class="relative min-h-screen flex items-center pt-28 pb-16 overflow-hidden">
             <!-- Background Effects -->
            <div class="absolute inset-0 grid-bg opacity-30 pointer-events-none"></div>
            <div class="absolute top-0 left-1/4 w-96 h-96 bg-blue-600/10 rounded-full blur-[100px] pointer-events-none"></div>
            <div class="absolute bottom-1/4 right-1/4 w-96 h-96 bg-orange-500/10 rounded-full blur-[100px] pointer-events-none"></div>

            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 w-full">
                <div class="grid lg:grid-cols-2 gap-12 lg:gap-16 items-center">
                    
                    <!-- Left Content -->
                    <div class="text-center lg:text-left">
                        <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-orange-500/10 text-orange-400 border border-orange-500/20 mb-8 hover:bg-orange-500/20 transition-colors">
                            <i class="ph ph-sparkle text-lg"></i>
                            <span class="text-sm font-medium">Sistema SaaS de Gestão</span>
                        </div>

                        <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold leading-tight mb-6 font-display">
                            <span class="text-white">SGT-</span>
                            <span class="text-gradient">Propostas</span>
                        </h1>

                        <p class="text-xl sm:text-2xl text-slate-300 font-medium mb-4">
                            Gestão de Prosperidade
                        </p>

                        <p class="text-slate-400 text-base sm:text-lg mb-8 max-w-xl mx-auto lg:mx-0 leading-relaxed">
                            Sistema integrado ao MySQL, seguro e acessível. Transforme leads em contratos fechados com nossa solução SaaS completa.
                        </p>

                        <div class="flex flex-col sm:flex-row gap-8 justify-center lg:justify-start mb-10">
                            <!-- Já Sou Cliente -->
                            <div class="space-y-3">
                                <p class="text-xs text-slate-500 uppercase tracking-wider font-bold">Já sou cliente</p>
                                <div class="flex flex-col sm:flex-row gap-3">
                                    <button onclick="toggleLoginModal()" class="btn-shine inline-flex items-center justify-center px-6 py-3 rounded-lg bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white shadow-lg shadow-blue-500/25 font-semibold transition-all hover:-translate-y-1">
                                        <i class="ph ph-crown text-lg mr-2"></i>
                                        Login Cliente PRO
                                    </button>
                                    <a href="login_demo.php" class="inline-flex items-center justify-center px-6 py-3 rounded-lg border border-white/20 text-slate-300 hover:text-white hover:bg-white/5 transition-all hover:-translate-y-1">
                                        <i class="ph ph-arrow-right text-lg mr-2"></i>
                                        Login Demo
                                    </a>
                                </div>
                            </div>

                            <!-- Quero Conhecer -->
                            <div class="space-y-3">
                                <p class="text-xs text-slate-500 uppercase tracking-wider font-bold">Quero conhecer</p>
                                <div class="flex flex-col sm:flex-row gap-3">
                                    <a href="criar_conta_demo.php" class="btn-shine inline-flex items-center justify-center px-6 py-3 rounded-lg bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white shadow-lg shadow-orange-500/25 font-semibold transition-all hover:-translate-y-1">
                                        <i class="ph ph-rocket-launch text-lg mr-2"></i>
                                        Criar Conta Demo
                                    </a>
                                    <a href="#planos" class="inline-flex items-center justify-center px-6 py-3 rounded-lg border border-green-500/30 text-green-400 hover:text-green-300 hover:bg-green-500/10 transition-all hover:-translate-y-1">
                                        <i class="ph ph-shopping-cart text-lg mr-2"></i>
                                        Plano PRO
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Badges -->
                        <div class="flex flex-wrap gap-3 justify-center lg:justify-start">
                            <div class="flex items-center gap-2 px-4 py-2 rounded-full bg-white/5 border border-white/10">
                                <i class="ph ph-database text-orange-400"></i>
                                <span class="text-sm text-slate-300">MySQL</span>
                            </div>
                            <div class="flex items-center gap-2 px-4 py-2 rounded-full bg-white/5 border border-white/10">
                                <i class="ph ph-shield-check text-orange-400"></i>
                                <span class="text-sm text-slate-300">Seguro</span>
                            </div>
                            <div class="flex items-center gap-2 px-4 py-2 rounded-full bg-white/5 border border-white/10">
                                <i class="ph ph-device-mobile text-orange-400"></i>
                                <span class="text-sm text-slate-300">Responsivo</span>
                            </div>
                        </div>
                    </div>

                    <!-- Right Content (Mockup 1 - Pie Chart) -->
                    <div class="relative mt-12 lg:mt-0 animate-float">
                        <div class="absolute -inset-4 bg-gradient-to-r from-orange-500/20 to-blue-500/20 rounded-3xl blur-2xl"></div>
                        
                        <div class="relative bg-surface rounded-2xl border border-white/10 shadow-2xl overflow-hidden">
                            <!-- Fake Browser Header -->
                            <div class="flex items-center gap-2 px-4 py-3 bg-[#0f172a] border-b border-white/5">
                                <div class="flex gap-1.5">
                                    <div class="w-3 h-3 rounded-full bg-red-500/80"></div>
                                    <div class="w-3 h-3 rounded-full bg-yellow-500/80"></div>
                                    <div class="w-3 h-3 rounded-full bg-green-500/80"></div>
                                </div>
                                <div class="flex-1 text-center">
                                    <span class="text-xs text-slate-500 font-mono">sgt-propostas.app/dashboard</span>
                                </div>
                            </div>
                            
                            <!-- Content -->
                            <div class="p-6">
                                <!-- Top Stats -->
                                <div class="grid grid-cols-3 gap-4 mb-6">
                                    <div class="p-4 rounded-xl bg-white/5 border border-white/10">
                                        <p class="text-xs text-slate-500 mb-1">Custo Total</p>
                                        <p class="text-sm font-bold text-blue-400">R$ 300.000,00</p>
                                    </div>
                                    <div class="p-4 rounded-xl bg-white/5 border border-white/10">
                                        <p class="text-xs text-slate-500 mb-1">Margem</p>
                                        <p class="text-sm font-bold text-green-400">20%</p>
                                    </div>
                                    <div class="p-4 rounded-xl bg-white/5 border border-white/10">
                                        <p class="text-xs text-slate-500 mb-1">Valor Proposta</p>
                                        <p class="text-sm font-bold text-orange-400">R$ 375.000,00</p>
                                    </div>
                                </div>

                                <!-- Chart Area -->
                                <div class="grid grid-cols-2 gap-4">
                                    <div class="p-4 rounded-xl bg-white/5 border border-white/10 flex flex-col items-center justify-center">
                                        <!-- Simple CSS Pie Chart representation -->
                                        <div class="relative w-32 h-32 rounded-full border-8 border-slate-700 flex items-center justify-center">
                                            <div class="absolute inset-0 rounded-full border-8 border-blue-500 border-t-transparent border-l-transparent transform rotate-45"></div>
                                            <div class="text-center">
                                                <p class="text-[10px] text-slate-500">Total</p>
                                                <p class="text-sm font-bold text-white">R$ 375k</p>
                                            </div>
                                        </div>
                                        <div class="mt-4 w-full space-y-2">
                                            <div class="flex items-center justify-between text-xs">
                                                <span class="flex items-center gap-2 text-slate-400"><span class="w-2 h-2 rounded-full bg-blue-500"></span>Mão de Obra</span>
                                                <span class="text-slate-300">35%</span>
                                            </div>
                                            <div class="flex items-center justify-between text-xs">
                                                <span class="flex items-center gap-2 text-slate-400"><span class="w-2 h-2 rounded-full bg-orange-500"></span>Materiais</span>
                                                <span class="text-slate-300">28%</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="p-4 rounded-xl bg-white/5 border border-white/10">
                                        <p class="text-xs font-medium text-white mb-3">Custos Recentes</p>
                                        <div class="space-y-2">
                                            <div class="flex justify-between py-1 border-b border-white/5">
                                                <span class="text-xs text-slate-400">Mão de Obra</span>
                                                <span class="text-xs text-slate-300">R$ 105k</span>
                                            </div>
                                            <div class="flex justify-between py-1 border-b border-white/5">
                                                <span class="text-xs text-slate-400">Materiais</span>
                                                <span class="text-xs text-slate-300">R$ 84k</span>
                                            </div>
                                            <div class="flex justify-between py-1 border-b border-white/5">
                                                <span class="text-xs text-slate-400">Equip.</span>
                                                <span class="text-xs text-slate-300">R$ 66k</span>
                                            </div>
                                            <div class="mt-2 pt-1 flex justify-between">
                                                <span class="text-xs font-bold text-white">Total</span>
                                                <span class="text-xs font-bold text-orange-400">R$ 375k</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </section>

        <!-- RECURSOS (FEATURES) -->
        <section id="recursos" class="relative py-24 overflow-hidden bg-[#0d121f]">
            <div class="absolute inset-0 bg-gradient-to-b from-transparent via-blue-500/5 to-transparent pointer-events-none"></div>

            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
                <div class="text-center mb-16">
                    <span class="inline-block px-4 py-2 rounded-full bg-orange-500/10 text-orange-400 text-sm font-medium mb-4 border border-orange-500/20">Recursos</span>
                    <h2 class="text-3xl sm:text-4xl lg:text-5xl font-bold text-white mb-4 font-display">
                        Tudo que você precisa para <span class="text-gradient">gerenciar propostas</span>
                    </h2>
                    <p class="text-slate-400 text-lg max-w-2xl mx-auto">
                        Ferramentas completas e intuitivas para transformar sua gestão comercial e aumentar suas conversões.
                    </p>
                </div>

                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <!-- Feature 1 -->
                    <div class="group relative p-6 rounded-2xl bg-blue-500/10 border border-blue-500/20 card-hover">
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center mb-4 shadow-lg group-hover:scale-110 transition-transform duration-300">
                            <i class="ph ph-monitor text-white text-xl"></i>
                        </div>
                        <h3 class="text-xl font-bold text-white mb-3 group-hover:text-blue-400 transition-colors">Multi-Plataforma</h3>
                        <p class="text-slate-400 text-sm leading-relaxed">Acesse suas propostas via PC, Notebook, Tablet ou Celular. Layout 100% responsivo.</p>
                    </div>

                    <!-- Feature 2 -->
                    <div class="group relative p-6 rounded-2xl bg-orange-500/10 border border-orange-500/20 card-hover">
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-orange-500 to-orange-600 flex items-center justify-center mb-4 shadow-lg group-hover:scale-110 transition-transform duration-300">
                            <i class="ph ph-trend-up text-white text-xl"></i>
                        </div>
                        <h3 class="text-xl font-bold text-white mb-3 group-hover:text-orange-400 transition-colors">Prosperidade em Vendas</h3>
                        <p class="text-slate-400 text-sm leading-relaxed">Dashboards intuitivos que mostram onde está o dinheiro e como otimizar resultados.</p>
                    </div>

                    <!-- Feature 3 -->
                    <div class="group relative p-6 rounded-2xl bg-green-500/10 border border-green-500/20 card-hover">
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-green-500 to-green-600 flex items-center justify-center mb-4 shadow-lg group-hover:scale-110 transition-transform duration-300">
                            <i class="ph ph-cloud-check text-white text-xl"></i>
                        </div>
                        <h3 class="text-xl font-bold text-white mb-3 group-hover:text-green-400 transition-colors">Cloud SaaS Seguro</h3>
                        <p class="text-slate-400 text-sm leading-relaxed">Seus dados MySQL blindados na nuvem. Backups automáticos e acesso rápido.</p>
                    </div>

                    <!-- Feature 4 -->
                    <div class="group relative p-6 rounded-2xl bg-purple-500/10 border border-purple-500/20 card-hover">
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-purple-500 to-purple-600 flex items-center justify-center mb-4 shadow-lg group-hover:scale-110 transition-transform duration-300">
                            <i class="ph ph-lock-key text-white text-xl"></i>
                        </div>
                        <h3 class="text-xl font-bold text-white mb-3 group-hover:text-purple-400 transition-colors">Segurança Total</h3>
                        <p class="text-slate-400 text-sm leading-relaxed">Criptografia de ponta a ponta, autenticação em duas etapas e conformidade LGPD.</p>
                    </div>
                    
                    <!-- Feature 5 -->
                    <div class="group relative p-6 rounded-2xl bg-yellow-500/10 border border-yellow-500/20 card-hover">
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-yellow-500 to-yellow-600 flex items-center justify-center mb-4 shadow-lg group-hover:scale-110 transition-transform duration-300">
                            <i class="ph ph-lightning text-white text-xl"></i>
                        </div>
                        <h3 class="text-xl font-bold text-white mb-3 group-hover:text-yellow-400 transition-colors">Alta Performance</h3>
                        <p class="text-slate-400 text-sm leading-relaxed">Sistema otimizado para carregamento rápido. Interface fluida sem espera.</p>
                    </div>

                    <!-- Feature 6 -->
                    <div class="group relative p-6 rounded-2xl bg-pink-500/10 border border-pink-500/20 card-hover">
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-pink-500 to-pink-600 flex items-center justify-center mb-4 shadow-lg group-hover:scale-110 transition-transform duration-300">
                            <i class="ph ph-headset text-white text-xl"></i>
                        </div>
                        <h3 class="text-xl font-bold text-white mb-3 group-hover:text-pink-400 transition-colors">Suporte Dedicado</h3>
                        <p class="text-slate-400 text-sm leading-relaxed">Equipe especializada pronta para ajudar. Atendimento rápido e eficiente.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- DASHBOARD PREVIEW START -->
        <section id="dashboard" class="relative py-24 overflow-hidden">
             <div class="absolute inset-0 bg-gradient-to-b from-transparent via-orange-500/5 to-transparent pointer-events-none"></div>

             <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
                <div class="text-center mb-16">
                    <span class="inline-block px-4 py-2 rounded-full bg-blue-500/10 text-blue-400 text-sm font-medium mb-4 border border-blue-500/20">Dashboard</span>
                    <h2 class="text-3xl sm:text-4xl lg:text-5xl font-bold text-white mb-4 font-display">
                        Visualize seu negócio em <span class="text-gradient-blue">tempo real</span>
                    </h2>
                    <p class="text-slate-400 text-lg max-w-2xl mx-auto">
                        Dashboards intuitivos que mostram exatamente onde está o dinheiro e como otimizar seus resultados.
                    </p>
                </div>

                <div class="relative">
                    <!-- Glow -->
                    <div class="absolute -inset-4 bg-gradient-to-r from-blue-500/10 to-orange-500/10 rounded-3xl blur-2xl pointer-events-none"></div>

                    <!-- Container -->
                    <div class="relative bg-surface rounded-2xl border border-white/10 shadow-2xl overflow-hidden">
                        <!-- Header -->
                        <div class="flex items-center justify-between px-6 py-4 bg-[#0f172a] border-b border-white/5">
                            <div class="flex items-center gap-4">
                                <div class="flex gap-1.5">
                                    <div class="w-3 h-3 rounded-full bg-red-500"></div>
                                    <div class="w-3 h-3 rounded-full bg-yellow-500"></div>
                                    <div class="w-3 h-3 rounded-full bg-green-500"></div>
                                </div>
                                <span class="text-sm text-slate-500 font-mono hidden sm:inline">sgt-propostas.app/dashboard</span>
                            </div>
                            <div class="flex items-center gap-4">
                                <div class="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-white/5">
                                    <i class="ph ph-calendar-blank text-slate-400"></i>
                                    <span class="text-sm text-slate-300">Últimos 30 dias</span>
                                </div>
                            </div>
                        </div>

                        <!-- Content -->
                        <div class="p-6">
                            <!-- Stats Grid -->
                            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                                <div class="p-4 rounded-xl bg-white/5 border border-white/10 hover:border-white/20 transition-colors">
                                    <div class="flex items-center justify-between mb-2">
                                        <i class="ph ph-file-text text-slate-400 text-xl"></i>
                                        <span class="text-xs text-green-400 flex items-center gap-1">+12% <i class="ph ph-trend-up"></i></span>
                                    </div>
                                    <p class="text-2xl font-bold text-white mb-1">1.234</p>
                                    <p class="text-xs text-slate-500">Propostas Enviadas</p>
                                </div>
                                <div class="p-4 rounded-xl bg-white/5 border border-white/10 hover:border-white/20 transition-colors">
                                    <div class="flex items-center justify-between mb-2">
                                        <i class="ph ph-chart-line-up text-slate-400 text-xl"></i>
                                        <span class="text-xs text-green-400 flex items-center gap-1">+5% <i class="ph ph-trend-up"></i></span>
                                    </div>
                                    <p class="text-2xl font-bold text-white mb-1">68%</p>
                                    <p class="text-xs text-slate-500">Conversão</p>
                                </div>
                                <div class="p-4 rounded-xl bg-white/5 border border-white/10 hover:border-white/20 transition-colors">
                                    <div class="flex items-center justify-between mb-2">
                                        <i class="ph ph-users text-slate-400 text-xl"></i>
                                        <span class="text-xs text-green-400 flex items-center gap-1">+23% <i class="ph ph-trend-up"></i></span>
                                    </div>
                                    <p class="text-2xl font-bold text-white mb-1">456</p>
                                    <p class="text-xs text-slate-500">Clientes</p>
                                </div>
                                <div class="p-4 rounded-xl bg-white/5 border border-white/10 hover:border-white/20 transition-colors">
                                    <div class="flex items-center justify-between mb-2">
                                        <i class="ph ph-currency-dollar text-slate-400 text-xl"></i>
                                        <span class="text-xs text-green-400 flex items-center gap-1">+18% <i class="ph ph-trend-up"></i></span>
                                    </div>
                                    <p class="text-2xl font-bold text-white mb-1">R$ 2.5M</p>
                                    <p class="text-xs text-slate-500">Faturamento</p>
                                </div>
                            </div>

                            <!-- List -->
                             <div class="grid lg:grid-cols-3 gap-6">
                                <div class="lg:col-span-2 p-5 rounded-xl bg-white/5 border border-white/10 flex items-center justify-center">
                                    <div class="text-center py-10">
                                        <i class="ph ph-chart-bar text-6xl text-slate-600 mb-4"></i>
                                        <p class="text-slate-400">Gráfico de Desempenho Visual</p>
                                    </div>
                                </div>
                                <div class="p-5 rounded-xl bg-white/5 border border-white/10">
                                    <h4 class="text-sm font-medium text-white mb-4">Recentes</h4>
                                    <div class="space-y-3">
                                        <div class="flex items-center justify-between p-3 rounded-lg bg-white/5">
                                            <div>
                                                <p class="text-sm text-white">Construtora ABC</p>
                                                <p class="text-xs text-slate-500">Hoje</p>
                                            </div>
                                            <div class="text-right">
                                                <p class="text-sm text-orange-400">R$ 150k</p>
                                                <span class="text-xs text-green-400">Aprovada</span>
                                            </div>
                                        </div>
                                        <div class="flex items-center justify-between p-3 rounded-lg bg-white/5">
                                            <div>
                                                <p class="text-sm text-white">Engenharia XYZ</p>
                                                <p class="text-xs text-slate-500">Ontem</p>
                                            </div>
                                            <div class="text-right">
                                                <p class="text-sm text-orange-400">R$ 89.5k</p>
                                                <span class="text-xs text-yellow-400">Pendente</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                             </div>

                        </div>
                    </div>
                </div>
             </div>
        </section>

        <!-- PLANOS -->
        <section id="planos" class="relative py-24 overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-b from-transparent via-blue-500/5 to-transparent pointer-events-none"></div>

            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
                <div class="text-center mb-16">
                    <span class="inline-block px-4 py-2 rounded-full bg-green-500/10 text-green-400 text-sm font-medium mb-4 border border-green-500/20">Planos</span>
                    <h2 class="text-3xl sm:text-4xl lg:text-5xl font-bold text-white mb-4 font-display">
                        Escolha o plano <span class="text-gradient">Ideal</span>
                    </h2>
                    <p class="text-slate-400 text-lg max-w-2xl mx-auto">
                        Desbloqueie o potencial máximo do seu negócio.
                    </p>
                </div>

                <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <!-- Mensal -->
                    <div class="relative rounded-2xl p-6 bg-[#111827] border border-white/10 hover:border-white/20 transition-all hover:-translate-y-2">
                        <div class="mb-6">
                            <h3 class="text-xl font-bold text-white mb-1">Mensal</h3>
                            <p class="text-sm text-slate-500">Sem fidelidade</p>
                        </div>
                        <div class="mb-6">
                            <div class="text-sm text-slate-500 line-through decoration-white mb-1">
                                R$ 33,00
                            </div>
                            <div class="flex items-baseline gap-1">
                                <span class="text-lg text-slate-400">R$</span>
                                <span class="text-4xl font-bold text-white">30,00</span>
                                <span class="text-slate-500">/mês</span>
                            </div>
                        </div>
                        <ul class="space-y-3 mb-8">
                            <li class="flex items-center gap-2 text-sm text-slate-400"><i class="ph ph-check text-blue-400"></i> Acesso Completo</li>
                            <li class="flex items-center gap-2 text-sm text-slate-400"><i class="ph ph-check text-blue-400"></i> Multi-Plataforma</li>
                            <li class="flex items-center gap-2 text-sm text-slate-400"><i class="ph ph-check text-blue-400"></i> Suporte Básico</li>
                        </ul>
                        <a href="https://mpago.la/2JrbxWt" target="_blank" class="block w-full py-3 text-center rounded-lg bg-gradient-to-r from-blue-600 to-blue-700 text-white font-bold hover:shadow-lg transition-all btn-shine">
                            Pagar com PIX
                        </a>
                    </div>

                    <!-- Trimestral -->
                    <div class="relative rounded-2xl p-6 bg-[#111827] border border-white/10 hover:border-white/20 transition-all hover:-translate-y-2">
                         <div class="absolute top-4 right-4"><span class="bg-purple-500/20 text-purple-400 text-xs font-bold px-2 py-1 rounded-full">5% OFF</span></div>
                        <div class="mb-6">
                            <h3 class="text-xl font-bold text-white mb-1">Trimestral</h3>
                            <p class="text-sm text-slate-500">Cobrado cada 3 meses</p>
                        </div>
                        <div class="mb-6">
                            <div class="text-sm text-slate-500 line-through decoration-white mb-1">
                                R$ 31,35
                            </div>
                            <div class="flex items-baseline gap-1">
                                <span class="text-lg text-slate-400">R$</span>
                                <span class="text-4xl font-bold text-white">28,50</span>
                                <span class="text-slate-500">/mês</span>
                            </div>
                        </div>
                        <ul class="space-y-3 mb-8">
                            <li class="flex items-center gap-2 text-sm text-slate-400"><i class="ph ph-check text-purple-400"></i> Tudo do Mensal</li>
                            <li class="flex items-center gap-2 text-sm text-slate-400"><i class="ph ph-check text-purple-400"></i> Desconto de 5%</li>
                            <li class="flex items-center gap-2 text-sm text-slate-400"><i class="ph ph-check text-purple-400"></i> Renovação Manual</li>
                        </ul>
                        <a href="https://www.mercadopago.com.br/subscriptions/checkout?preapproval_plan_id=6b8610a74e9e4f66aed94c9bd7a957af" target="_blank" class="block w-full py-3 text-center rounded-lg bg-gradient-to-r from-purple-600 to-purple-700 text-white font-bold hover:shadow-lg transition-all btn-shine">
                            Assinar Trimestral
                        </a>
                    </div>

                    <!-- Semestral -->
                    <div class="relative rounded-2xl p-6 bg-[#111827] border border-white/10 hover:border-white/20 transition-all hover:-translate-y-2">
                        <div class="absolute top-4 right-4"><span class="bg-green-500/20 text-green-400 text-xs font-bold px-2 py-1 rounded-full">10% OFF</span></div>
                        <div class="mb-6">
                            <h3 class="text-xl font-bold text-white mb-1">Semestral</h3>
                            <p class="text-sm text-slate-500">Cobrado cada 6 meses</p>
                        </div>
                        <div class="mb-6">
                            <div class="text-sm text-slate-500 line-through decoration-white mb-1">
                                R$ 29,70
                            </div>
                            <div class="flex items-baseline gap-1">
                                <span class="text-lg text-slate-400">R$</span>
                                <span class="text-4xl font-bold text-white">27,00</span>
                                <span class="text-slate-500">/mês</span>
                            </div>
                        </div>
                        <ul class="space-y-3 mb-8">
                            <li class="flex items-center gap-2 text-sm text-slate-400"><i class="ph ph-check text-green-400"></i> Tudo do Mensal</li>
                            <li class="flex items-center gap-2 text-sm text-slate-400"><i class="ph ph-check text-green-400"></i> Desconto de 10%</li>
                            <li class="flex items-center gap-2 text-sm text-slate-400"><i class="ph ph-check text-green-400"></i> Backup Diário</li>
                        </ul>
                        <a href="https://mpago.la/2MjigKn" target="_blank" class="block w-full py-3 text-center rounded-lg bg-gradient-to-r from-green-600 to-green-700 text-white font-bold hover:shadow-lg transition-all btn-shine">
                            Assinar Semestral
                        </a>
                    </div>

                    <!-- Anual (Popular) -->
                     <div class="relative rounded-2xl p-6 bg-gradient-to-b from-orange-500/20 to-orange-600/10 border-2 border-orange-500/50 scale-105 transition-all hover:-translate-y-2">
                        <div class="absolute -top-4 left-1/2 -translate-x-1/2"><span class="bg-gradient-to-r from-orange-500 to-orange-600 text-white text-xs font-bold px-4 py-1 rounded-full shadow-lg"><i class="ph ph-sparkle inline mr-1"></i>MELHOR ESCOLHA</span></div>
                         <div class="absolute top-4 right-4"><span class="bg-orange-500/20 text-orange-400 text-xs font-bold px-2 py-1 rounded-full">20% OFF</span></div>
                        <div class="mb-6 mt-2">
                            <h3 class="text-xl font-bold text-white mb-1">Anual</h3>
                            <p class="text-sm text-slate-400">Cobrado anualmente</p>
                        </div>
                        <div class="mb-6">
                            <div class="text-sm text-slate-500 line-through decoration-white mb-1">
                                R$ 26,40
                            </div>
                            <div class="flex items-baseline gap-1">
                                <span class="text-lg text-slate-400">R$</span>
                                <span class="text-4xl font-bold text-white">24,00</span>
                                <span class="text-slate-500">/mês</span>
                            </div>
                        </div>
                        <ul class="space-y-3 mb-8">
                            <li class="flex items-center gap-2 text-sm text-slate-300"><i class="ph ph-check text-orange-400"></i> 20% de Desconto</li>
                            <li class="flex items-center gap-2 text-sm text-slate-300"><i class="ph ph-check text-orange-400"></i> Acesso Vitalício aos Dados</li>
                            <li class="flex items-center gap-2 text-sm text-slate-300"><i class="ph ph-check text-orange-400"></i> Suporte VIP 24/7</li>
                        </ul>
                        <a href="https://mpago.la/1CuvPFA" target="_blank" class="block w-full py-3 text-center rounded-lg bg-gradient-to-r from-orange-500 to-orange-600 text-white font-bold hover:shadow-lg transition-all btn-shine shadow-orange-500/25">
                            Assinar Agora
                        </a>
                    </div>
                </div>

                <div class="mt-12 text-center">
                    <p class="text-slate-500 text-sm mb-4"><i class="ph ph-lock-key text-green-400 mr-1"></i> Pagamento 100% seguro via Mercado Pago. Ativação imediata.</p>
                     <a href="https://api.whatsapp.com/send?phone=5531971875928&text=Falar%20com%20um%20Consultor!" target="_blank" class="inline-flex items-center gap-2 px-4 py-2 border border-green-500/30 rounded-lg text-green-400 hover:text-green-300 hover:bg-green-500/10 transition-colors">
                        <i class="ph ph-whatsapp-logo text-lg"></i>
                        Falar com Consultor
                    </a>
                </div>
            </div>
        </section>

    </main>

    <!-- FOOTER -->
    <footer class="relative bg-[#0f172a] border-t border-white/10 pt-12 pb-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-8 mb-12">
                <div class="col-span-2 lg:col-span-2">
                    <a href="#inicio" class="flex items-center gap-2 mb-4">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-orange-500 to-orange-600 flex items-center justify-center shadow-lg">
                            <i class="ph ph-lightning text-white text-xl"></i>
                        </div>
                        <div class="flex flex-col">
                            <span class="text-lg font-bold text-white leading-tight">SGT</span>
                            <span class="text-xs text-orange-400 leading-tight">Propostas</span>
                        </div>
                    </a>
                    <p class="text-slate-400 text-sm mb-6 max-w-sm">Sistema integrado ao MySQL, seguro e acessível. Transforme leads em contratos fechados.</p>
                </div>
                <!-- Links (Simplified) -->
                <div>
                     <h4 class="text-sm font-semibold text-white mb-4">Produto</h4>
                     <ul class="space-y-2 text-sm text-slate-400">
                         <li><a href="#recursos" class="hover:text-orange-400">Recursos</a></li>
                         <li><a href="#dashboard" class="hover:text-orange-400">Dashboard</a></li>
                         <li><a href="#planos" class="hover:text-orange-400">Planos</a></li>
                     </ul>
                </div>
                 <div>
                     <h4 class="text-sm font-semibold text-white mb-4">Empresa</h4>
                     <ul class="space-y-2 text-sm text-slate-400">
                         <li><a href="https://elmtopografia.com.br" target="_blank" class="hover:text-orange-400">Sobre</a></li>
                         <li><a href="http://www.elmtopografia.com.br/Portfolio.html" target="_blank" class="hover:text-orange-400">Portfólio</a></li>
                     </ul>
                </div>
                 <div>
                     <h4 class="text-sm font-semibold text-white mb-4">Suporte</h4>
                     <ul class="space-y-2 text-sm text-slate-400">
                         <li><a href="#" class="hover:text-orange-400">Ajuda</a></li>
                         <li><a href="https://api.whatsapp.com/send?phone=5531971875928" target="_blank" class="hover:text-orange-400">WhatsApp</a></li>
                     </ul>
                </div>
                 <div>
                     <h4 class="text-sm font-semibold text-white mb-4">Legal</h4>
                     <ul class="space-y-2 text-sm text-slate-400">
                         <li><a href="termos_uso.php" class="hover:text-orange-400">Termos de Uso</a></li>
                         <li><a href="politica_privacidade.php" class="hover:text-orange-400">Privacidade</a></li>
                     </ul>
                </div>
            </div>
            <div class="border-t border-white/10 pt-6 flex flex-col sm:flex-row justify-between items-center gap-4">
                <p class="text-sm text-slate-500">© 2024 SGT-Propostas. Todos os direitos reservados.</p>
                <div class="flex gap-2">
                     <span class="px-2 py-1 rounded bg-blue-500/10 text-blue-400 text-xs">PHP</span>
                     <span class="px-2 py-1 rounded bg-orange-500/10 text-orange-400 text-xs">MySQL</span>
                </div>
            </div>
        </div>
    </footer>


    <!-- Login Modal (Preserved Logic) -->
    <div id="login-modal" class="fixed inset-0 z-[60] <?php echo $modal_aberto ? '' : 'hidden'; ?>">
        <div class="absolute inset-0 bg-black/80 backdrop-blur-sm" onclick="toggleLoginModal()"></div>
        <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-full max-w-md p-4">
            <div class="glass bg-surface rounded-2xl p-8 shadow-2xl relative overflow-hidden ring-1 ring-white/10">
                
                <button onclick="toggleLoginModal()" class="absolute top-4 right-4 text-slate-400 hover:text-white transition-colors">
                    <i class="ph ph-x text-xl"></i>
                </button>

                <div class="text-center mb-8">
                    <div class="inline-flex p-3 rounded-full bg-orange-500/10 mb-3 border border-orange-500/20">
                        <i class="ph ph-user-circle text-3xl text-orange-400"></i>
                    </div>
                    <h2 class="text-2xl font-bold text-white font-display">Bem-vindo</h2>
                    <p class="text-slate-400 text-sm">Acesse o painel SGT-Propostas</p>
                </div>

                <?php if (!empty($erro_login)): ?>
                    <div class="mb-4 p-3 rounded bg-red-500/20 border border-red-500/50 text-red-200 text-sm text-center">
                        <?php echo $erro_login; ?>
                    </div>
                <?php endif; ?>

                <form class="space-y-4" method="POST" action="index.php">
                    <div>
                        <label class="block text-xs font-medium text-slate-400 mb-1 ml-1 uppercase">Usuário / E-mail</label>
                        <div class="relative">
                            <i class="ph ph-envelope absolute left-3 top-3 text-slate-500"></i>
                            <input type="text" name="usuario" required autocomplete="username"
                                class="w-full bg-black/50 border border-white/10 rounded-lg py-2.5 pl-10 text-white focus:outline-none focus:border-orange-500 focus:ring-1 focus:ring-orange-500 transition-all placeholder-slate-600"
                                placeholder="Seu usuário">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-400 mb-1 ml-1 uppercase">Senha</label>
                        <div class="relative">
                            <i class="ph ph-lock-key absolute left-3 top-3 text-slate-500"></i>
                            <input type="password" name="senha" required autocomplete="current-password"
                                class="w-full bg-black/50 border border-white/10 rounded-lg py-2.5 pl-10 text-white focus:outline-none focus:border-orange-500 focus:ring-1 focus:ring-orange-500 transition-all placeholder-slate-600"
                                placeholder="••••••••">
                        </div>
                    </div>

                    <button type="submit"
                        class="w-full py-3 bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white font-bold rounded-lg shadow-lg hover:shadow-orange-500/25 transition-all btn-shine">
                        ENTRAR
                    </button>
                    
                    <div class="text-center mt-4">
                        <a href="esqueci_senha.php" class="text-xs text-orange-400 hover:text-orange-300">Esqueceu a senha?</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script>
        // Modal Logic
        function toggleLoginModal() {
            const modal = document.getElementById('login-modal');
            modal.classList.toggle('hidden');
        }

        // Mobile Menu Logic
        function toggleMobileMenu() {
            const menu = document.getElementById('mobile-menu');
            menu.classList.toggle('hidden');
        }

        // Navbar Scroll Effect
        window.addEventListener('scroll', () => {
            const navbar = document.getElementById('navbar');
            if (window.scrollY > 20) {
                navbar.classList.add('glass', 'border-b', 'border-white/10');
                navbar.classList.remove('bg-transparent');
            } else {
                navbar.classList.remove('glass', 'border-b', 'border-white/10');
                navbar.classList.add('bg-transparent');
            }
        });
    </script>
</body>
</html>
