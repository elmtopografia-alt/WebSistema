<?php
/**
 * index.php
 * Landing Page Premium - SGT (Dark Mode)
 * Integrada com Login e Segurança
 */

session_start();
require_once 'config.php';
require_once 'db.php';

// Se já está logado, redireciona para o painel
if (isset($_SESSION['usuario_id'])) {
    header('Location: painel.php');
    exit;
}

$erro_login = '';
$modal_aberto = false;

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
                    // VERIFICAÇÃO ADMINISTRATIVA (Admin liberado)
                        // VERIFICA VALIDADE
                        $hoje = new DateTime();
                        $val = new DateTime($user['validade_acesso'] ? $user['validade_acesso'] : '2000-01-01');
                        
                        if ($hoje > $val && $user['tipo_perfil'] !== 'admin') { // Admin não expira por data
                            $erro_login = "Sua assinatura venceu. Entre em contato.";
                            $modal_aberto = true;
                        } else {
                            if ($precisa_migrar) {
                                $novo_hash = password_hash($senha, PASSWORD_DEFAULT);
                                $upd = $conn->prepare("UPDATE Usuarios SET senha = ? WHERE id_usuario = ?");
                                $upd->bind_param('si', $novo_hash, $user['id_usuario']);
                                $upd->execute();
                            }

                            session_regenerate_id(true);
                            $_SESSION['usuario_id']    = $user['id_usuario'];
                            $_SESSION['usuario_nome']  = $user['nome_completo'];
                            $_SESSION['perfil']        = $user['tipo_perfil'];
                            $_SESSION['ambiente']      = 'producao'; 
                            $_SESSION['origem_login']  = 'cliente';
                            
                            header("Location: painel.php");
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
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SGT-Propostas | Sistema de Gestão SaaS</title>

    <!-- Tailwind CSS (Framework Moderno) -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Fontes Google (Exo 2 para ar tecnológico e Inter para leitura) -->
    <link
        href="https://fonts.googleapis.com/css2?family=Exo+2:wght@400;600;700&family=Inter:wght@300;400;500;600&display=swap"
        rel="stylesheet">

    <!-- Ícones (Phosphor Icons) -->
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
                            // Azul Profissional (Mantido)
                            dark: '#001e3c',
                            primary: '#0a2e5c',
                            surface: '#132f4c',

                            // NOVAS CORES: ABÓBORA (Pumpkin)
                            accent: '#FF7518',   // Abóbora Vibrante (Principal)
                            action: '#EA580C',   // Laranja Escuro (Botões/Hover)
                            glow: '#4fc3f7',     // Azul Celeste (Mantido para contraste)
                        }
                    },
                    animation: {
                        'float': 'float 6s ease-in-out infinite',
                        'pulse-slow': 'pulse 4s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                    },
                    keyframes: {
                        float: {
                            '0%, 100%': { transform: 'translateY(0)' },
                            '50%': { transform: 'translateY(-20px)' },
                        }
                    }
                }
            }
        }
    </script>

    <style>
        /* Efeitos de Vidro (Glassmorphism) - Otimizado para Projeção */
        .glass-panel {
            background: rgba(10, 46, 92, 0.65);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
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
            border-color: rgba(255, 117, 24, 0.6);
            transform: translateY(-5px);
            box-shadow: 0 12px 35px -10px rgba(255, 117, 24, 0.3);
        }

        /* Canvas de fundo - Gradiente Azul Profissional */
        #antigravity-canvas {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            background: radial-gradient(circle at center, #0a2e5c 0%, #001224 100%);
        }

        /* Scrollbar personalizada */
        ::-webkit-scrollbar {
            width: 10px;
        }

        ::-webkit-scrollbar-track {
            background: #001224;
        }

        ::-webkit-scrollbar-thumb {
            background: #1e40af;
            border-radius: 5px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #FF7518;
        }

        /* Destaque SGT-Propostas */
        .brand-highlight {
            font-size: 1.15em;
            font-weight: 800;
            letter-spacing: 0.5px;
            text-shadow: 0 0 20px rgba(255, 117, 24, 0.4);
        }
    </style>
</head>

