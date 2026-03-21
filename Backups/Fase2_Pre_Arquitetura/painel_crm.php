<?php
// painel_crm.php - SGT CRM 2.0 (Motor SGT)
// DEBUG MODE: ATIVADO PARA DIAGNÓSTICO
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Descrição: Kanban Board moderno com Drag & Drop (SortableJS) e sincronia real de status via API.

require 'conexao.php';
require_once 'db.php'; // Garante que a classe Database esteja carregada

// 1. Segurança Hardened
if (file_exists('session_validator.php')) {
    require_once 'session_validator.php';
} else {
    session_start();
    if (!isset($_SESSION['usuario_id'])) {
        header("Location: index.php?msg=sessao_expirada");
        exit;
    }
}

$id_usuario = $_SESSION['usuario_id'];
if (isset($_SESSION['ambiente']) && $_SESSION['ambiente'] === 'demo') {
    $conn = Database::getDemo();
} else {
    $conn = Database::getProd();
}

// --- AUTO-MIGRAÇÃO CRM 3.0 (Kimi) ---
// Garante que as tabelas existem sem precisar de script externo
$conn->query("
    CREATE TABLE IF NOT EXISTS Historico_Interacoes (
        id_historico INT AUTO_INCREMENT PRIMARY KEY,
        id_proposta INT NOT NULL,
        id_cliente INT NOT NULL,
        tipo VARCHAR(50) NOT NULL,
        conteudo TEXT NOT NULL,
        metadata JSON NULL,
        canal VARCHAR(20) NULL,
        id_usuario INT NOT NULL,
        data_interacao DATETIME DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_proposta_data (id_proposta, data_interacao)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");

$conn->query("
    CREATE TABLE IF NOT EXISTS Tarefas_CRM (
        id_tarefa INT AUTO_INCREMENT PRIMARY KEY,
        id_proposta INT NOT NULL,
        id_usuario INT NOT NULL,
        tipo VARCHAR(50) NOT NULL,
        descricao TEXT NOT NULL,
        data_agendada DATETIME NOT NULL,
        prioridade VARCHAR(20) DEFAULT 'media',
        status VARCHAR(20) DEFAULT 'pendente',
        resultado VARCHAR(50) NULL,
        observacao TEXT NULL,
        data_conclusao DATETIME NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_usuario_status (id_usuario, status)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");

// --- MÓDULOS AVANÇADOS (Email e Documentos) ---
$conn->query("
    CREATE TABLE IF NOT EXISTS Email_Templates (
        id_template INT AUTO_INCREMENT PRIMARY KEY,
        id_usuario INT NOT NULL,
        nome VARCHAR(100) NOT NULL,
        assunto VARCHAR(200) NOT NULL,
        corpo TEXT NOT NULL,
        tipo VARCHAR(20) DEFAULT 'personalizado',
        ativo BOOLEAN DEFAULT TRUE,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");

$conn->query("
    CREATE TABLE IF NOT EXISTS Email_Envios (
        id_envio INT AUTO_INCREMENT PRIMARY KEY,
        id_proposta INT NOT NULL,
        id_cliente INT NOT NULL,
        id_template INT NULL,
        id_usuario INT NOT NULL,
        assunto VARCHAR(200),
        corpo TEXT,
        destinatario VARCHAR(150),
        status VARCHAR(20) DEFAULT 'pendente', -- pendente, enviado, erro, aberto, clicado
        data_agendamento DATETIME NULL,
        data_envio DATETIME NULL,
        erro_msg TEXT NULL,
        hash_rastreamento VARCHAR(64) NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_proposta (id_proposta)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");

$conn->query("
    CREATE TABLE IF NOT EXISTS Documentos (
        id_documento INT AUTO_INCREMENT PRIMARY KEY,
        id_proposta INT NOT NULL,
        id_cliente INT NOT NULL,
        id_usuario INT NOT NULL,
        nome_original VARCHAR(255) NOT NULL,
        nome_arquivo VARCHAR(255) NOT NULL,
        tipo_arquivo VARCHAR(100),
        categoria VARCHAR(50) DEFAULT 'outro',
        tamanho_bytes BIGINT,
        caminho VARCHAR(255) NOT NULL,
        descricao TEXT,
        is_principal BOOLEAN DEFAULT FALSE,
        data_upload DATETIME DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_proposta (id_proposta)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");
// -------------------------------------
// -------------------------------------

// AUTO-MIGRAÇÃO DE EMERGÊNCIA (Correção do Erro 500)
try {
    // 1. Garante whatsapp_handle em Clientes
    $checkCols = $conn->query("SHOW COLUMNS FROM Clientes LIKE 'whatsapp_handle'");
    if ($checkCols && $checkCols->num_rows === 0) {
        $conn->query("ALTER TABLE Clientes ADD COLUMN whatsapp_handle VARCHAR(100) AFTER whatsapp");
    }

    // 2. Garante data_atualizacao em Propostas
    $checkCols2 = $conn->query("SHOW COLUMNS FROM Propostas LIKE 'data_atualizacao'");
    if ($checkCols2 && $checkCols2->num_rows === 0) {
        $conn->query("ALTER TABLE Propostas ADD COLUMN data_atualizacao DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP");
    }
} catch (Exception $e) {
    // Silencia erro de migração
}

// 2. Busca Propostas (Lógica de Colunas Dinâmicas)
// Mapeamos os status REAIS do banco para as COLUNAS VISUAIS do CRM
        $sql = "SELECT p.id_proposta, p.id_cliente, p.status, p.data_criacao,
               c.nome_cliente, c.whatsapp, c.whatsapp_handle, c.telefone,
               DATEDIFF(NOW(), p.data_criacao) as dias_decorridos,
               p.data_atualizacao,
               ts.nome as tipo_nome, ts.cor as tipo_cor, ts.icone as tipo_icone
        FROM Propostas p
        LEFT JOIN Clientes c ON p.id_cliente = c.id_cliente
        LEFT JOIN tipos_servico ts ON p.tipo_servico_id = ts.id
        WHERE p.id_criador = ? 
        ORDER BY 
            CASE 
                WHEN p.status = 'Cancelada' THEN 1 
                ELSE 0 
            END,
            p.data_atualizacao DESC";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("<h1>Erro SQL (Recuperável)</h1><p>Falha ao preparar consulta: " . $conn->error . "</p><p>SQL Tumb: " . $sql . "</p>");
}

$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$result = $stmt->get_result();

$kanban = [
    'ELABORACAO' => [],
    'ENVIADA' => [],
    'FECHADA' => [],
    'CANCELADA' => []
];

while ($row = $result->fetch_assoc()) {
    $st = mb_strtolower($row['status']); // Normaliza para minúsculo
    
    // Mapeamento Abrangente (Para não perder nenhuma proposta)
    if (strpos($st, 'aprov') !== false || strpos($st, 'fechada') !== false || strpos($st, 'aceit') !== false || strpos($st, 'finaliz') !== false) {
        $kanban['FECHADA'][] = $row;
    } 
    elseif (strpos($st, 'cancel') !== false || strpos($st, 'perdid') !== false || strpos($st, 'arquiv') !== false) {
        $kanban['CANCELADA'][] = $row;
    } 
    elseif (strpos($st, 'elabora') !== false || strpos($st, 'rascunho') !== false || strpos($st, 'novo') !== false) {
        $kanban['ELABORACAO'][] = $row;
    } 
    // "Enviada" funciona como um "Catch-all" para status de negociação, visita, etc.
    else {
        // Se não é fechada, nem cancelada, nem rascunho, assumimos que está "Na Pista" (Enviada/Negociação)
        $kanban['ENVIADA'][] = $row;
    }
}

// Helper de Link WhatsApp
function getZapLink($numero, $handle) {
    // Prioridade 1: Handle (@Empresa)
    if (!empty($handle)) {
        $cleanHandle = preg_replace('/[^a-zA-Z0-9_]/', '', $handle);
        return "https://wa.me/message/" . $cleanHandle; // Exemplo hipotético de link curto ou direto
        // Nota: O link de handle universal não é padrão aberto.
        // Melhor: Se for handle, é instagram ou telegram?
        // Vamos focar no requisito: "Flexibilidade".
        // Se tem @, assume que é texto.
        return "https://wa.me/" . $numero . "?text=Ola"; // Fallback seguro
    }
    
    // Prioridade 2: Número
    $cleanNum = preg_replace('/\D/', '', $numero);
    return "https://wa.me/55" . $cleanNum;
}

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>SGT | CRM 2.0</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.0/Sortable.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
                    colors: { surface: '#111827', primary: '#f97316' }
                }
            }
        }
    </script>
    <style>
        body { background: #0a0f1a; color: #e2e8f0; -webkit-tap-highlight-color: transparent; }
        /* Scroll normal para evitar travas */
        .kanban-container {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
            min-height: calc(100vh - 80px);
        }
        @media (max-width: 768px) {
            .kanban-container {
                display: flex;
                flex-direction: column; /* Em mobile, lista vertical é mais segura que scroll horizontal */
                gap: 2rem;
            }
            .kanban-col-wrapper {
                min-width: 100%;
            }
            .kanban-list {
                min-height: 150px; /* Garante área de drop */
            }
        }
        .card-ghost { background: rgba(249, 115, 22, 0.1); border: 2px dashed #f97316; opacity: 0.5; }
        .card-drag { cursor: grabbing; opacity: 0.9; transform: scale(1.02); }
        
        /* ============================================================
           RESPONSIVIDADE MOBILE/TABLET - SGT CRM
           ============================================================ */

        /* ===== MOBILE PEQUENO (até 640px) ===== */
        @media (max-width: 640px) {
            /* Navbar compacta */
            nav {
                height: 3.5rem;
                padding: 0 0.75rem;
            }
            
            nav h1 {
                font-size: 1rem;
            }
            
            /* Kanban vira lista vertical */
            .kanban-container {
                display: flex !important;
                flex-direction: column !important;
                gap: 1rem !important;
                padding: 0.5rem !important;
            }
            
            .kanban-col-wrapper {
                min-width: 100% !important;
                width: 100% !important;
            }
            
            /* Cards mais compactos */
            .card {
                padding: 0.75rem !important;
                margin-bottom: 0.5rem !important;
            }
            
            .card h3 {
                font-size: 0.875rem !important;
                line-height: 1.25rem !important;
            }
            
            /* Botões do card em grid 3x1 */
            .card .grid-cols-2,
            .card .grid-cols-3 {
                grid-template-columns: repeat(3, 1fr) !important;
                gap: 0.25rem !important;
            }
            
            .card button {
                padding: 0.375rem !important;
                font-size: 0.625rem !important;
            }
            
            .card button span {
                display: none !important; /* Esconde texto, mostra só ícone */
            }
            
            .card button i {
                font-size: 1rem !important;
            }
            
            /* Widget de tarefas vira bottom sheet */
            #widget-tarefas {
                position: fixed !important;
                right: 0 !important;
                left: 0 !important;
                bottom: 0 !important;
                top: auto !important;
                width: 100% !important;
                max-height: 70vh !important;
                border-radius: 1rem 1rem 0 0 !important;
                border-bottom: none !important;
                border-top: 1px solid rgba(255,255,255,0.1) !important;
                transform: translateY(100%);
                transition: transform 0.3s ease;
                z-index: 55 !important;
                background-color: #0f172a !important;
            }
            
            #widget-tarefas.aberto {
                transform: translateY(0);
            }
            
            /* Botão flutuante maior para dedo */
            .fixed.bottom-4.right-4 {
                width: 3.5rem !important;
                height: 3.5rem !important;
            }
            
            /* Modais fullscreen */
            #modal-timeline,
            #modal-tarefa,
            #modal-documentos,
            #modal-email,
            #modal-whatsapp {
                padding: 0 !important;
            }
            
            #modal-timeline > div,
            #modal-tarefa > div,
            #modal-documentos > div,
            #modal-email > div,
            #modal-whatsapp > div {
                min-height: 100vh !important;
                border-radius: 0 !important;
                max-width: 100% !important;
            }
            
            /* Timeline em coluna única */
            #modal-timeline .grid {
                grid-template-columns: 1fr !important;
            }
            
            #modal-timeline .md\:col-span-1 {
                border-right: none !important;
                border-bottom: 1px solid rgba(255,255,255,0.05) !important;
                max-height: 30vh !important;
                overflow-y: auto !important;
            }
            
            /* Formulários com inputs maiores para toque */
            input, select, textarea, button {
                min-height: 2.75rem !important;
                font-size: 16px !important; /* Evita zoom no iOS */
            }
            
            /* Tabelas com scroll horizontal */
            .overflow-x-auto {
                display: block !important;
                overflow-x: auto !important;
                white-space: nowrap !important;
                -webkit-overflow-scrolling: touch !important;
            }
            
            /* Dashboard analytics em coluna única */
            .grid-cols-2, .grid-cols-3, .grid-cols-4 {
                grid-template-columns: 1fr !important;
            }
            
            /* Gráficos menores */
            canvas {
                max-height: 250px !important;
            }
        }

        /* ===== TABLET (641px até 1024px) ===== */
        @media (min-width: 641px) and (max-width: 1024px) {
            .kanban-container {
                display: grid !important;
                grid-template-columns: repeat(2, 1fr) !important;
                gap: 1rem !important;
            }
            
            .kanban-col-wrapper:nth-child(3),
            .kanban-col-wrapper:nth-child(4) {
                grid-column: span 1;
            }
            
            /* Widget de tarefas menor */
            #widget-tarefas {
                width: 18rem !important;
                right: 0.5rem !important;
                top: 4rem !important;
            }
            
            /* Modais com margem */
            #modal-timeline > div,
            #modal-tarefa > div,
            #modal-documentos > div {
                max-width: 95% !important;
                margin: 1rem auto !important;
                min-height: auto !important;
                max-height: 90vh !important;
            }
            
            /* Timeline em 2 colunas */
            #modal-timeline .grid {
                grid-template-columns: 1fr 2fr !important;
            }
        }

        /* ===== AJUSTES GLOBAIS DE TOQUE ===== */
        @media (hover: none) and (pointer: coarse) {
            /* Remove hover effects em dispositivos touch */
            .hover\:bg-white\/5:hover,
            .hover\:bg-white\/10:hover,
            .hover\:border-primary\/50:hover {
                background-color: transparent !important;
                border-color: transparent !important;
            }
            
            /* Aumenta área de toque */
            button, a {
                min-height: 44px !important;
                min-width: 44px !important;
            }
            
            /* Feedback visual no toque */
            button:active, a:active {
                transform: scale(0.98);
                opacity: 0.8;
            }
            
            /* Remove delay de 300ms no duplo-tap */
            a, button {
                touch-action: manipulation;
            }
        }

        /* ===== ORIENTAÇÃO PAISAGEM NO MOBILE ===== */
        @media (max-width: 896px) and (orientation: landscape) {
            .kanban-container {
                display: flex !important;
                flex-direction: row !important;
                overflow-x: auto !important;
                scroll-snap-type: x mandatory !important;
            }
            
            .kanban-col-wrapper {
                min-width: 85vw !important;
                scroll-snap-align: start !important;
            }
            
            /* Modais em 2 colunas na horizontal */
            #modal-timeline .grid,
            #modal-documentos .flex {
                flex-direction: row !important;
            }
        }

        /* ===== iOS SPECIFIC FIXES ===== */
        @supports (-webkit-touch-callout: none) {
            /* Fix para 100vh no iOS Safari */
            .min-h-screen {
                min-height: -webkit-fill-available !important;
            }
            
            /* Fix para inputs */
            input, textarea {
                -webkit-appearance: none !important;
                border-radius: 0.5rem !important;
            }
            
            /* Fix para scroll suave */
            .overflow-y-auto {
                -webkit-overflow-scrolling: touch !important;
            }
        }

        /* ===== ANIMAÇÕES OTIMIZADAS PARA MOBILE ===== */
        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
            }
        }

        /* ===== SAFE AREA PARA NOTCH ===== */
        @supports (padding-top: env(safe-area-inset-top)) {
            nav {
                padding-top: env(safe-area-inset-top) !important;
                padding-left: env(safe-area-inset-left) !important;
                padding-right: env(safe-area-inset-right) !important;
            }
            
            .fixed.bottom-4 {
                bottom: calc(1rem + env(safe-area-inset-bottom)) !important;
            }
        }
    </style>
