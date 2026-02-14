<?php
// ==========================================================
// ARQUIVO: painel.php (ATUALIZADO 28/01/2026 - Visual Clean UI)
// ==========================================================

// 1. FORÇA MOSTRAR ERROS
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'config.php';
require_once 'db.php';
require_once 'session_validator.php';

// Identifica o usuário
$id_usuario = $_SESSION['usuario_id'];
$nome_usuario = $_SESSION['usuario_nome'] ?? 'Usuário';
$ambiente = $_SESSION['ambiente'] ?? 'producao';

// Lógica para Menu
$is_demo = ($ambiente === 'demo');
$modo_suporte = isset($_SESSION['admin_original_id']);
$primeiro_nome = explode(' ', trim($nome_usuario))[0];

// =========================================================================
// 2. MOTOR DE ATUALIZAÇÃO (AJAX) - Mantido Original
// =========================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax_acao'])) {
    while (ob_get_level()) {
        ob_end_clean();
    }
    header('Content-Type: application/json');

    try {
        // Lógica de Conexão Ajustada para Ambiente
        $conn = ($ambiente === 'demo') ? Database::getDemo() : Database::getProd();

        $id = intval($_POST['id']);
        $acao = $_POST['ajax_acao'];

        if ($acao === 'mudar_status') {
            $status = $_POST['status'];
            $status_banco = $status;
            if (strpos($status, 'Aceita') !== false) $status_banco = 'Aprovada';

            $sql = "UPDATE Propostas SET status = ? WHERE id_proposta = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $status_banco, $id);

            if ($stmt->execute()) {
                echo json_encode(['sucesso' => true]);
            } else {
                echo json_encode(['sucesso' => false, 'msg' => 'Erro SQL: ' . $conn->error]);
            }
        } elseif ($acao === 'mudar_data') {
            $nova_data = $_POST['nova_data'];
            if (empty($nova_data)) throw new Exception("Data inválida.");

            $sql = "UPDATE Propostas SET data_criacao = CONCAT(?, ' ', DATE_FORMAT(data_criacao, '%H:%i:%s')) WHERE id_proposta = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $nova_data, $id);

            if ($stmt->execute()) {
                echo json_encode(['sucesso' => true]);
            } else {
                echo json_encode(['sucesso' => false, 'msg' => 'Erro SQL: ' . $conn->error]);
            }
        } else {
            echo json_encode(['sucesso' => false, 'msg' => 'Ação desconhecida.']);
        }
    } catch (Exception $e) {
        echo json_encode(['sucesso' => false, 'msg' => 'Erro PHP: ' . $e->getMessage()]);
    }
    exit;
}

