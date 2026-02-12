<?php
// Nome do Arquivo: reset_admin.php
// Função: ATUALIZAÇÃO FORÇADA DA SENHA DO ADMIN PARA O PADRÃO HASH.
// Use uma vez e delete.

require_once 'config.php';
require_once 'db.php';

$conn = Database::getProd();

// Defina aqui a nova senha forte inicial para o Admin
$nova_senha_texto = "Elm@2025"; 

// Gera o Hash seguro
$senha_hash = password_hash($nova_senha_texto, PASSWORD_DEFAULT);

// Atualiza no banco
$stmt = $conn->prepare("UPDATE Usuarios SET senha = ? WHERE usuario = 'admin' OR id_usuario = 1");
$stmt->bind_param('s', $senha_hash);

if ($stmt->execute()) {
    echo "<h1>Sucesso!</h1>";
    echo "<p>A senha do Admin foi atualizada para o padrão seguro.</p>";
    echo "<p><strong>Nova Senha:</strong> $nova_senha_texto</p>";
    echo "<p>Agora você pode substituir os códigos de login pelo padrão seguro.</p>";
} else {
    echo "Erro ao atualizar: " . $conn->error;
}
?>