<?php
// Nome do Arquivo: migrar_admin.php
// Função: Renomeia o usuário 'admin' para o e-mail oficial e aplica senha forte.

require_once 'config.php';
require_once 'db.php';

$conn = Database::getProd();

// DADOS DO NOVO ADMIN
$novo_email = "edivaldo@elmtopografia.com.br";
$nova_senha = "Elm@2025"; // Senha Forte Inicial

// Gera o Hash
$senha_hash = password_hash($nova_senha, PASSWORD_DEFAULT);

// Atualiza
// Tenta pegar pelo ID 1 ou pelo nome antigo 'admin'
$sql = "UPDATE Usuarios SET usuario = ?, senha = ?, nome_completo = 'Edivaldo Admin' WHERE id_usuario = 1 OR usuario = 'admin'";

$stmt = $conn->prepare($sql);
$stmt->bind_param('ss', $novo_email, $senha_hash);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo "<h1>Sucesso!</h1>";
        echo "<p>O usuário 'admin' foi renomeado para: <strong>$novo_email</strong></p>";
        echo "<p>Senha definida para: <strong>$nova_senha</strong></p>";
        echo "<p>Agora o padrão de E-mail está 100% aplicado.</p>";
        echo "<br><a href='login_admin.php'>Ir para Login Admin</a>";
    } else {
        echo "<h1>Atenção</h1>";
        echo "<p>Nenhum registro foi alterado. Talvez já esteja atualizado ou o usuário 'admin' não foi encontrado.</p>";
    }
} else {
    echo "Erro SQL: " . $conn->error;
}
?>