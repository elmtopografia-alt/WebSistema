<?php
// Nome do Arquivo: meus_clientes.php
// Função: Lista de Clientes com MENU UNIVERSAL.

session_start();
require_once 'config.php';
require_once 'db.php';

if (!isset($_SESSION['usuario_id'])) { header("Location: login.php"); exit; }

$id_usuario = $_SESSION['usuario_id'];
$ambiente_atual = $_SESSION['ambiente'] ?? 'indefinido';
$is_demo = ($ambiente_atual === 'demo');
$modo_suporte = isset($_SESSION['admin_original_id']);
$nome_usuario = $_SESSION['usuario_nome'] ?? 'Usuário';
$primeiro_nome = explode(' ', trim($nome_usuario))[0];

$conn = $is_demo ? Database::getDemo() : Database::getProd();

$clientes = [];
try {
    $sql = "SELECT * FROM Clientes WHERE id_criador = ? ORDER BY id_cliente DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $id_usuario);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) $clientes[] = $row;
} catch (Exception $e) { }
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clientes | SGT</title>
    
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
                            accent: '#FF7518',
                            action: '#EA580C',
                            glow: '#4fc3f7',
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
                    <a href="painel.php" class="text-sm font-medium text-slate-300 hover:text-white transition-colors flex items-center gap-2">
                        <i class="ph ph-house"></i> Painel
                    </a>
                    <a href="minha_empresa.php" class="text-sm font-medium text-slate-300 hover:text-white transition-colors flex items-center gap-2">
                        <i class="ph ph-gear"></i> Empresa
                    </a>
                    <a href="meus_clientes.php" class="text-sm font-bold text-white flex items-center gap-2">
                        <i class="ph ph-users-three-fill text-brand-accent"></i> Clientes
                    </a>
                    
                    <div class="h-6 w-px bg-white/10 mx-2"></div>

                    <!-- User Dropdown -->
                    <div class="relative group">
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

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        <!-- Header Page -->
        <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-brand-accent/10 flex items-center justify-center text-brand-accent border border-brand-accent/20">
                    <i class="ph ph-address-book text-2xl"></i>
                </div>
                <div>
                    <h1 class="font-display text-2xl font-bold text-white">Carteira de Clientes</h1>
                    <p class="text-sm text-slate-400">Total: <strong class="text-white"><?php echo count($clientes); ?></strong> cadastrados</p>
                </div>
            </div>
            <a href="form_cliente.php" class="px-6 py-3 bg-brand-action hover:bg-brand-accent text-white font-bold rounded-xl shadow-lg shadow-orange-900/20 transition-all hover:scale-105 flex items-center gap-2">
                <i class="ph ph-user-plus text-xl"></i> Novo Cliente
            </a>
        </div>

        <?php if(isset($_GET['msg']) && $_GET['msg'] == 'sucesso'): ?>
            <div class="glass-panel rounded-xl p-4 mb-6 border-l-4 border-green-500 flex items-center gap-3">
                <i class="ph ph-check-circle text-green-400 text-xl"></i>
                <span class="text-green-100 font-medium">Operação realizada com sucesso!</span>
            </div>
        <?php endif; ?>

        <!-- Tabela de Clientes -->
        <div class="glass-panel rounded-2xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-black/20 text-xs text-slate-400 uppercase border-b border-white/5">
                        <tr>
                            <th class="px-6 py-4 font-semibold">Nome</th>
                            <th class="px-6 py-4 font-semibold">Empresa</th>
                            <th class="px-6 py-4 font-semibold">Contatos</th>
                            <th class="px-6 py-4 font-semibold text-right">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        <?php if (empty($clientes)): ?>
                            <tr>
                                <td colspan="4" class="px-6 py-12 text-center text-slate-500">
                                    <i class="ph ph-users-three text-4xl mb-3 block opacity-30"></i>
                                    Nenhum cliente encontrado. Cadastre o primeiro!
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($clientes as $c): $inicial = !empty($c['nome_cliente']) ? strtoupper(substr($c['nome_cliente'], 0, 1)) : '?'; ?>
                            <tr class="hover:bg-white/5 transition-colors group">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-full bg-brand-surface border border-white/10 flex items-center justify-center text-brand-accent font-bold text-lg shadow-inner">
                                            <?php echo $inicial; ?>
                                        </div>
                                        <div>
                                            <div class="font-bold text-white group-hover:text-brand-accent transition-colors"><?php echo htmlspecialchars($c['nome_cliente']); ?></div>
                                            <div class="text-xs text-slate-500"><?php echo htmlspecialchars($c['email']); ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-slate-300"><?php echo htmlspecialchars($c['empresa'] ?? '-'); ?></div>
                                    <div class="text-xs text-slate-500"><?php echo htmlspecialchars($c['cnpj_cpf']); ?></div>
                                </td>
                                <td class="px-6 py-4">
                                    <?php if($c['celular']): ?>
                                        <div class="flex items-center gap-2 text-slate-300 text-xs">
                                            <i class="ph ph-whatsapp-logo text-green-400 text-lg"></i>
                                            <?php echo $c['celular']; ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-slate-600">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <a href="form_cliente.php?id=<?php echo $c['id_cliente']; ?>" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-white/5 hover:bg-brand-accent hover:text-white text-slate-400 transition-all" title="Editar">
                                        <i class="ph ph-pencil-simple"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</body>
</html>