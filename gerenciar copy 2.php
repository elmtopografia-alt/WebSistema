<?php
// Nome da página: gerenciar.php
// VERSÃO: Ajustada para 'Marcas' em vez de 'Equipamento_Modelos'

session_start();
require 'db.php';
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$configTabelas = [
    // ALTERAÇÃO: Tabela 'Marcas' configurada corretamente
    'Marcas' => [ 
        'titulo' => 'Modelos de Equipamentos (Marcas)', 
        'pk' => 'id_marca', 
        'campos' => [ 
            'id_locacao' => ['label' => 'ID do Tipo (Ver Tabela Tipo_Locacao)', 'tipo' => 'number'], 
            'nome_marca' => ['label' => 'Nome do Modelo/Marca', 'tipo' => 'text']
        ]
    ],
    // Demais tabelas mantidas...
    'Clientes' => [ 'titulo' => 'Clientes', 'pk' => 'id_cliente', 'campos' => [ 'nome_cliente' => ['label' => 'Nome', 'tipo' => 'text'], 'empresa' => ['label' => 'Empresa', 'tipo' => 'text'], 'cnpj_cpf' => ['label' => 'CNPJ/CPF', 'tipo' => 'text'], 'email' => ['label' => 'E-mail', 'tipo' => 'email'], 'telefone' => ['label' => 'Telefone', 'tipo' => 'text'], 'celular' => ['label' => 'Celular', 'tipo' => 'text'], 'whatsapp' => ['label' => 'WhatsApp', 'tipo' => 'text'] ] ],
    'Tipo_Servicos' => [ 'titulo' => 'Tipos de Serviços', 'pk' => 'id_servico', 'campos' => [ 'nome' => ['label' => 'Nome', 'tipo' => 'text'], 'descricao' => ['label' => 'Descrição', 'tipo' => 'textarea']]],
    'Tipo_Funcoes' => [ 'titulo' => 'Funções', 'pk' => 'id_funcao', 'campos' => [ 'nome' => ['label' => 'Nome', 'tipo' => 'text'], 'salario_base_default' => ['label' => 'Salário Padrão', 'tipo' => 'number']]],
    'Tipo_Estadia' => [ 'titulo' => 'Custos de Estadia', 'pk' => 'id_estadia', 'campos' => [ 'nome' => ['label' => 'Nome', 'tipo' => 'text'], 'valor_unitario_default' => ['label' => 'Valor Padrão', 'tipo' => 'number']]],
    'Tipo_Locacao' => [ 'titulo' => 'Itens de Locação', 'pk' => 'id_locacao', 'campos' => [ 'nome' => ['label' => 'Nome', 'tipo' => 'text'], 'valor_mensal_default' => ['label' => 'Valor Mensal Padrão', 'tipo' => 'number']]],
    'Tipo_Consumo' => [ 'titulo' => 'Tipos de Consumo', 'pk' => 'id_consumo', 'campos' => [ 'nome' => ['label' => 'Nome', 'tipo' => 'text'], 'valor_litro_default' => ['label' => 'Valor Litro Padrão', 'tipo' => 'number'], 'consumo_kml_default' => ['label' => 'Consumo Km/L Padrão', 'tipo' => 'number']]],
    'Tipo_Custo_Admin' => [ 'titulo' => 'Custos Administrativos', 'pk' => 'id_custo_admin', 'campos' => [ 'nome' => ['label' => 'Nome', 'tipo' => 'text'], 'valor_default' => ['label' => 'Valor Padrão', 'tipo' => 'number']]]
];

$tabela = isset($_GET['tabela']) ? $_GET['tabela'] : '';
if (!array_key_exists($tabela, $configTabelas)) { die("Acesso inválido ou tabela não encontrada."); }

