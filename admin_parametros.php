<?php
// Nome do Arquivo: admin_parametros.php
// Função: Painel de Cadastros Auxiliares (Completo).

session_start();
require_once 'config.php';
require_once 'db.php';

// 1. Segurança
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

// Bloqueia se for DEMO (apenas leitura/não acessa) ou se não for Admin/Produção
if ($_SESSION['ambiente'] === 'demo') {
    header("Location: painel.php");
    exit;
}

$is_admin = ($_SESSION['perfil'] === 'admin');

function renderRestrictedBanner($item) {
    $msg = urlencode("Olá, preciso adicionar $item no sistema (SGT).");
    echo '<div class="glass-panel rounded-xl p-6 text-center border border-dashed border-slate-600">';
    echo '<i class="ph ph-lock-key text-4xl text-slate-500 mb-2 block"></i>';
    echo '<p class="text-sm font-bold text-slate-300 mb-1">Acesso Restrito</p>';
    echo '<p class="text-xs text-slate-500 mb-4">Para adicionar novos itens, solicite ao administrador.</p>';
    echo '<a href="https://api.whatsapp.com/send?phone=5531971875928&text='.$msg.'" target="_blank" class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 hover:bg-green-500 text-white text-sm font-bold rounded-lg transition-colors shadow-lg shadow-green-900/20">';
    echo '<i class="ph ph-whatsapp-logo text-lg"></i> Solicitar via WhatsApp</a>';
    echo '</div>';
}

$conn = Database::getProd();
$msg = '';
$msg_tipo = 'success';

