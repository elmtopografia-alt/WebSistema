<?php
// Arquivo: teste_demo.php
// Objetivo: Testar conexão isolada com o banco de dados PROPOSTA (Demo)

header('Content-Type: text/html; charset=utf-8');

// Credenciais EXATAS do ambiente Demo que você forneceu
$host     = 'proposta.mysql.dbaas.com.br';
$username = 'proposta';
$password = 'Qtamaqmde5202@';
$database = 'proposta';

echo "<h2>🕵️ Teste de Conexão: Ambiente DEMO</h2>";
echo "<p>Tentando conectar em: <strong>$host</strong> ...</p>";

// 1. Tenta Conectar
$conn = new mysqli($host, $username, $password, $database);

// 2. Verifica se deu erro
if ($conn->connect_error) {
    echo "<div style='color:red; border:1px solid red; padding:10px;'>";
    echo "❌ <strong>FALHA:</strong> Não foi possível conectar.<br>";
    echo "Erro detalhado: " . $conn->connect_error;
    echo "</div>";
    exit;
}

// 3. Se conectou, faz uma consulta simples para provar
echo "<div style='color:green; border:1px solid green; padding:10px; background-color:#e8f5e9;'>";
echo "✅ <strong>SUCESSO!</strong> Conexão estabelecida.<br>";
echo "</div>";

// Consulta de teste: Pega o nome do banco e conta quantos usuários existem
$sql = "SELECT DATABASE() as banco_atual, (SELECT COUNT(*) FROM Usuarios) as total_users";
$result = $conn->query($sql);

if ($result) {
    $row = $result->fetch_assoc();
    echo "<h3>Dados do Ambiente:</h3>";
    echo "<ul>";
    echo "<li><strong>Banco Conectado:</strong> " . $row['banco_atual'] . " (Deve ser 'proposta')</li>";
    echo "<li><strong>Total de Usuários na tabela:</strong> " . $row['total_users'] . "</li>";
    echo "</ul>";
    
    // Lista os usuários para confirmar se vemos o 'demo'
    echo "<h4>Usuários encontrados:</h4>";
    $users = $conn->query("SELECT id_usuario, usuario, validade_acesso FROM Usuarios LIMIT 5");
    while($u = $users->fetch_assoc()){
        echo "ID: " . $u['id_usuario'] . " | User: <strong>" . $u['usuario'] . "</strong> | Validade: " . ($u['validade_acesso'] ? $u['validade_acesso'] : 'Livre/Nula') . "<br>";
    }

} else {
    echo "<p style='color:orange'>Conectou, mas houve erro na consulta SQL: " . $conn->error . "</p>";
}

$conn->close();
?>