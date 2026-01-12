<?php
// Nome do Arquivo: admin_parametros.php
// Função: Painel de Cadastros Auxiliares (Completo).
// Correção: Botões de Editar visíveis e código formatado.

session_start();
require_once 'config.php';
require_once 'db.php';

// 1. Segurança
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

// Se for Produção, permite. Se for Admin, permite.
// O código original bloqueava tudo que não fosse admin.
// Se for Produção, permite. Se for Admin, permite.
// O código original bloqueava tudo que não fosse admin.
// NOVA LÓGICA: Se não for admin, mostra tela de solicitação.
$is_admin = ($_SESSION['perfil'] === 'admin');

function renderRestrictedBanner($item) {
    $msg = urlencode("Olá, preciso adicionar $item no sistema (SGT).");
    echo '<div class="text-center p-3 bg-white rounded border border-dashed">';
    echo '<i class="bi bi-shield-lock fs-1 text-secondary"></i>';
    echo '<p class="small text-muted mt-2 fw-bold">Acesso Restrito</p>';
    echo '<p class="small text-muted mb-3">Para adicionar novos itens, solicite ao administrador.</p>';
    echo '<a href="https://api.whatsapp.com/send?phone=5531971875928&text='.$msg.'" target="_blank" class="btn btn-success btn-sm w-100 shadow-sm"><i class="bi bi-whatsapp me-1"></i> Solicitar via WhatsApp</a>';
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
    <title>Parâmetros | Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body { background-color: #f8f9fa; }
        .nav-tabs .nav-link { color: #495057; font-weight: 600; }
        .nav-tabs .nav-link.active { color: #0d6efd; border-bottom: 3px solid #0d6efd; }
        .card-table { max-height: 600px; overflow-y: auto; }
    </style>
</head>
<body>

    <nav class="navbar navbar-dark bg-dark mb-4">
        <div class="container">
            <a class="navbar-brand" href="painel.php"><i class="bi bi-arrow-left me-2"></i>Voltar ao Painel</a>
            <span class="navbar-text text-white">Cadastros Auxiliares</span>
        </div>
    </nav>

    <div class="container pb-5">
        <?php if($msg): ?>
            <div class="alert alert-<?php echo $msg_tipo; ?> alert-dismissible fade show">
                <?php echo $msg; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="card shadow-sm border-0">
            <div class="card-header bg-white">
                <ul class="nav nav-tabs card-header-tabs" id="myTab" role="tablist">
                    <li class="nav-item"><button class="nav-link active" id="func-tab" data-bs-toggle="tab" data-bs-target="#func">Funções</button></li>
                    <li class="nav-item"><button class="nav-link" id="precos-tab" data-bs-toggle="tab" data-bs-target="#precos">Preços Base</button></li>
                    <li class="nav-item"><button class="nav-link" id="equip-tab" data-bs-toggle="tab" data-bs-target="#equip">Equipamentos</button></li>
                    <li class="nav-item"><button class="nav-link" id="serv-tab" data-bs-toggle="tab" data-bs-target="#serv">Serviços</button></li>
                    <li class="nav-item"><button class="nav-link text-primary" id="docs-tab" data-bs-toggle="tab" data-bs-target="#docs">Modelos Word</button></li>
                    <?php if($_SESSION['perfil'] === 'admin'): ?>
                    <li class="nav-item"><button class="nav-link text-danger" id="sys-tab" data-bs-toggle="tab" data-bs-target="#sys">Sistema</button></li>
                    <?php endif; ?>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content">
                    
                    <!-- 1. FUNÇÕES -->
                    <div class="tab-pane fade show active" id="func">
                        <div class="row">
                            <div class="col-md-4 border-end">
                                <?php if($is_admin): ?>
                                <form method="POST">
                                    <input type="hidden" name="acao" value="add_funcao">
                                    <div class="mb-3"><label>Nome</label><input type="text" name="nome" class="form-control" required></div>
                                    <div class="mb-3"><label>Salário</label><input type="number" step="0.01" name="salario" class="form-control" required></div>
                                    <button class="btn btn-primary w-100">Salvar</button>
                                </form>
                                <?php else: renderRestrictedBanner('uma nova Função'); endif; ?>
                            </div>
                            <div class="col-md-8 card-table">
                                <table class="table table-striped align-middle">
                                    <thead><tr><th>Cargo</th><th>Salário</th><th class="text-end">Ações</th></tr></thead>
                                    <tbody>
                                        <?php foreach($funcoes as $f): ?>
                                        <tr>
                                            <td><?php echo $f['nome']; ?></td>
                                            <td>R$ <?php echo number_format($f['salario_base_default'], 2, ',', '.'); ?></td>
                                            <td class="text-end">
                                                <?php if($is_admin): ?>
                                                <button class="btn btn-sm btn-outline-warning me-1 btn-edit-funcao" 
                                                    data-id="<?php echo $f['id_funcao']; ?>" 
                                                    data-nome="<?php echo $f['nome']; ?>" 
                                                    data-salario="<?php echo $f['salario_base_default']; ?>" 
                                                    data-bs-toggle="modal" data-bs-target="#modalEditFuncao"><i class="bi bi-pencil"></i></button>
                                                
                                                <form method="POST" style="display:inline;" onsubmit="return confirm('Excluir?');">
                                                    <input type="hidden" name="acao" value="del_funcao">
                                                    <input type="hidden" name="id" value="<?php echo $f['id_funcao']; ?>">
                                                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                                </form>
                                                <?php else: echo '<span class="badge bg-light text-secondary"><i class="bi bi-lock"></i></span>'; endif; ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- 2. PREÇOS BASE -->
                    <div class="tab-pane fade" id="precos">
                        <div class="row">
                            <div class="col-md-4 border-end">
                                <?php if($is_admin): ?>
                                <form method="POST">
                                    <input type="hidden" name="acao" value="add_tipo_locacao">
                                    <div class="mb-3"><label>Categoria</label><input type="text" name="nome" class="form-control" required></div>
                                    <div class="mb-3"><label>Valor Padrão</label><input type="number" step="0.01" name="valor" class="form-control" required></div>
                                    <button class="btn btn-primary w-100">Salvar</button>
                                </form>
                                <?php else: renderRestrictedBanner('uma nova Categoria'); endif; ?>
                            </div>
                            <div class="col-md-8 card-table">
                                <table class="table table-striped align-middle">
                                    <thead><tr><th>Categoria</th><th>Valor</th><th class="text-end">Ações</th></tr></thead>
                                    <tbody>
                                        <?php foreach($tipos_loc_array as $t): ?>
                                        <tr>
                                            <td><?php echo $t['nome']; ?></td>
                                            <td>R$ <?php echo number_format($t['valor_mensal_default'], 2, ',', '.'); ?></td>
                                            <td class="text-end">
                                                <?php if($is_admin): ?>
                                                <button class="btn btn-sm btn-outline-warning me-1 btn-edit-tipo" 
                                                    data-id="<?php echo $t['id_locacao']; ?>" 
                                                    data-nome="<?php echo $t['nome']; ?>" 
                                                    data-valor="<?php echo $t['valor_mensal_default']; ?>" 
                                                    data-bs-toggle="modal" data-bs-target="#modalEditTipoLocacao"><i class="bi bi-pencil"></i></button>
                                                <?php else: echo '<span class="badge bg-light text-secondary"><i class="bi bi-lock"></i></span>'; endif; ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- 3. EQUIPAMENTOS -->
                    <div class="tab-pane fade" id="equip">
                        <div class="row">
                            <div class="col-md-4 border-end">
                                <?php if($is_admin): ?>
                                <form method="POST">
                                    <input type="hidden" name="acao" value="add_marca">
                                    <div class="mb-3">
                                        <label>Categoria</label>
                                        <select name="id_locacao" class="form-select" required>
                                            <?php foreach($tipos_loc_array as $t): ?><option value="<?php echo $t['id_locacao']; ?>"><?php echo $t['nome']; ?></option><?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="mb-3"><label>Modelo</label><input type="text" name="nome" class="form-control" required></div>
                                    <button class="btn btn-primary w-100">Salvar</button>
                                </form>
                                <?php else: renderRestrictedBanner('um novo Equipamento'); endif; ?>
                            </div>
                            <div class="col-md-8 card-table">
                                <table class="table table-striped align-middle">
                                    <thead><tr><th>Categoria</th><th>Modelo</th><th class="text-end">Ações</th></tr></thead>
                                    <tbody>
                                        <?php foreach($marcas as $m): ?>
                                        <tr>
                                            <td><span class="badge bg-secondary"><?php echo $m['tipo']; ?></span></td>
                                            <td><?php echo $m['nome_marca']; ?></td>
                                            <td class="text-end">
                                                <?php if($is_admin): ?>
                                                <button class="btn btn-sm btn-outline-warning me-1 btn-edit-marca" 
                                                    data-id="<?php echo $m['id_marca']; ?>" 
                                                    data-nome="<?php echo $m['nome_marca']; ?>" 
                                                    data-locacao="<?php echo $m['id_locacao']; ?>" 
                                                    data-bs-toggle="modal" data-bs-target="#modalEditMarca"><i class="bi bi-pencil"></i></button>
                                                
                                                <form method="POST" style="display:inline;" onsubmit="return confirm('Excluir?');">
                                                    <input type="hidden" name="acao" value="del_marca">
                                                    <input type="hidden" name="id" value="<?php echo $m['id_marca']; ?>">
                                                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                                </form>
                                                <?php else: echo '<span class="badge bg-light text-secondary"><i class="bi bi-lock"></i></span>'; endif; ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- 4. SERVIÇOS -->
                    <div class="tab-pane fade" id="serv">
                        <div class="row">
                            <div class="col-md-4 border-end">
                                <?php if($is_admin): ?>
                                <form method="POST">
                                    <input type="hidden" name="acao" value="add_servico">
                                    <div class="mb-3"><label>Nome</label><input type="text" name="nome" class="form-control" required></div>
                                    <div class="mb-3"><label>Descrição</label><textarea name="descricao" class="form-control" rows="3"></textarea></div>
                                    <button class="btn btn-primary w-100">Salvar</button>
                                </form>
                                <?php else: renderRestrictedBanner('um novo Serviço'); endif; ?>
                            </div>
                            <div class="col-md-8 card-table">
                                <table class="table table-striped align-middle">
                                    <thead><tr><th>Serviço</th><th>Descrição</th><th class="text-end">Ações</th></tr></thead>
                                    <tbody>
                                        <?php foreach($servicos as $s): ?>
                                        <tr>
                                            <td class="fw-bold"><?php echo $s['nome']; ?></td>
                                            <td class="small text-muted"><?php echo substr($s['descricao'], 0, 40); ?>...</td>
                                            <td class="text-end">
                                                <?php if($is_admin): ?>
                                                <button class="btn btn-sm btn-outline-warning me-1 btn-edit-servico" 
                                                    data-id="<?php echo $s['id_servico']; ?>" 
                                                    data-nome="<?php echo $s['nome']; ?>" 
                                                    data-descricao="<?php echo $s['descricao']; ?>" 
                                                    data-bs-toggle="modal" data-bs-target="#modalEditServico"><i class="bi bi-pencil"></i></button>
                                                
                                                <form method="POST" style="display:inline;" onsubmit="return confirm('Excluir?');">
                                                    <input type="hidden" name="acao" value="del_servico">
                                                    <input type="hidden" name="id" value="<?php echo $s['id_servico']; ?>">
                                                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                                </form>
                                                <?php else: echo '<span class="badge bg-light text-secondary"><i class="bi bi-lock"></i></span>'; endif; ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- 5. ARQUIVOS E SISTEMA -->
                    <div class="tab-pane fade" id="docs">
                        <!-- Conteúdo de Docs mantido igual (já funciona) -->
                        <div class="row">
                            <div class="col-md-4 border-end">
                                <h6 class="fw-bold mb-3">Upload Modelo</h6>
                                <?php if($is_admin): ?>
                                <form method="POST" enctype="multipart/form-data"><input type="hidden" name="acao" value="upload_modelo"><div class="mb-2"><label>Destino</label><select name="ambiente_destino" class="form-select"><option value="prod">Produção</option><option value="demo">Demo</option></select></div><div class="mb-3"><input type="file" name="arquivo_docx" class="form-control" accept=".docx" required></div><button class="btn btn-success w-100">Enviar</button></form>
                                <?php else: renderRestrictedBanner('um novo Modelo'); endif; ?>
                            </div>
                            <div class="col-md-8">
                                <h6 class="text-success">Modelos Produção</h6>
                                <ul class="list-group small mb-3"><?php foreach($arquivos_prod as $a): ?><li class="list-group-item d-flex justify-content-between"><span><?php echo $a; ?></span><?php if($is_admin): ?><form method="POST" onsubmit="return confirm('Apagar?');" style="display:inline"><input type="hidden" name="acao" value="del_modelo"><input type="hidden" name="ambiente_origem" value="prod"><input type="hidden" name="nome_arquivo" value="<?php echo $a; ?>"><button class="btn btn-sm text-danger p-0"><i class="bi bi-trash"></i></button></form><?php endif; ?></li><?php endforeach; ?></ul>
                            </div>
                        </div>
                    </div>
                    
                    <?php if($_SESSION['perfil'] === 'admin'): ?>
                    <div class="tab-pane fade" id="sys">
                        <div class="alert alert-danger text-center mt-3">
                            <h4><i class="bi bi-exclamation-triangle-fill"></i> ZONA DE PERIGO</h4>
                            <p>Esta ação apagará todos os dados da DEMO. Use com cuidado.</p>
                            <form method="POST" onsubmit="return confirm('Confirmar limpeza TOTAL da Demo?');">
                                <input type="hidden" name="acao" value="reset_demo">
                                <button class="btn btn-danger fw-bold">LIMPAR AMBIENTE DEMO</button>
                            </form>
                        </div>
                    </div>
                    <?php endif; ?>

                </div>
            </div>
        </div>
    </div>
    
    <!-- MODAIS DE EDIÇÃO -->
    <div class="modal fade" id="modalEditFuncao" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><form method="POST"><div class="modal-header"><h5 class="modal-title">Editar Função</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><input type="hidden" name="acao" value="edit_funcao"><input type="hidden" name="id" id="edit_func_id"><div class="mb-3"><label>Nome</label><input type="text" name="nome" id="edit_func_nome" class="form-control" required></div><div class="mb-3"><label>Salário</label><input type="number" step="0.01" name="salario" id="edit_func_salario" class="form-control" required></div></div><div class="modal-footer"><button type="submit" class="btn btn-primary">Salvar</button></div></form></div></div></div>

    <div class="modal fade" id="modalEditTipoLocacao" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><form method="POST"><div class="modal-header"><h5 class="modal-title">Editar Preço</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><input type="hidden" name="acao" value="edit_tipo_locacao"><input type="hidden" name="id" id="edit_tipo_id"><div class="mb-3"><label>Nome</label><input type="text" name="nome" id="edit_tipo_nome" class="form-control" required></div><div class="mb-3"><label>Valor</label><input type="number" step="0.01" name="valor" id="edit_tipo_valor" class="form-control" required></div></div><div class="modal-footer"><button type="submit" class="btn btn-primary">Salvar</button></div></form></div></div></div>

    <div class="modal fade" id="modalEditMarca" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><form method="POST"><div class="modal-header"><h5 class="modal-title">Editar Equipamento</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><input type="hidden" name="acao" value="edit_marca"><input type="hidden" name="id" id="edit_marca_id"><div class="mb-3"><label>Categoria</label><select name="id_locacao" id="edit_marca_locacao" class="form-select" required><?php foreach($tipos_loc_array as $t): ?><option value="<?php echo $t['id_locacao']; ?>"><?php echo $t['nome']; ?></option><?php endforeach; ?></select></div><div class="mb-3"><label>Modelo</label><input type="text" name="nome" id="edit_marca_nome" class="form-control" required></div></div><div class="modal-footer"><button type="submit" class="btn btn-primary">Salvar</button></div></form></div></div></div>

    <div class="modal fade" id="modalEditServico" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><form method="POST"><div class="modal-header"><h5 class="modal-title">Editar Serviço</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><input type="hidden" name="acao" value="edit_servico"><input type="hidden" name="id" id="edit_serv_id"><div class="mb-3"><label>Nome</label><input type="text" name="nome" id="edit_serv_nome" class="form-control" required></div><div class="mb-3"><label>Descrição</label><textarea name="descricao" id="edit_serv_desc" class="form-control" rows="4"></textarea></div></div><div class="modal-footer"><button type="submit" class="btn btn-primary">Salvar</button></div></form></div></div></div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function(){
            // Scripts de ativação de abas e modais
            var triggerTabList = [].slice.call(document.querySelectorAll('#myTab button'))
            triggerTabList.forEach(function (triggerEl) {
                new bootstrap.Tab(triggerEl)
            })

            $('.btn-edit-funcao').click(function(){ $('#edit_func_id').val($(this).data('id')); $('#edit_func_nome').val($(this).data('nome')); $('#edit_func_salario').val($(this).data('salario')); });
            $('.btn-edit-tipo').click(function(){ $('#edit_tipo_id').val($(this).data('id')); $('#edit_tipo_nome').val($(this).data('nome')); $('#edit_tipo_valor').val($(this).data('valor')); });
            $('.btn-edit-marca').click(function(){ $('#edit_marca_id').val($(this).data('id')); $('#edit_marca_nome').val($(this).data('nome')); $('#edit_marca_locacao').val($(this).data('locacao')); });
            $('.btn-edit-servico').click(function(){ $('#edit_serv_id').val($(this).data('id')); $('#edit_serv_nome').val($(this).data('nome')); $('#edit_serv_desc').val($(this).data('descricao')); });
        });
    </script>
</body>
</html>