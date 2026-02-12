<?php
// ARQUIVO: processa_demo.php
// VERSÃO: 01 (Com Notificação de Boas-Vindas)

session_start();
require_once 'config.php';
require_once 'db.php'; // Garante acesso ao Database::getDemo()

// Carrega PHPMailer
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

ini_set('display_errors', 1);
error_reporting(E_ALL);

// Função de Envio de Boas-Vindas
function enviarEmailBoasVindas($email, $senha, $validade) {
    require_once 'GerenciadorEmail.php';

    $dataFim = date('d/m/Y H:i', strtotime($validade));
    $assunto = "SGT | Usuário Aprovado - Acesso Liberado! 🚀";

    $corpoHTML = "
        <div style='font-family: Arial, sans-serif; color: #333;'>
            <h1 style='color: #0d6efd;'>Acesso Liberado!</h1>
            <p>Seu usuário Demo foi aprovado e o acesso está liberado.</p>
            <hr>
            <h3>Suas Credenciais:</h3>
            <p><strong>Usuário:</strong> $email</p>
            <p><strong>Senha:</strong> $senha</p>
            <p><strong>Link de Acesso:</strong> <a href='" . BASE_URL . "/login_demo.php'>" . BASE_URL . "/login_demo.php</a></p>
            <hr>
            <p style='background: #fff3cd; padding: 10px; border-left: 5px solid #ffc107;'>
                <strong>⚠️ ATENÇÃO:</strong> Este é um ambiente de <strong>TESTE (DEMO)</strong>.<br>
                Seu acesso expira em: <strong>$dataFim</strong>.<br>
                Após esta data, todos os dados serão apagados automaticamente.
            </p>
            <p>Aproveite para testar todas as funcionalidades!</p>
        </div>
    ";

    // Envia usando GerenciadorEmail
    GerenciadorEmail::enviar($email, '', $assunto, $corpoHTML);
}

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8"><title>Processando...</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center justify-content-center" style="min-height: 100vh;">
    <div class="card shadow p-4 m-3" style="max-width: 600px; width: 100%;">
        <div class="text-center">
<?php

$conn = Database::getDemo(); // Usa a classe centralizada
if ($conn->connect_error) { die("Erro Conexão: " . $conn->connect_error); }

$email = $_POST['email_demo'] ?? '';
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { die("E-mail inválido."); }

$nova_senha = rand(100000, 999999);
$usuario_acao = "";
$validade = date('Y-m-d H:i:s', strtotime('+5 days'));

// Verifica Usuário
$stmt = $conn->prepare("SELECT id_usuario FROM Usuarios WHERE usuario = ?");
$stmt->bind_param('s', $email);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows > 0) {
    // RECUPERAÇÃO
    $row = $res->fetch_assoc();
    $id_usuario = $row['id_usuario'];
    $usuario_acao = "Conta Reativada/Recuperada";
    
    $conn->query("UPDATE Usuarios SET senha = '$nova_senha', validade_acesso = '$validade' WHERE id_usuario = $id_usuario");

} else {
    // NOVO USUÁRIO
    $usuario_acao = "Conta Criada com Sucesso";
    $nome = "Visitante (" . explode('@', $email)[0] . ")";
    
    $ins = $conn->prepare("INSERT INTO Usuarios (usuario, senha, nome_completo, setup_concluido, ambiente, validade_acesso) VALUES (?, ?, ?, 1, 'demo', ?)");
    $ins->bind_param('ssss', $email, $nova_senha, $nome, $validade);
    
    if ($ins->execute()) {
        $id_usuario = $conn->insert_id;
        
        // --- PREENCHE DADOS FICTÍCIOS ---
        $sql_emp = "INSERT INTO DadosEmpresa 
            (id_criador, Empresa, CNPJ, Endereco, Cidade, Estado, Telefone, Celular, Whatsapp, Banco, Agencia, Conta, PIX) 
            VALUES 
            (?, 'Topografia Exemplo Demo', '00.000.000/0001-99', 'Av. das Demonstrações, 1000', 'Cidade Modelo', 'SP', '(11) 3000-0000', '(11) 99999-9999', '(11) 99999-9999', 'Banco Fictício', '0001', '12345-X', 'financeiro@demo.com')";
            
        $stmt_emp = $conn->prepare($sql_emp);
        $stmt_emp->bind_param('i', $id_usuario);
        $stmt_emp->execute();
    }
}

// Envia E-mail de Boas-Vindas
enviarEmailBoasVindas($email, $nova_senha, $validade);

// Configura Sessão
$_SESSION['usuario_id'] = $id_usuario;
$_SESSION['usuario_nome'] = "Visitante Demo";
$_SESSION['ambiente'] = 'demo';
$_SESSION['setup_concluido'] = 1;
$_SESSION['validade_demo'] = $validade;

setcookie('elm_demo_tracker', base64_encode($email), time() + (86400 * 30), "/");

?>
            <h1 class="text-success mb-3"><i class="fa-solid fa-check-circle"></i> Usuário Aprovado!</h1>
            <h5 class="text-muted">Acesso Liberado com Sucesso.</h5>
            <hr>
            
            <div class="alert alert-info text-start">
                <h6>📧 E-mail Enviado!</h6>
                <p class="mb-0 small">Enviamos os dados de acesso para <strong><?= $email ?></strong>. Verifique sua caixa de entrada (e spam).</p>
            </div>

            <div class="bg-white border rounded p-3 mb-4 text-start">
                <h6 class="fw-bold text-primary"><i class="fa-solid fa-circle-info"></i> O que você pode fazer no DEMO:</h6>
                <ul class="small text-muted mb-0">
                    <li>Criar Clientes e Propostas ilimitadas.</li>
                    <li>Gerar PDFs e Relatórios Financeiros.</li>
                    <li>Testar o envio de e-mails (simulado).</li>
                </ul>
                <hr>
                <h6 class="fw-bold text-danger"><i class="fa-solid fa-ban"></i> Limitações:</h6>
                <ul class="small text-danger mb-0">
                    <li>Seus dados serão <strong>APAGADOS</strong> em 5 dias (<?= date('d/m H:i', strtotime($validade)) ?>).</li>
                    <li>Não é possível exportar dados para a versão final.</li>
                </ul>
            </div>

            <p>Anote sua senha temporária:</p>
            <div class="bg-dark text-white p-3 rounded mb-3">
                <div class="fw-bold fs-1 text-warning"><?= $nova_senha ?></div>
            </div>
            
            <a href="painel.php" class="btn btn-success btn-lg w-100 fw-bold">ACESSAR O SISTEMA AGORA</a>
        </div>
    </div>
</body>
</html>