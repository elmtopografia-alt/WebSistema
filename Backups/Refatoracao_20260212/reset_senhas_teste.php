<?php
// Nome do Arquivo: reset_senhas_teste.php
// Função: FORÇA a atualização das senhas dos usuários de teste para o padrão HASH correto.
// Execute uma vez e depois apague.

require_once 'config.php';
require_once 'db.php';

// A SENHA QUE VOCÊ QUER (Será aplicada para todos os testes)
$senha_texto_claro = "Ren!2026";

// GERA O HASH SEGURO (O código que o banco entende)
$senha_hash = password_hash($senha_texto_claro, PASSWORD_DEFAULT);

echo "<h1>🔧 Reparador de Senhas</h1>";
echo "<p>Definindo senha padrão: <strong>$senha_texto_claro</strong></p>";
echo "<hr>";

// ---------------------------------------------------------
// 1. ATUALIZAR PRODUÇÃO (renato_prod)
// ---------------------------------------------------------
try {
    $connProd = Database::getProd();
    $usuario_prod = "renato_prod@gmail.com";
    
    // Verifica se existe
    $check = $connProd->query("SELECT id_usuario FROM Usuarios WHERE usuario = '$usuario_prod'");
    if ($check->num_rows > 0) {
        $stmt = $connProd->prepare("UPDATE Usuarios SET senha = ? WHERE usuario = ?");
        $stmt->bind_param('ss', $senha_hash, $usuario_prod);
        $stmt->execute();
        echo "<p style='color:green'>✅ PRODUÇÃO: Senha de <strong>$usuario_prod</strong> atualizada com sucesso!</p>";
    } else {
        echo "<p style='color:red'>❌ PRODUÇÃO: Usuário $usuario_prod não encontrado no banco.</p>";
    }

} catch (Exception $e) {
    echo "<p>Erro Prod: " . $e->getMessage() . "</p>";
}

// ---------------------------------------------------------
// 2. ATUALIZAR DEMO (renato_demo)
// ---------------------------------------------------------
try {
    $connDemo = Database::getDemo();
    $usuario_demo = "renato_demo@gmail.com";
    
    // Verifica se existe
    $check = $connDemo->query("SELECT id_usuario FROM Usuarios WHERE usuario = '$usuario_demo'");
    if ($check->num_rows > 0) {
        $stmt = $connDemo->prepare("UPDATE Usuarios SET senha = ? WHERE usuario = ?");
        $stmt->bind_param('ss', $senha_hash, $usuario_demo);
        $stmt->execute();
        echo "<p style='color:green'>✅ DEMO: Senha de <strong>$usuario_demo</strong> atualizada com sucesso!</p>";
    } else {
        echo "<p style='color:red'>❌ DEMO: Usuário $usuario_demo não encontrado no banco.</p>";
    }

} catch (Exception $e) {
    echo "<p>Erro Demo: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<h3>Pode testar o login agora.</h3>";
?>