<?php
// Nome do Arquivo: criar_usuarios_teste.php
// Função: Gerar usuários de teste (Prod e Demo) com senha forte criptografada.
// EXECUTE UMA VEZ E APAGUE.

require_once 'config.php';
require_once 'db.php';

// Senha padrão para ambos
$senha_texto = "Ren!2026";
$senha_hash = password_hash($senha_texto, PASSWORD_DEFAULT);

echo "<h1>Gerando Usuários de Teste</h1>";
echo "<hr>";

// =========================================================================
// 1. USUÁRIO PRODUÇÃO
// =========================================================================
try {
    $connProd = Database::getProd();
    $email_prod = "renato_prod@gmail.com";
    $nome_prod = "Renato Produção";
    
    // Verifica se já existe
    $check = $connProd->query("SELECT id_usuario FROM Usuarios WHERE usuario = '$email_prod'");
    
    if ($check->num_rows == 0) {
        $validade = date('Y-m-d H:i:s', strtotime('+1 year')); // 1 ano de acesso
        
        $sql = "INSERT INTO Usuarios (usuario, senha, nome_completo, setup_concluido, ambiente, tipo_perfil, validade_acesso, data_cadastro) 
                VALUES (?, ?, ?, 1, 'producao', 'cliente', ?, NOW())";
        
        $stmt = $connProd->prepare($sql);
        $stmt->bind_param('ssss', $email_prod, $senha_hash, $nome_prod, $validade);
        
        if ($stmt->execute()) {
            $id = $connProd->insert_id;
            // Cria empresa vinculada
            $connProd->query("INSERT INTO DadosEmpresa (id_criador, Empresa, Cidade, Estado, CNPJ) VALUES ($id, 'Construtora Renato Prod', 'São Paulo', 'SP', '00.000.000/0001-00')");
            echo "<p style='color:green'>✅ Usuário <strong>PROD</strong> criado com sucesso!</p>";
        } else {
            echo "<p style='color:red'>❌ Erro ao criar PROD: " . $stmt->error . "</p>";
        }
    } else {
        echo "<p style='color:orange'>⚠️ Usuário PROD já existia.</p>";
    }
} catch (Exception $e) { echo "Erro Conexão Prod: " . $e->getMessage(); }

echo "<hr>";

// =========================================================================
// 2. USUÁRIO DEMO
// =========================================================================
try {
    $connDemo = Database::getDemo();
    $email_demo = "renato_demo@gmail.com";
    $nome_demo = "Renato Demonstração";
    
    // Verifica se já existe
    $check = $connDemo->query("SELECT id_usuario FROM Usuarios WHERE usuario = '$email_demo'");
    
    if ($check->num_rows == 0) {
        $validade = date('Y-m-d H:i:s', strtotime('+5 days')); // 5 dias de teste
        
        // Na demo, perfil geralmente é admin da própria conta
        $sql = "INSERT INTO Usuarios (usuario, senha, nome_completo, setup_concluido, ambiente, tipo_perfil, validade_acesso, data_cadastro) 
                VALUES (?, ?, ?, 1, 'demo', 'admin', ?, NOW())";
        
        $stmt = $connDemo->prepare($sql);
        $stmt->bind_param('ssss', $email_demo, $senha_hash, $nome_demo, $validade);
        
        if ($stmt->execute()) {
            $id = $connDemo->insert_id;
            // Cria empresa vinculada
            $connDemo->query("INSERT INTO DadosEmpresa (id_criador, Empresa, Cidade, Estado, CNPJ) VALUES ($id, 'Empresa Teste Demo', 'Rio de Janeiro', 'RJ', '11.111.111/0001-11')");
            echo "<p style='color:green'>✅ Usuário <strong>DEMO</strong> criado com sucesso!</p>";
        } else {
            echo "<p style='color:red'>❌ Erro ao criar DEMO: " . $stmt->error . "</p>";
        }
    } else {
        echo "<p style='color:orange'>⚠️ Usuário DEMO já existia.</p>";
    }
} catch (Exception $e) { echo "Erro Conexão Demo: " . $e->getMessage(); }

echo "<hr>";
echo "<h3>Dados para Acesso:</h3>";
echo "<ul>";
echo "<li><strong>Produção:</strong> renato_prod@gmail.com / Ren!2026 (Use login.php)</li>";
echo "<li><strong>Demo:</strong> renato_demo@gmail.com / Ren!2026 (Use login_demo.php)</li>";
echo "</ul>";
?>