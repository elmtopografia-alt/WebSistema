<?php
// Inicio: criar_demo_especifico.php
// Função: Cria o usuário específico solicitado com senha criptografada no Banco DEMO.

session_start();
require_once 'config.php';
require_once 'db.php';

// Conecta ao Banco DEMO
$conn = Database::getDemo();

// DADOS SOLICITADOS
$email = "contato_demo@elmtopografia.com.br";
$senha_texto = "Contato@8304063";
$nome = "Contato Demo ELM";

// 1. Gera o Hash da Senha (Obrigatório para o login funcionar)
$senha_hash = password_hash($senha_texto, PASSWORD_DEFAULT);

// 2. Define validade longa (1 ano) para este usuário específico não expirar rápido
$validade = date('Y-m-d H:i:s', strtotime('+365 days'));

// 3. Verifica se já existe
$check = $conn->prepare("SELECT id_usuario FROM Usuarios WHERE usuario = ?");
$check->bind_param('s', $email);
$check->execute();
$res = $check->get_result();

echo "<!DOCTYPE html><html lang='pt-br'><head><meta charset='UTF-8'><title>Criar Demo</title>";
echo "<link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css' rel='stylesheet'></head><body class='bg-light p-5'>";
echo "<div class='card shadow p-4 mx-auto' style='max-width: 600px;'>";
echo "<h3 class='text-primary'>Configuração de Usuário Demo</h3><hr>";

if ($res->num_rows > 0) {
    // ATUALIZA
    $row = $res->fetch_assoc();
    $id_usuario = $row['id_usuario'];
    
    $upd = $conn->prepare("UPDATE Usuarios SET senha = ?, validade_acesso = ?, nome_completo = ? WHERE id_usuario = ?");
    $upd->bind_param('sssi', $senha_hash, $validade, $nome, $id_usuario);
    $upd->execute();
    
    echo "<div class='alert alert-warning'>⚠️ O usuário <strong>$email</strong> já existia. A senha e validade foram atualizadas.</div>";
} else {
    // CRIA NOVO
    $ins = $conn->prepare("INSERT INTO Usuarios (usuario, senha, nome_completo, setup_concluido, ambiente, tipo_perfil, validade_acesso, data_cadastro) VALUES (?, ?, ?, 1, 'demo', 'admin', ?, NOW())");
    $ins->bind_param('ssss', $email, $senha_hash, $nome, $validade);
    
    if ($ins->execute()) {
        $id_usuario = $conn->insert_id;
        echo "<div class='alert alert-success'>✅ Usuário <strong>$email</strong> criado com sucesso!</div>";
    } else {
        die("<div class='alert alert-danger'>Erro ao criar: " . $conn->error . "</div>");
    }
}

// 4. Garante que existe DadosEmpresa (Vital para o sistema)
$checkEmp = $conn->query("SELECT id_empresa FROM DadosEmpresa WHERE id_criador = $id_usuario");
if ($checkEmp->num_rows == 0) {
    $sqlEmp = "INSERT INTO DadosEmpresa (id_criador, Empresa, CNPJ, Cidade, Estado) VALUES (?, 'ELM Topografia Demo', '00.000.000/0001-99', 'São Paulo', 'SP')";
    $stmtEmp = $conn->prepare($sqlEmp);
    $stmtEmp->bind_param('i', $id_usuario);
    $stmtEmp->execute();
    echo "<p class='text-muted small'>+ Dados da Empresa fictícia vinculados.</p>";
}

echo "<hr>";
echo "<h5>Dados de Acesso:</h5>";
echo "<p><strong>Ambiente:</strong> DEMO</p>";
echo "<p><strong>Login:</strong> $email</p>";
echo "<p><strong>Senha:</strong> $senha_texto</p>";
echo "<a href='login.php' class='btn btn-primary w-100 fw-bold mt-3'>Ir para Login</a>";

echo "</div></body></html>";
?>