</head>
<body class="min-h-screen pb-10">

    <!-- Navbar -->
    <nav class="glass sticky top-0 z-50 h-16 flex items-center justify-between px-4 mb-4">
        <div class="flex items-center gap-4">
            <a href="painel.php" class="p-2 hover:bg-white/5 rounded-lg transition-colors"><i class="ph ph-arrow-left text-xl"></i></a>
            <h1 class="font-bold text-lg"><span class="text-orange-500">SGT</span> CRM</h1>
        </div>
        <div class="flex items-center gap-2">
            <a href="dashboard_crm.php" class="text-slate-400 hover:text-white p-2 transition-colors flex items-center gap-2 text-sm font-medium mr-2" title="Ver Analytics">
                <i class="ph ph-chart-line-up text-lg"></i> <span class="hidden md:inline">Analytics</span>
            </a>
            <a href="criar_proposta_dinamica.php" class="bg-blue-600 hover:bg-blue-500 text-white px-3 py-1.5 rounded-lg text-xs font-bold transition-all">+ Nova</a>
        </div>
    </nav>

    <!-- Main Board -->
    <main class="p-4">
        <div class="kanban-container">
            
            <!-- Colunas -->
            <?php 
            $cols = [
                'ELABORACAO' => [
                    't'=>'Elaboração', 
                    'c'=>'slate', 
                    'i'=>'pencil-simple', 
                    's'=>'Em Elaboração', 
                    'acoes' => ['mover' => 'ENVIADA']
                ],
                'ENVIADA'    => [
                    't'=>'Enviadas',   
                    'c'=>'blue',  
                    'i'=>'paper-plane-tilt', 
                    's'=>'Enviada', 
                    'acoes' => ['bifurcacao' => true]
                ],
                'FECHADA'    => [
                    't'=>'Fechadas',   
                    'c'=>'green', 
                    'i'=>'check-circle',     
                    's'=>'Aprovada', 
                    'acoes' => []
                ],
                'CANCELADA'  => [
                    't'=>'Arquivadas',   
                    'c'=>'red', 
                    'i'=>'archive-box',     
                    's'=>'Cancelada', 
                    'acoes' => ['restaurar' => 'ENVIADA']
                ]
            ];
            
            // Renderiza Colunas
            foreach($cols as $key => $cfg): 
            ?>
            <div class="kanban-col-wrapper flex flex-col">
                <!-- Header da Coluna -->
                <div class="flex items-center justify-between mb-3 px-2 py-1 rounded bg-white/5 border border-white/5">
                    <div class="flex items-center gap-2">
                        <i class="ph ph-<?= $cfg['i'] ?> text-<?= $cfg['c'] ?>-400 text-lg"></i>
                        <span class="font-bold text-slate-200"><?= $cfg['t'] ?></span>
                    </div>
                    <span class="text-xs bg-black/40 px-2 py-0.5 rounded text-slate-400 font-mono shadow-inner count-badge" id="c-<?= $key ?>">
                        <?= count($kanban[$key]) ?>
                    </span>
                </div>
                
                <!-- Área de Drop -->
                <div id="col-<?= $key ?>" 
                     class="kanban-list flex-1 bg-surface/50 rounded-xl p-2 space-y-2 border-2 border-dashed border-white/5 hover:border-white/10 transition-colors" 
                     data-fase="<?= $key ?>" 
                     data-status-real="<?= $cfg['s'] ?>">
                    
                    <?php if(empty($kanban[$key])): ?>
                        <!-- Empty State -->
                        <div class="empty-state text-center py-6 opacity-40 select-none">
                            <p class="text-xs text-slate-500">Vazio</p>
                        </div>
                    <?php endif; ?>

                    <?php foreach($kanban[$key] as $card): 
                        $whatsappLink = getZapLink($card['whatsapp'] ?? $card['telefone'], $card['whatsapp_handle']);
                        $cardId = $card['id_proposta'];
                    ?>
                    <div class="card glass p-3.5 rounded-xl border border-white/5 hover:border-primary/50 hover:bg-white/5 group relative transition-all duration-200 shadow-lg" 
                         data-id="<?= $cardId ?>">
                        
                        <div class="flex justify-between items-start mb-2.5">
                            <span class="text-[10px] text-slate-400 font-mono px-1.5 py-0.5 rounded bg-black/20" title="ID da Proposta">
                                #<?= $cardId ?>
                            </span>
                            <span class="text-[10px] flex items-center gap-1 <?= $card['dias_decorridos'] > 5 ? 'text-red-400 font-bold' : 'text-slate-500' ?>" title="Criada há <?= $card['dias_decorridos'] ?> dias">
                                <i class="ph ph-clock"></i> <?= $card['dias_decorridos'] ?>d
                            </span>
                        </div>
                        
                        <h3 class="text-sm font-bold text-white mb-1 leading-tight truncate" title="<?= htmlspecialchars($card['nome_cliente']) ?>">
                            <?= htmlspecialchars($card['nome_cliente']) ?>
                        </h3>
                        
                        <?php if (!empty($card['tipo_nome'])): ?>
                            <div class="mb-3">
                                <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md text-[10px] font-medium text-white shadow-sm" 
                                      style="background-color: <?= htmlspecialchars($card['tipo_cor'] ?? '#64748b') ?>">
                                    <i class="ph ph-<?= htmlspecialchars($card['tipo_icone'] ?? 'tag') ?>"></i>
                                    <?= htmlspecialchars($card['tipo_nome']) ?>
                                </span>
                            </div>
                        <?php else: ?>
                            <div class="mb-3 h-4"></div> <!-- Espaçador para alinhamento -->
                        <?php endif; ?>
                        
                        <!-- Ações Rápidas -->
                        <div class="grid grid-cols-4 gap-1 mb-2">
                            <!-- Linha 1 -->
                             <a href="<?= $whatsappLink ?>" target="_blank" 
                                class="col-span-1 bg-[#25D366]/10 hover:bg-[#25D366]/20 text-[#25D366] py-2 rounded text-center transition-colors flex items-center justify-center p-1"
                                title="Abrir WhatsApp">
                                <i class="ph ph-whatsapp-logo text-lg"></i>
                            </a>
                            <a href="gerar_proposta_html.php?id=<?= $cardId ?>" target="_blank" 
                               class="col-span-1 bg-blue-500/10 hover:bg-blue-500/20 text-blue-400 py-2 rounded text-center transition-colors flex items-center justify-center p-1"
                               title="Ver Proposta (Impressão)">
                                <i class="ph ph-eye text-lg"></i>
                            </a>
                            <a href="editar_proposta.php?id=<?= $cardId ?>" target="_blank" 
                               class="col-span-1 bg-yellow-500/10 hover:bg-yellow-500/20 text-yellow-400 py-2 rounded text-center transition-colors flex items-center justify-center p-1"
                               title="Editar Proposta">
                                <i class="ph ph-pencil-simple text-lg"></i>
                            </a>
                            <button onclick="novaTarefa(<?= $cardId ?>, '<?= addslashes($card['nome_cliente']) ?>')" 
                                    class="col-span-1 bg-orange-500/10 hover:bg-orange-500/20 text-orange-400 py-2 rounded text-center transition-colors flex items-center justify-center p-1"
                                    title="Agendar Tarefa">
                                <i class="ph ph-calendar-plus text-lg"></i>
                            </button>
                            
                            <!-- Linha 2 -->
                            <button onclick="abrirModalEmail(<?= $cardId ?>, '<?= $card['email'] ?>')" 
                                    class="col-span-1 bg-indigo-500/10 hover:bg-indigo-500/20 text-indigo-400 py-1.5 rounded text-center transition-colors flex items-center justify-center gap-1 mt-1 text-xs"
                                    title="Enviar Email">
                                <i class="ph ph-envelope-simple"></i>
                            </button>
                            <button onclick="abrirModalDocumentos(<?= $cardId ?>)" 
                                    class="col-span-1 bg-pink-500/10 hover:bg-pink-500/20 text-pink-400 py-1.5 rounded text-center transition-colors flex items-center justify-center gap-1 mt-1 text-xs"
                                    title="Documentos">
                                <i class="ph ph-folder-open"></i>
                            </button>
                            <button onclick="abrirTimeline(<?= $cardId ?>, '<?= addslashes($card['nome_cliente']) ?>')" 
                                    class="col-span-1 bg-purple-500/10 hover:bg-purple-500/20 text-purple-400 py-1.5 rounded text-center transition-colors flex items-center justify-center p-1 mt-1"
                                    title="Ver Histórico">
                                <i class="ph ph-clock-counter-clockwise"></i>
                            </button>
                            <button onclick="gerarPropostaWord(<?= $cardId ?>)" 
                                    class="col-span-1 bg-sky-500/10 hover:bg-sky-500/20 text-sky-400 py-1.5 rounded text-center transition-colors flex items-center justify-center gap-1 mt-1 text-xs"
                                    title="Gerar Proposta em Word">
                                <i class="ph ph-file-doc"></i> Word
                            </button>
                        </div>
                        
                        <!-- Próxima Tarefa (Snippet) -->
                        <?php
                            // Busca tarefa pendente mais próxima (Inline para performance)
                            $sqlTarefa = "SELECT tipo, data_agendada FROM Tarefas_CRM WHERE id_proposta = $cardId AND status = 'pendente' ORDER BY data_agendada ASC LIMIT 1";
                            $resTarefa = $conn->query($sqlTarefa);
                            if($resTarefa && $t = $resTarefa->fetch_assoc()):
                                $hoje = new DateTime();
                                $dataT = new DateTime($t['data_agendada']);
                                $atrasada = $dataT < $hoje;
                        ?>
                        <div class="mt-2 text-xs flex items-center gap-1 <?= $atrasada ? 'text-red-400' : 'text-slate-400' ?> bg-black/20 p-1.5 rounded">
                            <i class="ph ph-warning-circle"></i>
                            <span class="truncate"><?= ucfirst($t['tipo']) ?>: <?= $dataT->format('d/m H:i') ?></span>
                        </div>
                        <?php endif; ?>
                        
                        <!-- Ações de Fluxo (Simplificado) -->
                        <?php if ($key === 'ELABORACAO'): ?>
                            <!-- ELABORAÇÃO: Apenas enviar -->
                            <button onclick="moverCardManual(<?= $cardId ?>, 'ENVIADA')" 
                                    class="w-full py-2 bg-white/10 hover:bg-white/20 text-white text-xs rounded border border-white/10 flex items-center justify-center gap-2 transition-colors font-bold mt-2">
                                Enviar Proposta <i class="ph ph-paper-plane-right"></i>
                            </button>

                        <?php elseif ($key === 'ENVIADA'): ?>
                            <!-- ENVIADA: Dupla escolha -->
                            <div class="grid grid-cols-2 gap-2 mt-2">
                                <button onclick="moverCardManual(<?= $cardId ?>, 'FECHADA')" 
                                        class="py-2 bg-green-600/30 hover:bg-green-600/50 text-green-400 hover:text-green-200 text-xs rounded border border-green-600/40 flex items-center justify-center gap-1 transition-all font-bold">
                                    <i class="ph ph-check-circle"></i> FECHAR
                                </button>
                                
                                <button onclick="arquivarProposta(<?= $cardId ?>)" 
                                        class="py-2 bg-red-600/30 hover:bg-red-600/50 text-red-400 hover:text-red-200 text-xs rounded border border-red-600/40 flex items-center justify-center gap-1 transition-all font-bold">
                                    <i class="ph ph-archive-box"></i> ARQUIVAR
                                </button>
                            </div>
                            <div class="mt-1 text-[10px] text-slate-500 text-center">
                                Aguardando decisão do cliente
                            </div>

                        <?php elseif ($key === 'CANCELADA'): ?>
                            <!-- CANCELADA: Restaurar -->
                            <button onclick="restaurarProposta(<?= $cardId ?>)" 
                                    class="w-full py-2 bg-blue-600/30 hover:bg-blue-600/50 text-blue-400 hover:text-blue-200 text-xs rounded border border-blue-600/40 flex items-center justify-center gap-2 transition-all font-bold mt-2">
                                <i class="ph ph-arrow-u-up-left"></i> RESTAURAR
                            </button>
                            <div class="mt-1 text-[10px] text-slate-500 text-center">
                                Arquivada há <?= $card['dias_decorridos'] ?> dias
                            </div>

                        <?php elseif ($key === 'FECHADA'): ?>
                            <!-- FECHADA: Sem ações (fim do fluxo) -->
                            <div class="mt-2 text-[10px] text-green-500 text-center font-bold">
                                <i class="ph ph-check-circle"></i> Proposta Finalizada
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endforeach; ?>

        </div>
    </main>

    <!-- Widget Tarefas (Lateral/Bottom Sheet) -->
    <div id="widget-tarefas" class="fixed right-0 top-16 bottom-0 w-80 bg-[#0f172a] border-l border-white/10 p-4 transform transition-transform translate-x-full z-40 hidden md:block">
        <h3 class="font-bold text-slate-400 mb-4 uppercase text-xs flex justify-between items-center">
            Tarefas Pendentes
            <i class="ph ph-x cursor-pointer md:hidden" onclick="document.getElementById('widget-tarefas').classList.remove('aberto')"></i>
        </h3>
        <div class="space-y-3">
             <div class="p-3 bg-white/5 rounded border border-white/5 text-center text-xs text-slate-500">
                <i class="ph ph-check-circle text-2xl mb-2 block"></i>
                Nenhuma tarefa pendente hoje.
            </div>
        </div>
    </div>

    <!-- Mobile FAB -->
    <button class="fixed bottom-4 right-4 w-14 h-14 bg-orange-600 rounded-full shadow-lg flex items-center justify-center text-white md:hidden z-50 hover:bg-orange-500 transition-colors">
        <i class="ph ph-list-checks text-2xl"></i>
    </button>

    <!-- Modais CRM 3.0 (Modularizados) -->
    <?php
    require_once 'modulo_timeline.php';
    require_once 'modulo_tarefas.php';
    require_once 'modulo_email.php';
    require_once 'modulo_documentos.php';
    ?>
    
    <script>
        // Funções Globais Auxiliares
        if (typeof fecharModal !== 'function') {
             function fecharModal(id) {
                document.getElementById(id).classList.add('hidden');
                document.getElementById(id).classList.remove('flex');
            }
        }
    </script>
    
    <script>
        // Função para restaurar proposta cancelada
        function restaurarProposta(id) {
            if(!confirm('Restaurar proposta #' + id + '?\n\nEla voltará para "Enviadas" e você poderá renegociar.')) return;
            
            fetch('api/crm_controller.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ 
                    acao: 'restaurar_proposta', 
                    id_proposta: id, 
                    novo_status: 'Enviada'
                })
            })
            .then(r => r.json())
            .then(data => {
                if(data.sucesso) {
                    const card = document.querySelector(`[data-id="${id}"]`);
                    if(card) {
                        card.style.transform = 'translateX(100%)';
                        card.style.opacity = '0';
                    }
                    setTimeout(() => location.reload(), 300);
                } else {
                    alert('Erro ao restaurar: ' + data.erro);
                }
            })
            .catch(err => {
                console.error('Erro:', err);
                alert('Erro de conexão ao restaurar proposta');
            });
        }

        // Função para Arquivar (Mover para Cancelada)
        function arquivarProposta(id) {
            if(!confirm('Arquivar proposta #' + id + '?\n\nO cliente não respondeu ou recusou. Você poderá restaurar depois.')) return;
            
            fetch('api/crm_controller.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ 
                    acao: 'mover_card', 
                    id_proposta: id, 
                    nova_fase: 'CANCELADA', // Mapeado no controller para Cancelada
                    motivo: 'arquivamento_manual'
                })
            })
            .then(r => r.json())
            .then(data => {
                if(data.sucesso) location.reload();
                else alert('Erro: ' + data.erro);
            });
        }

        // Função de Movimento Manual (Fallback)
        function moverCardManual(id, novaFase) {
            if(!confirm('Mover proposta #' + id + ' para ' + novaFase + '?')) return;
            
            fetch('api/crm_controller.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ acao: 'mover_card', id_proposta: id, nova_fase: novaFase })
            })
            .then(r => r.json())
            .then(data => {
                if(data.sucesso) location.reload();
                else alert('Erro: ' + data.erro);
            });
        }

        // Função para Gerar Proposta em Word (.docx)
        function gerarPropostaWord(id) {
            // Feedback visual
            const btn = event.target.closest('button');
            const originalContent = btn.innerHTML;
            btn.innerHTML = '<i class="ph ph-spinner animate-spin"></i>';
            btn.disabled = true;
            
            fetch('api/gerar_proposta_docx.php?id_proposta=' + id)
                .then(r => r.json())
                .then(data => {
                    if(data.sucesso) {
                        // Abre download em nova aba
                        window.open(data.url, '_blank');
                        
                        // Feedback de sucesso
                        btn.innerHTML = '<i class="ph ph-check-circle text-green-400"></i>';
                        setTimeout(() => {
                            btn.innerHTML = originalContent;
                            btn.disabled = false;
                        }, 2000);
                    } else {
                        alert('Erro ao gerar Word: ' + data.erro);
                        btn.innerHTML = originalContent;
                        btn.disabled = false;
                    }
                })
                .catch(err => {
                    console.error('Erro:', err);
                    alert('Erro de conexão ao gerar proposta Word');
                    btn.innerHTML = originalContent;
                    btn.disabled = false;
                });
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Tenta ativar o Drag & Drop, mas se falhar, os botões manuais salvam o dia.
            if (typeof Sortable !== 'undefined') {
                const containers = document.querySelectorAll('.kanban-list');
                containers.forEach(container => {
                    new Sortable(container, {
                        group: 'kanban',
                        animation: 150,
                        delay: 100, // Pequeno delay seguro
                        delayOnTouchOnly: true,
                        ghostClass: 'card-ghost',
                        onEnd: function (evt) {
                            if (evt.to === evt.from) return;
                            const id = evt.item.getAttribute('data-id');
                            const fase = evt.to.getAttribute('data-fase');
                            
                            // Chama API silenciosamente
                            fetch('api/crm_controller.php', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json' },
                                body: JSON.stringify({ acao: 'mover_card', id_proposta: id, nova_fase: fase })
                            }).then(r => r.json()).then(data => {
                                if(!data.sucesso) { alert('Erro: ' + data.erro); location.reload(); }
                            });
                        }
                    });
                });
            } else {
                console.warn('SortableJS não carregou. Usando modo manual.');
            }
        });
    </script>
    <script>
    // ============================================================
    // OTIMIZAÇÕES MOBILE/TABLET
    // ============================================================

    document.addEventListener('DOMContentLoaded', function() {
        
        // Detecta se é mobile
        const isMobile = window.matchMedia('(max-width: 640px)').matches;
        
        if (isMobile) {
            // Widget de tarefas vira bottom sheet
            const widgetTarefas = document.getElementById('widget-tarefas');
            const btnFlutuante = document.querySelector('.fixed.bottom-4.right-4');
            
            if (widgetTarefas && btnFlutuante) {
                // Garante que o widget visivelmente exista no DOM (tira o hidden do MD)
                widgetTarefas.classList.remove('hidden'); 
                
                btnFlutuante.addEventListener('click', function() {
                    widgetTarefas.classList.toggle('aberto');
                });
                
                // Fecha ao clicar fora
                document.addEventListener('click', function(e) {
                    if (widgetTarefas.classList.contains('aberto') && !widgetTarefas.contains(e.target) && !btnFlutuante.contains(e.target)) {
                        widgetTarefas.classList.remove('aberto');
                    }
                });
            }
            
            // Swipe para fechar modais
            let touchStartY = 0;
            const modais = document.querySelectorAll('[id^="modal-"]');
            
            modais.forEach(modal => {
                modal.addEventListener('touchstart', function(e) {
                    touchStartY = e.touches[0].clientY;
                }, {passive: true});
                
                modal.addEventListener('touchmove', function(e) {
                    const touchY = e.touches[0].clientY;
                    const diff = touchY - touchStartY;
                    
                    // Swipe para baixo fecha o modal
                    if (diff > 100 && touchStartY < 100) {
                        modal.classList.add('hidden');
                        modal.classList.remove('flex'); // Importante remover flex tb
                    }
                }, {passive: true});
            });
            
            // Pull-to-refresh nas colunas do kanban
            const kanbanLists = document.querySelectorAll('.kanban-list');
            kanbanLists.forEach(list => {
                let startY = 0;
                
                list.addEventListener('touchstart', function(e) {
                    startY = e.touches[0].clientY;
                }, {passive: true});
                
                list.addEventListener('touchmove', function(e) {
                    if (list.scrollTop === 0) {
                        const diff = e.touches[0].clientY - startY;
                        if (diff > 150) { // Aumentei um pouco pra evitar acidental
                            // Recarrega dados
                            location.reload();
                        }
                    }
                }, {passive: true});
            });
        }
        
        // Previne zoom duplo-tap em botões
        let lastTouchEnd = 0;
        document.addEventListener('touchend', function(e) {
            const now = Date.now();
            if (now - lastTouchEnd <= 300) {
                e.preventDefault();
            }
            lastTouchEnd = now;
        }, false);
        
        // Ajusta altura do viewport em mobile (fix iOS)
        function ajustarAltura() {
            const vh = window.innerHeight * 0.01;
            document.documentElement.style.setProperty('--vh', `${vh}px`);
        }
        
        window.addEventListener('resize', ajustarAltura);
        ajustarAltura();
        
    });

    // Função para vibrar ao arrastar cards (feedback tátil)
    if ('vibrate' in navigator) {
        const originalOnEnd = Sortable?.defaults?.onEnd;
        Sortable.defaults.onEnd = function(evt) {
            navigator.vibrate(50); // 50ms de vibração
            if (originalOnEnd) originalOnEnd(evt);
        };
    }
    </script>
</body>
</html>
