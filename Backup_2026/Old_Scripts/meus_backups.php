<?php
// Arquivo: meus_backups.php
// Função: Interface de Transparência de Dados e Backups
// Segurança: Acesso restrito via sessão e download mascarado via PHP

require_once 'session_validator.php'; // Já inclui auth e CSRF token
require_once 'config.php';
require_once 'db.php';
require_once 'BackupManager.php';

$id_usuario = $_SESSION['usuario_id'];
$nome_usuario = $_SESSION['usuario_nome'] ?? 'Usuário'; // Para navbar
$primeiro_nome = explode(' ', trim($nome_usuario))[0]; // Para navbar

$bm = new BackupManager($id_usuario);

$msg = '';
$tipo_msg = '';

// ==========================================================
// 1. PROCESSAR AÇÕES (POST/GET)
// ==========================================================

// A. GERAR BACKUP
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'gerar') {
    // Validação CSRF já feita nos formulários (VACINA)
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die("Erro de Segurança CSRF.");
    }

    try {
        $caminho = $bm->gerarBackupCompleto();
        if ($caminho) {
            $ext = pathinfo($caminho, PATHINFO_EXTENSION);
            $msg = "Backup completo gerado com sucesso! ($ext)";
            $tipo_msg = "success";
            
            // Aplica retenção para limpar velhos
            $res = $bm->aplicarPoliticaRetencao();
            if ($res['excluidos'] > 0) {
                $msg .= " (Limpeza automática removeu {$res['excluidos']} arquivos antigos).";
            }
        } else {
            $msg = "Erro ao gerar backup. Tente novamente.";
            $tipo_msg = "danger";
        }
    } catch (Exception $e) {
        $msg = "Erro Crítico: " . $e->getMessage();
        $tipo_msg = "danger";
    } catch (Error $e) {
        $msg = "Erro Crítico (Sistema): " . $e->getMessage();
        $tipo_msg = "danger";
    }
}

// B. DOWNLOAD DE ARQUIVO
if (isset($_GET['acao']) && $_GET['acao'] === 'baixar' && isset($_GET['arquivo'])) {
    $arquivo = basename($_GET['arquivo']); // Segurança contra Traversal
    $path = __DIR__ . '/backups/user_' . $id_usuario . '/' . $arquivo;

    if (file_exists($path)) {
        // Força download
        header('Content-Description: File Transfer');
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . $arquivo . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($path));
        readfile($path);
        exit;
    } else {
        $msg = "Arquivo não encontrado ou acesso negado.";
        $tipo_msg = "danger";
    }
}

