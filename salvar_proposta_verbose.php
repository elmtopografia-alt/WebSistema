<?php
// MODO DE DIAGNÓSTICO ATIVO - ESTE SCRIPT NÃO USA TRANSAÇÕES E É VERBOSO
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<pre style='background-color: #f0f0f0; border: 1px solid #ccc; padding: 10px; font-family: monospace; white-space: pre-wrap;'>";
echo "<h1>--- INÍCIO DO DIAGNÓSTICO ATIVO ---</h1>";

require 'db.php';

function gerarNumeroProposta($conn) { return "ELM-DIAG-" . time(); }

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Acesse esta página enviando o formulário de criar_proposta.php");
}

// Lógicas de busca de dados...
$cliente_data = ['nome_cliente' => null, 'email' => null, 'telefone' => null, 'celular' => null, 'whatsapp' => null];
if (!empty($_POST['id_cliente'])) {
    $stmt_cliente = $conn->prepare("SELECT nome_cliente, email, telefone, celular, whatsapp FROM Clientes WHERE id_cliente = ?");
    $stmt_cliente->bind_param('i', $_POST['id_cliente']);
    $stmt_cliente->execute();
    $result_cliente = $stmt_cliente->get_result();
    if ($result_cliente->num_rows > 0) { $cliente_data = $result_cliente->fetch_assoc(); }
}
echo "<p><strong>[OK]</strong> Dados do cliente processados.</p>";

$dados_da_empresa = null;
$resultado_empresa = $conn->query("SELECT * FROM DadosEmpresa LIMIT 1");
if ($resultado_empresa && $resultado_empresa->num_rows > 0) {
    $dados_da_empresa = $resultado_empresa->fetch_assoc();
}
echo "<p><strong>[OK]</strong> Dados da empresa processados.</p>";

$modo_edicao = isset($_POST['id_proposta']) && !empty($_POST['id_proposta']);

if ($modo_edicao) {
    die("MODO DE EDIÇÃO NÃO SUPORTADO NESTE DIAGNÓSTICO. Tente criar uma nova proposta.");
} else {
    $numero_proposta = gerarNumeroProposta($conn);
    $sql_proposta = "INSERT INTO Propostas (id_cliente, nome_cliente_salvo, email_salvo, telefone_salvo, celular_salvo, whatsapp_salvo, empresa_proponente_nome, empresa_proponente_cnpj, empresa_proponente_endereco, empresa_proponente_cidade, empresa_proponente_estado, empresa_proponente_banco, empresa_proponente_agencia, empresa_proponente_conta, empresa_proponente_pix, id_servico, contato_obra, finalidade, tipo_levantamento, area_obra, endereco_obra, bairro_obra, cidade_obra, estado_obra, prazo_execucao, dias_campo, dias_escritorio, total_custos_salarios, total_custos_estadia, total_custos_consumos, total_custos_locacao, total_custos_admin, percentual_lucro, valor_lucro, subtotal_com_lucro, valor_desconto, valor_final_proposta, mobilizacao_percentual, mobilizacao_valor, restante_percentual, restante_valor, numero_proposta, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
}

echo "<p><strong>[INFO]</strong> Preparando query principal para a tabela 'Propostas'.</p>";
$stmt_proposta = $conn->prepare($sql_proposta);
if ($stmt_proposta === false) { die("<strong><span style='color:red;'>FALHA CRÍTICA:</span></strong> Erro ao preparar a query principal: " . $conn->error); }

$params = [
    $_POST['id_cliente'] ?? null, $cliente_data['nome_cliente'], $cliente_data['email'], $cliente_data['telefone'], $cliente_data['celular'], $cliente_data['whatsapp'],
    $dados_da_empresa['Empresa'] ?? null, $dados_da_empresa['CNPJ'] ?? null, $dados_da_empresa['Endereco'] ?? null, $dados_da_empresa['Cidade'] ?? null, $dados_da_empresa['Estado'] ?? null, $dados_da_empresa['Banco'] ?? null, $dados_da_empresa['Agencia'] ?? null, $dados_da_empresa['Conta'] ?? null, $dados_da_empresa['PIX'] ?? null,
    $_POST['id_servico'] ?? null, $_POST['contato_obra'] ?? '', $_POST['finalidade'] ?? '', $_POST['tipo_levantamento'] ?? '', $_POST['area_obra'] ?? '',
    $_POST['endereco_obra'] ?? '', $_POST['bairro_obra'] ?? '', $_POST['cidade_obra'] ?? '', $_POST['estado_obra'] ?? 'MG', $_POST['prazo_execucao'] ?? '', $_POST['dias_campo'] ?? null, $_POST['dias_escritorio'] ?? null,
    $_POST['total_custos_salarios'] ?? 0.00, $_POST['total_custos_estadia'] ?? 0.00, $_POST['total_custos_consumos'] ?? 0.00, $_POST['total_custos_locacao'] ?? 0.00, $_POST['total_custos_admin'] ?? 0.00,
    $_POST['percentual_lucro'] ?? 0.00, $_POST['valor_lucro'] ?? 0.00, $_POST['subtotal_com_lucro'] ?? 0.00, $_POST['valor_desconto'] ?? 0.00, $_POST['valor_final_proposta'] ?? 0.00,
    $_POST['mobilizacao_percentual'] ?? 0.00, $_POST['mobilizacao_valor'] ?? 0.00, $_POST['restante_percentual'] ?? 0.00, $_POST['restante_valor'] ?? 0.00
];
$types = 'isssssssssssssssisssssssssii' . 'ddddddddddddd';
$params[] = $numero_proposta;
$params[] = 'Em Elaboração';
$types .= 'ss';

