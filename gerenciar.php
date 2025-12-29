<?php
// ARQUIVO: gerenciar.php
// VERSÃO: CORREÇÃO DE CAMPOS DESABILITADOS EM CLIENTES

session_start();
require_once 'config.php';
require_once 'db.php';
require_once 'valida_demo.php';

if (!isset($_SESSION['usuario_id'])) { header("Location: login.php"); exit; }

$id_usuario = $_SESSION['usuario_id'];
$is_demo = (isset($_SESSION['ambiente']) && $_SESSION['ambiente'] === 'demo');

// --- CONFIGURAÇÃO ---
$config = [
    'Clientes' => [
        'titulo' => 'Meus Clientes', 'pk' => 'id_cliente', 'privado' => true,
        'campos' => [
            'nome_cliente' => ['label' => 'Nome', 'type' => 'text'],
            'empresa' => ['label' => 'Empresa', 'type' => 'text'],
            'email' => ['label' => 'E-mail', 'type' => 'email'],
            'telefone' => ['label' => 'Telefone', 'type' => 'text']
        ]
    ],
    'Tipo_Servicos' => [
        'titulo' => 'Tipos de Serviços', 'pk' => 'id_servico', 'privado' => false,
        'campos' => ['nome' => ['label' => 'Nome', 'type' => 'text'], 'descricao' => ['label' => 'Escopo', 'type' => 'textarea']]
    ],
    'Tipo_Locacao' => [ 
        'titulo' => 'Tipos de Equipamentos (Categorias)', 'pk' => 'id_locacao', 'privado' => false,
        'campos' => [
            'nome' => ['label' => 'Nome da Categoria', 'type' => 'text'],
            'valor_mensal_default' => ['label' => 'Valor Mensal Base (R$)', 'type' => 'number']
        ]
    ],
    'Marcas' => [
        'titulo' => 'Modelos / Marcas de Equipamentos', 'pk' => 'id_marca', 'privado' => false,
        'campos' => [
            'id_locacao' => ['label' => 'Categoria do Equipamento', 'type' => 'select_locacao'],
            'nome_marca' => ['label' => 'Descrição do Modelo/Marca', 'type' => 'text']
        ]
    ],
    'Tipo_Funcoes' => [
        'titulo' => 'Funções e Salários', 'pk' => 'id_funcao', 'privado' => false,
        'campos' => [
            'nome' => ['label' => 'Cargo / Função', 'type' => 'text'],
            'salario_base_default' => ['label' => 'Salário Base (R$)', 'type' => 'number']
        ]
    ],
    'Tipo_Estadia' => [
        'titulo' => 'Custos de Estadia', 'pk' => 'id_estadia', 'privado' => false,
        'campos' => [
            'nome' => ['label' => 'Descrição', 'type' => 'text'],
            'valor_unitario_default' => ['label' => 'Valor Unitário (R$)', 'type' => 'number']
        ]
    ],
    'Tipo_Consumo' => [
        'titulo' => 'Custos de Combustível', 'pk' => 'id_consumo', 'privado' => false,
        'campos' => [
            'nome' => ['label' => 'Tipo (Gasolina/Diesel)', 'type' => 'text'],
            'valor_litro_default' => ['label' => 'Preço Litro (R$)', 'type' => 'number'],
            'consumo_kml_default' => ['label' => 'Consumo Médio (Km/L)', 'type' => 'number']
        ]
    ],
    'Tipo_Custo_Admin' => [
        'titulo' => 'Custos Administrativos', 'pk' => 'id_custo_admin', 'privado' => false,
        'campos' => [
            'nome' => ['label' => 'Descrição do Custo', 'type' => 'text'],
            'valor_default' => ['label' => 'Valor Padrão (R$)', 'type' => 'number']
        ]
    ]
];

$tabela = $_GET['tabela'] ?? '';
if (!array_key_exists($tabela, $config)) { 
    header("Location: index.php"); exit;
}

$cfg = $config[$tabela];
$pk = $cfg['pk'];
$is_private = $cfg['privado'];
$dados_editar = null;

// --- LÓGICA DE BLOQUEIO (Aqui define se pode editar ou não) ---
// Se for DEMO E a tabela NÃO for Clientes, bloqueia.
// Ou seja: Se for Clientes, $block_edition será FALSE (Permitido)
$block_edition = ($is_demo && $tabela !== 'Clientes');

// --- TRAVA DE BACKEND ---
if ($block_edition && ($_SERVER['REQUEST_METHOD'] === 'POST' || isset($_GET['acao']))) {
    $_SESSION['mensagem_erro'] = "Ação bloqueada no modo demonstração (Apenas leitura para configurações globais).";
    header("Location: gerenciar.php?tabela=$tabela");
    exit;
}