// 2. Processamento
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';

    try {
        // --- FUNÇÕES ---
        if ($acao === 'add_funcao') {
            $stmt = $conn->prepare("INSERT INTO Tipo_Funcoes (nome, salario_base_default) VALUES (?, ?)");
            $stmt->bind_param('sd', $_POST['nome'], $_POST['salario']);
            $stmt->execute();
            $msg = "Função adicionada!";
        }
        elseif ($acao === 'edit_funcao') {
            $stmt = $conn->prepare("UPDATE Tipo_Funcoes SET nome=?, salario_base_default=? WHERE id_funcao=?");
            $stmt->bind_param('sdi', $_POST['nome'], $_POST['salario'], $_POST['id']);
            $stmt->execute();
            $msg = "Função atualizada!";
        }
        elseif ($acao === 'del_funcao') {
            $conn->query("DELETE FROM Tipo_Funcoes WHERE id_funcao = ".intval($_POST['id']));
            $msg = "Função removida!";
        }

        // --- PREÇOS BASE (CATEGORIAS) ---
        elseif ($acao === 'add_tipo_locacao') {
            $stmt = $conn->prepare("INSERT INTO Tipo_Locacao (nome, valor_mensal_default) VALUES (?, ?)");
            $stmt->bind_param('sd', $_POST['nome'], $_POST['valor']);
            $stmt->execute();
            $msg = "Categoria adicionada!";
        }
        elseif ($acao === 'edit_tipo_locacao') {
            $stmt = $conn->prepare("UPDATE Tipo_Locacao SET nome=?, valor_mensal_default=? WHERE id_locacao=?");
            $stmt->bind_param('sdi', $_POST['nome'], $_POST['valor'], $_POST['id']);
            $stmt->execute();
            $msg = "Preço atualizado!";
        }
        elseif ($acao === 'del_tipo_locacao') {
            $conn->query("DELETE FROM Tipo_Locacao WHERE id_locacao = ".intval($_POST['id']));
            $msg = "Categoria removida!";
        }

        // --- MARCAS (EQUIPAMENTOS) ---
        elseif ($acao === 'add_marca') {
            $stmt = $conn->prepare("INSERT INTO Marcas (nome_marca, id_locacao) VALUES (?, ?)");
            $stmt->bind_param('si', $_POST['nome'], $_POST['id_locacao']);
            $stmt->execute();
            $msg = "Equipamento adicionado!";
        }
        elseif ($acao === 'edit_marca') {
            $stmt = $conn->prepare("UPDATE Marcas SET nome_marca=?, id_locacao=? WHERE id_marca=?");
            $stmt->bind_param('sii', $_POST['nome'], $_POST['id_locacao'], $_POST['id']);
            $stmt->execute();
            $msg = "Equipamento atualizado!";
        }
        elseif ($acao === 'del_marca') {
            $conn->query("DELETE FROM Marcas WHERE id_marca = ".intval($_POST['id']));
            $msg = "Equipamento removido!";
        }

        // --- SERVIÇOS ---
        elseif ($acao === 'add_servico') {
            $stmt = $conn->prepare("INSERT INTO Tipo_Servicos (nome, descricao) VALUES (?, ?)");
            $stmt->bind_param('ss', $_POST['nome'], $_POST['descricao']);
            $stmt->execute();
            $msg = "Serviço adicionado!";
        }
        elseif ($acao === 'edit_servico') {
            $stmt = $conn->prepare("UPDATE Tipo_Servicos SET nome=?, descricao=? WHERE id_servico=?");
            $stmt->bind_param('ssi', $_POST['nome'], $_POST['descricao'], $_POST['id']);
            $stmt->execute();
            $msg = "Serviço atualizado!";
        }
        elseif ($acao === 'del_servico') {
            $conn->query("DELETE FROM Tipo_Servicos WHERE id_servico = ".intval($_POST['id']));
            $msg = "Serviço removido!";
        }

        // --- MODELOS WORD ---
        elseif ($acao === 'upload_modelo') {
            $ambiente = $_POST['ambiente_destino']; 
            $pasta = ($ambiente === 'demo') ? 'modelos_demo/' : 'modelos_prod/';
            $diretorio = __DIR__ . '/' . $pasta;
            if (!is_dir($diretorio)) mkdir($diretorio, 0755, true);

            if (isset($_FILES['arquivo_docx']) && $_FILES['arquivo_docx']['error'] === 0) {
                $ext = strtolower(pathinfo($_FILES['arquivo_docx']['name'], PATHINFO_EXTENSION));
                if ($ext !== 'docx') throw new Exception("Apenas .docx");
                $nome_final = $_FILES['arquivo_docx']['name'];
                if(move_uploaded_file($_FILES['arquivo_docx']['tmp_name'], $diretorio . $nome_final)) {
                    $msg = "Arquivo enviado!";
                } else { throw new Exception("Erro ao mover."); }
            }
        }
        elseif ($acao === 'del_modelo') {
            $arquivo = $_POST['nome_arquivo'];
            $ambiente = $_POST['ambiente_origem'];
            $pasta = ($ambiente === 'demo') ? 'modelos_demo/' : 'modelos_prod/';
            $caminho = __DIR__ . '/' . $pasta . $arquivo;
            if (file_exists($caminho)) { unlink($caminho); $msg = "Excluído!"; }
        }

        // --- LIMPEZA DEMO ---
        elseif ($acao === 'reset_demo') {
            $connDemo = Database::getDemo();
            $sql = "SET FOREIGN_KEY_CHECKS = 0;
                    DELETE FROM Proposta_Salarios; ALTER TABLE Proposta_Salarios AUTO_INCREMENT = 1;
                    DELETE FROM Proposta_Estadia; ALTER TABLE Proposta_Estadia AUTO_INCREMENT = 1;
                    DELETE FROM Proposta_Consumos; ALTER TABLE Proposta_Consumos AUTO_INCREMENT = 1;
                    DELETE FROM Proposta_Locacao; ALTER TABLE Proposta_Locacao AUTO_INCREMENT = 1;
                    DELETE FROM Proposta_Custos_Administrativos; ALTER TABLE Proposta_Custos_Administrativos AUTO_INCREMENT = 1;
                    DELETE FROM Propostas; ALTER TABLE Propostas AUTO_INCREMENT = 1;
                    DELETE FROM Clientes; ALTER TABLE Clientes AUTO_INCREMENT = 1;
                    SET FOREIGN_KEY_CHECKS = 1;";
            if ($connDemo->multi_query($sql)) {
                while ($connDemo->next_result()) {;} 
                $msg = "Demo resetada!";
            }
        }

    } catch (Exception $e) {
        $msg = "Erro: " . $e->getMessage();
        $msg_tipo = 'danger';
    }
}

