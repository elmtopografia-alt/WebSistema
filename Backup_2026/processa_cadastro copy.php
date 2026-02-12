<?php
// ARQUIVO: processa_demo.php
// VERSÃO: CORREÇÃO DE UPDATE DE SENHA NO BANCO DEMO

session_start();

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

// 1. Conexão Explícita DEMO
$host_demo = 'proposta.mysql.dbaas.com.br';
$user_demo = 'proposta';
$pass_demo = 'Qtamaqmde5202@';
$base_demo = 'proposta';

$conn = new mysqli($host_demo, $user_demo, $pass_demo, $base_demo);
if ($conn->connect_error) { die("Erro de conexão demo: " . $conn->connect_error); }

$email_original = $_POST['email_demo'] ?? '';
if (!filter_var($email_original, FILTER_VALIDATE_EMAIL)) { die("E-mail inválido. <a href='novo_demo.php'>Voltar</a>"); }

$email_busca = normalizarEmail($email_original);
$nova_senha = rand(100000, 999999);

$id_usuario = null;
$validade = null;
$nome = null;

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

} else {
    // NOVO USUÁRIO
    $nome = "Visitante (" . explode('@', $email_original)[0] . ")";
    $validade = date('Y-m-d H:i:s', strtotime('+5 days'));
    
    $sql_insert = "INSERT INTO Usuarios (usuario, senha, nome_completo, setup_concluido, ambiente, validade_acesso) VALUES (?, ?, ?, 1, 'demo', ?)";
    $ins = $conn->prepare($sql_insert);
    
    if (!$ins) { die("Erro SQL Insert (NOVO): " . $conn->error); }

    $ins->bind_param('ssss', $email_original, $nova_senha, $nome, $validade);
    
    if (!$ins->execute()) { die("Erro ao executar INSERT (NOVO): " . $ins->error); }
    $id_usuario = $conn->insert_id;
}

// --- PONTO CRÍTICO: ATUALIZAÇÃO DA SENHA EM AMBOS OS CASOS ---
// Mesmo se for novo, o insert acima usou a senha, mas vamos forçar um update seguro aqui.
// Se a conta for antiga, esta é a linha que atualiza a senha de fato.
$upd_senha_sql = "UPDATE Usuarios SET senha = ? WHERE id_usuario = ?";
$upd_stmt = $conn->prepare($upd_senha_sql);
if (!$upd_stmt) { die("Erro SQL Prepare (UPDATE SENHA): " . $conn->error); }

$upd_stmt->bind_param('si', $nova_senha, $id_usuario);
if (!$upd_stmt->execute()) {
     die("Erro ao executar UPDATE de SENHA: " . $upd_stmt->error);
}

// 3. Sessão
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