// --- PREPARA DROPDOWN PARA MARCAS ---
$opcoes_locacao = [];
if ($tabela === 'Marcas') {
    $res_loc = $conn->query("SELECT id_locacao, nome FROM Tipo_Locacao ORDER BY nome ASC");
    while($r = $res_loc->fetch_assoc()) { $opcoes_locacao[] = $r; }
}

// --- EXCLUSÃO ---
if (isset($_GET['acao']) && $_GET['acao'] === 'excluir' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $where = "$pk = ?";
    $types = 'i'; $params = [$id];
    
    // Se for tabela privada, garante que só apaga o que é do usuário
    if ($is_private) { 
        $where .= " AND id_criador = ?";
        $types .= 'i'; 
        $params[] = $id_usuario;
    }

    $stmt = $conn->prepare("DELETE FROM $tabela WHERE $where");
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $_SESSION['mensagem_sucesso'] = 'Registro excluído com sucesso!';
    header("Location: gerenciar.php?tabela=$tabela");
    exit;
}

// --- EDIÇÃO (BUSCA DADOS) ---
if (isset($_GET['acao']) && $_GET['acao'] === 'editar' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $where = "$pk = ?";
    $types = 'i'; $params = [$id];
    
    if ($is_private) { 
        $where .= " AND id_criador = ?";
        $types .= 'i'; 
        $params[] = $id_usuario;
    }
    
    $stmt = $conn->prepare("SELECT * FROM $tabela WHERE $where");
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $dados_editar = $stmt->get_result()->fetch_assoc();
}

// --- SALVAR (POST) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $types = ""; $params = []; $campos = array_keys($cfg['campos']);
    
    foreach ($campos as $col) {
        $val = $_POST[$col] ?? null;
        $meta = $cfg['campos'][$col];
        
        if ($meta['type'] == 'number') { $types .= 'd'; $params[] = (float)$val; }
        elseif ($meta['type'] == 'select_locacao') { $types .= 'i'; $params[] = (int)$val; }
        else { $types .= 's'; $params[] = $val; }
    }

    if (isset($_POST['id_editar']) && !empty($_POST['id_editar'])) {
        // UPDATE
        $id_edit = intval($_POST['id_editar']);
        $set_clause = implode('=?, ', $campos) . '=?';
        $where = "WHERE $pk = ?";
        $types .= 'i'; $params[] = $id_edit;

        if ($is_private) {
            $where .= " AND id_criador = ?";
            $types .= 'i'; $params[] = $id_usuario;
        }
        $sql = "UPDATE $tabela SET $set_clause $where";
    } else {
        // INSERT
        $cols = implode(', ', $campos);
        $placeholders = str_repeat('?,', count($campos) - 1) . '?';
        
        if ($is_private) { 
            $cols .= ', id_criador';
            $placeholders .= ', ?';
            $types .= 'i'; $params[] = $id_usuario;
        }
        $sql = "INSERT INTO $tabela ($cols) VALUES ($placeholders)";
    }
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    
    if ($stmt->execute()) {
        $_SESSION['mensagem_sucesso'] = 'Registro salvo com sucesso!';
    } else {
        $_SESSION['mensagem_erro'] = "Erro: " . $stmt->error;
    }
    header("Location: gerenciar.php?tabela=$tabela");
    exit;
}

// --- LISTAR ---
$where_clause = '';
// Se for tabela privada, filtra pelo usuário. Se for pública, mostra tudo.
if ($is_private) { $where_clause = " WHERE id_criador = $id_usuario"; }