// =========================================================================
// 3. CARREGAMENTO DA TELA
// =========================================================================
try {
    $conn = ($ambiente === 'demo') ? Database::getDemo() : Database::getProd();

    // A. KPIs (Mês Atual) - OTIMIZADO (SARGable)
    $kpi = ['elaborada' => 0, 'enviada' => 0, 'aprovada' => 0, 'cancelada' => 0];
    
    // Calcula o primeiro e último dia do mês atual via PHP para permitir uso de índice no SQL
    $inicio_mes = date('Y-m-01 00:00:00');
    $fim_mes = date('Y-m-t 23:59:59');

    $sqlKPI = "SELECT status, count(*) as qtd 
               FROM Propostas 
               WHERE id_criador = ? 
               AND data_criacao BETWEEN ? AND ?
               GROUP BY status";
               
    $stmtKPI = $conn->prepare($sqlKPI);
    $stmtKPI->bind_param('iss', $id_usuario, $inicio_mes, $fim_mes);
    $stmtKPI->execute();
    $resKPI = $stmtKPI->get_result();

    while ($row = $resKPI->fetch_assoc()) {
        $st = mb_strtolower($row['status']);
        
        // Ignora status "Excluída" ou "Arquivada" nos KPIs
        if (strpos($st, 'exclu') !== false || strpos($st, 'arquiv') !== false) {
            continue;
        }

        if (strpos($st, 'aprov') !== false || strpos($st, 'conclu') !== false || strpos($st, 'aceit') !== false) $kpi['aprovada'] += $row['qtd'];
        elseif (strpos($st, 'envia') !== false) $kpi['enviada'] += $row['qtd'];
        elseif (strpos($st, 'cancel') !== false || strpos($st, 'perdid') !== false) $kpi['cancelada'] += $row['qtd'];
        else $kpi['elaborada'] += $row['qtd'];
    }

    // B. Lista de Propostas
    $sqlLista = "SELECT p.*, c.nome_cliente, d.Empresa as nome_empresa_proponente
                 FROM Propostas p 
                 LEFT JOIN Clientes c ON p.id_cliente = c.id_cliente 
                 LEFT JOIN DadosEmpresa d ON p.id_criador = d.id_criador
                 WHERE p.id_criador = ? 
                 ORDER BY p.data_criacao DESC LIMIT 50";
    $stmtLista = $conn->prepare($sqlLista);
    $stmtLista->bind_param('i', $id_usuario);
    $stmtLista->execute();
    $resultLista = $stmtLista->get_result();
} catch (Exception $e) {
    die("<h1>Erro Crítico de Banco:</h1> " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="pt-br" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SGT | Painel de Controle</title>

    <!-- Tailwind CSS -->
    <!-- Preconnect & Prefetch para performance -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="dns-prefetch" href="https://cdn.tailwindcss.com">
    <link rel="dns-prefetch" href="https://unpkg.com">

    <!-- Tailwind CSS (Keep blocking for JIT) -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Fonts & Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Exo+2:wght@400;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    
    <!-- Phosphor Icons (Defer to unblock render) -->
    <script src="https://unpkg.com/@phosphor-icons/web" defer></script>

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
                        primary: '#f97316',
                        secondary: '#3b82f6',
                        demo: '#eab308'
                    },
                    animation: {
                        'pulse-glow': 'pulse-glow 2s ease-in-out infinite',
                    },
                    keyframes: {
                        'pulse-glow': {
                            '0%, 100%': {
                                boxShadow: '0 0 20px rgba(249, 115, 22, 0.4)'
                            },
                            '50%': {
                                boxShadow: '0 0 40px rgba(249, 115, 22, 0.6)'
                            },
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

        .glass-card, .glass-panel {
            background: rgba(255, 255, 255, 0.05);
            /* Match mock dashboard */
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.3s ease;
        }

        /* --- SGT Multi-Device Vaccines --- */
        
        /* VACINA 3: Mobile Touch Targets & Input Zoom Prevention */
        @media (max-width: 768px) {
            button, a.btn, input, select, .touch-target {
                min-height: 48px !important; /* Dedo gordo friendly */
                font-size: 16px !important; /* Previne zoom no iOS */
            }
            .mobile-stack {
                flex-direction: column !important;
            }
        }

        /* VACINA 8: TV / High-DPI Dashboard (10-foot UI) */
        @media (min-width: 1920px) {
            html {
                font-size: 18px; /* Scale base font */
            }
            .kpi-value {
                font-size: 3rem !important; /* Huge numbers */
            }
            .glass-card {
                padding: 2.5rem !important; /* More breathing room */
            }
        }

        .glass-card:hover {
            background: rgba(255, 255, 255, 0.08);
            border-color: rgba(249, 115, 22, 0.5);
            transform: translateY(-4px);
            box-shadow: 0 4px 20px -5px rgba(0, 0, 0, 0.3);
        }

        /* Grid Background pattern */
        .grid-bg {
            background-image:
                linear-gradient(rgba(255, 255, 255, 0.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255, 255, 255, 0.03) 1px, transparent 1px);
            background-size: 50px 50px;
        }

        /* Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #0a0f1a;
        }

        ::-webkit-scrollbar-thumb {
            background: #334155;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #475569;
        }

/* ============================================
   CORREÇÕES MOBILE - KIMI (PAINEL.PHP)
   ============================================ */

/* Botões de ação sempre visíveis em mobile */
@media (max-width: 768px) {
    .touch-target {
        min-height: 44px !important;
        min-width: 44px !important;
    }
    
    /* Força botões de ação a serem sempre visíveis */
    #tabelaPrincipal tbody tr .flex.items-center.justify-end {
        opacity: 1 !important;
    }
    
    /* Aumenta área de toque nas linhas da tabela */
    #tabelaPrincipal tbody tr {
        position: relative;
    }
    
    #tabelaPrincipal tbody tr td {
        padding-top: 0.75rem;
        padding-bottom: 0.75rem;
    }
    
    /* Status dropdown mais amigável em mobile */
    .dropdown-container button {
        min-height: 44px;
        padding: 0.5rem 0.75rem;
    }
    
    /* Dropdown de status em tela cheia em mobile */
    [id^="dropdown-"] {
        position: fixed !important;
        top: auto !important;
        left: 1rem !important;
        right: 1rem !important;
        bottom: 1rem !important;
        width: auto !important;
        border-radius: 16px !important;
        box-shadow: 0 -10px 40px rgba(0,0,0,0.5) !important;
        z-index: 9999 !important;
    }
    
    [id^="dropdown-"]::before {
        content: '';
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0.5);
        z-index: -1;
        pointer-events: none;
    }
}

/* Animação suave para dropdown */
[id^="dropdown-"] {
    transition: opacity 0.2s ease, transform 0.2s ease;
}

[id^="dropdown-"].hidden {
    opacity: 0;
    transform: translateY(10px);
    pointer-events: none;
}

[id^="dropdown-"]:not(.hidden) {
    opacity: 1;
    transform: translateY(0);
    pointer-events: auto;
}
/* ============================================
   DOCK FLUTUANTE - HUB NAVIGATION
   ============================================ */
.dock-menu {
    position: fixed;
    bottom: 20px;
    left: 50%;
    transform: translateX(-50%);
    background: rgba(17, 24, 39, 0.9);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: 25px;
    padding: 10px 20px;
    display: flex;
    gap: 8px;
    z-index: 1000;
    box-shadow: 0 8px 32px rgba(0,0,0,0.4);
}

.dock-item {
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-direction: column;
    border-radius: 12px;
    transition: all 0.2s ease;
    color: #94a3b8;
    text-decoration: none;
    position: relative;
}

.dock-item:hover {
    transform: scale(1.15) translateY(-6px);
    color: #f97316;
    background: rgba(249, 115, 22, 0.15);
}

