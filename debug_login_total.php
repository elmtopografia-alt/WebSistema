<?php
// Nome do Arquivo: debug_login_total.php
// Função: Diagnóstico profundo de Login e Hash.

ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once 'config.php';
require_once 'db.php';

echo "<h1>🕵️‍♂️ Diagnóstico de Login</h1>";
echo "<hr>";

// SENHA QUE ESTAMOS TENTANDO
$senha_teste = "Ren!2026";
echo "<p>Senha sendo testada: <strong>[$senha_teste]</strong></p>";

// =================================================================
// TESTE 1: BANCO DE PRODUÇÃO
// =================================================================
echo "<h3>1. Verificando Banco de PRODUÇÃO (demanda)</h3>";
try {
    $conn = Database::getProd();
    $email_alvo = "renato_prod@gmail.com";
    
    // Busca usuário
    $sql = "SELECT id_usuario, usuario, senha, validade_acesso FROM Usuarios WHERE usuario LIKE '%renato%'";
    $res = $conn->query($sql);
    
    if ($res->num_rows > 0) {
        while ($row = $res->fetch_assoc()) {
            echo "<div style='border:1px solid #ccc; padding:10px; margin-bottom:10px;'>";
            echo "ID: " . $row['id_usuario'] . "<br>";
            echo "Usuário (Banco): <strong>[" . $row['usuario'] . "]</strong> (Tamanho: " . strlen($row['usuario']) . ")<br>";
            echo "Hash (Banco): " . substr($row['senha'], 0, 20) . "... <br>";
            
            // TESTE DO HASH
            if (password_verify($senha_teste, $row['senha'])) {
                echo "<h4 style='color:green'>✅ SENHA BATE! O login deveria funcionar.</h4>";
            } else {
                echo "<h4 style='color:red'>❌ SENHA NÃO BATE. O Hash no banco é diferente da senha '$senha_teste'.</h4>";
            }
            
            // TESTE DE VALIDADE
            $hoje = new DateTime();
            $val = new DateTime($row['validade_acesso']);
            if ($hoje > $val) {
                echo "<strong style='color:red'>⚠️ CONTA VENCIDA (Data: " . $val->format('d/m/Y') . ")</strong>";
            } else {
                echo "<strong style='color:green'>Data OK (Vence: " . $val->format('d/m/Y') . ")</strong>";
            }
            echo "</div>";
        }
    } else {
        echo "<p style='color:red'>Nenhum usuário 'renato' encontrado no banco PROD.</p>";
    }

} catch (Exception $e) { echo "Erro Prod: " . $e->getMessage(); }

echo "<hr>";

// =================================================================
// TESTE 2: BANCO DEMO
// =================================================================
echo "<h3>2. Verificando Banco DEMO (proposta)</h3>";
try {
    $conn = Database::getDemo();
    
    $sql = "SELECT id_usuario, usuario, senha, validade_acesso FROM Usuarios WHERE usuario LIKE '%renato%'";
    $res = $conn->query($sql);
    
    if ($res->num_rows > 0) {
        while ($row = $res->fetch_assoc()) {
            echo "<div style='border:1px solid #ccc; padding:10px; margin-bottom:10px;'>";
            echo "ID: " . $row['id_usuario'] . "<br>";
            echo "Usuário (Banco): <strong>[" . $row['usuario'] . "]</strong><br>";
            
            if (password_verify($senha_teste, $row['senha'])) {
                echo "<h4 style='color:green'>✅ SENHA BATE!</h4>";
            } else {
                echo "<h4 style='color:red'>❌ SENHA NÃO BATE.</h4>";
            }
             // TESTE DE VALIDADE
             $hoje = new DateTime();
             $val = new DateTime($row['validade_acesso']);
             if ($hoje > $val) {
                 echo "<strong style='color:red'>⚠️ CONTA VENCIDA (Data: " . $val->format('d/m/Y') . ")</strong>";
             } else {
                 echo "<strong style='color:green'>Data OK (Vence: " . $val->format('d/m/Y') . ")</strong>";
             }
            echo "</div>";
        }
    } else {
        echo "<p style='color:red'>Nenhum usuário 'renato' encontrado no banco DEMO.</p>";
    }

} catch (Exception $e) { echo "Erro Demo: " . $e->getMessage(); }
?>