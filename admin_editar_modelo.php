<?php
require_once 'db.php';

$slug = $_GET['slug'] ?? '';
$env = isset($_GET['env']) && $_GET['env'] === 'demo' ? 'demo' : 'prod';

$selectedVarId = $_GET['id_variation'] ?? ''; // ID da variação selecionada (opcional)

if (empty($slug)) {
    header("Location: admin_modelos.php");
    exit;
}

// Conexão
try {
    $conn = ($env === 'demo') ? Database::getDemo() : Database::getProd();
} catch (Exception $e) {
    die("Erro de Conexão: " . $e->getMessage());
}

// 1. Busca Informações do Bloco
$stmtBlock = $conn->prepare("SELECT * FROM proposal_block_templates WHERE slug = ?");
$stmtBlock->bind_param("s", $slug);
$stmtBlock->execute();
$resBlock = $stmtBlock->get_result();
$block = $resBlock->fetch_assoc();

if (!$block) {
    die("Bloco não encontrado.");
}

$config = json_decode($block['default_content_json'], true);
$allowedVars = $config['allowed_vars'] ?? [];

// 2. Busca TODAS as Variações disponíveis para este bloco
$variations = [];
$stmtVars = $conn->prepare("SELECT * FROM proposal_content_variations WHERE block_slug = ? ORDER BY is_default DESC, variation_name ASC");
$stmtVars->bind_param("s", $slug);
$stmtVars->execute();
$resVars = $stmtVars->get_result();
while ($row = $resVars->fetch_assoc()) {
    $variations[] = $row;
}
$stmtVars->close();

// 3. Determina qual variação carregar
$currentContent = '';
$currentVarName = '';
$currentVarId = 0;
$isDefault = 0;

if (!empty($selectedVarId)) {
    // Busca específica
    foreach ($variations as $v) {
        if ($v['id_variation'] == $selectedVarId) {
            $currentContent = $v['content_text'];
            $currentVarName = $v['variation_name'];
            $currentVarId = $v['id_variation'];
            $isDefault = $v['is_default'];
            break;
        }
    }
} else {
    // Busca a Padrão (primeira da lista pois ordenamos por is_default DESC)
    if (count($variations) > 0) {
        $currentContent = $variations[0]['content_text'];
        $currentVarName = $variations[0]['variation_name'];
        $currentVarId = $variations[0]['id_variation'];
        $isDefault = $variations[0]['is_default'];
    }
}

$stmtBlock->close();
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editando: <?php echo htmlspecialchars($block['name']); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
    <script>
        function confirmDelete(id) {
            if (confirm('Tem certeza que deseja excluir esta variação?')) {
                window.location.href = 'admin_excluir_variacao.php?id=' + id + '&slug=<?php echo $slug; ?>&env=<?php echo $env; ?>';
            }
        }
    </script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8fafc;
        }

        .tox-tinymce {
            border-radius: 0.5rem !important;
            border: 1px solid #e2e8f0 !important;
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f5f9;
        }

        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 3px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
    </style>
</head>