// 3. Consultas
$funcoes = $conn->query("SELECT * FROM Tipo_Funcoes ORDER BY nome");
$tipos_loc_tabela = $conn->query("SELECT * FROM Tipo_Locacao ORDER BY nome");
$marcas  = $conn->query("SELECT m.*, t.nome as tipo FROM Marcas m LEFT JOIN Tipo_Locacao t ON m.id_locacao = t.id_locacao ORDER BY t.nome ASC, m.nome_marca ASC");
$servicos = $conn->query("SELECT * FROM Tipo_Servicos ORDER BY nome");

$tipos_loc_array = [];
foreach($tipos_loc_tabela as $row) { $tipos_loc_array[] = $row; }

function listarArquivos($pasta) {
    $caminho = __DIR__ . '/' . $pasta . '/';
    $arquivos = [];
    if (is_dir($caminho)) {
        $todos = scandir($caminho);
        foreach ($todos as $a) { if ($a !== '.' && $a !== '..' && strpos($a, '.docx') !== false) $arquivos[] = $a; }
    }
    return $arquivos;
}
$arquivos_prod = listarArquivos('modelos_prod');
$arquivos_demo = listarArquivos('modelos_demo');
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Parâmetros | Admin</title>
    
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
        .glass-modal {
            background: rgba(10, 30, 60, 0.95);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.1);
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
                </div>

                <!-- Menu Desktop -->
                <div class="flex items-center gap-4">
                    <a href="painel.php" class="text-sm font-medium text-slate-300 hover:text-white transition-colors flex items-center gap-2">
                        <i class="ph ph-arrow-left"></i> Voltar ao Painel
                    </a>
                    <span class="px-3 py-1 rounded-full bg-brand-accent/20 text-brand-accent text-xs font-bold border border-brand-accent/30 uppercase">
                        Cadastros Auxiliares
                    </span>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        <?php if($msg): ?>
            <div class="glass-panel rounded-xl p-4 mb-6 border-l-4 <?php echo $msg_tipo == 'success' ? 'border-green-500' : 'border-red-500'; ?> flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <i class="ph <?php echo $msg_tipo == 'success' ? 'ph-check-circle text-green-400' : 'ph-warning-circle text-red-400'; ?> text-xl"></i>
                    <span class="text-white font-medium"><?php echo $msg; ?></span>
                </div>
                <button onclick="this.parentElement.remove()" class="text-slate-400 hover:text-white"><i class="ph ph-x"></i></button>
            </div>
        <?php endif; ?>

        <div class="glass-panel rounded-2xl overflow-hidden min-h-[600px] flex flex-col">
            <!-- Tabs Header -->
            <div class="border-b border-white/10 bg-black/20 px-6 pt-4">
                <div class="flex gap-6 overflow-x-auto">
                    <button onclick="openTab('func')" class="tab-btn active pb-4 text-sm font-bold text-brand-accent border-b-2 border-brand-accent transition-colors">Funções</button>
                    <button onclick="openTab('precos')" class="tab-btn pb-4 text-sm font-medium text-slate-400 hover:text-white border-b-2 border-transparent transition-colors">Preços Base</button>
                    <button onclick="openTab('equip')" class="tab-btn pb-4 text-sm font-medium text-slate-400 hover:text-white border-b-2 border-transparent transition-colors">Equipamentos</button>
                    <button onclick="openTab('serv')" class="tab-btn pb-4 text-sm font-medium text-slate-400 hover:text-white border-b-2 border-transparent transition-colors">Serviços</button>
                    <button onclick="openTab('docs')" class="tab-btn pb-4 text-sm font-medium text-slate-400 hover:text-white border-b-2 border-transparent transition-colors">Modelos Word</button>
                    <?php if($_SESSION['perfil'] === 'admin'): ?>
                    <button onclick="openTab('sys')" class="tab-btn pb-4 text-sm font-medium text-red-400 hover:text-red-300 border-b-2 border-transparent transition-colors">Sistema</button>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Tabs Content -->
            <div class="p-6 flex-1 bg-brand-surface/30">
                
                <!-- 1. FUNÇÕES -->
                <div id="func" class="tab-content block">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                        <div class="md:col-span-1">
                            <h4 class="text-white font-bold mb-4 flex items-center gap-2"><i class="ph ph-plus-circle text-brand-accent"></i> Nova Função</h4>
                            <?php if($is_admin): ?>
                            <form method="POST" class="space-y-4">
                                <input type="hidden" name="acao" value="add_funcao">
                                <div>
                                    <label class="block text-xs text-slate-400 mb-1">Nome do Cargo</label>
                                    <input type="text" name="nome" class="w-full bg-black/20 border border-white/10 rounded-lg px-4 py-2 text-white focus:border-brand-accent focus:ring-1 focus:ring-brand-accent outline-none" required>
                                </div>
                                <div>
                                    <label class="block text-xs text-slate-400 mb-1">Salário Base (R$)</label>
                                    <input type="number" step="0.01" name="salario" class="w-full bg-black/20 border border-white/10 rounded-lg px-4 py-2 text-white focus:border-brand-accent focus:ring-1 focus:ring-brand-accent outline-none" required>
                                </div>
                                <button class="w-full py-2 bg-brand-action hover:bg-brand-accent text-white font-bold rounded-lg transition-colors">Salvar</button>
                            </form>
                            <?php else: renderRestrictedBanner('uma nova Função'); endif; ?>
                        </div>
                        <div class="md:col-span-2 overflow-y-auto max-h-[500px] pr-2">
                            <table class="w-full text-left text-sm">
                                <thead class="text-xs text-slate-500 uppercase border-b border-white/10"><tr><th class="py-2">Cargo</th><th class="py-2">Salário</th><th class="py-2 text-right">Ações</th></tr></thead>
                                <tbody class="divide-y divide-white/5">
                                    <?php foreach($funcoes as $f): ?>
                                    <tr class="group hover:bg-white/5 transition-colors">
                                        <td class="py-3 text-white font-medium"><?php echo $f['nome']; ?></td>
                                        <td class="py-3 text-slate-300">R$ <?php echo number_format($f['salario_base_default'], 2, ',', '.'); ?></td>
                                        <td class="py-3 text-right">
                                            <?php if($is_admin): ?>
                                            <button onclick="editFuncao(<?php echo $f['id_funcao']; ?>, '<?php echo $f['nome']; ?>', '<?php echo $f['salario_base_default']; ?>')" class="text-slate-400 hover:text-brand-accent mr-2"><i class="ph ph-pencil-simple"></i></button>
                                            <form method="POST" style="display:inline;" onsubmit="return confirm('Excluir?');">
                                                <input type="hidden" name="acao" value="del_funcao"><input type="hidden" name="id" value="<?php echo $f['id_funcao']; ?>">
                                                <button class="text-slate-400 hover:text-red-400"><i class="ph ph-trash"></i></button>
                                            </form>
                                            <?php else: echo '<i class="ph ph-lock text-slate-600"></i>'; endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- 2. PREÇOS BASE -->
                <div id="precos" class="tab-content hidden">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                        <div class="md:col-span-1">
                            <h4 class="text-white font-bold mb-4 flex items-center gap-2"><i class="ph ph-plus-circle text-brand-accent"></i> Nova Categoria</h4>
                            <?php if($is_admin): ?>
                            <form method="POST" class="space-y-4">
                                <input type="hidden" name="acao" value="add_tipo_locacao">
                                <div><label class="block text-xs text-slate-400 mb-1">Categoria</label><input type="text" name="nome" class="w-full bg-black/20 border border-white/10 rounded-lg px-4 py-2 text-white focus:border-brand-accent focus:ring-1 focus:ring-brand-accent outline-none" required></div>
                                <div><label class="block text-xs text-slate-400 mb-1">Valor Padrão (R$)</label><input type="number" step="0.01" name="valor" class="w-full bg-black/20 border border-white/10 rounded-lg px-4 py-2 text-white focus:border-brand-accent focus:ring-1 focus:ring-brand-accent outline-none" required></div>
                                <button class="w-full py-2 bg-brand-action hover:bg-brand-accent text-white font-bold rounded-lg transition-colors">Salvar</button>
                            </form>
                            <?php else: renderRestrictedBanner('uma nova Categoria'); endif; ?>
                        </div>
                        <div class="md:col-span-2 overflow-y-auto max-h-[500px] pr-2">
                            <table class="w-full text-left text-sm">
                                <thead class="text-xs text-slate-500 uppercase border-b border-white/10"><tr><th class="py-2">Categoria</th><th class="py-2">Valor</th><th class="py-2 text-right">Ações</th></tr></thead>
                                <tbody class="divide-y divide-white/5">
                                    <?php foreach($tipos_loc_array as $t): ?>
                                    <tr class="group hover:bg-white/5 transition-colors">
                                        <td class="py-3 text-white font-medium"><?php echo $t['nome']; ?></td>
                                        <td class="py-3 text-slate-300">R$ <?php echo number_format($t['valor_mensal_default'], 2, ',', '.'); ?></td>
                                        <td class="py-3 text-right">
                                            <?php if($is_admin): ?>
                                            <button onclick="editTipo(<?php echo $t['id_locacao']; ?>, '<?php echo $t['nome']; ?>', '<?php echo $t['valor_mensal_default']; ?>')" class="text-slate-400 hover:text-brand-accent mr-2"><i class="ph ph-pencil-simple"></i></button>
                                            <?php else: echo '<i class="ph ph-lock text-slate-600"></i>'; endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- 3. EQUIPAMENTOS -->
                <div id="equip" class="tab-content hidden">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                        <div class="md:col-span-1">
                            <h4 class="text-white font-bold mb-4 flex items-center gap-2"><i class="ph ph-plus-circle text-brand-accent"></i> Novo Equipamento</h4>
                            <?php if($is_admin): ?>
                            <form method="POST" class="space-y-4">
                                <input type="hidden" name="acao" value="add_marca">
                                <div>
                                    <label class="block text-xs text-slate-400 mb-1">Categoria</label>
                                    <select name="id_locacao" class="w-full bg-black/20 border border-white/10 rounded-lg px-4 py-2 text-white focus:border-brand-accent focus:ring-1 focus:ring-brand-accent outline-none">
                                        <?php foreach($tipos_loc_array as $t): ?><option value="<?php echo $t['id_locacao']; ?>" class="text-black"><?php echo $t['nome']; ?></option><?php endforeach; ?>
                                    </select>
                                </div>
                                <div><label class="block text-xs text-slate-400 mb-1">Modelo</label><input type="text" name="nome" class="w-full bg-black/20 border border-white/10 rounded-lg px-4 py-2 text-white focus:border-brand-accent focus:ring-1 focus:ring-brand-accent outline-none" required></div>
                                <button class="w-full py-2 bg-brand-action hover:bg-brand-accent text-white font-bold rounded-lg transition-colors">Salvar</button>
                            </form>
                            <?php else: renderRestrictedBanner('um novo Equipamento'); endif; ?>
                        </div>
                        <div class="md:col-span-2 overflow-y-auto max-h-[500px] pr-2">
                            <table class="w-full text-left text-sm">
                                <thead class="text-xs text-slate-500 uppercase border-b border-white/10"><tr><th class="py-2">Categoria</th><th class="py-2">Modelo</th><th class="py-2 text-right">Ações</th></tr></thead>
                                <tbody class="divide-y divide-white/5">
                                    <?php foreach($marcas as $m): ?>
                                    <tr class="group hover:bg-white/5 transition-colors">
                                        <td class="py-3"><span class="px-2 py-0.5 rounded bg-white/10 text-slate-300 text-xs"><?php echo $m['tipo']; ?></span></td>
                                        <td class="py-3 text-white font-medium"><?php echo $m['nome_marca']; ?></td>
                                        <td class="py-3 text-right">
                                            <?php if($is_admin): ?>
                                            <button onclick="editMarca(<?php echo $m['id_marca']; ?>, '<?php echo $m['nome_marca']; ?>', '<?php echo $m['id_locacao']; ?>')" class="text-slate-400 hover:text-brand-accent mr-2"><i class="ph ph-pencil-simple"></i></button>
                                            <form method="POST" style="display:inline;" onsubmit="return confirm('Excluir?');">
                                                <input type="hidden" name="acao" value="del_marca"><input type="hidden" name="id" value="<?php echo $m['id_marca']; ?>">
                                                <button class="text-slate-400 hover:text-red-400"><i class="ph ph-trash"></i></button>
                                            </form>
                                            <?php else: echo '<i class="ph ph-lock text-slate-600"></i>'; endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- 4. SERVIÇOS -->
                <div id="serv" class="tab-content hidden">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                        <div class="md:col-span-1">
                            <h4 class="text-white font-bold mb-4 flex items-center gap-2"><i class="ph ph-plus-circle text-brand-accent"></i> Novo Serviço</h4>
                            <?php if($is_admin): ?>
                            <form method="POST" class="space-y-4">
                                <input type="hidden" name="acao" value="add_servico">
                                <div><label class="block text-xs text-slate-400 mb-1">Nome</label><input type="text" name="nome" class="w-full bg-black/20 border border-white/10 rounded-lg px-4 py-2 text-white focus:border-brand-accent focus:ring-1 focus:ring-brand-accent outline-none" required></div>
                                <div><label class="block text-xs text-slate-400 mb-1">Descrição</label><textarea name="descricao" class="w-full bg-black/20 border border-white/10 rounded-lg px-4 py-2 text-white focus:border-brand-accent focus:ring-1 focus:ring-brand-accent outline-none" rows="3"></textarea></div>
                                <button class="w-full py-2 bg-brand-action hover:bg-brand-accent text-white font-bold rounded-lg transition-colors">Salvar</button>
                            </form>
                            <?php else: renderRestrictedBanner('um novo Serviço'); endif; ?>
                        </div>
                        <div class="md:col-span-2 overflow-y-auto max-h-[500px] pr-2">
                            <table class="w-full text-left text-sm">
                                <thead class="text-xs text-slate-500 uppercase border-b border-white/10"><tr><th class="py-2">Serviço</th><th class="py-2">Descrição</th><th class="py-2 text-right">Ações</th></tr></thead>
                                <tbody class="divide-y divide-white/5">
                                    <?php foreach($servicos as $s): ?>
                                    <tr class="group hover:bg-white/5 transition-colors">
                                        <td class="py-3 text-white font-medium"><?php echo $s['nome']; ?></td>
                                        <td class="py-3 text-slate-400 text-xs"><?php echo substr($s['descricao'], 0, 40); ?>...</td>
                                        <td class="py-3 text-right">
                                            <?php if($is_admin): ?>
                                            <button onclick="editServico(<?php echo $s['id_servico']; ?>, '<?php echo $s['nome']; ?>', '<?php echo $s['descricao']; ?>')" class="text-slate-400 hover:text-brand-accent mr-2"><i class="ph ph-pencil-simple"></i></button>
                                            <form method="POST" style="display:inline;" onsubmit="return confirm('Excluir?');">
                                                <input type="hidden" name="acao" value="del_servico"><input type="hidden" name="id" value="<?php echo $s['id_servico']; ?>">
                                                <button class="text-slate-400 hover:text-red-400"><i class="ph ph-trash"></i></button>
                                            </form>
                                            <?php else: echo '<i class="ph ph-lock text-slate-600"></i>'; endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- 5. MODELOS WORD -->
                <div id="docs" class="tab-content hidden">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                        <div class="md:col-span-1">
                            <h4 class="text-white font-bold mb-4 flex items-center gap-2"><i class="ph ph-upload-simple text-brand-accent"></i> Upload Modelo</h4>
                            <?php if($is_admin): ?>
                            <form method="POST" enctype="multipart/form-data" class="space-y-4">
                                <input type="hidden" name="acao" value="upload_modelo">
                                <div>
                                    <label class="block text-xs text-slate-400 mb-1">Destino</label>
                                    <select name="ambiente_destino" class="w-full bg-black/20 border border-white/10 rounded-lg px-4 py-2 text-white focus:border-brand-accent focus:ring-1 focus:ring-brand-accent outline-none">
                                        <option value="prod" class="text-black">Produção</option>
                                        <option value="demo" class="text-black">Demo</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs text-slate-400 mb-1">Arquivo .docx</label>
                                    <input type="file" name="arquivo_docx" class="w-full text-sm text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-white/10 file:text-white hover:file:bg-white/20 transition-all" accept=".docx" required>
                                </div>
                                <button class="w-full py-2 bg-green-600 hover:bg-green-500 text-white font-bold rounded-lg transition-colors">Enviar</button>
                            </form>
                            <?php else: renderRestrictedBanner('um novo Modelo'); endif; ?>
                        </div>
                        <div class="md:col-span-2">
                            <h4 class="text-green-400 font-bold mb-3 text-sm uppercase tracking-wider">Modelos em Produção</h4>
                            <ul class="space-y-2 mb-6">
                                <?php foreach($arquivos_prod as $a): ?>
                                <li class="flex justify-between items-center p-3 bg-white/5 rounded-lg border border-white/5">
                                    <span class="text-slate-300 text-sm"><i class="ph ph-file-doc text-blue-400 mr-2"></i> <?php echo $a; ?></span>
                                    <?php if($is_admin): ?>
                                    <form method="POST" onsubmit="return confirm('Apagar?');">
                                        <input type="hidden" name="acao" value="del_modelo"><input type="hidden" name="ambiente_origem" value="prod"><input type="hidden" name="nome_arquivo" value="<?php echo $a; ?>">
                                        <button class="text-slate-500 hover:text-red-400"><i class="ph ph-trash"></i></button>
                                    </form>
                                    <?php endif; ?>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- 6. SISTEMA -->
                <?php if($_SESSION['perfil'] === 'admin'): ?>
                <div id="sys" class="tab-content hidden">
                    <div class="flex items-center justify-center h-full">
                        <div class="text-center p-8 border border-red-500/30 bg-red-500/10 rounded-2xl max-w-md">
                            <i class="ph ph-warning-octagon text-5xl text-red-500 mb-4"></i>
                            <h4 class="text-xl font-bold text-red-400 mb-2">ZONA DE PERIGO</h4>
                            <p class="text-slate-300 mb-6 text-sm">Esta ação apagará <strong>TODOS</strong> os dados do ambiente de demonstração. Isso não pode ser desfeito.</p>
                            <form method="POST" onsubmit="return confirm('Confirmar limpeza TOTAL da Demo?');">
                                <input type="hidden" name="acao" value="reset_demo">
                                <button class="px-6 py-3 bg-red-600 hover:bg-red-500 text-white font-bold rounded-lg transition-colors shadow-lg shadow-red-900/20">
                                    LIMPAR AMBIENTE DEMO
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

            </div>
        </div>
    </div>

    <!-- MODAL EDIT GENÉRICO (Preenchido via JS) -->
    <div id="modalEdit" class="fixed inset-0 z-[100] hidden">
        <div class="absolute inset-0 bg-black/80 backdrop-blur-sm" onclick="closeModal()"></div>
        <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-full max-w-md p-6 glass-modal rounded-2xl shadow-2xl border border-white/20">
            <div class="flex justify-between items-center mb-6">
                <h3 id="modalTitle" class="text-xl font-bold text-white">Editar</h3>
                <button onclick="closeModal()" class="text-slate-400 hover:text-white"><i class="ph ph-x text-xl"></i></button>
            </div>
            <form method="POST" id="modalForm" class="space-y-4">
                <input type="hidden" name="acao" id="modalAcao">
                <input type="hidden" name="id" id="modalId">
                <div id="modalFields"></div>
                <button type="submit" class="w-full py-3 bg-brand-action hover:bg-brand-accent text-white font-bold rounded-xl transition-colors mt-4">Salvar Alterações</button>
            </form>
        </div>
    </div>

    <script>
        // Tabs Logic
        function openTab(tabId) {
            document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
            document.querySelectorAll('.tab-btn').forEach(el => {
                el.classList.remove('active', 'text-brand-accent', 'border-brand-accent');
                el.classList.add('text-slate-400', 'border-transparent');
            });
            
            document.getElementById(tabId).classList.remove('hidden');
            const btn = document.querySelector(`button[onclick="openTab('${tabId}')"]`);
            if(btn) {
                btn.classList.add('active', 'text-brand-accent', 'border-brand-accent');
                btn.classList.remove('text-slate-400', 'border-transparent');
            }
        }

        // Modal Logic
        const modal = document.getElementById('modalEdit');
        const modalTitle = document.getElementById('modalTitle');
        const modalAcao = document.getElementById('modalAcao');
        const modalId = document.getElementById('modalId');
        const modalFields = document.getElementById('modalFields');

        function openModal(title, acao, id, fieldsHtml) {
            modalTitle.innerText = title;
            modalAcao.value = acao;
            modalId.value = id;
            modalFields.innerHTML = fieldsHtml;
            modal.classList.remove('hidden');
        }

        function closeModal() {
            modal.classList.add('hidden');
        }

        // Edit Functions
        function editFuncao(id, nome, salario) {
            openModal('Editar Função', 'edit_funcao', id, `
                <div><label class="block text-xs text-slate-400 mb-1">Nome</label><input type="text" name="nome" value="${nome}" class="w-full bg-black/20 border border-white/10 rounded-lg px-4 py-2 text-white outline-none focus:border-brand-accent"></div>
                <div><label class="block text-xs text-slate-400 mb-1">Salário</label><input type="number" step="0.01" name="salario" value="${salario}" class="w-full bg-black/20 border border-white/10 rounded-lg px-4 py-2 text-white outline-none focus:border-brand-accent"></div>
            `);
        }

        function editTipo(id, nome, valor) {
            openModal('Editar Preço', 'edit_tipo_locacao', id, `
                <div><label class="block text-xs text-slate-400 mb-1">Categoria</label><input type="text" name="nome" value="${nome}" class="w-full bg-black/20 border border-white/10 rounded-lg px-4 py-2 text-white outline-none focus:border-brand-accent"></div>
                <div><label class="block text-xs text-slate-400 mb-1">Valor</label><input type="number" step="0.01" name="valor" value="${valor}" class="w-full bg-black/20 border border-white/10 rounded-lg px-4 py-2 text-white outline-none focus:border-brand-accent"></div>
            `);
        }

        function editMarca(id, nome, idLocacao) {
            // Note: Select options need to be passed or regenerated. For simplicity, we'll just use text input for name here, 
            // but ideally we should pass the categories array to JS.
            // Let's grab the select from the add form to clone options
            const options = document.querySelector('select[name="id_locacao"]').innerHTML;
            openModal('Editar Equipamento', 'edit_marca', id, `
                <div><label class="block text-xs text-slate-400 mb-1">Categoria</label><select name="id_locacao" class="w-full bg-black/20 border border-white/10 rounded-lg px-4 py-2 text-white outline-none focus:border-brand-accent">${options}</select></div>
                <div><label class="block text-xs text-slate-400 mb-1">Modelo</label><input type="text" name="nome" value="${nome}" class="w-full bg-black/20 border border-white/10 rounded-lg px-4 py-2 text-white outline-none focus:border-brand-accent"></div>
            `);
            // Set selected value
            setTimeout(() => document.querySelector('#modalFields select').value = idLocacao, 0);
        }

        function editServico(id, nome, descricao) {
            openModal('Editar Serviço', 'edit_servico', id, `
                <div><label class="block text-xs text-slate-400 mb-1">Nome</label><input type="text" name="nome" value="${nome}" class="w-full bg-black/20 border border-white/10 rounded-lg px-4 py-2 text-white outline-none focus:border-brand-accent"></div>
                <div><label class="block text-xs text-slate-400 mb-1">Descrição</label><textarea name="descricao" class="w-full bg-black/20 border border-white/10 rounded-lg px-4 py-2 text-white outline-none focus:border-brand-accent" rows="3">${descricao}</textarea></div>
            `);
        }
    </script>
</body>
</html>