<body
    class="text-slate-200 font-sans antialiased overflow-x-hidden selection:bg-brand-accent selection:text-brand-dark text-base md:text-lg">

    <!-- Fundo Animado (Antigravity) -->
    <canvas id="antigravity-canvas"></canvas>

    <!-- Navbar -->
    <header class="fixed w-full z-50 top-0 transition-all duration-300" id="navbar">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div
                class="flex justify-between items-center h-20 glass-panel mt-4 rounded-2xl px-6 shadow-2xl shadow-black/30">

                <!-- Logo Ajustado -->
                <div class="flex items-center gap-3 cursor-pointer select-none" onclick="window.scrollTo(0,0)">
                    <img src="<?= BASE_URL ?>/assets/img/logo_sgt.png" alt="SGT Propostas" class="h-12">
                </div>

                <!-- Menu Desktop - Centralizado e Uniforme -->
                <nav class="hidden md:flex flex-1 justify-center gap-10 text-sm font-medium">
                    <a href="#inicio" class="text-white hover:text-brand-accent transition-colors relative group">
                        Início
                        <span
                            class="absolute -bottom-1 left-0 w-0 h-0.5 bg-brand-accent transition-all group-hover:w-full"></span>
                    </a>
                    <a href="#funcionalidades" class="hover:text-brand-accent transition-colors relative group">
                        Recursos
                        <span
                            class="absolute -bottom-1 left-0 w-0 h-0.5 bg-brand-accent transition-all group-hover:w-full"></span>
                    </a>
                    <a href="#video" onclick="scrollToVideoAndPlay()" class="hover:text-brand-accent transition-colors relative group">
                        Apresentação
                        <span
                            class="absolute -bottom-1 left-0 w-0 h-0.5 bg-brand-accent transition-all group-hover:w-full"></span>
                    </a>
                    <a href="#planos" class="hover:text-brand-accent transition-colors relative group">
                        Planos
                        <span
                            class="absolute -bottom-1 left-0 w-0 h-0.5 bg-brand-accent transition-all group-hover:w-full"></span>
                    </a>
                </nav>

                <!-- Botão Login (CTA) -->
                <div class="hidden md:block">
                    <!-- Sombra ajustada para laranja -->
                    <button onclick="toggleLoginModal()"
                        class="group relative px-6 py-2 bg-transparent overflow-hidden rounded-full border border-brand-accent text-brand-accent font-semibold hover:bg-brand-accent hover:text-white transition-all duration-300 shadow-[0_0_15px_rgba(255,117,24,0.2)]">
                        <span class="relative flex items-center gap-2">
                            <i class="ph ph-sign-in"></i> Acesso Cliente
                        </span>
                    </button>
                </div>

                <!-- Menu Mobile Button -->
                <button class="md:hidden text-2xl text-white hover:text-brand-accent transition-colors p-2"
                    onclick="toggleMobileMenu()">
                    <i class="ph ph-list"></i>
                </button>
            </div>
        </div>

        <!-- Menu Mobile Dropdown -->
        <div id="mobile-menu"
            class="hidden absolute top-24 left-4 right-4 glass-panel rounded-xl p-4 flex-col gap-4 text-center md:hidden bg-brand-dark/95 backdrop-blur-xl border-brand-primary/50 z-50">
            <a href="#inicio" class="block py-3 hover:bg-white/5 rounded-lg transition-colors border-b border-white/5"
                onclick="toggleMobileMenu()">Início</a>
            <a href="#funcionalidades"
                class="block py-3 hover:bg-white/5 rounded-lg transition-colors border-b border-white/5"
                onclick="toggleMobileMenu()">Recursos</a>
            <a href="#video" class="block py-3 hover:bg-white/5 rounded-lg transition-colors border-b border-white/5"
                onclick="toggleMobileMenu(); scrollToVideoAndPlay()">Apresentação</a>
            <a href="#planos" class="block py-3 hover:bg-white/5 rounded-lg transition-colors"
                onclick="toggleMobileMenu()">Planos</a>
            <button onclick="toggleLoginModal(); toggleMobileMenu()"
                class="w-full py-3 bg-brand-accent text-white font-bold rounded-lg mt-2 shadow-lg shadow-brand-accent/20">Acessar
                Sistema</button>
        </div>
    </header>

    <!-- Main Content -->
    <main>
        <!-- Hero Section -->
        <section id="inicio" class="min-h-screen flex items-center pt-32 pb-20 relative">

            <!-- Glow Effect de fundo - Azul e Abóbora -->
            <div class="absolute top-1/4 left-1/4 w-96 h-96 bg-blue-600/20 rounded-full blur-[128px]"></div>
            <div class="absolute bottom-1/4 right-1/4 w-96 h-96 bg-brand-accent/10 rounded-full blur-[128px]"></div>

            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid lg:grid-cols-2 gap-12 items-center relative z-10">

                <!-- Texto Hero -->
                <div class="space-y-10 animate-float text-center lg:text-left">
                    <div
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-full border border-blue-400/40 bg-blue-900/30 text-blue-200 text-sm font-semibold tracking-wide uppercase">
                        <span class="w-2.5 h-2.5 rounded-full bg-brand-glow animate-pulse"></span>
                        Sistema SaaS 4.0
                    </div>

                    <h1 class="font-display text-5xl sm:text-6xl lg:text-8xl font-bold text-white leading-tight">
                        <span class="brand-highlight text-transparent bg-clip-text bg-gradient-to-r from-brand-accent via-orange-400 to-orange-300">SGT-Propostas</span>
                        <br>
                        <span class="text-4xl sm:text-5xl lg:text-6xl">Gestão de Prosperidade</span>
                    </h1>

                    <p class="text-lg sm:text-xl lg:text-2xl text-slate-200 max-w-2xl mx-auto lg:mx-0 leading-relaxed">
                        A solução definitiva para transformar leads em contratos fechados. Integrado ao MySQL, seguro e
                        acessível em qualquer dispositivo.
                    </p>

                    <div class="flex flex-col sm:flex-row gap-5 justify-center lg:justify-start">
                        <!-- Botão CTA principal - Abóbora -->
                        <button onclick="scrollToVideoAndPlay()"
                            class="px-10 py-5 text-lg bg-gradient-to-r from-brand-accent to-brand-action text-white font-bold rounded-xl shadow-lg shadow-orange-600/40 hover:shadow-orange-500/60 transform hover:scale-105 transition-all flex items-center justify-center gap-3 border border-orange-400/30">
                            <i class="ph ph-play-circle text-2xl"></i>
                            Ver Demonstração
                        </button>
                        <button onclick="window.location.href='https://api.whatsapp.com/send?phone=5531971875928&text=Falar%20com%20um%20Consultor!'"
                            class="px-10 py-5 text-lg glass-panel text-white font-semibold rounded-xl hover:bg-white/15 transition-all flex items-center justify-center gap-3 border border-white/15">
                            <i class="ph ph-whatsapp-logo text-2xl text-green-400"></i>
                            Falar com Consultor
                        </button>
                    </div>

                    <!-- Indicadores de Tecnologia -->
                    <div
                        class="pt-10 border-t border-white/10 flex flex-wrap justify-center lg:justify-start gap-6 sm:gap-8 text-slate-300 grayscale opacity-70 hover:grayscale-0 hover:opacity-100 transition-all duration-500 text-base sm:text-lg">
                        <div class="flex items-center gap-2"><i class="ph ph-database text-brand-glow text-xl"></i> MySQL</div>
                        <div class="flex items-center gap-2"><i class="ph ph-code text-brand-accent text-xl"></i> HTML5/PHP
                        </div>
                        <div class="flex items-center gap-2"><i class="ph ph-shield-check text-green-400 text-xl"></i> SSL
                            Seguro</div>
                    </div>
                </div>

                <!-- Área do Vídeo / Preview -->
                <div class="relative group perspective-1000 mt-8 lg:mt-0" id="video">
                    <!-- Moldura decorativa -->
                    <div
                        class="absolute -inset-1 bg-gradient-to-r from-brand-primary to-brand-accent rounded-2xl blur opacity-30 group-hover:opacity-60 transition duration-1000 group-hover:duration-200">
                    </div>

                    <div
                        class="relative glass-panel rounded-2xl p-2 shadow-2xl transform transition-transform duration-500 group-hover:rotate-1 bg-brand-dark/50">
                        <!-- Header do "Browser" Fake -->
                        <div class="bg-brand-dark/80 rounded-t-xl p-3 flex items-center gap-2 border-b border-white/5">
                            <div class="flex gap-1.5">
                                <div class="w-3 h-3 rounded-full bg-red-500/80"></div>
                                <div class="w-3 h-3 rounded-full bg-yellow-500/80"></div>
                                <div class="w-3 h-3 rounded-full bg-green-500/80"></div>
                            </div>
                            <div
                                class="bg-black/30 flex-1 ml-4 rounded-md h-6 flex items-center px-3 text-xs text-blue-200/50 font-mono truncate">
                                sgt-propostas.app/dashboard
                            </div>
                        </div>

                        <!-- Container do Video / Carousel Inline -->
                        <div class="aspect-video bg-black/60 rounded-b-xl overflow-hidden relative group">
                            
                            <!-- Carousel Track (Slides) -->
                            <div id="carousel-track" class="absolute inset-0 flex transition-transform duration-700 ease-in-out">
                                <img src="assets/img/slider/carousel_dashboard_20260111202511.png" class="w-full h-full object-cover flex-shrink-0" alt="Dashboard - Visão Geral">
                                <img src="assets/img/slider/carousel_financeiro_20260111202511.png" class="w-full h-full object-cover flex-shrink-0" alt="Relatório Financeiro">
                                <img src="assets/img/slider/carousel_proposta_20260111202511.png" class="w-full h-full object-cover flex-shrink-0" alt="Formulário de Proposta">
                                <img src="assets/img/slider/carousel_detalhado_20260111202511.png" class="w-full h-full object-cover flex-shrink-0" alt="Dashboard Detalhado">
                            </div>


                            <!-- Controls (Hidden by default, shown when active) -->
                            <div id="carousel-controls" class="opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                                <button onclick="prevSlide()" class="absolute z-20 left-4 top-1/2 -translate-y-1/2 bg-black/40 hover:bg-brand-accent/90 text-white p-3 rounded-full backdrop-blur-sm transition-all">
                                    <i class="ph ph-caret-left text-2xl"></i>
                                </button>
                                <button onclick="nextSlide()" class="absolute z-20 right-4 top-1/2 -translate-y-1/2 bg-black/40 hover:bg-brand-accent/90 text-white p-3 rounded-full backdrop-blur-sm transition-all">
                                    <i class="ph ph-caret-right text-2xl"></i>
                                </button>
                                <!-- Indicators -->
                                <div class="absolute z-20 bottom-4 left-1/2 -translate-x-1/2 flex gap-2">
                                    <button onclick="goToSlide(0)" class="w-3 h-3 rounded-full bg-white/50 hover:bg-white transition-colors indicator active"></button>
                                    <button onclick="goToSlide(1)" class="w-3 h-3 rounded-full bg-white/50 hover:bg-white transition-colors indicator"></button>
                                    <button onclick="goToSlide(2)" class="w-3 h-3 rounded-full bg-white/50 hover:bg-white transition-colors indicator"></button>
                                    <button onclick="goToSlide(3)" class="w-3 h-3 rounded-full bg-white/50 hover:bg-white transition-colors indicator"></button>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </section>
        
        <!-- FIM CAROUSEL -->

        <!-- Features Cards -->
        <section id="funcionalidades" class="py-24 relative z-10">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid md:grid-cols-3 gap-10">
                    <!-- Card 1 -->
                    <div class="glass-card p-10 rounded-2xl transition-all duration-300">
                        <div
                            class="w-16 h-16 bg-brand-glow/10 rounded-xl flex items-center justify-center mb-6 text-brand-glow border border-brand-glow/20">
                            <i class="ph ph-device-mobile-camera text-4xl"></i>
                        </div>
                        <h3 class="text-2xl font-bold text-white mb-4">Multi-Plataforma</h3>
                        <p class="text-slate-200 text-base leading-relaxed">
                            Acesse suas propostas via PC, Notebook, Tablet ou Celular. Layout 100% responsivo que
                            acompanha você.
                        </p>
                    </div>

                    <!-- Card 2 -->
                    <div
                        class="glass-card p-10 rounded-2xl transition-all duration-300 border-brand-accent/20 bg-brand-accent/5 relative overflow-hidden">
                        <!-- Glow extra no card principal -->
                        <div class="absolute -right-10 -top-10 w-20 h-20 bg-brand-accent/20 blur-2xl rounded-full">
                        </div>

                        <div
                            class="w-16 h-16 bg-brand-accent/10 rounded-xl flex items-center justify-center mb-6 text-brand-accent border border-brand-accent/20">
                            <i class="ph ph-chart-line-up text-4xl"></i>
                        </div>
                        <h3 class="text-2xl font-bold text-white mb-4">Prosperidade em Vendas</h3>
                        <p class="text-slate-200 text-base leading-relaxed">
                            Ferramentas focadas em conversão. Dashboards intuitivos que mostram onde está o dinheiro.
                        </p>
                    </div>

                    <!-- Card 3 -->
                    <div class="glass-card p-10 rounded-2xl transition-all duration-300">
                        <div
                            class="w-16 h-16 bg-blue-500/10 rounded-xl flex items-center justify-center mb-6 text-blue-400 border border-blue-500/20">
                            <i class="ph ph-cloud-check text-4xl"></i>
                        </div>
                        <h3 class="text-2xl font-bold text-white mb-4">Cloud SaaS Seguro</h3>
                        <p class="text-slate-200 text-base leading-relaxed">
                            Seus dados MySQL blindados na nuvem. Backups automáticos e acesso rápido de qualquer lugar.
                        </p>
                    </div>
                </div>
            </div>
        </section>


        <!-- Plans Section -->
        <section id="planos" class="py-20 relative z-10 scroll-mt-32">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <div class="text-center mb-16">
                    <h2 class="font-display text-3xl md:text-4xl font-bold text-white mb-4">
                        Escolha o Plano <span class="text-brand-accent">Ideal</span>
                    </h2>
                    <p class="text-slate-400 max-w-2xl mx-auto">
                        Desbloqueie o potencial máximo do seu negócio com nossos planos flexíveis.
                    </p>
                </div>

                <div class="grid md:grid-cols-2 xl:grid-cols-4 gap-6">
                    
                    <!-- Plano Mensal -->
                    <div class="glass-card p-6 rounded-2xl flex flex-col relative group hover:border-brand-accent/50 transition-all duration-300">
                        <div class="mb-4">
                            <h3 class="text-lg font-bold text-slate-300 uppercase tracking-wider">Mensal</h3>
                            <div class="flex items-baseline gap-1 mt-2">
                                <span class="text-sm text-slate-400">R$</span>
                                <span class="text-4xl font-bold text-white">30,00</span>
                                <span class="text-sm text-slate-400">/mês</span>
                            </div>
                            <p class="text-xs text-slate-500 mt-1">Sem fidelidade</p>
                        </div>

                        <ul class="space-y-3 mb-8 flex-1">
                            <li class="flex items-center gap-2 text-sm text-slate-300">
                                <i class="ph ph-check-circle text-brand-accent"></i> Acesso Completo
                            </li>
                            <li class="flex items-center gap-2 text-sm text-slate-300">
                                <i class="ph ph-check-circle text-brand-accent"></i> Multi-Plataforma
                            </li>
                            <li class="flex items-center gap-2 text-sm text-slate-300">
                                <i class="ph ph-check-circle text-brand-accent"></i> Suporte Básico
                            </li>
                        </ul>

                        <div class="space-y-3">
                            <a href="https://mpago.la/2JrbxWt" target="_blank" 
                               class="w-full py-2.5 bg-green-600 hover:bg-green-500 text-white font-bold rounded-lg transition-colors flex items-center justify-center gap-2 shadow-lg shadow-green-900/20">
                                <i class="ph ph-qr-code"></i> Pagar com PIX
                            </a>
                            
                            <div class="flex items-center gap-2 text-xs text-slate-500 justify-center">
                                <span class="w-full h-px bg-white/10"></span>
                                OU
                                <span class="w-full h-px bg-white/10"></span>
                            </div>

                            <a href="https://www.mercadopago.com.br/subscriptions/checkout?preapproval_plan_id=6b8610a74e9e4f66aed94c9bd7a957af" target="_blank"
                               class="w-full py-2.5 border border-brand-primary hover:bg-brand-primary/30 text-blue-200 font-semibold rounded-lg transition-colors flex items-center justify-center gap-2">
                                <i class="ph ph-credit-card"></i> Assinatura (Cartão)
                            </a>
                        </div>
                    </div>

                    <!-- Plano Trimestral -->
                    <div class="glass-card p-6 rounded-2xl flex flex-col relative group hover:border-brand-accent/50 transition-all duration-300">
                        <div class="absolute -top-3 right-4 bg-blue-600 text-white text-[10px] font-bold px-2 py-1 rounded-full shadow-lg">
                            5% OFF
                        </div>
                        <div class="mb-4">
                            <h3 class="text-lg font-bold text-blue-400 uppercase tracking-wider">Trimestral</h3>
                            <div class="flex items-baseline gap-1 mt-2">
                                <span class="text-sm text-slate-400">R$</span>
                                <span class="text-4xl font-bold text-white">28,50</span>
                                <span class="text-sm text-slate-400">/mês</span>
                            </div>
                            <p class="text-xs text-slate-500 mt-1">Cobrado R$ 85,50 a cada 3 meses</p>
                        </div>

                        <ul class="space-y-3 mb-8 flex-1">
                            <li class="flex items-center gap-2 text-sm text-slate-300">
                                <i class="ph ph-check-circle text-blue-400"></i> Tudo do Mensal
                            </li>
                            <li class="flex items-center gap-2 text-sm text-slate-300">
                                <i class="ph ph-check-circle text-blue-400"></i> Desconto de 5%
                            </li>
                            <li class="flex items-center gap-2 text-sm text-slate-300">
                                <i class="ph ph-check-circle text-blue-400"></i> Renovação Manual
                            </li>
                        </ul>

                        <a href="https://mpago.la/2BV5xy6" target="_blank"
                           class="w-full py-3 bg-blue-600 hover:bg-blue-500 text-white font-bold rounded-lg transition-colors shadow-lg shadow-blue-900/20 mt-auto text-center">
                            Assinar Trimestral
                        </a>
                    </div>

                    <!-- Plano Semestral -->
                    <div class="glass-card p-6 rounded-2xl flex flex-col relative group hover:border-brand-accent/50 transition-all duration-300">
                        <div class="absolute -top-3 right-4 bg-blue-500 text-white text-[10px] font-bold px-2 py-1 rounded-full shadow-lg">
                            10% OFF
                        </div>
                        <div class="mb-4">
                            <h3 class="text-lg font-bold text-blue-300 uppercase tracking-wider">Semestral</h3>
                            <div class="flex items-baseline gap-1 mt-2">
                                <span class="text-sm text-slate-400">R$</span>
                                <span class="text-4xl font-bold text-white">27,00</span>
                                <span class="text-sm text-slate-400">/mês</span>
                            </div>
                            <p class="text-xs text-slate-500 mt-1">Cobrado R$ 162,00 a cada 6 meses</p>
                        </div>

                        <ul class="space-y-3 mb-8 flex-1">
                            <li class="flex items-center gap-2 text-sm text-slate-300">
                                <i class="ph ph-check-circle text-blue-400"></i> Tudo do Mensal
                            </li>
                            <li class="flex items-center gap-2 text-sm text-slate-300">
                                <i class="ph ph-check-circle text-blue-400"></i> Desconto de 10%
                            </li>
                            <li class="flex items-center gap-2 text-sm text-slate-300">
                                <i class="ph ph-check-circle text-blue-400"></i> Prioridade no Suporte
                            </li>
                        </ul>

                        <a href="https://mpago.la/2MjigKn" target="_blank"
                           class="w-full py-3 bg-blue-600 hover:bg-blue-500 text-white font-bold rounded-lg transition-colors shadow-lg shadow-blue-900/20 mt-auto text-center">
                            Assinar Semestral
                        </a>
                    </div>

                    <!-- Plano Anual -->
                    <div class="glass-card p-6 rounded-2xl flex flex-col relative border border-brand-accent/30 bg-brand-accent/5 transform hover:-translate-y-2 transition-all duration-300 shadow-xl shadow-brand-accent/10">
                        <div class="absolute -top-4 left-1/2 transform -translate-x-1/2 bg-gradient-to-r from-brand-accent to-orange-500 text-white text-xs font-bold px-4 py-1.5 rounded-full shadow-lg shadow-orange-500/30">
                            MELHOR ESCOLHA
                        </div>
                        <div class="mb-4 pt-2">
                            <h3 class="text-lg font-bold text-brand-accent uppercase tracking-wider">Anual</h3>
                            <div class="flex items-baseline gap-1 mt-2">
                                <span class="text-sm text-slate-400">R$</span>
                                <span class="text-5xl font-bold text-white">24,00</span>
                                <span class="text-sm text-slate-400">/mês</span>
                            </div>
                            <p class="text-xs text-slate-400 mt-1">Cobrado R$ 288,00 anualmente</p>
                        </div>

                        <ul class="space-y-3 mb-8 flex-1">
                            <li class="flex items-center gap-2 text-sm text-slate-200">
                                <i class="ph ph-check-circle text-brand-accent"></i> <strong>20% de Desconto</strong>
                            </li>
                            <li class="flex items-center gap-2 text-sm text-slate-300">
                                <i class="ph ph-check-circle text-brand-accent"></i> Acesso Vitalício aos Dados
                            </li>
                            <li class="flex items-center gap-2 text-sm text-slate-300">
                                <i class="ph ph-check-circle text-brand-accent"></i> Suporte VIP 24/7
                            </li>
                            <li class="flex items-center gap-2 text-sm text-slate-300">
                                <i class="ph ph-check-circle text-brand-accent"></i> Backup Diário
                            </li>
                        </ul>

                        <a href="https://mpago.la/1CuvPFA" target="_blank"
                           class="w-full py-4 bg-gradient-to-r from-brand-accent to-brand-action hover:to-orange-500 text-white font-bold rounded-xl transition-all shadow-lg shadow-orange-500/30 mt-auto flex items-center justify-center gap-2">
                            <i class="ph ph-star-fill text-yellow-300"></i> Assinar Agora
                        </a>
                    </div>

                </div>

                <div class="mt-12 text-center">
                    <p class="text-slate-400 text-sm mb-4">
                        <i class="ph ph-lock-key text-green-400"></i> Pagamento 100% seguro via Mercado Pago. Ativação imediata.
                    </p>
                    <a href="https://api.whatsapp.com/send?phone=5531971875928&text=Falar%20com%20um%20Consultor!" target="_blank" 
                       class="inline-flex items-center gap-2 text-brand-accent hover:text-white transition-colors text-sm font-semibold">
                        <i class="ph ph-whatsapp-logo text-lg"></i>
                        Falar com Consultor
                    </a>
                </div>

            </div>
        </section>

    <footer class="border-t border-white/5 bg-black/40 backdrop-blur-sm py-8 mt-12">
        <div class="max-w-7xl mx-auto px-4 text-center">
            <p class="text-blue-200/60 text-sm">© 2024 SGT-Propostas. Todos os direitos reservados. Tecnologia PHP +
                MySQL.</p>
        </div>
    </footer>

    <!-- Login Modal (Hidden by default) -->
    <div id="login-modal" class="fixed inset-0 z-[60] <?php echo $modal_aberto ? '' : 'hidden'; ?>">
        <!-- Backdrop Blur -->
        <div class="absolute inset-0 bg-brand-dark/80 backdrop-blur-sm" onclick="toggleLoginModal()"></div>

        <!-- Modal Content -->
        <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-full max-w-md p-4">
            <div
                class="glass-panel bg-brand-surface rounded-2xl p-8 border border-brand-primary shadow-2xl relative overflow-hidden">

                <!-- Decorativo Abóbora -->
                <div
                    class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-transparent via-brand-accent to-transparent">
                </div>

                <button onclick="toggleLoginModal()" class="absolute top-4 right-4 text-slate-400 hover:text-white">
                    <i class="ph ph-x text-xl"></i>
                </button>

                <div class="text-center mb-8">
                    <div class="inline-block p-3 rounded-full bg-brand-dark/50 mb-3 border border-brand-accent/20">
                        <i class="ph ph-user-circle text-4xl text-brand-accent"></i>
                    </div>
                    <h2 class="text-2xl font-bold text-white font-display">Bem-vindo de volta</h2>
                    <p class="text-blue-200/70 text-sm">Acesse o painel SGT-Propostas</p>
                </div>

                <?php if (!empty($erro_login)): ?>
                    <div class="mb-4 p-3 rounded bg-red-500/20 border border-red-500/50 text-red-200 text-sm text-center">
                        <?php echo $erro_login; ?>
                    </div>
                <?php endif; ?>

                <form class="space-y-4" method="POST" action="index.php">
                    <div>
                        <label class="block text-xs font-medium text-blue-200/70 mb-1 ml-1">USUÁRIO / E-MAIL</label>
                        <div class="relative">
                            <i class="ph ph-envelope absolute left-3 top-3 text-slate-400"></i>
                            <input type="text" name="usuario" required
                                class="w-full bg-black/20 border border-brand-primary/50 rounded-lg py-2.5 pl-10 text-white focus:outline-none focus:border-brand-accent focus:ring-1 focus:ring-brand-accent transition-all placeholder-slate-500"
                                placeholder="Seu usuário de acesso">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-blue-200/70 mb-1 ml-1">SENHA</label>
                        <div class="relative">
                            <i class="ph ph-lock-key absolute left-3 top-3 text-slate-400"></i>
                            <input type="password" name="senha" required
                                class="w-full bg-black/20 border border-brand-primary/50 rounded-lg py-2.5 pl-10 text-white focus:outline-none focus:border-brand-accent focus:ring-1 focus:ring-brand-accent transition-all placeholder-slate-500"
                                placeholder="••••••••">
                        </div>
                    </div>

                    <button type="submit"
                        class="w-full py-3 bg-gradient-to-r from-brand-action to-orange-600 text-white font-bold rounded-lg shadow-lg hover:shadow-brand-accent/20 transform hover:-translate-y-0.5 transition-all">
                        ENTRAR NO SISTEMA
                    </button>
                </form>

                <div class="mt-6 text-center">
                    <a href="#" class="text-xs text-brand-accent hover:underline">Esqueceu sua senha?</a>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Carousel Logic
        let currentSlide = 0;
        const totalSlides = 4;
        let slideInterval;
        const track = document.getElementById('carousel-track');
        const indicators = document.querySelectorAll('.indicator');
        let isPlaying = false;

        function updateCarousel() {
            track.style.transform = `translateX(-${currentSlide * 100}%)`;
            indicators.forEach((ind, index) => {
                if(index === currentSlide) {
                    ind.classList.add('bg-white', 'scale-125');
                    ind.classList.remove('bg-white/50');
                } else {
                    ind.classList.remove('bg-white', 'scale-125');
                    ind.classList.add('bg-white/50');
                }
            });
        }

        function nextSlide() {
            currentSlide = (currentSlide + 1) % totalSlides;
            updateCarousel();
        }

        function prevSlide() {
            currentSlide = (currentSlide - 1 + totalSlides) % totalSlides;
            updateCarousel();
        }

        function goToSlide(index) {
            currentSlide = index;
            updateCarousel();
            resetInterval();
        }

        function startSlideShow() {
            if (slideInterval) clearInterval(slideInterval);
            slideInterval = setInterval(nextSlide, 5000); // 5 segundos por slide
            isPlaying = true;
        }

        function stopSlideShow() {
            clearInterval(slideInterval);
            isPlaying = false;
        }

        function resetInterval() {
            stopSlideShow();
            startSlideShow();
        }

        // Auto-start Carousel immediately
        document.addEventListener('DOMContentLoaded', () => {
            startSlideShow();
        });

        // Pause on hover (opcional, boa prática UX)
        track.addEventListener('mouseenter', stopSlideShow);
        track.addEventListener('mouseleave', startSlideShow);

        // Função para scrollar (usada pelos botões)
        function scrollToVideoAndPlay() {
            const videoSection = document.getElementById('video');
            videoSection.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }

        // Lógica do Modal
        function toggleLoginModal() {
            const modal = document.getElementById('login-modal');
            if (modal.classList.contains('hidden')) {
                modal.classList.remove('hidden');
                setTimeout(() => {
                    modal.querySelector('.glass-panel').classList.add('scale-100', 'opacity-100');
                    modal.querySelector('.glass-panel').classList.remove('scale-95', 'opacity-0');
                }, 10);
            } else {
                modal.classList.add('hidden');
            }
        }

        // Se o PHP abriu o modal (por erro), aplica a animação de entrada
        <?php if ($modal_aberto): ?>
        document.addEventListener('DOMContentLoaded', () => {
             const modal = document.getElementById('login-modal');
             const panel = modal.querySelector('.glass-panel');
             // Já está visível via classe PHP, só ajusta animação
             panel.classList.add('scale-100', 'opacity-100');
             panel.classList.remove('scale-95', 'opacity-0');
        });
        <?php endif; ?>

        // Lógica do Menu Mobile
        function toggleMobileMenu() {
            const menu = document.getElementById('mobile-menu');
            menu.classList.toggle('hidden');
            menu.classList.toggle('flex');
        }

        // Sistema "Antigravity" de Partículas
        const canvas = document.getElementById('antigravity-canvas');
        const ctx = canvas.getContext('2d');
        let particles = [];

        function resizeCanvas() {
            canvas.width = window.innerWidth;
            canvas.height = window.innerHeight;
        }
        window.addEventListener('resize', resizeCanvas);
        resizeCanvas();

        class Particle {
            constructor() {
                this.x = Math.random() * canvas.width;
                this.y = Math.random() * canvas.height;
                this.size = Math.random() * 2;
                this.speedY = -1 * (Math.random() * 0.5 + 0.1);
                this.speedX = (Math.random() - 0.5) * 0.5;

                // Cores ajustadas: Abóbora (Accent) e Azul (Glow)
                this.color = Math.random() > 0.6
                    ? `rgba(255, 117, 24, ${Math.random() * 0.5})` // Abóbora (#FF7518)
                    : `rgba(79, 195, 247, ${Math.random() * 0.3})`; // Light Blue
            }

            update() {
                this.y += this.speedY;
                this.x += this.speedX;

                if (this.y < 0) {
                    this.y = canvas.height;
                    this.x = Math.random() * canvas.width;
                }
            }

            draw() {
                ctx.fillStyle = this.color;
                ctx.beginPath();
                ctx.arc(this.x, this.y, this.size, 0, Math.PI * 2);
                ctx.fill();
            }
        }

        function initParticles() {
            for (let i = 0; i < 110; i++) {
                particles.push(new Particle());
            }
        }

        function animateParticles() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            // Gradiente Azul Profissional de Fundo
            const gradient = ctx.createRadialGradient(canvas.width / 2, canvas.height / 2, 0, canvas.width / 2, canvas.height / 2, canvas.width);
            gradient.addColorStop(0, '#0a2e5c'); // Azul Royal Escuro
            gradient.addColorStop(1, '#001224'); // Quase Preto/Azul
            ctx.fillStyle = gradient;
            ctx.fillRect(0, 0, canvas.width, canvas.height);

            for (let particle of particles) {
                particle.update();
                particle.draw();
            }
            requestAnimationFrame(animateParticles);
        }

        initParticles();
        animateParticles();
    </script>
</body>

</html>
