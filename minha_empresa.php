<?php
// Nome do Arquivo: minha_empresa.php
// Função: Configuração da Empresa. BLOQUEIA EDIÇÃO SE FOR DEMO.

session_start();
require_once 'config.php';
require_once 'db.php';

// 1. Segurança
if (!isset($_SESSION['usuario_id'])) { header("Location: login.php"); exit; }

$id_usuario = $_SESSION['usuario_id'];
$ambiente_atual = $_SESSION['ambiente'] ?? 'indefinido';
$is_demo = ($ambiente_atual === 'demo');

// Lógica de Menu
$modo_suporte = isset($_SESSION['admin_original_id']);
$nome_usuario = $_SESSION['usuario_nome'] ?? 'Usuário';
$primeiro_nome = explode(' ', trim($nome_usuario))[0];

$conn = $is_demo ? Database::getDemo() : Database::getProd();

// Carregar dados
$empresa = [];
$stmt = $conn->prepare("SELECT * FROM DadosEmpresa WHERE id_criador = ? LIMIT 1");
$stmt->bind_param('i', $id_usuario);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows > 0) {
    $empresa = $res->fetch_assoc();
} else {
    $stmtIns = $conn->prepare("INSERT INTO DadosEmpresa (id_criador, Empresa, CNPJ) VALUES (?, 'Minha Empresa Demo', '')");
    $stmtIns->bind_param('i', $id_usuario);
    $stmtIns->execute();
    header("Refresh:0");
    exit;
}

