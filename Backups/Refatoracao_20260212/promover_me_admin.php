<?php
// Nome do Arquivo: promover_me_admin.php
// Função: Força o usuário atual a se tornar ADMIN no banco de dados e na sessão.
// Execute uma vez e apague.

session_start();
require_once 'config.php';
require_once 'db.php';

if (!isset($_SESSION['usuario_id'])) {
    die("<h1>Erro</h1><p>Você precisa estar logado (mesmo que como cliente) para rodar este script.</p><a href='login.php'>Fazer Login</a>");
}

$id_usuario = $_SESSION['usuario_id'];
$conn = Database::getProd(); // Conecta no banco principal

echo "<h1>🔧 Promoção de Administrador</h1>";
echo "<p>Usuário ID: $id_usuario</p>";

// 1. Atualiza no Banco
$sql = "UPDATE Usuarios SET tipo_perfil = 'admin' WHERE id_usuario = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $id_usuario);

if ($stmt->execute()) {
    // 2. Atualiza a Sessão Atual (para não precisar relogar)
    $_SESSION['perfil'] = 'admin';
    
    echo "<h2 style='color:green'>✅ SUCESSO!</h2>";
    echo "<p>Você agora é um <strong>ADMINISTRADOR</strong>.</p>";
    echo "<p>A trava de segurança foi removida para o seu usuário.</p>";
    echo "<hr>";
    echo "<a href='admin_limpeza.php' style='font-size:20px; font-weight:bold;'>👉 Clique aqui para acessar a Limpeza</a>";
} else {
    echo "<h2 style='color:red'>❌ Erro ao atualizar banco: " . $conn->error . "</h2>";
}
?>