// ... (O restante do código CRUD é genérico e funciona automaticamente com a nova config) ...
// ... (Copiar a lógica de insert/update/delete do arquivo original) ...
$config = $configTabelas[$tabela];
$titulo = $config['titulo']; $pk = $config['pk']; $campos = $config['campos'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao'])) {
    try {
        $valores = array(); $tipos = ''; $refs = array();
        foreach (array_keys($campos) as $campo) {
            $valorPost = isset($_POST[$campo]) ? $_POST[$campo] : '';
            $isNumeric = in_array($campos[$campo]['tipo'], ['number', 'decimal']);
            if ($isNumeric && $valorPost === '') { $valores[$campo] = NULL; } else { $valores[$campo] = $valorPost; }
            $tipos .= $isNumeric ? 'd' : 's';
        }

        if ($_POST['acao'] === 'adicionar') {
            $placeholders = implode(', ', array_fill(0, count($campos), '?'));
            $sql = sprintf("INSERT INTO %s (%s) VALUES (%s)", $tabela, implode(', ', array_keys($campos)), $placeholders);
            $stmt = $conn->prepare($sql);
            $params = array_merge(array($tipos), array_values($valores));
            foreach ($params as $key => $value) { $refs[$key] = &$params[$key]; }
            call_user_func_array(array($stmt, 'bind_param'), $refs);
            $_SESSION['mensagem'] = 'Registro adicionado com sucesso!';
        } elseif ($_POST['acao'] === 'editar' && isset($_POST[$pk])) {
            $id = $_POST[$pk];
            $setClauses = array();
            foreach (array_keys($campos) as $campo) { $setClauses[] = "$campo = ?"; }
            $sql = sprintf("UPDATE %s SET %s WHERE %s = ?", $tabela, implode(', ', $setClauses), $pk);
            $tipos .= 'i';
            $valores['id'] = $id;
            $stmt = $conn->prepare($sql);
            $params = array_merge(array($tipos), array_values($valores));
            foreach ($params as $key => $value) { $refs[$key] = &$params[$key]; }
            call_user_func_array(array($stmt, 'bind_param'), $refs);
            $_SESSION['mensagem'] = 'Registro atualizado com sucesso!';
        }
        if (isset($stmt)) { $stmt->execute(); }
        header("Location: gerenciar.php?tabela=$tabela"); exit;
    } catch (Exception $e) { die("Erro: " . $e->getMessage()); }
}
// Delete logic and list logic remains the same...
if (isset($_GET['acao']) && $_GET['acao'] === 'excluir' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $conn->query("DELETE FROM $tabela WHERE $pk = $id");
    header("Location: gerenciar.php?tabela=$tabela"); exit;
}
$resultados = $conn->query("SELECT * FROM $tabela ORDER BY $pk DESC");
$dadosEdicao = null;
if (isset($_GET['acao']) && $_GET['acao'] === 'editar') {
    $id = intval($_GET['id']);
    $dadosEdicao = $conn->query("SELECT * FROM $tabela WHERE $pk = $id")->fetch_assoc();
}
?>
<!DOCTYPE html><html lang="pt-br"><head><meta charset="UTF-8"><title>Gerenciar</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body>
<div class="container mt-4">
    <h3>Gerenciar <?= htmlspecialchars($titulo) ?></h3>
    <!-- Formulário e Tabela iguais ao original -->
    <div class="card mb-4"><div class="card-body">
    <form method="POST">
        <input type="hidden" name="acao" value="<?= $dadosEdicao ? 'editar' : 'adicionar' ?>">
        <?php if ($dadosEdicao): ?><input type="hidden" name="<?= $pk ?>" value="<?= htmlspecialchars($dadosEdicao[$pk]) ?>"><?php endif; ?>
        <?php foreach ($campos as $nomeCampo => $props): ?>
            <div class="mb-3"><label><?= $props['label'] ?></label><input type="<?= $props['tipo'] ?>" name="<?= $nomeCampo ?>" class="form-control" value="<?= htmlspecialchars($dadosEdicao[$nomeCampo] ?? '') ?>"></div>
        <?php endforeach; ?>
        <button type="submit" class="btn btn-primary">Salvar</button>
    </form>
    </div></div>
    <table class="table table-striped">
        <thead><tr><?php foreach ($campos as $props) echo "<th>{$props['label']}</th>"; ?><th>Ações</th></tr></thead>
        <tbody><?php while ($r = $resultados->fetch_assoc()): ?>
            <tr>
                <?php foreach (array_keys($campos) as $campo) echo "<td>".htmlspecialchars($r[$campo])."</td>"; ?>
                <td><a href="?tabela=<?= $tabela ?>&acao=editar&id=<?= $r[$pk] ?>" class="btn btn-sm btn-warning">Edit</a> <a href="?tabela=<?= $tabela ?>&acao=excluir&id=<?= $r[$pk] ?>" class="btn btn-sm btn-danger">Del</a></td>
            </tr>
        <?php endwhile; ?></tbody>
    </table>
    <a href="index.php" class="btn btn-secondary">Voltar</a>
</div></body></html>