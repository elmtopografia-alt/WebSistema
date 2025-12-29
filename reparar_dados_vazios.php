<?php
// Nome do Arquivo: reparar_dados_vazios.php
// Função: Preenche dados de empresa que estão vazios para evitar buracos no PDF.

require_once 'config.php';
require_once 'db.php';

echo "<h1>🔧 Reparador de Dados da Empresa</h1>";
echo "<hr>";

$dados_ficticios = [
    'Endereco' => 'Av. das Nações Unidas, 1000 - Centro',
    'Cidade'   => 'São Paulo',
    'Estado'   => 'SP',
    'Telefone' => '(11) 3000-0000',
    'Celular'  => '(11) 99999-9999',
    'Whatsapp' => '(11) 99999-9999',
    'Banco'    => 'Banco do Brasil',
    'Agencia'  => '1234-X',
    'Conta'    => '56789-0',
    'PIX'      => 'financeiro@empresa.com.br'
];

function repararBanco($conn, $nome_banco, $dados) {
    echo "<h3>Analisando Banco: $nome_banco</h3>";
    
    // Atualiza onde está vazio
    $sql = "UPDATE DadosEmpresa SET 
            Endereco = IF(Endereco='', ?, Endereco),
            Cidade   = IF(Cidade='', ?, Cidade),
            Estado   = IF(Estado='', ?, Estado),
            Telefone = IF(Telefone='', ?, Telefone),
            Celular  = IF(Celular='', ?, Celular),
            Whatsapp = IF(Whatsapp='', ?, Whatsapp),
            Banco    = IF(Banco='', ?, Banco),
            Agencia  = IF(Agencia='', ?, Agencia),
            Conta    = IF(Conta='', ?, Conta),
            PIX      = IF(PIX='', ?, PIX)
            WHERE id_empresa > 0";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ssssssssss', 
        $dados['Endereco'], $dados['Cidade'], $dados['Estado'], 
        $dados['Telefone'], $dados['Celular'], $dados['Whatsapp'],
        $dados['Banco'], $dados['Agencia'], $dados['Conta'], $dados['PIX']
    );

    if ($stmt->execute()) {
        echo "<p style='color:green'>✅ Registros atualizados/verificados com sucesso.</p>";
    } else {
        echo "<p style='color:red'>❌ Erro: " . $conn->error . "</p>";
    }
}

// Executa nos dois
try {
    repararBanco(Database::getProd(), "PRODUÇÃO", $dados_ficticios);
    repararBanco(Database::getDemo(), "DEMO", $dados_ficticios);
} catch (Exception $e) {
    echo "Erro fatal: " . $e->getMessage();
}

echo "<hr><p>Agora gere uma proposta e veja se os campos apareceram.</p>";
?>