// ==========================================================
// 2. LISTAGEM DE ARQUIVOS
// ==========================================================
$pastaUser = __DIR__ . '/backups/user_' . $id_usuario . '/';
$arquivos = [];
if (is_dir($pastaUser)) {
    $files = glob($pastaUser . '*.{zip,json}', GLOB_BRACE);
    if (!$files) $files = glob($pastaUser . '*.zip'); // Fallback para servidores sem GLOB_BRACE
    // Ordena do mais recente para o mais antigo
    usort($files, function($a, $b) { return filemtime($b) - filemtime($a); });
    
    foreach ($files as $f) {
        $arquivos[] = [
            'nome' => basename($f),
            'tamanho' => round(filesize($f) / 1024, 2) . ' KB',
            'data' => date('d/m/Y H:i:s', filemtime($f))
        ];
    }
}

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meus Backups | SGT</title>
    
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

    <?php include 'components/navbar.php'; ?>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        <!-- Header -->
        <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-brand-accent/10 flex items-center justify-center text-brand-accent border border-brand-accent/20">
                    <i class="ph ph-database text-2xl"></i>
                </div>
                <div>
                    <h1 class="font-display text-2xl font-bold text-white">Meus Dados & Backups</h1>
                    <p class="text-sm text-slate-400">Gerencie, exporte e proteja seus dados.</p>
                </div>
            </div>
            <a href="painel.php" class="text-sm text-slate-400 hover:text-white flex items-center gap-1 transition-colors">
                <i class="ph ph-arrow-left"></i> Voltar ao Painel
            </a>
        </div>

        <?php if ($msg): ?>
            <div class="glass-panel rounded-xl p-4 mb-6 border-l-4 <?= $tipo_msg == 'success' ? 'border-green-500' : 'border-red-500' ?> flex items-center gap-3">
                <i class="ph <?= $tipo_msg == 'success' ? 'ph-check-circle text-green-400' : 'ph-warning-circle text-red-400' ?> text-xl"></i>
                <span class="<?= $tipo_msg == 'success' ? 'text-green-100' : 'text-red-100' ?> font-medium"><?= htmlspecialchars($msg) ?></span>
            </div>
        <?php endif; ?>

        <!-- Política de Retenção -->
        <div class="glass-panel rounded-2xl p-6 mb-8">
            <h5 class="flex items-center gap-2 text-brand-glow font-bold mb-4">
                <i class="ph ph-shield-check text-xl"></i> Transparência e Política de Dados
            </h5>
            <p class="text-slate-300 mb-6 text-sm leading-relaxed">
                Para sua segurança jurídica e conformidade (LGPD), o SGT mantém seus dados salvos com a seguinte política de retenção automática. 
                Arquivos mais antigos são removidos automaticamente para segurança.
            </p>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-black/20 rounded-xl p-4 text-center border border-white/5">
                    <i class="ph ph-calendar-plus text-green-400 text-3xl mb-2"></i>
                    <h6 class="text-white font-bold text-sm">Diária</h6>
                    <span class="text-xs text-slate-400">Últimos 7 dias</span>
                </div>
                <div class="bg-black/20 rounded-xl p-4 text-center border border-white/5">
                    <i class="ph ph-calendar-blank text-yellow-400 text-3xl mb-2"></i>
                    <h6 class="text-white font-bold text-sm">Semanal</h6>
                    <span class="text-xs text-slate-400">Últimas 4 semanas</span>
                </div>
                <div class="bg-black/20 rounded-xl p-4 text-center border border-white/5">
                    <i class="ph ph-calendar text-red-400 text-3xl mb-2"></i>
                    <h6 class="text-white font-bold text-sm">Mensal</h6>
                    <span class="text-xs text-slate-400">Últimos 6 meses</span>
                </div>
            </div>
        </div>

        <!-- Layout Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- Coluna Esquerda: Ação -->
            <div class="lg:col-span-1">
                <div class="glass-panel rounded-2xl p-6 h-full flex flex-col items-center text-center">
                    <div class="w-16 h-16 rounded-full bg-brand-accent flex items-center justify-center text-white mb-4 shadow-lg shadow-orange-500/30">
                        <i class="ph ph-download-simple text-3xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-2">Gerar Backup Agora</h3>
                    <p class="text-slate-400 text-sm mb-6">
                        Crie uma cópia instantânea de todos os seus Clientes, Propostas e Itens.
                    </p>
                    
                    <form method="POST" action="meus_backups.php" class="w-full mt-auto">
                        <input type="hidden" name="acao" value="gerar">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                        <button type="submit" class="w-full py-3 bg-brand-action hover:bg-brand-accent text-white font-bold rounded-xl shadow-lg shadow-orange-900/20 transition-all hover:scale-105 flex items-center justify-center gap-2">
                            <i class="ph ph-file-zip"></i> Gerar Arquivo ZIP
                        </button>
                    </form>
                    <p class="text-[10px] text-slate-500 mt-3">Formato: JSON Portável (Universal)</p>
                </div>
            </div>

            <!-- Coluna Direita: Lista -->
            <div class="lg:col-span-2">
                <div class="glass-panel rounded-2xl overflow-hidden min-h-[400px]">
                    <div class="px-6 py-4 border-b border-white/5 bg-black/20 flex justify-between items-center">
                        <h3 class="font-bold text-white">Arquivos Disponíveis</h3>
                        <span class="px-2 py-1 rounded bg-white/10 text-xs font-mono text-slate-300"><?= count($arquivos) ?> arquivos</span>
                    </div>

                    <div class="divide-y divide-white/5">
                        <?php if (empty($arquivos)): ?>
                            <div class="p-12 text-center text-slate-500 flex flex-col items-center">
                                <i class="ph ph-folder-open text-6xl mb-4 opacity-30"></i>
                                <p>Nenhum backup encontrado.</p>
                                <p class="text-sm">Gere seu primeiro backup agora mesmo!</p>
                            </div>
                        <?php else: ?>
                            <?php foreach($arquivos as $arq): ?>
                                <div class="px-6 py-4 flex items-center justify-between hover:bg-white/5 transition-colors group">
                                    <div class="flex items-center gap-4">
                                        <div class="w-10 h-10 rounded-lg bg-blue-500/10 text-blue-400 flex items-center justify-center border border-blue-500/20">
                                            <i class="ph ph-file-zip text-xl"></i>
                                        </div>
                                        <div>
                                            <p class="text-white font-medium group-hover:text-brand-glow transition-colors"><?= $arq['nome'] ?></p>
                                            <div class="flex items-center gap-3 text-xs text-slate-400">
                                                <span class="flex items-center gap-1"><i class="ph ph-clock"></i> <?= $arq['data'] ?></span>
                                                <span class="flex items-center gap-1"><i class="ph ph-hard-drives"></i> <?= $arq['tamanho'] ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <a href="meus_backups.php?acao=baixar&arquivo=<?= $arq['nome'] ?>&csrf_token=<?= $_SESSION['csrf_token'] ?>" class="p-2 rounded-lg text-slate-400 hover:text-green-400 hover:bg-green-400/10 transition-colors" title="Baixar">
                                        <i class="ph ph-download-simple text-xl"></i>
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

        </div>

    </div>
</body>
</html>