<body class="h-screen flex flex-col overflow-hidden">

    <!-- Header -->
    <header class="bg-white border-b border-slate-200 px-6 py-4 flex justify-between items-center flex-shrink-0 z-10 shadow-sm">
        <div class="flex items-center gap-4">
            <a href="admin_modelos.php?env=<?php echo $env; ?>" class="text-slate-500 hover:text-slate-800 transition-colors flex items-center gap-1 font-medium text-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Voltar
            </a>
            <div class="h-6 w-px bg-slate-300"></div>
            <div>
                <h1 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                    <?php echo htmlspecialchars($block['name']); ?>
                    <span class="text-[10px] uppercase bg-slate-100 text-slate-500 px-2 py-0.5 rounded border border-slate-200"><?php echo htmlspecialchars($block['slug']); ?></span>
                </h1>
                <p class="text-xs text-slate-500">
                    Ambiente: <strong class="<?php echo $env === 'demo' ? 'text-orange-600' : 'text-blue-600'; ?> uppercase"><?php echo $env; ?></strong>
                    <span class="mx-1">•</span>
                    Variação: <strong><?php echo htmlspecialchars($currentVarName ?: 'Nova Variação'); ?></strong>
                    <?php if ($isDefault): ?>
                        <span class="ml-1 text-[10px] bg-green-100 text-green-700 px-1.5 py-0.5 rounded font-bold tracking-wide">PADRÃO</span>
                    <?php endif; ?>
                </p>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <?php if (!$isDefault && $currentVarId > 0): ?>
                <button onclick="confirmDelete(<?php echo $currentVarId; ?>)" type="button" class="text-red-500 hover:text-red-700 text-sm font-medium px-3 py-2">
                    Excluir Variação
                </button>
            <?php endif; ?>

            <button type="submit" form="formEditor" class="px-6 py-2.5 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg shadow-lg shadow-green-600/20 transition-all flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path>
                </svg>
                Salvar Texto
            </button>
        </div>
    </header>

    <div class="flex-1 flex overflow-hidden">

        <!-- Sidebar Left: Gerenciador de Variações -->
        <aside class="w-64 bg-slate-50 border-r border-slate-200 flex flex-col overflow-hidden">
            <div class="p-4 border-b border-slate-200 bg-white">
                <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-3">Variações de Texto</h3>

                <!-- Criar Nova Variação Form -->
                <form action="admin_criar_variacao.php" method="POST" class="flex gap-2">
                    <input type="hidden" name="env" value="<?php echo $env; ?>">
                    <input type="hidden" name="slug" value="<?php echo $slug; ?>">
                    <input type="text" name="new_variation_name" placeholder="Nova (ex: Drone)" required class="flex-1 min-w-0 text-xs px-2 py-1.5 border border-slate-300 rounded focus:border-blue-500 focus:outline-none">
                    <button type="submit" class="bg-blue-600 text-white p-1.5 rounded hover:bg-blue-700 transition-colors" title="Adicionar Variação">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                    </button>
                </form>
            </div>

            <div class="flex-1 overflow-y-auto p-2 space-y-1">
                <?php foreach ($variations as $var):
                    $isActive = ($var['id_variation'] == $currentVarId) || (empty($selectedVarId) && $var['is_default']);
                    $activeClass = $isActive ? 'bg-white border-blue-500 shadow-sm ring-1 ring-blue-500 z-10' : 'hover:bg-slate-100 border-transparent text-slate-600';
                ?>
                    <a href="?slug=<?php echo $slug; ?>&env=<?php echo $env; ?>&id_variation=<?php echo $var['id_variation']; ?>"
                        class="block px-3 py-2.5 rounded-md border-l-4 text-sm transition-all <?php echo $activeClass; ?>">
                        <div class="flex justify-between items-center">
                            <span class="font-medium truncate <?php echo $isActive ? 'text-blue-700' : ''; ?>"><?php echo htmlspecialchars($var['variation_name']); ?></span>
                            <?php if ($var['is_default']): ?>
                                <svg class="w-3 h-3 text-green-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                            <?php endif; ?>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </aside>

        <!-- Main Editor Area -->
        <main class="flex-1 overflow-y-auto bg-slate-100 p-6">
            <form id="formEditor" action="admin_salvar_modelo.php" method="POST" class="h-full flex flex-col">
                <input type="hidden" name="env" value="<?php echo $env; ?>">
                <input type="hidden" name="slug" value="<?php echo htmlspecialchars($slug); ?>">
                <input type="hidden" name="id_variation" value="<?php echo $currentVarId; ?>">

                <?php if ($currentVarId == 0 && count($variations) == 0): ?>
                    <!-- Caso de borda: Nenhuma variação existe -->
                    <div class="bg-yellow-50 text-yellow-800 p-4 rounded mb-4 border border-yellow-200">
                        Crie uma variação na lateral para começar a editar.
                    </div>
                <?php else: ?>
                    <div class="bg-white rounded-lg shadow-sm flex-1 flex flex-col overflow-hidden border border-slate-200">
                        <textarea id="editor" name="content_text" class="flex-1"><?php echo htmlspecialchars($currentContent); ?></textarea>
                    </div>
                <?php endif; ?>
            </form>
        </main>

        <!-- Sidebar Right: Variables -->
        <aside class="w-72 bg-white border-l border-slate-200 overflow-y-auto p-5 hidden xl:block">
            <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wide mb-4 flex items-center gap-2">
                <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Variáveis
            </h3>

            <?php if (!empty($allowedVars)): ?>
                <div class="space-y-2">
                    <?php foreach ($allowedVars as $var): ?>
                        <button onclick="copyVar('${<?php echo $var; ?>}')" class="w-full group flex items-center justify-between p-2.5 rounded-md border border-slate-200 hover:border-blue-300 hover:bg-blue-50 transition-all text-left">
                            <code class="text-xs font-bold text-blue-700 font-mono">${<?php echo $var; ?>}</code>
                        </button>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="text-xs text-slate-400">Sem variáveis dinâmicas.</p>
            <?php endif; ?>
        </aside>

    </div>

    <script>
        // Inicialização do TinyMCE
        tinymce.init({
            selector: '#editor',
            language: 'pt_BR',
            plugins: 'preview importcss searchreplace autolink autosave save directionality code visualblocks visualchars fullscreen image link media template codesample table charmap pagebreak nonbreaking anchor insertdatetime advlist lists wordcount help charmap quickbars emoticons',
            menubar: 'file edit view insert format tools table help',
            toolbar: 'undo redo | blocks | bold italic | alignleft aligncenter alignright | bullist numlist | forecolor backcolor | table | preview',
            height: '100%',
            setup: function(editor) {
                editor.on('change', function() {
                    editor.save();
                });
            }
        });

        function copyVar(text) {
            navigator.clipboard.writeText(text);
        }
    </script>
</body>

</html>