.dock-item.active {
    color: #f97316;
    background: rgba(249, 115, 22, 0.2);
}

.dock-item i {
    font-size: 1.5rem;
}

.dock-item .dock-label {
    font-size: 9px;
    font-weight: 600;
    margin-top: 2px;
    opacity: 0;
    transform: translateY(5px);
    transition: all 0.2s;
}

.dock-item:hover .dock-label {
    opacity: 1;
    transform: translateY(0);
}

/* Hero Welcome */
.hero-welcome {
    background: linear-gradient(135deg, rgba(249, 115, 22, 0.1) 0%, rgba(59, 130, 246, 0.05) 100%);
    border: 1px solid rgba(249, 115, 22, 0.2);
    border-radius: 20px;
    padding: 1.5rem 2rem;
}

/* QR Code Card */
.qr-card {
    background: rgba(255, 255, 255, 0.03);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 16px;
    padding: 1rem;
    text-align: center;
}

.qr-card img {
    border-radius: 8px;
    margin: 0 auto;
}

/* Ações Rápidas Grid */
.quick-actions {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 12px;
}

.quick-action-btn {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 1.25rem 1rem;
    background: rgba(255, 255, 255, 0.03);
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 16px;
    color: #94a3b8;
    text-decoration: none;
    transition: all 0.2s;
}

.quick-action-btn:hover {
    background: rgba(249, 115, 22, 0.1);
    border-color: rgba(249, 115, 22, 0.3);
    color: #f97316;
    transform: translateY(-2px);
}

.quick-action-btn i {
    font-size: 1.75rem;
}