$stmt_proposta->bind_param($types, ...$params);

echo "<p><strong>[INFO]</strong> Executando query principal...</p>";
if (!$stmt_proposta->execute()) {
    die("<strong><span style='color:red;'>FALHA CRÍTICA NA QUERY PRINCIPAL:</span></strong> " . htmlspecialchars($stmt_proposta->error));
}
echo "<p style='color:green; font-weight:bold;'>[SUCESSO] Query principal executada. Proposta inserida na tabela 'Propostas'.</p>";
$id_proposta = $conn->insert_id;
echo "<p><strong>[INFO]</strong> Novo ID da proposta é: $id_proposta</p>";


// INSERÇÃO DE CUSTOS
echo "<h3>--- Iniciando Inserção de Custos ---</h3>";

// SALÁRIOS
if (isset($_POST['salarios']['id_funcao'])) {
    echo "<p><strong>[INFO]</strong> Processando Salários...</p>";
    $stmt_sal = $conn->prepare("INSERT INTO Proposta_Salarios (id_proposta, id_funcao, quantidade, salario_base, fator_encargos, dias) VALUES (?, ?, ?, ?, ?, ?)");
    if ($stmt_sal === false) { die("<strong><span style='color:red;'>FALHA CRÍTICA:</span></strong> Erro ao preparar a query de Salários: " . $conn->error); }
    foreach ($_POST['salarios']['id_funcao'] as $i => $id_funcao) {
        if (empty($id_funcao)) continue;
        $stmt_sal->bind_param("iiidds", $id_proposta, $id_funcao, $_POST['salarios']['quantidade'][$i], $_POST['salarios']['salario_base'][$i], $_POST['salarios']['encargos'][$i], $_POST['salarios']['dias'][$i]);
        if (!$stmt_sal->execute()) { die("<strong><span style='color:red;'>FALHA CRÍTICA AO INSERIR SALÁRIO (índice $i):</span></strong> " . htmlspecialchars($stmt_sal->error)); }
    }
    echo "<p style='color:green;'><strong>[SUCESSO]</strong> Salários inseridos.</p>";
}

// ESTADIA
if (isset($_POST['estadia']['id_estadia'])) {
    echo "<p><strong>[INFO]</strong> Processando Estadia...</p>";
    $stmt_est = $conn->prepare("INSERT INTO Proposta_Estadia (id_proposta, id_estadia, quantidade, valor_unitario, dias) VALUES (?, ?, ?, ?, ?)");
    if ($stmt_est === false) { die("<strong><span style='color:red;'>FALHA CRÍTICA:</span></strong> Erro ao preparar a query de Estadia: " . $conn->error); }
    foreach ($_POST['estadia']['id_estadia'] as $i => $id_estadia) {
        if (empty($id_estadia)) continue;
        $stmt_est->bind_param("iiidi", $id_proposta, $id_estadia, $_POST['estadia']['quantidade'][$i], $_POST['estadia']['valor_unitario'][$i], $_POST['estadia']['dias'][$i]);
        if (!$stmt_est->execute()) { die("<strong><span style='color:red;'>FALHA CRÍTICA AO INSERIR ESTADIA (índice $i):</span></strong> " . htmlspecialchars($stmt_est->error)); }
    }
    echo "<p style='color:green;'><strong>[SUCESSO]</strong> Estadia inserida.</p>";
}

// ... (outros custos) ...

echo "<h1>--- DIAGNÓSTICO CONCLUÍDO ---</h1>";
echo "</pre>";
exit();
?>