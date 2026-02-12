<?php
// Nome da página: db.php
// MODO DE DEPURAÇÃO ESTRITO - ESTE ARQUIVO NÃO SALVA NADA NO BANCO

echo "<pre>"; // Habilita uma formatação de texto mais legível

// Inclui a conexão apenas para ter a função gerarNumeroProposta
require 'db.php';

function gerarNumeroProposta($conn) {
    // Simplesmente retorna um valor de exemplo para o debug
    return "ELM-DEBUG-001";
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Acesse esta página enviando o formulário de criar_proposta.php");
}

echo "<h1>--- INÍCIO DO RELATÓRIO DE DEPURAÇÃO ---</h1>\n";

// --- REPLICAÇÃO DA LÓGICA DE 'salvar_proposta.php' ---

$cliente_data = ['nome_cliente' => null, 'email' => null, 'telefone' => null, 'celular' => null, 'whatsapp' => null];
// Simulação da busca de dados (não precisamos do banco para o debug)
if (!empty($_POST['id_cliente'])) {
    $cliente_data = [
        'nome_cliente' => "Cliente Teste", 
        'email' => "teste@email.com", 
        'telefone' => "3133333333", 
        'celular' => "31999999999", 
        'whatsapp' => "31999999999"
    ];
}

$dados_da_empresa = [
    'Empresa' => 'Empresa Proponente Teste', 'CNPJ' => '00.000.000/0001-00', 'Endereco' => 'Rua Teste, 123',
    'Cidade' => 'Cidade Teste', 'Estado' => 'TS', 'Banco' => 'Banco Teste', 'Agencia' => '0001', 'Conta' => '12345-6', 'PIX' => 'pix@teste.com'
];

$modo_edicao = isset($_POST['id_proposta']) && !empty($_POST['id_proposta']);

if ($modo_edicao) {
    // Lógica de UPDATE (a lista de colunas é a mesma)
    $sql_proposta = "UPDATE Propostas SET id_cliente=?, nome_cliente_salvo=?, ... (query longa omitida para clareza)";
} else {
    // Lógica de INSERT
    $sql_proposta = "INSERT INTO Propostas (id_cliente, nome_cliente_salvo, email_salvo, telefone_salvo, celular_salvo, whatsapp_salvo, empresa_proponente_nome, empresa_proponente_cnpj, empresa_proponente_endereco, empresa_proponente_cidade, empresa_proponente_estado, empresa_proponente_banco, empresa_proponente_agencia, empresa_proponente_conta, empresa_proponente_pix, id_servico, contato_obra, finalidade, tipo_levantamento, area_obra, endereco_obra, bairro_obra, cidade_obra, estado_obra, prazo_execucao, dias_campo, dias_escritorio, total_custos_salarios, total_custos_estadia, total_custos_consumos, total_custos_locacao, total_custos_admin, percentual_lucro, valor_lucro, subtotal_com_lucro, valor_desconto, valor_final_proposta, mobilizacao_percentual, mobilizacao_valor, restante_percentual, restante_valor, numero_proposta, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
}

$params = [
    $_POST['id_cliente'] ?? null, $cliente_data['nome_cliente'], $cliente_data['email'], $cliente_data['telefone'], $cliente_data['celular'], $cliente_data['whatsapp'],
    $dados_da_empresa['Empresa'] ?? null, $dados_da_empresa['CNPJ'] ?? null, $dados_da_empresa['Endereco'] ?? null, $dados_da_empresa['Cidade'] ?? null, $dados_da_empresa['Estado'] ?? null, $dados_da_empresa['Banco'] ?? null, $dados_da_empresa['Agencia'] ?? null, $dados_da_empresa['Conta'] ?? null, $dados_da_empresa['PIX'] ?? null,
    $_POST['id_servico'] ?? null, $_POST['contato_obra'] ?? '', $_POST['finalidade'] ?? '', $_POST['tipo_levantamento'] ?? '', $_POST['area_obra'] ?? '',
    $_POST['endereco_obra'] ?? '', $_POST['bairro_obra'] ?? '', $_POST['cidade_obra'] ?? '', $_POST['estado_obra'] ?? 'MG', $_POST['prazo_execucao'] ?? '', $_POST['dias_campo'] ?? null, $_POST['dias_escritorio'] ?? null,
    $_POST['total_custos_salarios'] ?? 0.00, $_POST['total_custos_estadia'] ?? 0.00, $_POST['total_custos_consumos'] ?? 0.00, $_POST['total_custos_locacao'] ?? 0.00, $_POST['total_custos_admin'] ?? 0.00,
    $_POST['percentual_lucro'] ?? 0.00, $_POST['valor_lucro'] ?? 0.00, $_POST['subtotal_com_lucro'] ?? 0.00, $_POST['valor_desconto'] ?? 0.00, $_POST['valor_final_proposta'] ?? 0.00,
    $_POST['mobilizacao_percentual'] ?? 0.00, $_POST['mobilizacao_valor'] ?? 0.00, $_POST['restante_percentual'] ?? 0.00, $_POST['restante_valor'] ?? 0.00
];

$types = 'isssss' . 'sssssssss' . 'isssssssssii' . 'ddddd' . 'ddddd' . 'dddd';

if ($modo_edicao) {
    // Lógica para UPDATE
} else {
    // Para INSERT, adicionamos os 2 últimos parâmetros
    $params[] = gerarNumeroProposta($conn);
    $params[] = 'Em Elaboração';
    $types .= 's' . 's'; // Adiciona os tipos para numero_proposta e status
}

echo "<h2>Análise de Contagem (Modo INSERT)</h2>\n";
echo "==================================================\n";

$num_placeholders = substr_count($sql_proposta, '?');
$num_params = count($params);
$num_types = strlen($types);

echo "Número de Placeholders (?) na query SQL: " . $num_placeholders . "\n";
echo "Número de Parâmetros no array \$params:   " . $num_params . "\n";
echo "Número de Caracteres na string \$types:  " . $num_types . "\n\n";

if ($num_placeholders === $num_params && $num_params === $num_types) {
    echo "<strong>VEREDITO PRELIMINAR: As contagens estão CORRETAS. O problema é mais complexo.</strong>\n\n";
} else {
    echo "<strong><span style='color:red;'>VEREDITO PRELIMINAR: ERRO DE CONTAGEM ENCONTRADO!</span></strong>\n\n";
}

echo "<h2>Análise Detalhada dos Parâmetros</h2>\n";
echo "==================================================\n";

for ($i = 0; $i < $num_params; $i++) {
    $type_char = $types[$i] ?? 'FALTA';
    $param_val = $params[$i] ?? 'NULO';
    echo "Índice " . str_pad($i, 2, '0', STR_PAD_LEFT) . " | Tipo: " . $type_char . " | Valor: " . htmlspecialchars(print_r($param_val, true)) . "\n";
}

echo "\n\n<h2>Dados POST Recebidos</h2>\n";
echo "==================================================\n";
print_r($_POST);


echo "\n--- FIM DO RELATÓRIO DE DEPURAÇÃO ---";
echo "</pre>";

exit(); // Interrompe a execução
?>