.quick-action-btn span {
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* Mobile: 2 colunas nas ações rápidas */
@media (max-width: 768px) {
    .quick-actions {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .dock-menu {
        bottom: 10px;
        padding: 8px 12px;
        gap: 4px;
    }
    
    .dock-item {
        width: 44px;
        height: 44px;
    }
    
    /* Oculta QR em mobile */
    .qr-card {
        display: none;
    }
    
    /* Adiciona padding inferior para não ficar sob o dock */
    main {
        padding-bottom: 100px !important;
    }
}

    </style>
</head>

<body class="antialiased overflow-x-hidden min-h-screen">

    <!-- Background Effects -->
    <div class="fixed inset-0 grid-bg opacity-30 pointer-events-none z-0"></div>
    <div class="fixed top-0 left-1/4 w-96 h-96 bg-blue-600/10 rounded-full blur-[100px] pointer-events-none z-0"></div>
    <div class="fixed bottom-1/4 right-1/4 w-96 h-96 bg-orange-500/10 rounded-full blur-[100px] pointer-events-none z-0"></div>

    <!-- Navbar (Inclui Topbar de Conversão e Menu) -->
    <?php include 'components/navbar.php'; ?>

    <!-- Main Content -->
    <main class="relative z-10 pt-8 pb-12 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- HERO WELCOME - Saudação Personalizada + QR Code -->
        <?php
            // Emoji dinâmico baseado na hora
            $hora = (int)date('H');
            if ($hora < 12) {
                $emoji = '🌅';
                $saudacao = 'Bom dia';
            } elseif ($hora < 18) {
                $emoji = '☀️';
                $saudacao = 'Boa tarde';
            } else {
                $emoji = '🌙';
                $saudacao = 'Boa noite';
            }
            
            // Data por extenso
            $meses = ['Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'];
            $dias_semana = ['Domingo','Segunda-feira','Terça-feira','Quarta-feira','Quinta-feira','Sexta-feira','Sábado'];
            $data_extenso = $dias_semana[date('w')] . ', ' . date('d') . ' de ' . $meses[date('n')-1];
            
            // URL para QR Code COM AUTENTICAÇÃO (mesmo sistema do qrcode_login.php)
            // Garante tabela existe
            $conn->query("
                CREATE TABLE IF NOT EXISTS Tokens_Acesso_Rapido (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    id_usuario INT NOT NULL,
                    token VARCHAR(64) NOT NULL UNIQUE,
                    expiracao DATETIME NOT NULL,
                    usado BOOLEAN DEFAULT FALSE,
                    INDEX idx_token (token)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ");
            
            // Gera novo token (expira em 5 minutos)
            $token = bin2hex(random_bytes(32));
            $expiracao = date('Y-m-d H:i:s', strtotime('+5 minutes'));
            
            // Limpa tokens antigos deste usuário
            $conn->query("DELETE FROM Tokens_Acesso_Rapido WHERE id_usuario = $id_usuario OR expiracao < NOW()");
            
            // Salva novo token
            $stmt = $conn->prepare("INSERT INTO Tokens_Acesso_Rapido (id_usuario, token, expiracao) VALUES (?, ?, ?)");
            $stmt->bind_param('iss', $id_usuario, $token, $expiracao);
            $stmt->execute();
            
            // Monta URL com token de autenticação
            $protocolo = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http";
            $scriptDir = dirname($_SERVER['SCRIPT_NAME']);
            $scriptDir = ($scriptDir === '/' || $scriptDir === '\\') ? '' : $scriptDir;
            $url_painel = $protocolo . "://" . $_SERVER['HTTP_HOST'] . $scriptDir . "/magic_login.php?t=" . $token;
            $qr_api = "https://api.qrserver.com/v1/create-qr-code/?size=120x120&bgcolor=111827&color=f97316&data=" . urlencode($url_painel);
        ?>
        
        <div class="flex flex-col lg:flex-row gap-6 mb-8">
            <!-- Saudação Principal -->
            <div class="hero-welcome flex-1">
                <div class="flex items-center gap-3 mb-2">
                    <span class="text-4xl"><?= $emoji ?></span>
                    <div>
                        <h1 class="font-display text-2xl md:text-3xl font-bold text-white">
                            <?= $saudacao ?>, <span class="text-orange-400"><?= $primeiro_nome ?></span>!
                        </h1>
                        <p class="text-slate-400 text-sm"><?= $data_extenso ?></p>
                    </div>
                </div>
                
                <p class="text-slate-300 mt-3 text-sm">
                    <i class="ph ph-chart-line-up text-green-400"></i>
                    Você tem <strong class="text-white"><?= $kpi['elaborada'] + $kpi['enviada'] ?></strong> propostas ativas este mês
                </p>
            </div>
            
            <!-- QR Code Card -->
            <div class="qr-card flex flex-col items-center">
                <img src="<?= $qr_api ?>" alt="QR Code de Acesso" width="120" height="120" loading="lazy">
                <p class="text-xs text-slate-400 mt-2">Escaneie para acessar</p>
                <p class="text-xs text-orange-400 font-bold">no celular</p>
            </div>
        </div>
        
        <!-- AÇÕES RÁPIDAS -->
        <div class="mb-8">
            <h2 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-3">
                <i class="ph ph-lightning"></i> Ações Rápidas
            </h2>
            <div class="quick-actions" style="grid-template-columns: repeat(5, 1fr);">
                <a href="criar_proposta.php" class="quick-action-btn group">
                    <i class="ph ph-plus-circle text-green-400 group-hover:text-green-300"></i>
                    <span>Nova Proposta</span>
                </a>
                <a href="painel_crm.php" class="quick-action-btn group">
                    <i class="ph ph-kanban text-blue-400 group-hover:text-blue-300"></i>
                    <span>Painel CRM</span>
                </a>
                <a href="dashboard_crm.php" class="quick-action-btn group">
                    <i class="ph ph-chart-pie-slice text-purple-400 group-hover:text-purple-300"></i>
                    <span>Relatórios</span>
                </a>
                <a href="meus_clientes.php" class="quick-action-btn group">
                    <i class="ph ph-users-three text-orange-400 group-hover:text-orange-300"></i>
                    <span>Clientes</span>
                </a>
                <a href="admin_parametros.php" class="quick-action-btn group">
                    <i class="ph ph-gear text-slate-400 group-hover:text-white"></i>
                    <span>Admin</span>
                </a>
            </div>
        </div>

        <!-- KPI Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Elaboradas -->
            <div class="glass-card p-5 rounded-2xl flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-yellow-500/10 flex items-center justify-center text-yellow-500 border border-yellow-500/20">
                    <i class="ph ph-file-text text-2xl"></i>
                </div>
                <div>
                    <h3 class="text-2xl font-bold text-white"><?= $kpi['elaborada'] ?></h3>
                    <p class="text-xs text-slate-400 uppercase font-bold">Elaboradas (Mês)</p>
                </div>
            </div>

            <!-- Enviadas -->
            <div class="glass-card p-5 rounded-2xl flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-blue-500/10 flex items-center justify-center text-blue-400 border border-blue-500/20">
                    <i class="ph ph-paper-plane-tilt text-2xl"></i>
                </div>
                <div>
                    <h3 class="text-2xl font-bold text-white"><?= $kpi['enviada'] ?></h3>
                    <p class="text-xs text-slate-400 uppercase font-bold">Enviadas (Mês)</p>
                </div>
            </div>

            <!-- Aprovadas -->
            <div class="glass-card p-5 rounded-2xl flex items-center gap-4 border border-green-500/30 bg-green-500/5">
                <div class="w-12 h-12 rounded-xl bg-green-500/10 flex items-center justify-center text-green-400 border border-green-500/20">
                    <i class="ph ph-check-circle text-2xl"></i>
                </div>
                <div>
                    <h3 class="text-2xl font-bold text-white"><?= $kpi['aprovada'] ?></h3>
                    <p class="text-xs text-green-400 uppercase font-bold">Aprovadas (Mês)</p>
                </div>
            </div>

            <!-- Canceladas -->
            <div class="glass-card p-5 rounded-2xl flex items-center gap-4 opacity-75 hover:opacity-100">
                <div class="w-12 h-12 rounded-xl bg-red-500/10 flex items-center justify-center text-red-400 border border-red-500/20">
                    <i class="ph ph-x-circle text-2xl"></i>
                </div>
                <div>
                    <h3 class="text-2xl font-bold text-white"><?= $kpi['cancelada'] ?></h3>
                    <p class="text-xs text-slate-400 uppercase font-bold">Canceladas (Mês)</p>
                </div>
            </div>
        </div>

        <!-- Inclui gráfico se houver (mantido compatibilidade) -->
        <div class="mb-8">
            <?php include 'dashboard_include.php'; ?>
        </div>

        <!-- Table Card -->
        <div class="glass rounded-2xl overflow-hidden border border-white/5">
            <div class="p-6 border-b border-white/5 flex flex-col md:flex-row justify-between items-center gap-4 bg-surface/30">
                <h2 class="font-bold text-white text-lg flex items-center gap-2">
                    <i class="ph ph-list-dashes text-orange-400"></i> Últimas Propostas
                </h2>
                <div class="relative w-full md:w-72">
                    <i class="ph ph-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-500"></i>
                    <input type="text" id="filtroTabela" class="w-full bg-black/20 border border-white/10 rounded-lg py-2.5 pl-10 text-sm text-white focus:outline-none focus:border-orange-500 transition-colors placeholder-slate-600" placeholder="Buscar cliente, número...">
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left" id="tabelaPrincipal">
                    <thead>
                        <tr class="bg-white/5 border-b border-white/5 text-xs text-slate-400 uppercase tracking-wider">
                            <th class="px-6 py-4 font-semibold">Data</th>
                            <th class="px-6 py-4 font-semibold">Número</th>
                            <th class="px-6 py-4 font-semibold">Cliente</th>
                            <th class="px-6 py-4 font-semibold">Status</th>
                            <th class="px-6 py-4 font-semibold text-right">Valor</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5 text-sm">
                        <?php while ($row = $resultLista->fetch_assoc()):
                            $id = $row['id_proposta'];
                            $st = mb_strtolower($row['status']);

                            // Cores Status (Visual Tags)
                            $badgeClass = 'bg-yellow-500/10 text-yellow-500 border-yellow-500/20';
                            if (strpos($st, 'aprov') !== false) $badgeClass = 'bg-green-500/10 text-green-400 border-green-500/20';
                            elseif (strpos($st, 'envia') !== false) $badgeClass = 'bg-blue-500/10 text-blue-400 border-blue-500/20';
                            elseif (strpos($st, 'cancel') !== false) $badgeClass = 'bg-red-500/10 text-red-400 border-red-500/20';
                            elseif (strpos($st, 'exclu') !== false) $badgeClass = 'bg-slate-500/10 text-slate-400 border-slate-500/20 decoration-line-through opacity-75';
                        ?>
                            <tr class="hover:bg-white/5 transition-colors group">
                                <td class="px-6 py-4 text-slate-400 font-mono whitespace-nowrap">
                                    <div class="flex items-center gap-2 group/edit cursor-pointer" onclick="abrirEditorData(<?= $id ?>, '<?= date('Y-m-d', strtotime($row['data_criacao'])) ?>')">
                                        <span><?= date('d/m/Y', strtotime($row['data_criacao'])); ?></span>
                                        <i class="ph ph-pencil-simple text-orange-400 opacity-0 group-hover/edit:opacity-100 transition-opacity"></i>
                                    </div>
                                </td>
                                <td class="px-6 py-4 font-bold text-white whitespace-nowrap">
                                    <?= $row['numero_proposta']; ?>
                                </td>
                                <td class="px-6 py-4 text-slate-300">
                                    <?= htmlspecialchars($row['nome_cliente_salvo']); ?>
                                </td>
                                <td class="px-6 py-4">
                                    <!-- Status Dropdown -->
                                    <div class="relative dropdown-container">
                                        <button onclick="toggleDropdown(<?= $id ?>)" id="btn-<?= $id ?>" class="inline-flex items-center gap-2 px-3 py-1 rounded-lg border text-[11px] font-bold uppercase tracking-wider transition-all hover:brightness-110 <?= $badgeClass ?>">
                                            <span id="label-<?= $id ?>"><?= $row['status'] ?></span>
                                            <i class="ph ph-caret-down"></i>
                                        </button>

                                        <div id="dropdown-<?= $id ?>" class="hidden absolute left-0 top-full mt-2 w-40 glass rounded-xl shadow-xl z-50 overflow-hidden border border-white/10">
                                            <div class="py-1">
                                                <a href="#" onclick="trocarStatus(<?= $id ?>, 'Em Elaboração'); return false;" class="block px-4 py-2 text-xs text-yellow-500 hover:bg-white/5 font-bold">🟡 Em Elaboração</a>
                                                <a href="#" onclick="trocarStatus(<?= $id ?>, 'Enviada'); return false;" class="block px-4 py-2 text-xs text-blue-400 hover:bg-white/5 font-bold">🔵 Enviada</a>
                                                <a href="#" onclick="trocarStatus(<?= $id ?>, 'Aprovada'); return false;" class="block px-4 py-2 text-xs text-green-400 hover:bg-white/5 font-bold">🟢 Aprovada</a>
                                                <div class="h-px bg-white/10 my-1"></div>
                                                <a href="#" onclick="trocarStatus(<?= $id ?>, 'Cancelada'); return false;" class="block px-4 py-2 text-xs text-red-400 hover:bg-white/5 font-bold">🔴 Cancelada</a>
                                                <a href="#" onclick="trocarStatus(<?= $id ?>, 'Excluída'); return false;" class="block px-4 py-2 text-xs text-slate-500 hover:bg-white/5 font-bold">⚫ Excluída</a>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex flex-col items-end">
                                        <span class="text-xs text-slate-500 line-through decoration-white decoration-1">
                                            R$ <?= number_format($row['valor_final_proposta'] * 1.1, 2, ',', '.'); ?>
                                        </span>
                                        <div class="font-bold text-white">
                                            R$ <?= number_format($row['valor_final_proposta'], 2, ',', '.'); ?>
                                        </div>
                                    </div>
                                    <?php
                                    // --- LÓGICA DE ARQUIVOS OTIMIZADA (PERF-002) ---
                                    $caminhoFinal = '';
                                    $arquivoExiste = false;
                                    $dirBase = __DIR__ . '/propostas_emitidas';
                                    
                                    $anoProp = date('Y', strtotime($row['data_criacao']));
                                    $numPropLimpo = preg_replace('/\D/', '', explode('-', $row['numero_proposta'])[1] ?? '000');
                                    $numPad = str_pad($numPropLimpo, 3, '0', STR_PAD_LEFT);
                                    
                                    $patternBusca = $dirBase . "/*-{$anoProp}-{$numPad}*.docx";
                                    $encontrados = glob($patternBusca);

                                    if ($encontrados && count($encontrados) > 0) {
                                        $arquivoExiste = true;
                                        $caminhoFinal = 'propostas_emitidas/' . basename($encontrados[0]);
                                    }
                                    ?>
                                    <!-- BLOCO DE BOTÕES DE AÇÃO - VERSÃO MOBILE CORRIGIDA KIMI -->
                                    <div class="flex items-center justify-end gap-2 mt-2">
                                        <!-- Editar -->
                                        <a href="editar_proposta.php?id=<?= $id ?>" 
                                           class="w-10 h-10 md:w-auto md:h-auto md:px-3 md:py-1.5 rounded-lg bg-yellow-500/10 text-yellow-500 hover:bg-yellow-500 hover:text-white flex items-center justify-center transition-all touch-target" 
                                           title="Editar">
                                            <i class="ph ph-pencil-simple text-lg"></i>
                                            <span class="hidden md:inline ml-1 text-xs font-medium">Editar</span>
                                        </a>
                                        
                                        <!-- Relatório -->
                                        <a href="relatorio_proposta.php?id=<?= $id ?>" 
                                           class="w-10 h-10 md:w-auto md:h-auto md:px-3 md:py-1.5 rounded-lg bg-orange-500/10 text-orange-400 hover:bg-orange-500 hover:text-white flex items-center justify-center transition-all touch-target" 
                                           title="Relatório">
                                            <i class="ph ph-file-text text-lg"></i>
                                            <span class="hidden md:inline ml-1 text-xs font-medium">Relatório</span>
                                        </a>

                                        <?php if ($arquivoExiste): ?>
                                            <!-- Email -->
                                            <a href="enviar_email.php?id=<?= $id ?>" 
                                               class="w-10 h-10 md:w-auto md:h-auto md:px-3 md:py-1.5 rounded-lg bg-blue-500/10 text-blue-400 hover:bg-blue-500 hover:text-white flex items-center justify-center transition-all touch-target" 
                                               title="Enviar Email">
                                                <i class="ph ph-paper-plane-tilt text-lg"></i>
                                                <span class="hidden md:inline ml-1 text-xs font-medium">Email</span>
                                            </a>
                                            
                                            <!-- Download -->
                                            <a href="<?= $caminhoFinal ?>" download 
                                               class="w-10 h-10 md:w-auto md:h-auto md:px-3 md:py-1.5 rounded-lg bg-green-500/10 text-green-400 hover:bg-green-500 hover:text-white flex items-center justify-center transition-all touch-target" 
                                               title="Baixar DOCX">
                                                <i class="ph ph-download-simple text-lg"></i>
                                                <span class="hidden md:inline ml-1 text-xs font-medium">Download</span>
                                            </a>
                                        <?php else: ?>
                                            <!-- Arquivo não encontrado -->
                                            <span class="w-10 h-10 rounded-lg bg-slate-700/30 text-slate-600 flex items-center justify-center cursor-not-allowed touch-target" title="Arquivo não encontrado">
                                                <i class="ph ph-file-dashed text-lg"></i>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-white/5 text-center text-xs text-slate-500">
                Mostrando as últimas 50 propostas. Use a busca para filtrar.
            </div>
        </div>

    </main>

    <!-- Footer -->
    <footer class="relative z-10 border-t border-white/5 mt-12 py-8">
        <div class="max-w-7xl mx-auto px-4 text-center">
            <p class="text-slate-500 text-sm mb-2">© <?= date('Y') ?> SGT-Propostas. Todos os direitos reservados.</p>
        </div>
    </footer>

    <!-- Modal Novidades -->
    <div id="modalNovidades" class="fixed inset-0 z-[70] hidden">
        <div class="absolute inset-0 bg-black/80 backdrop-blur-sm" onclick="closeModal()"></div>
        <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-full max-w-lg p-6">
            <div class="glass bg-surface rounded-2xl border border-white/10 shadow-2xl overflow-hidden relative">
                <button onclick="closeModal()" class="absolute top-4 right-4 text-slate-400 hover:text-white"><i class="ph ph-x text-xl"></i></button>
                <div class="mb-4">
                    <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-orange-500/10 text-orange-400 text-xs font-bold border border-orange-500/20">
                        <i class="ph ph-confetti"></i> Novidades v<span id="novidadeVersao"></span>
                    </span>
                </div>
                <h3 id="novidadeTitulo" class="text-2xl font-bold text-white mb-4"></h3>
                <div id="novidadeDescricao" class="text-slate-300 text-sm leading-relaxed mb-6 space-y-2"></div>
                <button onclick="closeModal()" class="w-full py-3 bg-gradient-to-r from-orange-500 to-orange-600 text-white font-bold rounded-lg shadow-lg hover:shadow-orange-500/20 transition-all">
                    Entendi, ver meu painel
                </button>
            </div>
        </div>
    </div>

    <!-- Modal Magic Login -->
    <div id="modalMagicLogin" class="fixed inset-0 z-[9999] hidden flex items-center justify-center">
        <div class="absolute inset-0 bg-black/90 backdrop-blur-md" onclick="closeMagicLogin()"></div>
        <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-full max-w-sm p-6">
            <div class="glass bg-surface rounded-2xl border border-white/10 shadow-2xl overflow-hidden relative text-center">
                <button onclick="closeMagicLogin()" class="absolute top-4 right-4 text-slate-400 hover:text-white"><i class="ph ph-x text-xl"></i></button>
                
                <div class="mb-6 mt-2">
                    <div class="inline-flex p-4 rounded-full bg-green-500/10 mb-4 border border-green-500/20 shadow-[0_0_20px_rgba(34,197,94,0.3)]">
                        <i class="ph ph-qr-code text-4xl text-green-400"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-white mb-1">Acesso Mágico</h3>
                    <p class="text-slate-400 text-sm">Escaneie para entrar no celular</p>
                </div>

                <div class="flex justify-center mb-6 relative">
                    <div class="absolute inset-0 bg-green-500/20 blur-xl rounded-full"></div>
                    <div id="magicQrCode" class="relative z-10 bg-white p-4 rounded-xl shadow-lg"></div>
                </div>

                <div class="text-xs text-slate-500 mb-4">
                    <p>Este código expira em <span class="text-white font-bold">2 minutos</span>.</p>
                    <p class="mt-1">Não compartilhe com ninguém.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts (Mantidos) -->
    <script src="https://unpkg.com/qrcodejs@1.0.0/qrcode.min.js"></script>
    <script>
        // Magic Login Logic
        function abrirMagicLogin() {
            const modal = document.getElementById('modalMagicLogin');
            const qrContainer = document.getElementById('magicQrCode');
            
            // Limpa anterior
            qrContainer.innerHTML = '<div class="flex justify-center p-4"><div class="animate-spin rounded-full h-8 w-8 border-b-2 border-green-500"></div></div>';
            modal.classList.remove('hidden');

            if (typeof QRCode === 'undefined') {
                qrContainer.innerHTML = '<p class="text-red-400 text-xs">Biblioteca QR Code não carregou.<br>Tente recarregar a página.</p>';
                return;
            }

            // Chama API
            fetch('api/auth_mobile.php', { method: 'POST' })
                .then(r => r.text()) // Get text first to debug
                .then(text => {
                    try {
                        const res = JSON.parse(text); // Try parsing
                        qrContainer.innerHTML = '';
                        if (res.sucesso) {
                            try {
                                new QRCode(qrContainer, {
                                    text: res.url,
                                    width: 180,
                                    height: 180,
                                    colorDark : "#0a0f1a",
                                    colorLight : "#ffffff",
                                    correctLevel : QRCode.CorrectLevel.M
                                });
                                // Link de Backup
                                const linkBackup = document.createElement('a');
                                linkBackup.href = res.url;
                                linkBackup.className = "block mt-4 text-xs text-green-400 hover:underline";
                                linkBackup.innerText = "Ou clique aqui para abrir direto";
                                linkBackup.target = "_blank";
                                qrContainer.appendChild(linkBackup);

                            } catch (err) {
                                console.error(err);
                                qrContainer.innerHTML = `<p class="text-red-400 text-xs">Erro ao gerar QR: ${err.message}</p>`;
                            }
                        } else {
                            qrContainer.innerHTML = `<p class="text-red-400 text-xs text-center">Erro API: ${res.msg}</p>`;
                        }
                    } catch (e) {
                        console.error('Erro Resposta:', text);
                        qrContainer.innerHTML = `<p class="text-red-400 text-xs text-center">Erro Inesperado.<br>Verifique o console.</p>`;
                    }
                })
                .catch(e => {
                    console.error(e);
                    qrContainer.innerHTML = '<p class="text-red-400 text-xs text-center">Erro de Conexão.</p>';
                });
        }

        function closeMagicLogin() {
            document.getElementById('modalMagicLogin').classList.add('hidden');
        }

        // FUNÇÃO CORRIGIDA PARA MOBILE - KIMI
        function toggleDropdown(id) {
            const dropdown = document.getElementById('dropdown-' + id);
            const isHidden = dropdown.classList.contains('hidden');
            
            // Fecha todos os dropdowns primeiro
            document.querySelectorAll('[id^="dropdown-"]').forEach(el => {
                el.classList.add('hidden');
            });
            
            // Abre o atual se estava fechado
            if (isHidden) {
                dropdown.classList.remove('hidden');
                
                // Em mobile, adiciona overlay para fechar
                if (window.innerWidth <= 768) {
                    let overlay = document.getElementById('dropdown-overlay');
                    if (!overlay) {
                        overlay = document.createElement('div');
                        overlay.id = 'dropdown-overlay';
                        overlay.style.cssText = 'position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:9998;';
                        overlay.onclick = () => {
                            document.querySelectorAll('[id^="dropdown-"]').forEach(el => el.classList.add('hidden'));
                            overlay.remove();
                        };
                        document.body.appendChild(overlay);
                    }
                }
            } else {
                // Remove overlay se existir
                const overlay = document.getElementById('dropdown-overlay');
                if (overlay) overlay.remove();
            }
        }

        // Fecha dropdown ao clicar fora (atualizado)
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.dropdown-container')) {
                document.querySelectorAll('[id^="dropdown-"]').forEach(el => {
                    el.classList.add('hidden');
                });
                const overlay = document.getElementById('dropdown-overlay');
                if (overlay) overlay.remove();
            }
        });

        const filtro = document.getElementById('filtroTabela');
        if (filtro) {
            filtro.addEventListener('keyup', function() {
                let valor = this.value.toLowerCase();
                document.querySelectorAll('#tabelaPrincipal tbody tr').forEach(tr => {
                    tr.style.display = tr.innerText.toLowerCase().includes(valor) ? '' : 'none';
                });
            });
        }

        function trocarStatus(id, novoStatus) {
            const btn = document.getElementById('btn-' + id);
            const label = document.getElementById('label-' + id);
            const originalText = label.innerText;

            label.innerText = '...';
            btn.classList.add('opacity-50', 'cursor-not-allowed');
            document.body.style.cursor = 'wait';

            let dados = new FormData();
            dados.append('ajax_acao', 'mudar_status');
            dados.append('id', id);
            dados.append('status', novoStatus);

            fetch('painel.php', {
                    method: 'POST',
                    body: dados
                })
                .then(r => r.json())
                .then(res => {
                    if (res.sucesso) window.location.reload();
                    else {
                        alert('Erro: ' + res.msg);
                        label.innerText = originalText;
                        btn.classList.remove('opacity-50');
                    }
                })
                .catch(e => {
                    alert('Erro de conexão.');
                    label.innerText = originalText;
                    btn.classList.remove('opacity-50');
                })
                .finally(() => document.body.style.cursor = 'default');
        }

        function abrirEditorData(id, dataAtual) {
            const novaData = prompt("Corrigir Data (AAAA-MM-DD):", dataAtual);
            if (novaData && novaData !== dataAtual) {
                if (!/^\d{4}-\d{2}-\d{2}$/.test(novaData)) {
                    alert("Data inválida. Use AAAA-MM-DD");
                    return;
                }

                document.body.style.cursor = 'wait';
                let dados = new FormData();
                dados.append('ajax_acao', 'mudar_data');
                dados.append('id', id);
                dados.append('nova_data', novaData);

                fetch('painel.php', {
                        method: 'POST',
                        body: dados
                    })
                    .then(r => r.json())
                    .then(res => {
                        if (res.sucesso) window.location.reload();
                        else alert('Erro: ' + res.msg);
                    })
                    .finally(() => document.body.style.cursor = 'default');
            }
        }

        // Novidades
        function closeModal() {
            document.getElementById('modalNovidades').classList.add('hidden');
        }

        document.addEventListener('DOMContentLoaded', function() {
            fetch('api_check_novidades.php')
                .then(r => r.json())
                .then(res => {
                    if (res.tem_novidade) {
                        document.getElementById('novidadeVersao').innerText = res.versao;
                        document.getElementById('novidadeTitulo').innerText = res.titulo;
                        document.getElementById('novidadeDescricao').innerHTML = res.descricao;
                        document.getElementById('modalNovidades').classList.remove('hidden');
                        fetch('api_check_novidades.php', {
                            method: 'POST',
                            body: new URLSearchParams({
                                acao: 'marcar_lida',
                                versao: res.versao
                            })
                        });
                    }
                });
        });
    </script>

    <!-- DOCK FLUTUANTE - Navegação Hub -->
    <nav class="dock-menu" aria-label="Navegação Principal">
        <a href="painel.php" class="dock-item active" title="Início">
            <i class="ph ph-house"></i>
            <span class="dock-label">Início</span>
        </a>
        <a href="criar_proposta.php" class="dock-item" title="Nova Proposta">
            <i class="ph ph-file-plus"></i>
            <span class="dock-label">Proposta</span>
        </a>
        <a href="painel_crm.php" class="dock-item" title="CRM">
            <i class="ph ph-kanban"></i>
            <span class="dock-label">CRM</span>
        </a>
        <a href="dashboard_crm.php" class="dock-item" title="Relatórios">
            <i class="ph ph-chart-pie-slice"></i>
            <span class="dock-label">Relatórios</span>
        </a>
        <a href="painel_financeiro.php" class="dock-item" title="Financeiro">
            <i class="ph ph-bank"></i>
            <span class="dock-label">Financeiro</span>
        </a>
        <a href="empresa_config.php" class="dock-item" title="Configurações">
            <i class="ph ph-gear-six"></i>
            <span class="dock-label">Config</span>
        </a>
    </nav>

</body>

</html>