<?php
// Nome do Arquivo: minha_empresa.php
// Função: Configuração da Empresa. BLOQUEIA EDIÇÃO SE FOR DEMO.

require_once 'session_validator.php';
require_once 'config.php';
require_once 'db.php';

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
                            dark: '#0a0f1a',      // Was #001e3c -> SGT Background
                            primary: '#111827',   // Was #0a2e5c -> SGT Surface
                            surface: 'rgba(255,255,255,0.03)', // Was #132f4c -> SGT Glass Ultra Light
                            accent: '#f97316',    // Was #FF7518 -> SGT Orange
                            action: '#ea580c',    // Was #EA580C -> SGT Orange Dark
                            glow: '#3b82f6',      // Was #4fc3f7 -> SGT Blue
                        }
                    }
                }
            }
        }
    </script>

    <style>
        /* Glassmorphism - Remapped to SGT Visuals */
        .glass-panel {
            background: rgba(17, 24, 39, 0.7); /* SGT Glass Base */
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        
        /* Background - Remapped to SGT Dark */
        body {
            background-color: #0a0f1a;
            color: #f8fafc;
            /* Optional: Subtle grid pattern if desired */
            background-image: linear-gradient(rgba(255, 255, 255, 0.03) 1px, transparent 1px),
            linear-gradient(90deg, rgba(255, 255, 255, 0.03) 1px, transparent 1px);
            background-size: 50px 50px;
            min-height: 100vh;
        }

        /* Scrollbar */
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #0a0f1a; }
        ::-webkit-scrollbar-thumb { background: #374151; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #f97316; }
    </style>
</head>
<body class="text-slate-200 font-sans antialiased selection:bg-brand-accent selection:text-brand-dark">

    <!-- Navbar -->
    <?php include 'components/navbar.php'; ?>
    
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
        <?php if ($is_demo): ?>
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

                    <?php $logo_atual = !empty($empresa['logo_caminho']) && file_exists(__DIR__ . '/' . $empresa['logo_caminho']) ? $empresa['logo_caminho'] : 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyMDAgNjAiIHdpZHRoPSIyMDAiIGhlaWdodD0iNjAiPjxkZWZzPjxsaW5lYXJHcmFkaWVudCBpZD0iZ3JhZEljb24iIHgxPSIwJSIgeTE9IjAlIiB4Mj0iMTAwJSIgeTI9IjEwMCUiPjxzdG9wIG9mZnNldD0iMCUiIHN0eWxlPSJzdG9wLWNvbG9yOiNmOTczMTY7c3RvcC1vcGFjaXR5OjEiIC8+PHN0b3Agb2Zmc2V0PSIxMDAlIiBzdHlsZT0ic3RvcC1jb2xvcjojZWE1ODBjO3N0b3Atb3BhY2l0eToxIiAvPjwvbGluZWFyR3JhZGllbnQ+PC9kZWZzPjxyZWN0IHg9IjUiIHk9IjUiIHdpZHRoPSI1MCIgaGVpZ2h0PSI1MCIgcng9IjEwIiBmaWxsPSJ1cmwoI2dyYWRJY29uKSIvPjxwYXRoIGQ9Ik0yOCAxNSBMMjIgMjggTDMwIDI4IEwyNiA0NSBMMzggMzAgTDMwIDMwIEwzNCAxNSBaIiBmaWxsPSJ3aGl0ZSIvPjx0ZXh0IHg9IjY1IiB5PSIzNSIgZm9udC1mYW1pbHk9IkFyaWFsLCBzYW5zLXNlcmlmIiBmb250LXdlaWdodD0iYm9sZCIgZm9udC1zaXplPSIyNCIgZmlsbD0id2hpdGUiPlNHVDwvdGV4dD48dGV4dCB4PSI2NSIgeT0iNTAiIGZvbnQtZmFtaWx5PSJBcmlhbCwgc2Fucy1zZXJpZiIgZm9udC1zaXplPSIxMiIgZmlsbD0iI2ZiOTIzYyI+UHJvcG9zdGFzPC90ZXh0Pjwvc3ZnPg=='; ?>

                    <!-- Novo Container Flexível -->
                    <div class="w-full min-h-[200px] bg-white/5 rounded-xl border border-white/10 flex items-center justify-center p-4 mb-4 overflow-hidden relative group"
                        style="background-image: radial-gradient(#ffffff 1px, transparent 1px); background-size: 10px 10px; background-color: rgba(255,255,255,0.02);">
                        <img src="<?php echo $logo_atual; ?>?t=<?php echo time(); ?>" alt="Logo" class="max-w-full max-h-[180px] object-contain shadow-sm">

                        <?php if (!$is_demo): ?>
                            <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity flex flex-col gap-2 items-center justify-center">
                                <span class="text-white text-sm font-medium">Alterar Logo</span>
                            </div>
                        <?php endif; ?>
                    </div>

                    <?php if (!$is_demo): ?>
                        <form action="upload_logo.php" method="POST" enctype="multipart/form-data" class="mb-3">
                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
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

                <!-- Card: Configuração do Header -->
                <div class="glass-panel rounded-2xl p-6 mt-6">
                    <h3 class="font-display text-lg font-bold text-white mb-4">
                        <i class="ph ph-layout text-brand-accent mr-2"></i>Modo do Header
                    </h3>

                    <p class="text-sm text-slate-400 mb-4">Escolha como o logo será exibido no cabeçalho das propostas:</p>

                    <?php
                    $header_mode = $empresa['header_logo_mode'] ?? 'full';
                    $logo_icon = $empresa['logo_icon_caminho'] ?? '';
                    ?>

                    <div class="space-y-3">
                        <!-- Opção: Logo Completo -->
                        <label class="flex items-center gap-3 p-3 rounded-xl cursor-pointer transition-all border <?= $header_mode === 'full' ? 'border-brand-accent bg-brand-accent/10' : 'border-white/10 bg-white/5 hover:bg-white/10'; ?> <?= $is_demo ? 'pointer-events-none opacity-60' : ''; ?>">
                            <input type="radio" name="header_logo_mode" value="full"
                                <?= $header_mode === 'full' ? 'checked' : ''; ?>
                                <?= $is_demo ? 'disabled' : ''; ?>
                                onchange="updateHeaderModePreview(this.value)"
                                class="text-brand-accent focus:ring-brand-accent bg-black/20 border-white/20">
                            <div class="flex-1">
                                <span class="text-white font-medium flex items-center gap-2">
                                    <i class="ph ph-image text-lg text-brand-glow"></i> Logo Completo
                                </span>
                                <span class="text-xs text-slate-400 block mt-1">Header expandido com logo grande e nome da empresa</span>
                            </div>
                            <div class="w-16 h-8 bg-white/10 rounded flex items-center justify-center">
                                <i class="ph ph-rectangle text-brand-glow text-2xl"></i>
                            </div>
                        </label>

                        <!-- Opção: Logo Ícone -->
                        <label class="flex items-center gap-3 p-3 rounded-xl cursor-pointer transition-all border <?= $header_mode === 'icon' ? 'border-brand-accent bg-brand-accent/10' : 'border-white/10 bg-white/5 hover:bg-white/10'; ?> <?= $is_demo ? 'pointer-events-none opacity-60' : ''; ?>">
                            <input type="radio" name="header_logo_mode" value="icon"
                                <?= $header_mode === 'icon' ? 'checked' : ''; ?>
                                <?= $is_demo ? 'disabled' : ''; ?>
                                onchange="updateHeaderModePreview(this.value)"
                                class="text-brand-accent focus:ring-brand-accent bg-black/20 border-white/20">
                            <div class="flex-1">
                                <span class="text-white font-medium flex items-center gap-2">
                                    <i class="ph ph-app-window text-lg text-brand-accent"></i> Logo Compacto
                                </span>
                                <span class="text-xs text-slate-400 block mt-1">Header minimalista com ícone pequeno</span>
                            </div>
                            <div class="w-8 h-8 bg-white/10 rounded flex items-center justify-center">
                                <i class="ph ph-square text-brand-accent text-lg"></i>
                            </div>
                        </label>
                    </div>

                    <?php if (!$is_demo): ?>
                        <!-- Upload do Ícone (aparece quando modo icon está selecionado) -->
                        <div id="iconUploadSection" class="mt-4 p-4 bg-black/20 rounded-xl border border-white/10 <?= $header_mode !== 'icon' ? 'hidden' : ''; ?>">
                            <h4 class="text-sm font-bold text-brand-glow mb-3">
                                <i class="ph ph-upload mr-1"></i> Logo Ícone (Opcional)
                            </h4>

                            <?php if (!empty($logo_icon) && file_exists(__DIR__ . '/' . $logo_icon)): ?>
                                <div class="flex items-center gap-3 mb-3 p-2 bg-green-500/10 border border-green-500/30 rounded-lg">
                                    <img src="<?= htmlspecialchars($logo_icon) ?>?t=<?= time() ?>" alt="Ícone atual" class="w-10 h-10 object-contain rounded">
                                    <span class="text-xs text-green-400">Ícone configurado</span>
                                </div>
                            <?php endif; ?>

                            <form action="upload_logo_icon.php" method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                <input type="file" name="logo_icon"
                                    class="block w-full text-xs text-slate-400
                                   file:mr-2 file:py-1.5 file:px-3
                                   file:rounded-lg file:border-0
                                   file:text-xs file:font-semibold
                                   file:bg-brand-surface file:text-white
                                   file:cursor-pointer hover:file:bg-white/10
                                   transition-all" accept="image/png, image/jpeg" required>
                                <p class="text-xs text-slate-500 mt-2">Recomendado: imagem quadrada (ex: 100x100px)</p>
                                <button type="submit" class="mt-2 w-full py-2 bg-brand-primary hover:bg-brand-surface border border-white/10 rounded-lg text-white font-medium transition-colors flex items-center justify-center gap-2 text-sm">
                                    <i class="ph ph-upload-simple"></i> Enviar Ícone
                                </button>
                            </form>
                        </div>

                        <!-- Botão Salvar Modo -->
                        <button type="button" onclick="salvarModoHeader()" class="mt-4 w-full py-2.5 bg-green-600 hover:bg-green-500 text-white font-bold rounded-lg transition-colors shadow-lg shadow-green-900/20 flex items-center justify-center gap-2 text-sm">
                            <i class="ph ph-check-circle"></i> Aplicar Modo
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
                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

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
                        <?php if (isset($_GET['msg']) && $_GET['msg'] == 'sucesso'): ?>
                            <span class="text-green-400 text-sm font-bold flex items-center gap-1"><i class="ph ph-check-circle"></i> Salvo!</span>
                        <?php endif; ?>
                    </div>

                    <form action="salvar_dados_empresa.php" method="POST">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
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

                            <?php if (!$is_demo): ?>
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

    <!-- JavaScript para Modo do Header -->
    <script>
        function updateHeaderModePreview(mode) {
            const iconUploadSection = document.getElementById('iconUploadSection');
            const radioLabels = document.querySelectorAll('input[name="header_logo_mode"]');

            // Atualizar estilos visuais dos cards
            radioLabels.forEach(radio => {
                const label = radio.closest('label');
                if (radio.value === mode) {
                    label.classList.remove('border-white/10', 'bg-white/5');
                    label.classList.add('border-brand-accent', 'bg-brand-accent/10');
                } else {
                    label.classList.remove('border-brand-accent', 'bg-brand-accent/10');
                    label.classList.add('border-white/10', 'bg-white/5');
                }
            });

            // Mostrar/esconder seção de upload do ícone
            if (iconUploadSection) {
                if (mode === 'icon') {
                    iconUploadSection.classList.remove('hidden');
                } else {
                    iconUploadSection.classList.add('hidden');
                }
            }
        }

        function salvarModoHeader() {
            const mode = document.querySelector('input[name="header_logo_mode"]:checked').value;
            const csrfToken = '<?= $_SESSION['csrf_token'] ?>';

            // Mostrar loading
            const btn = event.target.closest('button');
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="ph ph-spinner animate-spin"></i> Salvando...';
            btn.disabled = true;

            fetch('salvar_modo_header.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `header_logo_mode=${mode}&csrf_token=${csrfToken}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        btn.innerHTML = '<i class="ph ph-check-circle"></i> Salvo!';
                        btn.classList.remove('bg-green-600', 'hover:bg-green-500');
                        btn.classList.add('bg-green-500');

                        setTimeout(() => {
                            btn.innerHTML = originalText;
                            btn.classList.remove('bg-green-500');
                            btn.classList.add('bg-green-600', 'hover:bg-green-500');
                            btn.disabled = false;
                        }, 2000);
                    } else {
                        throw new Error(data.error || 'Erro desconhecido');
                    }
                })
                .catch(error => {
                    btn.innerHTML = '<i class="ph ph-warning"></i> Erro!';
                    btn.classList.remove('bg-green-600');
                    btn.classList.add('bg-red-600');
                    console.error('Erro:', error);

                    setTimeout(() => {
                        btn.innerHTML = originalText;
                        btn.classList.remove('bg-red-600');
                        btn.classList.add('bg-green-600', 'hover:bg-green-500');
                        btn.disabled = false;
                    }, 3000);
                });
        }
    </script>

</body>

</html>