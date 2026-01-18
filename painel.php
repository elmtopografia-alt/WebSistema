<?php
// ==========================================================
// ARQUIVO: painel.php (VERSÃO ESTÁVEL / BLINDADA)
// ==========================================================

// 1. FORÇA MOSTRAR ERROS (Para não dar tela branca nunca mais)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'config.php';
require_once 'db.php';

if (session_status() === PHP_SESSION_NONE) { session_start(); }

// Identifica o usuário (Fallback para evitar erro se a sessão cair)
$id_usuario = $_SESSION['usuario_id'] ?? $_SESSION['id_criador'] ?? 1;
$nome_usuario = $_SESSION['usuario_nome'] ?? 'Usuário';
$ambiente = $_SESSION['ambiente'] ?? 'producao';

// Lógica para Menu
$is_demo = ($ambiente === 'demo');
$modo_suporte = isset($_SESSION['admin_original_id']);
$primeiro_nome = explode(' ', trim($nome_usuario))[0];

// =========================================================================
// 2. MOTOR DE ATUALIZAÇÃO (Recebe o clique do botão)
// =========================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax_acao'])) {
    // Limpa buffer para garantir que só saia JSON
    while (ob_get_level()) { ob_end_clean(); }
    header('Content-Type: application/json');

    try {
        $conn = Database::getProd();
        $id = intval($_POST['id']);
        $status = $_POST['status'];
        
        // Tradução Visual -> Banco
        $status_banco = $status;
        if(strpos($status, 'Aceita') !== false) $status_banco = 'Aprovada';
        
        $sql = "UPDATE Propostas SET status = ? WHERE id_proposta = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $status_banco, $id);
        
        if ($stmt->execute()) {
            echo json_encode(['sucesso' => true]);
        } else {
            echo json_encode(['sucesso' => false, 'msg' => 'Erro SQL: ' . $conn->error]);
        }
    } catch (Exception $e) {
        echo json_encode(['sucesso' => false, 'msg' => 'Erro PHP: ' . $e->getMessage()]);
    }
    exit; // FIM DO AJAX
}

