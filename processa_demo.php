<?php
// ARQUIVO: processa_demo.php
// VERSÃO: DADOS FICTÍCIOS COMPLETOS (Para não ficar vazio)

session_start();
require_once 'config.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

// HTML de cabeçalho para a mensagem de sucesso
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8"><title>Processando...</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center justify-content-center" style="height: 100vh;">
    <div class="card shadow p-4" style="max-width: 500px; width: 100%;">
        <div class="text-center">
<?php

$conn = new mysqli(DB_DEMO_HOST, DB_DEMO_USER, DB_DEMO_PASS, DB_DEMO_NAME);
if ($conn->connect_error) { die("Erro Conexão: " . $conn->connect_error); }

$email = $_POST['email_demo'] ?? '';
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { die("E-mail inválido."); }

$nova_senha = rand(100000, 999999);
$usuario_acao = "";

// Verifica Usuário
$stmt = $conn->prepare("SELECT id_usuario FROM Usuarios WHERE usuario = ?");
$stmt->bind_param('s', $email);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows > 0) {
    // RECUPERAÇÃO
    $row = $res->fetch_assoc();
    $id_usuario = $row['id_usuario'];
    $usuario_acao = "Conta Recuperada";
    
    $validade = date('Y-m-d H:i:s', strtotime('+5 days'));
    $conn->query("UPDATE Usuarios SET senha = '$nova_senha', validade_acesso = '$validade' WHERE id_usuario = $id_usuario");

} else {
    // NOVO USUÁRIO
    $usuario_acao = "Conta Criada";
    $nome = "Visitante (" . explode('@', $email)[0] . ")";
    $validade = date('Y-m-d H:i:s', strtotime('+5 days'));
    
    $ins = $conn->prepare("INSERT INTO Usuarios (usuario, senha, nome_completo, setup_concluido, ambiente, validade_acesso) VALUES (?, ?, ?, 1, 'demo', ?)");
    $ins->bind_param('ssss', $email, $nova_senha, $nome, $validade);
    
    if ($ins->execute()) {
        $id_usuario = $conn->insert_id;
        
        // --- AQUI ESTÁ A CORREÇÃO: PREENCHE TUDO ---
        $sql_emp = "INSERT INTO DadosEmpresa 
            (id_criador, Empresa, CNPJ, Endereco, Cidade, Estado, Telefone, Celular, Whatsapp, Banco, Agencia, Conta, PIX) 
            VALUES 
            (?, 'Topografia Exemplo Demo', '00.000.000/0001-99', 'Av. das Demonstrações, 1000', 'Cidade Modelo', 'SP', '(11) 3000-0000', '(11) 99999-9999', '(11) 99999-9999', 'Banco Fictício', '0001', '12345-X', 'financeiro@demo.com')";
            
        $stmt_emp = $conn->prepare($sql_emp);
        $stmt_emp->bind_param('i', $id_usuario);
        $stmt_emp->execute();
        // -------------------------------------------
    }
}

// Configura Sessão
$_SESSION['usuario_id'] = $id_usuario;
$_SESSION['usuario_nome'] = "Visitante Demo";
$_SESSION['ambiente'] = 'demo';
$_SESSION['setup_concluido'] = 1;
$_SESSION['validade_demo'] = $validade;

setcookie('elm_demo_tracker', base64_encode($email), time() + (86400 * 30), "/");

// Exibição
?>
            <h1 class="text-success mb-3"><i class="fa-solid fa-check-circle"></i> Sucesso!</h1>
            <h5 class="text-muted"><?= $usuario_acao ?></h5>
            <hr>
            <p>Anote suas credenciais:</p>
            <div class="bg-primary text-white p-3 rounded mb-3">
                <div class="small opacity-75">Usuário</div>
                <div class="fw-bold fs-5"><?= $email ?></div>
                <div class="small opacity-75 mt-2">Senha Temporária</div>
                <div class="fw-bold fs-1"><?= $nova_senha ?></div>
            </div>
            <p class="small text-danger">⚠️ A senha mudou! Use esta nova senha.</p>
            <a href="index.php" class="btn btn-success btn-lg w-100">Acessar Painel Agora</a>
        </div>
    </div>
</body>
</html>