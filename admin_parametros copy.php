<?php
// Nome do Arquivo: admin_parametros.php
// Função: Gerenciamento completo (RH, Equipamentos, Serviços, Preços e ARQUIVOS).
// Correção: Sintaxe verificada (Parênteses e Chaves).

session_start();
require_once 'config.php';
require_once 'db.php';

// 1. Segurança
if (!isset($_SESSION['usuario_id']) || $_SESSION['perfil'] !== 'admin') {
    header("Location: index.php");
    exit;
}

$conn = Database::getProd();
$msg = '';
$msg_tipo = 'success'; // success, danger, warning

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
            $id = intval($_POST['id']);
            $conn->query("DELETE FROM Tipo_Funcoes WHERE id_funcao = $id");
            $msg = "Função removida!";
        }

        // --- PREÇOS BASE ---
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
            $id = intval($_POST['id']);
            $conn->query("DELETE FROM Tipo_Locacao WHERE id_locacao = $id");
            $msg = "Categoria removida!";
        }

        // --- MARCAS ---
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
            $id = intval($_POST['id']);
            $conn->query("DELETE FROM Marcas WHERE id_marca = $id");
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
            $id = intval($_POST['id']);
            $conn->query("DELETE FROM Tipo_Servicos WHERE id_servico = $id");
            $msg = "Serviço removido!";
        }

        // --- ARQUIVOS DE MODELO ---
        elseif ($acao === 'upload_modelo') {
            $ambiente = $_POST['ambiente_destino']; 
            $pasta = ($ambiente === 'demo') ? 'modelos_demo/' : 'modelos_prod/';
            $diretorio = __DIR__ . '/' . $pasta;
            
            if (!is_dir($diretorio)) {
                mkdir($diretorio, 0755, true);
            }

            if (isset($_FILES['arquivo_docx']) && $_FILES['arquivo_docx']['error'] === 0) {
                $info = pathinfo($_FILES['arquivo_docx']['name']);
                $ext = strtolower($info['extension']);
                
                if ($ext !== 'docx') {
                    throw new Exception("Apenas arquivos .docx são permitidos.");
                }
                
                $nome_final = $_FILES['arquivo_docx']['name'];
                
                if(move_uploaded_file($_FILES['arquivo_docx']['tmp_name'], $diretorio . $nome_final)) {
                    $msg = "Arquivo <strong>$nome_final</strong> enviado com sucesso!";
                } else {
                    throw new Exception("Erro ao mover arquivo.");
                }
            } else {
                throw new Exception("Nenhum arquivo selecionado.");
            }
        }
        elseif ($acao === 'del_modelo') {
            $arquivo = $_POST['nome_arquivo'];
            $ambiente = $_POST['ambiente_origem'];
            $pasta = ($ambiente === 'demo') ? 'modelos_demo/' : 'modelos_prod/';
            $caminho = __DIR__ . '/' . $pasta . $arquivo;
            
            if (basename($caminho) == $arquivo && file_exists($caminho)) {
                unlink($caminho);
                $msg = "Arquivo excluído!";
            } else {
                throw new Exception("Arquivo não encontrado.");
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

// 4. Listagem de Arquivos
function listarArquivos($pasta) {
    $caminho = __DIR__ . '/' . $pasta . '/';
    $arquivos = [];
    if (is_dir($caminho)) {
        $todos = scandir($caminho);
        foreach ($todos as $a) {
            if ($a !== '.' && $a !== '..' && strpos($a, '.docx') !== false) {
                $arquivos[] = $a;
            }
        }
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
    <title>Parâmetros do Sistema | Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body { background-color: #f8f9fa; }
        .nav-tabs .nav-link { color: #495057; font-weight: 600; }
        .nav-tabs .nav-link.active { color: #0d6efd; border-bottom: 3px solid #0d6efd; }
        .card-table { max-height: 500px; overflow-y: auto; }
        .bg-folder-prod { background-color: #e8f5e9; }
        .bg-folder-demo { background-color: #e7f1ff; }
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
                    <li class="nav-item"><button class="nav-link active" id="func-tab" data-bs-toggle="tab" data-bs-target="#func" type="button">Funções</button></li>
                    <li class="nav-item"><button class="nav-link" id="precos-tab" data-bs-toggle="tab" data-bs-target="#precos" type="button">Preços Base</button></li>
                    <li class="nav-item"><button class="nav-link" id="equip-tab" data-bs-toggle="tab" data-bs-target="#equip" type="button">Equipamentos</button></li>
                    <li class="nav-item"><button class="nav-link" id="serv-tab" data-bs-toggle="tab" data-bs-target="#serv" type="button">Serviços</button></li>
                    <li class="nav-item"><button class="nav-link text-primary" id="docs-tab" data-bs-toggle="tab" data-bs-target="#docs" type="button"><i class="bi bi-file-earmark-word me-1"></i> Modelos Word</button></li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content" id="myTabContent">
                    
                    <!-- ABA FUNÇÕES -->
                    <div class="tab-pane fade show active" id="func">
                        <div class="row">
                            <div class="col-md-4 border-end">
                                <h6 class="fw-bold mb-3">Adicionar Função</h6>
                                <form method="POST"><input type="hidden" name="acao" value="add_funcao"><div class="mb-2"><label>Nome</label><input type="text" name="nome" class="form-control" required></div><div class="mb-3"><label>Salário</label><input type="number" step="0.01" name="salario" class="form-control" required></div><button class="btn btn-primary w-100">Salvar</button></form>
                            </div>
                            <div class="col-md-8 card-table">
                                <table class="table table-striped align-middle"><thead><tr><th>Cargo</th><th>Salário</th><th></th></tr></thead>
                                    <tbody><?php foreach($funcoes as $f): ?><tr><td><?php echo $f['nome']; ?></td><td>R$ <?php echo number_format($f['salario_base_default'], 2, ',', '.'); ?></td><td class="text-end"><button class="btn btn-sm btn-outline-warning me-1 btn-edit-funcao" data-id="<?php echo $f['id_funcao']; ?>" data-nome="<?php echo $f['nome']; ?>" data-salario="<?php echo $f['salario_base_default']; ?>" data-bs-toggle="modal" data-bs-target="#modalEditFuncao"><i class="bi bi-pencil"></i></button><form method="POST" style="display:inline;" onsubmit="return confirm('Excluir?');"><input type="hidden" name="acao" value="del_funcao"><input type="hidden" name="id" value="<?php echo $f['id_funcao']; ?>"><button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button></form></td></tr><?php endforeach; ?></tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- ABA PREÇOS -->
                    <div class="tab-pane fade" id="precos">
                        <div class="row">
                            <div class="col-md-4 border-end">
                                <h6 class="fw-bold mb-3">Nova Categoria</h6>
                                <form method="POST"><input type="hidden" name="acao" value="add_tipo_locacao"><div class="mb-2"><label>Nome</label><input type="text" name="nome" class="form-control" required></div><div class="mb-3"><label>Valor Mensal</label><input type="number" step="0.01" name="valor" class="form-control" required></div><button class="btn btn-primary w-100">Salvar</button></form>
                            </div>
                            <div class="col-md-8 card-table">
                                <table class="table table-striped align-middle"><thead><tr><th>Categoria</th><th>Valor</th><th></th></tr></thead>
                                    <tbody><?php foreach($tipos_loc_array as $t): ?><tr><td><?php echo $t['nome']; ?></td><td class="text-success fw-bold">R$ <?php echo number_format($t['valor_mensal_default'], 2, ',', '.'); ?></td><td class="text-end"><button class="btn btn-sm btn-outline-warning me-1 btn-edit-tipo" data-id="<?php echo $t['id_locacao']; ?>" data-nome="<?php echo $t['nome']; ?>" data-valor="<?php echo $t['valor_mensal_default']; ?>" data-bs-toggle="modal" data-bs-target="#modalEditTipoLocacao"><i class="bi bi-pencil"></i></button><form method="POST" style="display:inline;" onsubmit="return confirm('Excluir?');"><input type="hidden" name="acao" value="del_tipo_locacao"><input type="hidden" name="id" value="<?php echo $t['id_locacao']; ?>"><button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button></form></td></tr><?php endforeach; ?></tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- ABA EQUIPAMENTOS -->
                    <div class="tab-pane fade" id="equip">
                        <div class="row">
                            <div class="col-md-4 border-end">
                                <h6 class="fw-bold mb-3">Novo Equipamento</h6>
                                <form method="POST"><input type="hidden" name="acao" value="add_marca"><div class="mb-2"><label>Categoria</label><select name="id_locacao" class="form-select" required><?php foreach($tipos_loc_array as $t): ?><option value="<?php echo $t['id_locacao']; ?>"><?php echo $t['nome']; ?></option><?php endforeach; ?></select></div><div class="mb-3"><label>Modelo</label><input type="text" name="nome" class="form-control" required></div><button class="btn btn-primary w-100">Salvar</button></form>
                            </div>
                            <div class="col-md-8 card-table">
                                <table class="table table-striped align-middle"><thead><tr><th>Categoria</th><th>Modelo</th><th></th></tr></thead>
                                    <tbody><?php foreach($marcas as $m): ?><tr><td><span class="badge bg-secondary"><?php echo $m['tipo']; ?></span></td><td><?php echo $m['nome_marca']; ?></td><td class="text-end"><button class="btn btn-sm btn-outline-warning me-1 btn-edit-marca" data-id="<?php echo $m['id_marca']; ?>" data-nome="<?php echo $m['nome_marca']; ?>" data-locacao="<?php echo $m['id_locacao']; ?>" data-bs-toggle="modal" data-bs-target="#modalEditMarca"><i class="bi bi-pencil"></i></button><form method="POST" style="display:inline;" onsubmit="return confirm('Excluir?');"><input type="hidden" name="acao" value="del_marca"><input type="hidden" name="id" value="<?php echo $m['id_marca']; ?>"><button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button></form></td></tr><?php endforeach; ?></tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- ABA SERVIÇOS -->
                    <div class="tab-pane fade" id="serv">
                        <div class="row">
                            <div class="col-md-4 border-end">
                                <h6 class="fw-bold mb-3">Novo Serviço</h6>
                                <form method="POST"><input type="hidden" name="acao" value="add_servico"><div class="mb-2"><label>Nome</label><input type="text" name="nome" class="form-control" required></div><div class="mb-3"><label>Descrição</label><textarea name="descricao" class="form-control" rows="3"></textarea></div><button class="btn btn-primary w-100">Salvar</button></form>
                            </div>
                            <div class="col-md-8 card-table">
                                <table class="table table-striped align-middle"><thead><tr><th>Serviço</th><th>Descrição</th><th></th></tr></thead>
                                    <tbody><?php foreach($servicos as $s): ?><tr><td class="fw-bold"><?php echo $s['nome']; ?></td><td class="small text-muted"><?php echo substr($s['descricao'], 0, 50); ?>...</td><td class="text-end"><button class="btn btn-sm btn-outline-warning me-1 btn-edit-servico" data-id="<?php echo $s['id_servico']; ?>" data-nome="<?php echo $s['nome']; ?>" data-descricao="<?php echo $s['descricao']; ?>" data-bs-toggle="modal" data-bs-target="#modalEditServico"><i class="bi bi-pencil"></i></button><form method="POST" style="display:inline;" onsubmit="return confirm('Excluir?');"><input type="hidden" name="acao" value="del_servico"><input type="hidden" name="id" value="<?php echo $s['id_servico']; ?>"><button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button></form></td></tr><?php endforeach; ?></tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- ABA ARQUIVOS -->
                    <div class="tab-pane fade" id="docs">
                        <div class="row">
                            <div class="col-md-4 border-end">
                                <h6 class="fw-bold mb-3">Upload de Modelo (.docx)</h6>
                                <div class="alert alert-info small">
                                    <strong>Regra de Nome:</strong><br>
                                    Use: <code>ModeloPropostaNomeDoServico.docx</code>
                                </div>
                                <form method="POST" enctype="multipart/form-data">
                                    <input type="hidden" name="acao" value="upload_modelo">
                                    <div class="mb-2">
                                        <label>Ambiente de Destino</label>
                                        <select name="ambiente_destino" class="form-select">
                                            <option value="prod">Produção (Clientes)</option>
                                            <option value="demo">Demo (Testes)</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label>Arquivo Word</label>
                                        <input type="file" name="arquivo_docx" class="form-control" accept=".docx" required>
                                    </div>
                                    <button type="submit" class="btn btn-success w-100"><i class="bi bi-upload"></i> Enviar Modelo</button>
                                </form>
                            </div>
                            
                            <div class="col-md-8">
                                <div class="p-2 mb-3 rounded bg-folder-prod border border-success">
                                    <h6 class="fw-bold text-success mb-2"><i class="bi bi-folder-fill"></i> Modelos Produção</h6>
                                    <ul class="list-group list-group-flush small">
                                        <?php if(empty($arquivos_prod)): ?>
                                            <li class="list-group-item bg-transparent text-muted">Pasta vazia ou sem arquivos .docx</li>
                                        <?php else: foreach($arquivos_prod as $arq): ?>
                                            <li class="list-group-item bg-transparent d-flex justify-content-between align-items-center">
                                                <span><i class="bi bi-file-word text-primary"></i> <?php echo $arq; ?></span>
                                                <form method="POST" onsubmit="return confirm('Apagar este arquivo permanentemente?');">
                                                    <input type="hidden" name="acao" value="del_modelo">
                                                    <input type="hidden" name="ambiente_origem" value="prod">
                                                    <input type="hidden" name="nome_arquivo" value="<?php echo $arq; ?>">
                                                    <button class="btn btn-sm text-danger border-0 p-0"><i class="bi bi-trash-fill"></i></button>
                                                </form>
                                            </li>
                                        <?php endforeach; endif; ?>
                                    </ul>
                                </div>
                                <div class="p-2 rounded bg-folder-demo border border-primary">
                                    <h6 class="fw-bold text-primary mb-2"><i class="bi bi-folder-fill"></i> Modelos Demo</h6>
                                    <ul class="list-group list-group-flush small">
                                        <?php if(empty($arquivos_demo)): ?>
                                            <li class="list-group-item bg-transparent text-muted">Pasta vazia</li>
                                        <?php else: foreach($arquivos_demo as $arq): ?>
                                            <li class="list-group-item bg-transparent d-flex justify-content-between align-items-center">
                                                <span><i class="bi bi-file-word text-primary"></i> <?php echo $arq; ?></span>
                                                <form method="POST" onsubmit="return confirm('Apagar este arquivo?');">
                                                    <input type="hidden" name="acao" value="del_modelo">
                                                    <input type="hidden" name="ambiente_origem" value="demo">
                                                    <input type="hidden" name="nome_arquivo" value="<?php echo $arq; ?>">
                                                    <button class="btn btn-sm text-danger border-0 p-0"><i class="bi bi-trash-fill"></i></button>
                                                </form>
                                            </li>
                                        <?php endforeach; endif; ?>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- Modais -->
    <div class="modal fade" id="modalEditFuncao" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><form method="POST"><div class="modal-header"><h5 class="modal-title">Editar Função</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><input type="hidden" name="acao" value="edit_funcao"><input type="hidden" name="id" id="edit_func_id"><div class="mb-3"><label>Nome</label><input type="text" name="nome" id="edit_func_nome" class="form-control" required></div><div class="mb-3"><label>Salário</label><input type="number" step="0.01" name="salario" id="edit_func_salario" class="form-control" required></div></div><div class="modal-footer"><button type="submit" class="btn btn-primary">Salvar</button></div></form></div></div></div>

    <div class="modal fade" id="modalEditTipoLocacao" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><form method="POST"><div class="modal-header"><h5 class="modal-title">Editar Preço</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><input type="hidden" name="acao" value="edit_tipo_locacao"><input type="hidden" name="id" id="edit_tipo_id"><div class="mb-3"><label>Nome</label><input type="text" name="nome" id="edit_tipo_nome" class="form-control" required></div><div class="mb-3"><label>Valor</label><input type="number" step="0.01" name="valor" id="edit_tipo_valor" class="form-control" required></div></div><div class="modal-footer"><button type="submit" class="btn btn-primary">Salvar</button></div></form></div></div></div>

    <div class="modal fade" id="modalEditMarca" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><form method="POST"><div class="modal-header"><h5 class="modal-title">Editar Equipamento</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><input type="hidden" name="acao" value="edit_marca"><input type="hidden" name="id" id="edit_marca_id"><div class="mb-3"><label>Categoria</label><select name="id_locacao" id="edit_marca_locacao" class="form-select" required><?php foreach($tipos_loc_array as $t): ?><option value="<?php echo $t['id_locacao']; ?>"><?php echo $t['nome']; ?></option><?php endforeach; ?></select></div><div class="mb-3"><label>Modelo</label><input type="text" name="nome" id="edit_marca_nome" class="form-control" required></div></div><div class="modal-footer"><button type="submit" class="btn btn-primary">Salvar</button></div></form></div></div></div>

    <div class="modal fade" id="modalEditServico" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><form method="POST"><div class="modal-header"><h5 class="modal-title">Editar Serviço</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><input type="hidden" name="acao" value="edit_servico"><input type="hidden" name="id" id="edit_serv_id"><div class="mb-3"><label>Nome</label><input type="text" name="nome" id="edit_serv_nome" class="form-control" required></div><div class="mb-3"><label>Descrição</label><textarea name="descricao" id="edit_serv_desc" class="form-control" rows="4"></textarea></div></div><div class="modal-footer"><button type="submit" class="btn btn-primary">Salvar</button></div></form></div></div></div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        $(document).ready(function(){
            $('.btn-edit-funcao').click(function(){ $('#edit_func_id').val($(this).data('id')); $('#edit_func_nome').val($(this).data('nome')); $('#edit_func_salario').val($(this).data('salario')); });
            $('.btn-edit-tipo').click(function(){ $('#edit_tipo_id').val($(this).data('id')); $('#edit_tipo_nome').val($(this).data('nome')); $('#edit_tipo_valor').val($(this).data('valor')); });
            $('.btn-edit-marca').click(function(){ $('#edit_marca_id').val($(this).data('id')); $('#edit_marca_nome').val($(this).data('nome')); $('#edit_marca_locacao').val($(this).data('locacao')); });
            $('.btn-edit-servico').click(function(){ $('#edit_serv_id').val($(this).data('id')); $('#edit_serv_nome').val($(this).data('nome')); $('#edit_serv_desc').val($(this).data('descricao')); });
        });
    </script>
</body>
</html><?php
// Nome do Arquivo: admin_parametros.php
// Função: Gerenciamento de tabelas auxiliares.
// ATUALIZAÇÃO: Grava nos DOIS bancos (Prod e Demo) simultaneamente para manter sincronia.

session_start();
require_once 'config.php';
require_once 'db.php';

// 1. Segurança: Apenas Admin
if (!isset($_SESSION['usuario_id']) || $_SESSION['perfil'] !== 'admin') {
    header("Location: painel.php"); // Corrigido para painel.php
    exit;
}

// Conecta nos DOIS bancos para operações de escrita
$connProd = Database::getProd();
$connDemo = Database::getDemo();

$msg = '';
$msg_tipo = 'success'; 

// 2. Processamento (Grava em DUPLO)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';

    try {
        // --- FUNÇÕES ---
        if ($acao === 'add_funcao') {
            // Prod
            $stmt1 = $connProd->prepare("INSERT INTO Tipo_Funcoes (nome, salario_base_default) VALUES (?, ?)");
            $stmt1->bind_param('sd', $_POST['nome'], $_POST['salario']);
            $stmt1->execute();
            // Demo
            $stmt2 = $connDemo->prepare("INSERT INTO Tipo_Funcoes (nome, salario_base_default) VALUES (?, ?)");
            $stmt2->bind_param('sd', $_POST['nome'], $_POST['salario']);
            $stmt2->execute();
            
            $msg = "Função adicionada em ambos os ambientes!";
        }
        elseif ($acao === 'del_funcao') {
            $id = intval($_POST['id']);
            // Tenta deletar nos dois (pode ser que IDs não batam se bancos estiverem muito diferentes, mas é o melhor esforço)
            $connProd->query("DELETE FROM Tipo_Funcoes WHERE id_funcao = $id");
            $connDemo->query("DELETE FROM Tipo_Funcoes WHERE id_funcao = $id");
            $msg = "Função removida!";
        }

        // --- PREÇOS BASE ---
        elseif ($acao === 'add_tipo_locacao') {
            $stmt1 = $connProd->prepare("INSERT INTO Tipo_Locacao (nome, valor_mensal_default) VALUES (?, ?)");
            $stmt1->bind_param('sd', $_POST['nome'], $_POST['valor']);
            $stmt1->execute();
            
            $stmt2 = $connDemo->prepare("INSERT INTO Tipo_Locacao (nome, valor_mensal_default) VALUES (?, ?)");
            $stmt2->bind_param('sd', $_POST['nome'], $_POST['valor']);
            $stmt2->execute();
            
            $msg = "Categoria adicionada!";
        }
        elseif ($acao === 'edit_tipo_locacao') {
            // Edição é delicada sincronizar por ID. Vamos editar no PROD e torcer pelo Demo, 
            // ou o ideal é manter sync manual. Aqui faremos nos dois pelo ID.
            $stmt1 = $connProd->prepare("UPDATE Tipo_Locacao SET nome=?, valor_mensal_default=? WHERE id_locacao=?");
            $stmt1->bind_param('sdi', $_POST['nome'], $_POST['valor'], $_POST['id']);
            $stmt1->execute();
            
            $stmt2 = $connDemo->prepare("UPDATE Tipo_Locacao SET nome=?, valor_mensal_default=? WHERE id_locacao=?");
            $stmt2->bind_param('sdi', $_POST['nome'], $_POST['valor'], $_POST['id']);
            $stmt2->execute();
            
            $msg = "Preço atualizado!";
        }

        // --- MARCAS (EQUIPAMENTOS) ---
        elseif ($acao === 'add_marca') {
            $stmt1 = $connProd->prepare("INSERT INTO Marcas (nome_marca, id_locacao) VALUES (?, ?)");
            $stmt1->bind_param('si', $_POST['nome'], $_POST['id_locacao']);
            $stmt1->execute();
            
            $stmt2 = $connDemo->prepare("INSERT INTO Marcas (nome_marca, id_locacao) VALUES (?, ?)");
            $stmt2->bind_param('si', $_POST['nome'], $_POST['id_locacao']);
            $stmt2->execute();
            
            $msg = "Equipamento adicionado!";
        }
        elseif ($acao === 'del_marca') {
            $id = intval($_POST['id']);
            $connProd->query("DELETE FROM Marcas WHERE id_marca = $id");
            $connDemo->query("DELETE FROM Marcas WHERE id_marca = $id");
            $msg = "Equipamento removido!";
        }

        // --- SERVIÇOS ---
        elseif ($acao === 'add_servico') {
            $stmt1 = $connProd->prepare("INSERT INTO Tipo_Servicos (nome, descricao) VALUES (?, ?)");
            $stmt1->bind_param('ss', $_POST['nome'], $_POST['descricao']);
            $stmt1->execute();
            
            $stmt2 = $connDemo->prepare("INSERT INTO Tipo_Servicos (nome, descricao) VALUES (?, ?)");
            $stmt2->bind_param('ss', $_POST['nome'], $_POST['descricao']);
            $stmt2->execute();
            
            $msg = "Serviço adicionado!";
        }
        elseif ($acao === 'del_servico') {
            $id = intval($_POST['id']);
            $connProd->query("DELETE FROM Tipo_Servicos WHERE id_servico = $id");
            $connDemo->query("DELETE FROM Tipo_Servicos WHERE id_servico = $id");
            $msg = "Serviço removido!";
        }

        // --- ARQUIVOS DE MODELO ---
        elseif ($acao === 'upload_modelo') {
            // Upload já era separado por pasta, aqui mantemos a lógica original de escolher destino
            // Mas facilitamos para subir nos dois se quiser
            $ambiente = $_POST['ambiente_destino']; 
            $pasta = ($ambiente === 'demo') ? 'modelos_demo/' : 'modelos_prod/';
            $diretorio = __DIR__ . '/' . $pasta;
            
            if (!is_dir($diretorio)) mkdir($diretorio, 0755, true);

            if (isset($_FILES['arquivo_docx']) && $_FILES['arquivo_docx']['error'] === 0) {
                $ext = strtolower(pathinfo($_FILES['arquivo_docx']['name'], PATHINFO_EXTENSION));
                if ($ext !== 'docx') throw new Exception("Apenas .docx");
                
                $nome_final = $_FILES['arquivo_docx']['name'];
                if(move_uploaded_file($_FILES['arquivo_docx']['tmp_name'], $diretorio . $nome_final)) {
                    $msg = "Arquivo enviado para <strong>$ambiente</strong>!";
                } else {
                    throw new Exception("Erro ao mover arquivo.");
                }
            }
        }
        elseif ($acao === 'del_modelo') {
            $arquivo = $_POST['nome_arquivo'];
            $ambiente = $_POST['ambiente_origem'];
            $pasta = ($ambiente === 'demo') ? 'modelos_demo/' : 'modelos_prod/';
            $caminho = __DIR__ . '/' . $pasta . $arquivo;
            if (file_exists($caminho)) { unlink($caminho); $msg = "Arquivo excluído!"; }
        }

    } catch (Exception $e) {
        $msg = "Erro: " . $e->getMessage();
        $msg_tipo = 'danger';
    }
}

// 3. Consultas (Lê do PROD para exibir na tabela)
$funcoes = $connProd->query("SELECT * FROM Tipo_Funcoes ORDER BY nome");
$tipos_loc_tabela = $connProd->query("SELECT * FROM Tipo_Locacao ORDER BY nome");
$marcas  = $connProd->query("SELECT m.*, t.nome as tipo FROM Marcas m LEFT JOIN Tipo_Locacao t ON m.id_locacao = t.id_locacao ORDER BY t.nome ASC, m.nome_marca ASC");
$servicos = $connProd->query("SELECT * FROM Tipo_Servicos ORDER BY nome");

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
        .card-table { max-height: 500px; overflow-y: auto; }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark bg-dark mb-4"><div class="container"><a class="navbar-brand" href="painel.php"><i class="bi bi-arrow-left me-2"></i>Voltar ao Painel</a><span class="text-white">Cadastros</span></div></nav>
    <div class="container pb-5">
        <?php if($msg): ?><div class="alert alert-<?php echo $msg_tipo; ?> alert-dismissible fade show"><?php echo $msg; ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>

        <div class="card shadow-sm border-0">
            <div class="card-header bg-white">
                <ul class="nav nav-tabs card-header-tabs" id="myTab" role="tablist">
                    <li class="nav-item"><button class="nav-link active" id="func-tab" data-bs-toggle="tab" data-bs-target="#func">Funções</button></li>
                    <li class="nav-item"><button class="nav-link" id="precos-tab" data-bs-toggle="tab" data-bs-target="#precos">Preços Base</button></li>
                    <li class="nav-item"><button class="nav-link" id="equip-tab" data-bs-toggle="tab" data-bs-target="#equip">Equipamentos</button></li>
                    <li class="nav-item"><button class="nav-link" id="serv-tab" data-bs-toggle="tab" data-bs-target="#serv">Serviços</button></li>
                    <li class="nav-item"><button class="nav-link text-primary" id="docs-tab" data-bs-toggle="tab" data-bs-target="#docs">Modelos Word</button></li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content" id="myTabContent">
                    <!-- ABA FUNÇÕES -->
                    <div class="tab-pane fade show active" id="func"><div class="row"><div class="col-md-4 border-end"><form method="POST"><input type="hidden" name="acao" value="add_funcao"><div class="mb-2"><label>Nome</label><input type="text" name="nome" class="form-control" required></div><div class="mb-3"><label>Salário</label><input type="number" step="0.01" name="salario" class="form-control" required></div><button class="btn btn-primary w-100">Salvar (Prod + Demo)</button></form></div><div class="col-md-8 card-table"><table class="table table-striped align-middle"><thead><tr><th>Cargo</th><th>Salário</th><th></th></tr></thead><tbody><?php foreach($funcoes as $f): ?><tr><td><?php echo $f['nome']; ?></td><td>R$ <?php echo number_format($f['salario_base_default'], 2, ',', '.'); ?></td><td class="text-end"><form method="POST" style="display:inline;" onsubmit="return confirm('Excluir?');"><input type="hidden" name="acao" value="del_funcao"><input type="hidden" name="id" value="<?php echo $f['id_funcao']; ?>"><button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button></form></td></tr><?php endforeach; ?></tbody></table></div></div></div>
                    
                    <!-- ABA PREÇOS -->
                    <div class="tab-pane fade" id="precos"><div class="row"><div class="col-md-4 border-end"><form method="POST"><input type="hidden" name="acao" value="add_tipo_locacao"><div class="mb-2"><label>Nome</label><input type="text" name="nome" class="form-control" required></div><div class="mb-3"><label>Valor</label><input type="number" step="0.01" name="valor" class="form-control" required></div><button class="btn btn-primary w-100">Salvar (Prod + Demo)</button></form></div><div class="col-md-8 card-table"><table class="table table-striped align-middle"><thead><tr><th>Categoria</th><th>Valor</th><th></th></tr></thead><tbody><?php foreach($tipos_loc_array as $t): ?><tr><td><?php echo $t['nome']; ?></td><td>R$ <?php echo number_format($t['valor_mensal_default'], 2, ',', '.'); ?></td><td class="text-end"><button class="btn btn-sm btn-outline-warning me-1 btn-edit-tipo" data-id="<?php echo $t['id_locacao']; ?>" data-nome="<?php echo $t['nome']; ?>" data-valor="<?php echo $t['valor_mensal_default']; ?>" data-bs-toggle="modal" data-bs-target="#modalEditTipoLocacao"><i class="bi bi-pencil"></i></button></td></tr><?php endforeach; ?></tbody></table></div></div></div>

                    <!-- ABA EQUIPAMENTOS -->
                    <div class="tab-pane fade" id="equip"><div class="row"><div class="col-md-4 border-end"><form method="POST"><input type="hidden" name="acao" value="add_marca"><div class="mb-2"><label>Categoria</label><select name="id_locacao" class="form-select" required><?php foreach($tipos_loc_array as $t): ?><option value="<?php echo $t['id_locacao']; ?>"><?php echo $t['nome']; ?></option><?php endforeach; ?></select></div><div class="mb-3"><label>Modelo</label><input type="text" name="nome" class="form-control" required></div><button class="btn btn-primary w-100">Salvar (Prod + Demo)</button></form></div><div class="col-md-8 card-table"><table class="table table-striped align-middle"><thead><tr><th>Categoria</th><th>Modelo</th><th></th></tr></thead><tbody><?php foreach($marcas as $m): ?><tr><td><?php echo $m['tipo']; ?></td><td><?php echo $m['nome_marca']; ?></td><td class="text-end"><form method="POST" style="display:inline;" onsubmit="return confirm('Excluir?');"><input type="hidden" name="acao" value="del_marca"><input type="hidden" name="id" value="<?php echo $m['id_marca']; ?>"><button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button></form></td></tr><?php endforeach; ?></tbody></table></div></div></div>

                    <!-- ABA SERVIÇOS -->
                    <div class="tab-pane fade" id="serv"><div class="row"><div class="col-md-4 border-end"><form method="POST"><input type="hidden" name="acao" value="add_servico"><div class="mb-2"><label>Nome</label><input type="text" name="nome" class="form-control" required></div><div class="mb-3"><label>Descrição</label><textarea name="descricao" class="form-control" rows="3"></textarea></div><button class="btn btn-primary w-100">Salvar (Prod + Demo)</button></form></div><div class="col-md-8 card-table"><table class="table table-striped align-middle"><thead><tr><th>Serviço</th><th>Descrição</th><th></th></tr></thead><tbody><?php foreach($servicos as $s): ?><tr><td><?php echo $s['nome']; ?></td><td><?php echo substr($s['descricao'], 0, 50); ?></td><td class="text-end"><form method="POST" style="display:inline;" onsubmit="return confirm('Excluir?');"><input type="hidden" name="acao" value="del_servico"><input type="hidden" name="id" value="<?php echo $s['id_servico']; ?>"><button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button></form></td></tr><?php endforeach; ?></tbody></table></div></div></div>

                    <!-- ABA DOCS -->
                    <div class="tab-pane fade" id="docs">
                        <div class="row">
                            <div class="col-md-4 border-end">
                                <h6 class="fw-bold">Upload Modelo</h6>
                                <form method="POST" enctype="multipart/form-data"><input type="hidden" name="acao" value="upload_modelo"><div class="mb-2"><label>Destino</label><select name="ambiente_destino" class="form-select"><option value="prod">Produção</option><option value="demo">Demo</option></select></div><div class="mb-3"><input type="file" name="arquivo_docx" class="form-control" accept=".docx" required></div><button class="btn btn-success w-100">Enviar</button></form>
                            </div>
                            <div class="col-md-8">
                                <h6 class="text-success">Modelos Produção</h6>
                                <ul class="list-group small mb-3"><?php foreach($arquivos_prod as $a): ?><li class="list-group-item d-flex justify-content-between"><span><?php echo $a; ?></span><form method="POST" onsubmit="return confirm('Apagar?');" style="display:inline"><input type="hidden" name="acao" value="del_modelo"><input type="hidden" name="ambiente_origem" value="prod"><input type="hidden" name="nome_arquivo" value="<?php echo $a; ?>"><button class="btn btn-sm text-danger p-0"><i class="bi bi-trash"></i></button></form></li><?php endforeach; ?></ul>
                                <h6 class="text-primary">Modelos Demo</h6>
                                <ul class="list-group small"><?php foreach($arquivos_demo as $a): ?><li class="list-group-item d-flex justify-content-between"><span><?php echo $a; ?></span><form method="POST" onsubmit="return confirm('Apagar?');" style="display:inline"><input type="hidden" name="acao" value="del_modelo"><input type="hidden" name="ambiente_origem" value="demo"><input type="hidden" name="nome_arquivo" value="<?php echo $a; ?>"><button class="btn btn-sm text-danger p-0"><i class="bi bi-trash"></i></button></form></li><?php endforeach; ?></ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal Editar Preço (Único que deixei completo por ser mais complexo) -->
    <div class="modal fade" id="modalEditTipoLocacao" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><form method="POST"><div class="modal-header"><h5 class="modal-title">Editar Preço</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><input type="hidden" name="acao" value="edit_tipo_locacao"><input type="hidden" name="id" id="edit_tipo_id"><div class="mb-3"><label>Nome</label><input type="text" name="nome" id="edit_tipo_nome" class="form-control" required></div><div class="mb-3"><label>Valor</label><input type="number" step="0.01" name="valor" id="edit_tipo_valor" class="form-control" required></div></div><div class="modal-footer"><button type="submit" class="btn btn-primary">Salvar (Prod + Demo)</button></div></form></div></div></div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function(){
            $('.btn-edit-tipo').click(function(){ $('#edit_tipo_id').val($(this).data('id')); $('#edit_tipo_nome').val($(this).data('nome')); $('#edit_tipo_valor').val($(this).data('valor')); });
        });
    </script>
</body>
</html>