$sql_lista = "SELECT * FROM $tabela $where_clause ORDER BY $pk DESC";
if ($tabela === 'Marcas') {
    $sql_lista = "SELECT m.*, tl.nome as nome_categoria FROM Marcas m LEFT JOIN Tipo_Locacao tl ON m.id_locacao = tl.id_locacao ORDER BY m.id_marca DESC";
}
$resultado = $conn->query($sql_lista);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8"><title><?= $cfg['titulo'] ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-4 mb-5">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold text-dark"><i class="fa-solid fa-gear text-primary me-2"></i><?= $cfg['titulo'] ?></h3>
        <a href="index.php" class="btn btn-secondary"><i class="fa-solid fa-arrow-left me-2"></i>Voltar</a>
    </div>

    <?php if (isset($_SESSION['mensagem_sucesso'])): ?>
        <div class="alert alert-success alert-dismissible fade show"><?= $_SESSION['mensagem_sucesso'] ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php unset($_SESSION['mensagem_sucesso']); ?>
    <?php endif; ?>
    <?php if (isset($_SESSION['mensagem_erro'])): ?>
        <div class="alert alert-danger alert-dismissible fade show"><?= $_SESSION['mensagem_erro'] ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php unset($_SESSION['mensagem_erro']); ?>
    <?php endif; ?>

    <!-- FORMULÁRIO -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <h6 class="mb-0 fw-bold"><?= $dados_editar ? '✏️ Editar Registro' : '➕ Adicionar Novo' ?></h6>
        </div>
        <div class="card-body">
            <form method="POST" action="gerenciar.php?tabela=<?= $tabela ?>">
                <?php if($dados_editar): ?><input type="hidden" name="id_editar" value="<?= $dados_editar[$pk] ?>"><?php endif; ?>

                <div class="row g-3">
                    <?php foreach($cfg['campos'] as $campo => $meta): ?>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-uppercase text-muted"><?= htmlspecialchars($meta['label']) ?></label>
                            
                            <?php 
                            $valor_atual = $dados_editar[$campo] ?? '';
                            // CORREÇÃO AQUI: Usa a variável $block_edition calculada no topo
                            $disabled = $block_edition ? 'disabled' : ''; 
                            ?>

                            <?php if($meta['type'] === 'textarea'): ?>
                                <textarea name="<?= $campo ?>" class="form-control" rows="2" <?= $disabled ?>><?= htmlspecialchars($valor_atual) ?></textarea>
                            
                            <?php elseif($meta['type'] === 'select_locacao'): ?>
                                <select name="<?= $campo ?>" class="form-select" <?= $disabled ?> required>
                                    <option value="">Selecione...</option>
                                    <?php foreach($opcoes_locacao as $opt): ?>
                                        <option value="<?= $opt['id_locacao'] ?>" <?= ((string)$valor_atual == (string)$opt['id_locacao']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($opt['nome']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>

                            <?php else: ?>
                                <input type="<?= $meta['type'] ?>" name="<?= $campo ?>" class="form-control" 
                                       step="<?= $meta['type'] == 'number' ? '0.01' : '1' ?>" 
                                       value="<?= htmlspecialchars($valor_atual) ?>" 
                                       <?= $disabled ?> required>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="mt-4 d-flex gap-2">
                    <?php if (!$block_edition): ?>
                        <button type="submit" class="btn btn-primary px-4"><?= $dados_editar ? 'Salvar Alterações' : 'Cadastrar' ?></button>
                    <?php else: ?>
                        <span class="text-muted small align-self-center"><i class="fa-solid fa-lock me-1"></i> Edição bloqueada no modo Demo.</span>
                    <?php endif; ?>
                    
                    <?php if ($dados_editar): ?>
                        <a href="gerenciar.php?tabela=<?= $tabela ?>" class="btn btn-outline-secondary">Cancelar</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <!-- LISTAGEM -->
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0 align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th class="ps-3">ID</th>
                            <?php foreach($cfg['campos'] as $meta): ?><th><?= $meta['label'] ?></th><?php endforeach; ?>
                            <th class="text-end pe-3">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if($resultado && $resultado->num_rows > 0): while($row = $resultado->fetch_assoc()): ?>
                        <tr>
                            <td class="ps-3 fw-bold">#<?= $row[$pk] ?></td>
                            <?php foreach(array_keys($cfg['campos']) as $campo): ?>
                                <td>
                                    <?php 
                                        $valor = $row[$campo] ?? '';
                                        if ($tabela === 'Marcas' && $campo === 'id_locacao') {
                                            $valor = $row['nome_categoria'] ?? 'N/A';
                                        } elseif (isset($cfg['campos'][$campo]['type']) && $cfg['campos'][$campo]['type'] == 'number') {
                                            $valor = 'R$ ' . number_format((float)$valor, 2, ',', '.');
                                        }
                                        echo htmlspecialchars($valor);
                                    ?>
                                </td>
                            <?php endforeach; ?>
                            <td class="text-end pe-3">
                                <?php if (!$block_edition): ?>
                                    <a href="?tabela=<?= $tabela ?>&acao=editar&id=<?= $row[$pk] ?>" class="btn btn-sm btn-outline-warning me-1"><i class="fa-solid fa-pen"></i></a>
                                    <a href="?tabela=<?= $tabela ?>&acao=excluir&id=<?= $row[$pk] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Tem certeza?')"><i class="fa-solid fa-trash"></i></a>
                                <?php else: ?>
                                    <span class="badge bg-secondary"><i class="fa-solid fa-lock"></i></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; else: ?>
                            <tr><td colspan="10" class="text-center py-4 text-muted">Nenhum registro encontrado.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>