<?php
/**
 * reset_senhas_teste.php
 * Força a redefinição de senhas para os usuários de teste.
 * Após rodar, acesse com Ren!2026
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

$senha_texto = "Ren!2026";
$senha_hash = password_hash($senha_texto, PASSWORD_DEFAULT);

echo "<h2>Forçando o Reset de Senhas de Teste</h2>";

// 1. Reset Produção
try {
    $connProd = Database::getProd();
    $email_prod = "renato_prod@gmail.com";
    
    $stmt = $connProd->prepare("UPDATE Usuarios SET senha = ? WHERE usuario = ?");
    $stmt->bind_param('ss', $senha_hash, $email_prod);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo "<p style='color:green'>✅ Senha do usuário PROD ($email_prod) redefinida para: <strong>$senha_texto</strong></p>";
        } else {
            echo "<p style='color:orange'>⚠️ Usuário PROD ($email_prod) não encontrado na base de produção.</p>";
        }
    } else {
        echo "<p style='color:red'>❌ Erro ao atualizar PROD: " . $stmt->error . "</p>";
    }
} catch (Exception $e) {
    echo "<p style='color:red'>Erro BD Prod: " . $e->getMessage() . "</p>";
}

// 2. Reset Demo
try {
    $connDemo = Database::getDemo();
    $email_demo = "renato_demo@gmail.com";
    
    $stmt = $connDemo->prepare("UPDATE Usuarios SET senha = ? WHERE usuario = ?");
    $stmt->bind_param('ss', $senha_hash, $email_demo);
    
    if ($stmt->execute()) {
         if ($stmt->affected_rows > 0) {
            echo "<p style='color:green'>✅ Senha do usuário DEMO ($email_demo) redefinida para: <strong>$senha_texto</strong></p>";
        } else {
            echo "<p style='color:orange'>⚠️ Usuário DEMO ($email_demo) não encontrado na base demo.</p>";
        }
    } else {
        echo "<p style='color:red'>❌ Erro ao atualizar DEMO: " . $stmt->error . "</p>";
    }
} catch (Exception $e) {
    echo "<p style='color:red'>Erro BD Demo: " . $e->getMessage() . "</p>";
}

echo "<hr><p>Finalizado. Pode apagar este arquivo após o uso.</p>";
?>