// =========================================================================
// 3. CARREGAMENTO DA TELA
// =========================================================================
try {
    $conn = ($ambiente === 'demo') ? Database::getDemo() : Database::getProd();

    // A. KPIs (Contadores)
    $kpi = ['elaborada'=>0, 'enviada'=>0, 'aprovada'=>0, 'cancelada'=>0];
    $sqlKPI = "SELECT status, count(*) as qtd FROM Propostas WHERE id_criador = ? GROUP BY status";
    $stmtKPI = $conn->prepare($sqlKPI);
    $stmtKPI->bind_param('i', $id_usuario);
    $stmtKPI->execute();
    $resKPI = $stmtKPI->get_result();

    while($row = $resKPI->fetch_assoc()) {
        $st = mb_strtolower($row['status']);
        if (strpos($st, 'aprov')!==false || strpos($st, 'conclu')!==false || strpos($st, 'aceit')!==false) $kpi['aprovada'] += $row['qtd'];
        elseif (strpos($st, 'envia')!==false) $kpi['enviada'] += $row['qtd'];
        elseif (strpos($st, 'cancel')!==false || strpos($st, 'perdid')!==false) $kpi['cancelada'] += $row['qtd'];
        else $kpi['elaborada'] += $row['qtd'];
    }

    // B. Lista de Propostas
    $sqlLista = "SELECT p.*, c.nome_cliente 
                 FROM Propostas p 
                 LEFT JOIN Clientes c ON p.id_cliente = c.id_cliente 
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
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SGT | Painel</title>
    
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
                            accent: '#FF7518',   // Abóbora Vibrante
                            action: '#EA580C',
                            glow: '#4fc3f7',
                        }
                    },
                    animation: {
                        'float': 'float 6s ease-in-out infinite',
                    },
                    keyframes: {
                        float: {
                            '0%, 100%': { transform: 'translateY(0)' },
                            '50%': { transform: 'translateY(-10px)' },
                        }
                    }
                }
            }
        }
    </script>

    <style>
        /* Glassmorphism */
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
            border-color: rgba(255, 117, 24, 0.6);
            transform: translateY(-5px);
            box-shadow: 0 12px 35px -10px rgba(255, 117, 24, 0.3);
        }
        
        /* Background */
        body {
            background: radial-gradient(circle at center, #0a2e5c 0%, #001224 100%);
            min-height: 100vh;
        }

        /* Scrollbar */
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #001224; }
        ::-webkit-scrollbar-thumb { background: #1e40af; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #FF7518; }
        
        /* Dropdown custom */
        .custom-select {
            appearance: none;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%2394a3b8' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
            background-position: right 0.5rem center;
            background-repeat: no-repeat;
            background-size: 1.5em 1.5em;
        }
    </style>
</head>
<body class="text-slate-200 font-sans antialiased selection:bg-brand-accent selection:text-brand-dark">

    <!-- Navbar -->
    <nav class="w-full glass-panel sticky top-0 z-50 border-b border-white/10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <!-- Logo -->
                <div class="flex items-center gap-4">
                    <img src="<?= BASE_URL ?>/assets/img/logo_sgt.png" alt="SGT" class="h-10">
                    <?php if($is_demo): ?>
                        <span class="px-2 py-0.5 rounded bg-yellow-500/20 text-yellow-400 text-[10px] font-bold border border-yellow-500/30 uppercase tracking-wider">DEMO</span>
                    <?php endif; ?>
                </div>

                <!-- Menu Desktop -->
                <div class="hidden md:flex items-center gap-4">
                    <a href="minha_empresa.php" class="text-sm font-medium text-slate-300 hover:text-white transition-colors flex items-center gap-2">
                        <i class="ph ph-gear"></i> Empresa
                    </a>
                    <a href="meus_clientes.php" class="text-sm font-medium text-slate-300 hover:text-white transition-colors flex items-center gap-2">
                        <i class="ph ph-users"></i> Clientes
                    </a>
                    <a href="admin_parametros.php" class="text-sm font-medium text-slate-300 hover:text-white transition-colors flex items-center gap-2">
                        <i class="ph ph-list-checks"></i> Cadastro
                    </a>

                    <?php if($is_demo): ?>
                        <a href="contratar.php" class="px-4 py-2 bg-green-600 hover:bg-green-500 text-white text-xs font-bold rounded-lg transition-colors shadow-lg shadow-green-900/20 uppercase tracking-wider">
                            Contratar
                        </a>
                    <?php endif; ?>

                    <?php if(!$is_demo && !$modo_suporte && isset($_SESSION['perfil']) && $_SESSION['perfil'] == 'admin'): ?>
                        <a href="admin_usuarios.php" class="px-3 py-1.5 bg-yellow-600/20 hover:bg-yellow-600/40 text-yellow-400 text-xs font-bold rounded-lg border border-yellow-600/30 transition-colors">
                            Admin
                        </a>
                    <?php endif; ?>

                    <a href="criar_proposta.php" class="px-4 py-2 bg-brand-accent hover:bg-brand-action text-white text-sm font-bold rounded-lg transition-colors shadow-lg shadow-brand-accent/20 flex items-center gap-2">
                        <i class="ph ph-plus-bold"></i> Nova Proposta
                    </a>

                    <!-- User Dropdown -->
                    <div class="relative group ml-2">
                        <button class="flex items-center gap-2 text-white font-medium hover:text-brand-accent transition-colors">
                            <div class="w-8 h-8 rounded-full bg-brand-surface border border-white/10 flex items-center justify-center text-brand-accent">
                                <i class="ph ph-user"></i>
                            </div>
                            <span><?= htmlspecialchars($primeiro_nome) ?></span>
                            <i class="ph ph-caret-down text-xs text-slate-500"></i>
                        </button>
                        <!-- Dropdown Menu -->
                        <div class="absolute right-0 mt-2 w-48 glass-panel rounded-xl shadow-xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 transform origin-top-right z-50">
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

    <!-- Aviso Demo -->
    <?php if($is_demo && isset($_SESSION['validade_demo'])): 
        $hoje = new DateTime();
        $validade = new DateTime($_SESSION['validade_demo']);
        $diff = $hoje->diff($validade);
        $diasRestantes = $diff->invert ? 0 : $diff->days;
        
        $corAlerta = ($diasRestantes <= 1) ? 'border-red-500/50 bg-red-500/10 text-red-200' : 'border-yellow-500/50 bg-yellow-500/10 text-yellow-200';
        $textoAlerta = ($diasRestantes <= 1) ? "⚠️ SEU TESTE ACABA EM BREVE! Seus dados serão apagados em menos de 24h." : "⏳ Restam $diasRestantes dias de teste.";
    ?>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-6">
        <div class="glass-panel rounded-xl p-4 border <?= $corAlerta ?> flex flex-col sm:flex-row justify-between items-center gap-4">
            <div>
                <strong class="block sm:inline"><?= $textoAlerta ?></strong>
                <span class="text-sm opacity-80 hidden sm:inline ml-2">Contrate agora para manter seu acesso.</span>
            </div>
            <a href="contratar.php" class="px-4 py-2 bg-white/10 hover:bg-white/20 text-white text-xs font-bold rounded-lg transition-colors uppercase tracking-wider border border-white/20">
                Contratar Agora
            </a>
        </div>
    </div>
    <?php endif; ?>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        <!-- Header Page -->
        <div class="glass-panel rounded-2xl p-6 mb-8 flex justify-between items-center bg-brand-surface/50">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-brand-accent/10 flex items-center justify-center text-brand-accent border border-brand-accent/20">
                    <i class="ph ph-chart-line-up text-2xl"></i>
                </div>
                <div>
                    <h1 class="font-display text-2xl font-bold text-white">Evolução Comercial</h1>
                    <p class="text-sm text-slate-400">Visão Geral do seu Negócio</p>
                </div>
            </div>
            <div class="hidden md:block text-right">
                <span class="text-xs text-slate-500 uppercase font-bold tracking-wider">Hoje</span>
                <div class="font-display text-xl font-bold text-white"><?php echo date('d/m/Y'); ?></div>
            </div>
        </div>

        <!-- KPIs -->
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6 mb-8">
            <!-- Elaboradas -->
            <div class="glass-card p-5 rounded-2xl flex items-center gap-4 group">
                <div class="w-14 h-14 rounded-xl bg-yellow-500/10 flex items-center justify-center text-yellow-500 border border-yellow-500/20 group-hover:scale-110 transition-transform">
                    <i class="ph ph-file-text text-3xl"></i>
                </div>
                <div>
                    <p class="text-xs text-slate-400 uppercase font-bold tracking-wider">Elaboradas</p>
                    <h3 class="font-display text-3xl font-bold text-white"><?= $kpi['elaborada'] ?></h3>
                    <p class="text-xs text-yellow-500/80">Rascunhos</p>
                </div>
            </div>

            <!-- Enviadas -->
            <div class="glass-card p-5 rounded-2xl flex items-center gap-4 group">
                <div class="w-14 h-14 rounded-xl bg-blue-500/10 flex items-center justify-center text-blue-400 border border-blue-500/20 group-hover:scale-110 transition-transform">
                    <i class="ph ph-paper-plane-tilt text-3xl"></i>
                </div>
                <div>
                    <p class="text-xs text-slate-400 uppercase font-bold tracking-wider">Enviadas</p>
                    <h3 class="font-display text-3xl font-bold text-white"><?= $kpi['enviada'] ?></h3>
                    <p class="text-xs text-blue-400/80">Aguardando</p>
                </div>
            </div>

            <!-- Aprovadas -->
            <div class="glass-card p-5 rounded-2xl flex items-center gap-4 group border-green-500/20 bg-green-500/5">
                <div class="w-14 h-14 rounded-xl bg-green-500/10 flex items-center justify-center text-green-400 border border-green-500/20 group-hover:scale-110 transition-transform">
                    <i class="ph ph-check-circle text-3xl"></i>
                </div>
                <div>
                    <p class="text-xs text-green-400/80 uppercase font-bold tracking-wider">Aprovadas</p>
                    <h3 class="font-display text-3xl font-bold text-white"><?= $kpi['aprovada'] ?></h3>
                    <p class="text-xs text-green-400 font-bold">Sucesso!</p>
                </div>
            </div>

            <!-- Canceladas -->
            <div class="glass-card p-5 rounded-2xl flex items-center gap-4 group border-red-500/20">
                <div class="w-14 h-14 rounded-xl bg-red-500/10 flex items-center justify-center text-red-400 border border-red-500/20 group-hover:scale-110 transition-transform">
                    <i class="ph ph-x-circle text-3xl"></i>
                </div>
                <div>
                    <p class="text-xs text-slate-400 uppercase font-bold tracking-wider">Canceladas</p>
                    <h3 class="font-display text-3xl font-bold text-white"><?= $kpi['cancelada'] ?></h3>
                    <p class="text-xs text-red-400/80">Perdidas</p>
                </div>
            </div>
        </div>

        <!-- Dashboard Include Wrapper -->
        <div class="mb-8">
            <?php include 'dashboard_include.php'; ?>
        </div>

        <!-- Tabela de Propostas -->
        <div class="glass-panel rounded-2xl overflow-hidden">
            <div class="p-6 border-b border-white/5 flex flex-col md:flex-row justify-between items-center gap-4">
                <h2 class="font-display text-lg font-bold text-white flex items-center gap-2">
                    <i class="ph ph-list-dashes text-brand-accent"></i> Últimas Propostas
                </h2>
                <div class="relative w-full md:w-64">
                    <i class="ph ph-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-500"></i>
                    <input type="text" id="filtroTabela" 
                           class="w-full bg-black/20 border border-white/10 rounded-lg py-2 pl-10 text-sm text-white focus:outline-none focus:border-brand-accent focus:ring-1 focus:ring-brand-accent transition-all placeholder-slate-600"
                           placeholder="Buscar proposta...">
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse" id="tabelaPrincipal">
                    <thead>
                        <tr class="bg-white/5 text-xs text-slate-400 uppercase tracking-wider border-b border-white/5">
                            <th class="px-6 py-4 font-semibold">Data</th>
                            <th class="px-6 py-4 font-semibold">Número</th>
                            <th class="px-6 py-4 font-semibold">Cliente</th>
                            <th class="px-6 py-4 font-semibold">Status</th>
                            <th class="px-6 py-4 font-semibold text-right">Valor</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5 text-sm">
                        <?php while($row = $resultLista->fetch_assoc()): 
                             $id = $row['id_proposta'];
                             $st = mb_strtolower($row['status']);
                             
                             // Cores do Status
                             $statusClass = 'bg-yellow-500/10 text-yellow-500 border-yellow-500/20';
                             if(strpos($st, 'aprov')!==false) $statusClass = 'bg-green-500/10 text-green-400 border-green-500/20';
                             elseif(strpos($st, 'envia')!==false) $statusClass = 'bg-blue-500/10 text-blue-400 border-blue-500/20';
                             elseif(strpos($st, 'cancel')!==false) $statusClass = 'bg-red-500/10 text-red-400 border-red-500/20';
                        ?>
                        <tr class="hover:bg-white/5 transition-colors group">
                            <td class="px-6 py-4 text-slate-400 font-mono">
                                <?= date('d/m/Y', strtotime($row['data_criacao'])); ?>
                            </td>
                            <td class="px-6 py-4 font-bold text-white">
                                <?= $row['numero_proposta']; ?>
                            </td>
                            <td class="px-6 py-4 text-slate-300">
                                <?= htmlspecialchars($row['nome_cliente_salvo']); ?>
                            </td>
                            <td class="px-6 py-4">
                                <!-- Custom Dropdown Trigger -->
                                <div class="relative inline-block text-left dropdown-container">
                                    <button type="button" 
                                            class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg border text-xs font-bold uppercase tracking-wider transition-all <?= $statusClass ?> hover:brightness-110"
                                            onclick="toggleDropdown(<?= $id ?>)"
                                            id="btn-<?= $id ?>">
                                        <span id="label-<?= $id ?>"><?= $row['status'] ?></span>
                                        <i class="ph ph-caret-down"></i>
                                    </button>
                                    
                                    <!-- Dropdown Menu -->
                                    <div id="dropdown-<?= $id ?>" class="hidden absolute left-0 mt-2 w-40 glass-panel rounded-xl shadow-xl z-50 overflow-hidden">
                                        <div class="py-1">
                                            <a href="#" onclick="trocarStatus(<?= $id ?>, 'Em Elaboração'); return false;" class="block px-4 py-2 text-xs text-yellow-500 hover:bg-white/5 font-bold">🟡 Em Elaboração</a>
                                            <a href="#" onclick="trocarStatus(<?= $id ?>, 'Enviada'); return false;" class="block px-4 py-2 text-xs text-blue-400 hover:bg-white/5 font-bold">🔵 Enviada</a>
                                            <a href="#" onclick="trocarStatus(<?= $id ?>, 'Aprovada'); return false;" class="block px-4 py-2 text-xs text-green-400 hover:bg-white/5 font-bold">🟢 Aprovada</a>
                                            <div class="h-px bg-white/10 my-1"></div>
                                            <a href="#" onclick="trocarStatus(<?= $id ?>, 'Cancelada'); return false;" class="block px-4 py-2 text-xs text-red-400 hover:bg-white/5 font-bold">🔴 Cancelada</a>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="font-bold text-white mb-1">R$ <?= number_format($row['valor_final_proposta'], 2, ',', '.'); ?></div>
                                <a href="editar_proposta.php?id=<?= $id ?>" class="inline-flex items-center gap-1 text-xs text-yellow-500 hover:text-white transition-colors mr-3">
                                    <i class="ph ph-pencil-simple"></i> Editar
                                </a>
                                <a href="relatorio_proposta.php?id=<?= $id ?>" class="inline-flex items-center gap-1 text-xs text-brand-accent hover:text-white transition-colors">
                                    <i class="ph ph-file-text"></i> Relatório
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <!-- Modal Novidades (Dark Mode) -->
    <div id="modalNovidades" class="fixed inset-0 z-[60] hidden">
        <div class="absolute inset-0 bg-black/80 backdrop-blur-sm" onclick="closeModal()"></div>
        <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-full max-w-lg p-4">
            <div class="glass-panel bg-brand-surface rounded-2xl border border-brand-primary shadow-2xl overflow-hidden">
                <div class="bg-brand-primary/50 p-4 flex justify-between items-center border-b border-white/10">
                    <h5 class="font-bold text-white flex items-center gap-2">
                        <i class="ph ph-sparkle text-yellow-400"></i> Novidades da Versão <span id="novidadeVersao"></span>
                    </h5>
                    <button onclick="closeModal()" class="text-slate-400 hover:text-white"><i class="ph ph-x text-lg"></i></button>
                </div>
                <div class="p-6">
                    <h4 id="novidadeTitulo" class="font-bold text-xl text-white mb-3"></h4>
                    <div id="novidadeDescricao" class="text-slate-300 text-sm leading-relaxed"></div>
                </div>
                <div class="p-4 bg-black/20 text-right">
                    <button onclick="closeModal()" class="px-6 py-2 bg-brand-accent hover:bg-brand-action text-white font-bold rounded-lg transition-colors shadow-lg">
                        Entendi, vamos lá!
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Dropdown Logic
        function toggleDropdown(id) {
            // Fecha outros
            document.querySelectorAll('[id^="dropdown-"]').forEach(el => {
                if (el.id !== 'dropdown-' + id) el.classList.add('hidden');
            });
            
            const dropdown = document.getElementById('dropdown-' + id);
            dropdown.classList.toggle('hidden');
        }

        // Fecha dropdowns ao clicar fora
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.dropdown-container')) {
                document.querySelectorAll('[id^="dropdown-"]').forEach(el => el.classList.add('hidden'));
            }
        });

        // AJAX Status Change
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

            fetch('painel.php', { method: 'POST', body: dados })
            .then(r => r.json())
            .then(res => {
                if (res.sucesso) {
                    window.location.reload(); 
                } else {
                    alert('Erro: ' + res.msg);
                    label.innerText = originalText;
                    btn.classList.remove('opacity-50', 'cursor-not-allowed');
                }
            })
            .catch(e => {
                console.error(e);
                alert('Erro de conexão.');
                label.innerText = originalText;
                btn.classList.remove('opacity-50', 'cursor-not-allowed');
            })
            .finally(() => document.body.style.cursor = 'default');
        }

        // Search Filter
        const filtro = document.getElementById('filtroTabela');
        if(filtro){
            filtro.addEventListener('keyup', function() {
                let valor = this.value.toLowerCase();
                let linhas = document.querySelectorAll('#tabelaPrincipal tbody tr');
                linhas.forEach(tr => {
                    let texto = tr.innerText.toLowerCase();
                    tr.style.display = texto.includes(valor) ? '' : 'none';
                });
            });
        }

        // Modal Logic
        function closeModal() {
            document.getElementById('modalNovidades').classList.add('hidden');
        }

        // Check Novidades
        document.addEventListener('DOMContentLoaded', function() {
            fetch('api_check_novidades.php')
                .then(r => r.json())
                .then(res => {
                    if (res.tem_novidade) {
                        document.getElementById('novidadeVersao').innerText = res.versao;
                        document.getElementById('novidadeTitulo').innerText = res.titulo;
                        document.getElementById('novidadeDescricao').innerHTML = res.descricao;
                        document.getElementById('modalNovidades').classList.remove('hidden');

                        fetch('api_check_novidades.php', { method: 'POST', body: new URLSearchParams({acao: 'marcar_lida', versao: res.versao}) });
                    }
                });
        });
    </script>
</body>
</html>