// DEFINE SE É SOMENTE LEITURA
$readonly = $is_demo ? 'disabled' : '';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Minha Empresa | SGT</title>
    
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
        .glass-card {
            background: rgba(19, 47, 76, 0.6);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.12);
            transition: all 0.3s ease;
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
                    <a href="minha_empresa.php" class="text-sm font-bold text-white flex items-center gap-2">
                        <i class="ph ph-gear-fill text-brand-accent"></i> Empresa
                    </a>
                    <a href="meus_clientes.php" class="text-sm font-medium text-slate-300 hover:text-white transition-colors flex items-center gap-2">
                        <i class="ph ph-users"></i> Clientes
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
        <div class="glass-panel rounded-2xl p-6 mb-8 flex justify-between items-center bg-brand-surface/50">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-brand-accent/10 flex items-center justify-center text-brand-accent border border-brand-accent/20">
                    <i class="ph ph-buildings text-2xl"></i>
                </div>
                <div>
                    <h1 class="font-display text-2xl font-bold text-white">Minha Empresa</h1>
                    <p class="text-sm text-slate-400">Configure seus dados para as propostas</p>
                </div>
            </div>
        </div>

        <!-- Alerta Demo -->
        <?php if($is_demo): ?>
        <div class="glass-panel rounded-xl p-4 border border-yellow-500/50 bg-yellow-500/10 flex items-center justify-between mb-8">
            <div class="flex items-center gap-3">
                <i class="ph ph-lock-key text-2xl text-yellow-500"></i>
                <div>
                    <h5 class="font-bold text-yellow-200">Edição Bloqueada na Versão Demo</h5>
                    <p class="text-sm text-yellow-200/70">Para personalizar os dados, contrate o plano completo.</p>
                </div>
            </div>
            <a href="contratar.php" class="px-4 py-2 bg-yellow-500 hover:bg-yellow-400 text-black font-bold rounded-lg transition-colors text-sm">
                DESBLOQUEAR
            </a>
        </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            
            <!-- Coluna Esquerda: Logo -->
            <div class="md:col-span-1">
                <div class="glass-panel rounded-2xl p-6">
                    <h3 class="font-display text-lg font-bold text-white mb-4">Logotipo</h3>
                    
                    <?php $logo_atual = !empty($empresa['logo_caminho']) && file_exists(__DIR__ . '/' . $empresa['logo_caminho']) ? $empresa['logo_caminho'] : 'assets/img/sem_logo.png'; ?>
                    
                    <!-- Novo Container Flexível -->
                    <div class="w-full min-h-[200px] bg-white/5 rounded-xl border border-white/10 flex items-center justify-center p-4 mb-4 overflow-hidden relative group" 
                         style="background-image: radial-gradient(#ffffff 1px, transparent 1px); background-size: 10px 10px; background-color: rgba(255,255,255,0.02);">
                        <img src="<?php echo $logo_atual; ?>?t=<?php echo time(); ?>" alt="Logo" class="max-w-full max-h-[180px] object-contain shadow-sm">
                        
                        <?php if(!$is_demo): ?>
                            <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity flex flex-col gap-2 items-center justify-center">
                                <span class="text-white text-sm font-medium">Alterar Logo</span>
                            </div>
                        <?php endif; ?>
                    </div>

                    <?php if(!$is_demo): ?>
                        <form action="upload_logo.php" method="POST" enctype="multipart/form-data" class="mb-3">
                            <label class="block w-full cursor-pointer">
                                <span class="sr-only">Escolher arquivo</span>
                                <input type="file" name="logo" class="block w-full text-xs text-slate-400
                                  file:mr-4 file:py-2 file:px-4
                                  file:rounded-lg file:border-0
                                  file:text-xs file:font-semibold
                                  file:bg-brand-accent file:text-white
                                  file:cursor-pointer hover:file:bg-brand-action
                                  transition-all" accept="image/png, image/jpeg" required>
                            </label>
                            <button type="submit" class="mt-2 w-full py-2 bg-brand-primary hover:bg-brand-surface border border-white/10 rounded-lg text-white font-bold transition-colors flex items-center justify-center gap-2 text-sm">
                                <i class="ph ph-upload-simple"></i> Enviar Imagem
                            </button>
                        </form>

                        <!-- Botão Gerador de Logo -->
                        <div class="border-t border-white/10 pt-3">
                            <button type="button" onclick="document.getElementById('modalGeradorLogo').classList.remove('hidden')" class="w-full py-2 bg-brand-surface hover:bg-white/5 border border-white/10 rounded-lg text-brand-glow font-bold transition-colors flex items-center justify-center gap-2 text-sm">
                                <i class="ph ph-magic-wand"></i> Criar Logo Texto
                            </button>
                        </div>
                    <?php else: ?>
                        <button disabled class="w-full py-2 bg-white/5 border border-white/10 rounded-lg text-slate-500 font-bold cursor-not-allowed flex items-center justify-center gap-2">
                            <i class="ph ph-lock"></i> Upload Bloqueado
                        </button>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Modal Gerador de Logo -->
             <div id="modalGeradorLogo" class="fixed inset-0 z-[60] hidden">
                <div class="absolute inset-0 bg-black/80 backdrop-blur-sm" onclick="document.getElementById('modalGeradorLogo').classList.add('hidden')"></div>
                <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-full max-w-sm p-4">
                    <div class="glass-panel bg-brand-surface rounded-2xl border border-brand-primary shadow-2xl overflow-hidden">
                        <div class="bg-brand-primary/50 p-4 flex justify-between items-center border-b border-white/10">
                            <h5 class="font-bold text-white flex items-center gap-2">
                                <i class="ph ph-magic-wand text-brand-glow"></i> Gerador de Logo
                            </h5>
                            <button onclick="document.getElementById('modalGeradorLogo').classList.add('hidden')" class="text-slate-400 hover:text-white"><i class="ph ph-x text-lg"></i></button>
                        </div>
                        <form action="gerar_logo.php" method="POST" class="p-6">
                            
                            <div class="mb-4">
                                <label class="block text-xs text-slate-400 mb-2">Cor do Texto</label>
                                <div class="flex items-center gap-3">
                                    <input type="color" name="cor" value="#0a2e5c" class="h-10 w-10 rounded border border-white/20 p-0 cursor-pointer">
                                    <span class="text-xs text-slate-500">Escolha a cor principal</span>
                                </div>
                            </div>

                            <div class="mb-6">
                                <label class="block text-xs text-slate-400 mb-2">Estilo do Texto</label>
                                <div class="space-y-2">
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="radio" name="modo_texto" value="empresa" checked class="text-brand-accent focus:ring-brand-accent bg-black/20 border-white/10">
                                        <span class="text-sm text-white">Usar Nome da Empresa</span>
                                    </label>
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="radio" name="modo_texto" value="nome_topografia" class="text-brand-accent focus:ring-brand-accent bg-black/20 border-white/10">
                                        <span class="text-sm text-white">Primeiro Nome + Topografia</span>
                                    </label>
                                </div>
                            </div>

                            <button type="submit" class="w-full py-2 bg-brand-accent hover:bg-brand-action text-white font-bold rounded-lg transition-colors shadow-lg">
                                Gerar e Salvar Logo
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Coluna Direita: Formulário -->
            <div class="md:col-span-2">
                <div class="glass-panel rounded-2xl p-6 <?php echo $is_demo ? 'opacity-75 pointer-events-none' : ''; ?>">
                    <div class="flex justify-between items-center mb-6 border-b border-white/10 pb-4">
                        <h3 class="font-display text-lg font-bold text-white">Dados Cadastrais</h3>
                        <?php if(isset($_GET['msg']) && $_GET['msg']=='sucesso'): ?>
                            <span class="text-green-400 text-sm font-bold flex items-center gap-1"><i class="ph ph-check-circle"></i> Salvo!</span>
                        <?php endif; ?>
                    </div>

                    <form action="salvar_dados_empresa.php" method="POST">
                        <fieldset <?php echo $readonly; ?>>
                            
                            <!-- Identidade -->
                            <h4 class="text-brand-accent text-sm font-bold uppercase tracking-wider mb-4">Identidade</h4>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                                <div class="md:col-span-2">
                                    <label class="block text-xs text-slate-400 mb-1">Razão Social / Nome</label>
                                    <input type="text" name="Empresa" class="w-full bg-black/20 border border-white/10 rounded-lg px-4 py-2 text-white focus:border-brand-accent focus:ring-1 focus:ring-brand-accent outline-none transition-colors" value="<?php echo htmlspecialchars($empresa['Empresa']); ?>" required>
                                </div>
                                <div>
                                    <label class="block text-xs text-slate-400 mb-1">CNPJ / CPF</label>
                                    <input type="text" name="CNPJ" class="w-full bg-black/20 border border-white/10 rounded-lg px-4 py-2 text-white focus:border-brand-accent focus:ring-1 focus:ring-brand-accent outline-none transition-colors" value="<?php echo htmlspecialchars($empresa['CNPJ']); ?>">
                                </div>
                            </div>

                            <div class="mb-6">
                                <label class="block text-xs text-slate-400 mb-1">Endereço Completo</label>
                                <input type="text" name="Endereco" class="w-full bg-black/20 border border-white/10 rounded-lg px-4 py-2 text-white focus:border-brand-accent focus:ring-1 focus:ring-brand-accent outline-none transition-colors" value="<?php echo htmlspecialchars($empresa['Endereco']); ?>">
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
                                <div class="md:col-span-3">
                                    <label class="block text-xs text-slate-400 mb-1">Cidade</label>
                                    <input type="text" name="Cidade" class="w-full bg-black/20 border border-white/10 rounded-lg px-4 py-2 text-white focus:border-brand-accent focus:ring-1 focus:ring-brand-accent outline-none transition-colors" value="<?php echo htmlspecialchars($empresa['Cidade']); ?>">
                                </div>
                                <div>
                                    <label class="block text-xs text-slate-400 mb-1">UF</label>
                                    <input type="text" name="Estado" class="w-full bg-black/20 border border-white/10 rounded-lg px-4 py-2 text-white focus:border-brand-accent focus:ring-1 focus:ring-brand-accent outline-none transition-colors" value="<?php echo htmlspecialchars($empresa['Estado']); ?>" maxlength="2">
                                </div>
                            </div>

                            <!-- Contatos -->
                            <h4 class="text-brand-accent text-sm font-bold uppercase tracking-wider mb-4">Contatos</h4>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
                                <div>
                                    <label class="block text-xs text-slate-400 mb-1">Telefone</label>
                                    <input type="text" name="Telefone" class="w-full bg-black/20 border border-white/10 rounded-lg px-4 py-2 text-white focus:border-brand-accent focus:ring-1 focus:ring-brand-accent outline-none transition-colors" value="<?php echo htmlspecialchars($empresa['Telefone']); ?>">
                                </div>
                                <div>
                                    <label class="block text-xs text-slate-400 mb-1">Celular</label>
                                    <input type="text" name="Celular" class="w-full bg-black/20 border border-white/10 rounded-lg px-4 py-2 text-white focus:border-brand-accent focus:ring-1 focus:ring-brand-accent outline-none transition-colors" value="<?php echo htmlspecialchars($empresa['Celular']); ?>">
                                </div>
                                <div>
                                    <label class="block text-xs text-slate-400 mb-1">WhatsApp</label>
                                    <input type="text" name="Whatsapp" class="w-full bg-black/20 border border-white/10 rounded-lg px-4 py-2 text-white focus:border-brand-accent focus:ring-1 focus:ring-brand-accent outline-none transition-colors" value="<?php echo htmlspecialchars($empresa['Whatsapp']); ?>">
                                </div>
                            </div>

                            <!-- Dados Bancários -->
                            <h4 class="text-brand-accent text-sm font-bold uppercase tracking-wider mb-4">Dados Bancários</h4>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                                <div>
                                    <label class="block text-xs text-slate-400 mb-1">Banco</label>
                                    <input type="text" name="Banco" class="w-full bg-black/20 border border-white/10 rounded-lg px-4 py-2 text-white focus:border-brand-accent focus:ring-1 focus:ring-brand-accent outline-none transition-colors" value="<?php echo htmlspecialchars($empresa['Banco']); ?>">
                                </div>
                                <div>
                                    <label class="block text-xs text-slate-400 mb-1">Agência</label>
                                    <input type="text" name="Agencia" class="w-full bg-black/20 border border-white/10 rounded-lg px-4 py-2 text-white focus:border-brand-accent focus:ring-1 focus:ring-brand-accent outline-none transition-colors" value="<?php echo htmlspecialchars($empresa['Agencia']); ?>">
                                </div>
                                <div>
                                    <label class="block text-xs text-slate-400 mb-1">Conta</label>
                                    <input type="text" name="Conta" class="w-full bg-black/20 border border-white/10 rounded-lg px-4 py-2 text-white focus:border-brand-accent focus:ring-1 focus:ring-brand-accent outline-none transition-colors" value="<?php echo htmlspecialchars($empresa['Conta']); ?>">
                                </div>
                            </div>
                            <div class="mb-8">
                                <label class="block text-xs text-slate-400 mb-1">Chave PIX</label>
                                <input type="text" name="PIX" class="w-full bg-black/20 border border-white/10 rounded-lg px-4 py-2 text-white focus:border-brand-accent focus:ring-1 focus:ring-brand-accent outline-none transition-colors" value="<?php echo htmlspecialchars($empresa['PIX']); ?>">
                            </div>

                            <?php if(!$is_demo): ?>
                            <div class="text-right">
                                <button type="submit" class="px-6 py-3 bg-green-600 hover:bg-green-500 text-white font-bold rounded-lg transition-colors shadow-lg shadow-green-900/20 flex items-center gap-2 ml-auto">
                                    <i class="ph ph-check-circle text-xl"></i> Salvar Alterações
                                </button>
                            </div>
                            <?php endif; ?>

                        </fieldset>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>