<?php
// ARQUIVO: processa_demo_login.php
// VERSÃO: DIAGNÓSTICO (Mostra erros na tela)

ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
require_once 'config.php';

echo "<h2>🕵️ Diagnóstico de Login Demo</h2>";

// 1. Teste de Conexão
echo "<p>Tentando conectar em: <strong>" . DB_DEMO_HOST . "</strong> (Base: ".DB_DEMO_NAME.")</p>";

$conn = new mysqli(DB_DEMO_HOST, DB_DEMO_USER, DB_DEMO_PASS, DB_DEMO_NAME);

if ($conn->connect_error) {
    die("<h3 style='color:red'>ERRO DE CONEXÃO: " . $conn->connect_error . "</h3>");
}
echo "<p style='color:green'>✅ Conexão com banco DEMO estabelecida.</p>";

$usuario = $_POST['usuario'] ?? '';
$senha   = $_POST['senha'] ?? '';

echo "<p>Buscando usuário: <strong>$usuario</strong></p>";

// 2. Busca Usuário
$stmt = $conn->prepare("SELECT id_usuario, nome_completo, senha, validade_acesso, setup_concluido FROM Usuarios WHERE usuario = ?");
$stmt->bind_param('s', $usuario);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows > 0) {
    $dados = $res->fetch_assoc();
    echo "<p style='color:green'>✅ Usuário encontrado (ID: {$dados['id_usuario']})</p>";
    
    echo "<p>Senha digitada: $senha<br>";
    echo "Senha no banco: {$dados['senha']}</p>";
    
    if ($senha === $dados['senha']) {
        echo "<h3 style='color:green'>✅ SENHAS CONFEREM! LOGIN SUCESSO.</h3>";
        
        // Se chegou aqui, loga e redireciona
        $_SESSION['usuario_id'] = $dados['id_usuario'];
        $_SESSION['usuario_nome'] = $dados['nome_completo'];
        $_SESSION['setup_concluido'] = $dados['setup_concluido'];
        $_SESSION['ambiente'] = 'demo';
        $_SESSION['validade_demo'] = $dados['validade_acesso'];
        
        echo "<p>Redirecionando para index.php em 3 segundos...</p>";
        header("refresh:3;url=index.php");
        exit;
    } else {
        echo "<h3 style='color:red'>❌ SENHAS NÃO CONFEREM.</h3>";
        echo "<p>Causa provável: Você gerou um novo teste (o que mudou a senha no banco) mas tentou usar a senha antiga.</p>";
    }

} else {
    echo "<h3 style='color:red'>❌ E-MAIL NÃO ENCONTRADO NO BANCO DE DADOS.</h3>";
    echo "<p>Verifique se você criou a conta através do botão 'Testar Grátis'.</p>";
}
?>