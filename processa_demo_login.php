<?php
// ARQUIVO: processa_demo.php
// VERSÃO: CRIAÇÃO DE EMPRESA AUTOMÁTICA (RESOLVE ERRO DE CADASTRO)

session_start();

// Exibir erros para diagnóstico
ini_set('display_errors', 1);
error_reporting(E_ALL);

function normalizarEmail($email) {
    $partes = explode('@', strtolower($email));
    if (count($partes) != 2) return $email;
    $usuario = $partes[0];
    $dominio = $partes[1];
    if ($dominio === 'gmail.com') {
        $usuario = str_replace('.', '', $usuario);
        $usuario = explode('+', $usuario)[0];
    }
    return $usuario . '@' . $dominio;
}

// 1. Conexão Explícita ao Banco DEMO
$host = 'proposta.mysql.dbaas.com.br';
$user = 'proposta';
$pass = 'Qtamaqmde5202@';
$base = 'proposta';

$conn = new mysqli($host, $user, $pass, $base);
if ($conn->connect_error) { die("Erro de conexão demo: " . $conn->connect_error); }

$email_original = $_POST['email_demo'] ?? '';
if (!filter_var($email_original, FILTER_VALIDATE_EMAIL)) { die("E-mail inválido. <a href='novo_demo.php'>Voltar</a>"); }

$nova_senha = rand(100000, 999999);

// 2. Busca Usuário Existente
$sql_busca = "SELECT id_usuario, nome_completo, validade_acesso FROM Usuarios WHERE usuario = ?";
$stmt = $conn->prepare($sql_busca);
$stmt->bind_param('s', $email_original);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows > 0) {
    // RECUPERAÇÃO
    $dados = $res->fetch_assoc();
    $id_usuario = $dados['id_usuario'];
    $validade = $dados['validade_acesso'];
    $nome = $dados['nome_completo'];

    $upd = $conn->prepare("UPDATE Usuarios SET senha = ? WHERE id_usuario = ?");
    $upd->bind_param('si', $nova_senha, $id_usuario);
    $upd->execute();

} else {
    // NOVO USUÁRIO
    $nome = "Visitante (" . explode('@', $email_original)[0] . ")";
    $validade = date('Y-m-d H:i:s', strtotime('+5 days'));
    
    $ins = $conn->prepare("INSERT INTO Usuarios (usuario, senha, nome_completo, setup_concluido, ambiente, validade_acesso) VALUES (?, ?, ?, 1, 'demo', ?)");
    $ins->bind_param('ssss', $email_original, $nova_senha, $nome, $validade);
    
    if (!$ins->execute()) { die("Erro ao criar usuário: " . $conn->error); }
    $id_usuario = $conn->insert_id;
    
    // ======================================================
    // 🚀 CORREÇÃO: CRIAÇÃO AUTOMÁTICA DA EMPRESA DEMO
    // ======================================================
    $sql_emp = "INSERT INTO DadosEmpresa (id_criador, Empresa, CNPJ) VALUES (?, ?, ?)";
    $nome_empresa = "Topografia Demo de " . explode(' ', $nome)[0];
    $cnpj_ficticio = "00.000.000/0001-".str_pad($id_usuario, 2, '0', STR_PAD_LEFT);

    $stmt_emp = $conn->prepare($sql_emp);
    $stmt_emp->bind_param('iss', $id_usuario, $nome_empresa, $cnpj_ficticio);
    
    if (!$stmt_emp->execute()) {
        // Loga o erro, mas não trava o login do usuário
        error_log("Falha ao criar DadosEmpresa para o Demo ID $id_usuario: " . $conn->error);
    }
    // ======================================================
}

// 3. Configura Sessão
$_SESSION['usuario_id'] = $id_usuario;
$_SESSION['usuario_nome'] = $nome;
$_SESSION['setup_concluido'] = 1;
$_SESSION['ambiente'] = 'demo';
$_SESSION['validade_demo'] = $validade;

$_SESSION['show_credentials'] = true;
$_SESSION['temp_user'] = $email_original;
$_SESSION['temp_pass'] = $nova_senha;

setcookie('elm_demo_tracker', base64_encode($email_original), time() + (86400 * 30), "/");

$conn->close();
header("Location: index.php");
exit;
?>