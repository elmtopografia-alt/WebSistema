<?php
// Nome do Arquivo: salvar_dados_empresa.php
// Função: Recebe o POST de minha_empresa.php e atualiza a tabela DadosEmpresa

session_start();
require_once 'config.php';
require_once 'db.php';

// 1. Verificação de Segurança
if (!isset($_SESSION['usuario_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: login.php");
    exit;
}

$id_usuario = $_SESSION['usuario_id'];
$is_demo = ($_SESSION['ambiente'] === 'demo');
$conn = $is_demo ? Database::getDemo() : Database::getProd();

try {
    // 2. Coleta de Dados
    $empresa  = trim($_POST['Empresa'] ?? '');
    $cnpj     = trim($_POST['CNPJ'] ?? '');
    $endereco = trim($_POST['Endereco'] ?? '');
    $cidade   = trim($_POST['Cidade'] ?? '');
    $estado   = trim($_POST['Estado'] ?? '');
    $telefone = trim($_POST['Telefone'] ?? '');
    $celular  = trim($_POST['Celular'] ?? '');
    $whatsapp = trim($_POST['Whatsapp'] ?? '');
    $banco    = trim($_POST['Banco'] ?? '');
    $agencia  = trim($_POST['Agencia'] ?? '');
    $conta    = trim($_POST['Conta'] ?? '');
    $pix      = trim($_POST['PIX'] ?? '');

    // 3. Atualização no Banco de Dados
    // Usamos UPDATE onde id_criador = usuário logado
    $sql = "UPDATE DadosEmpresa SET 
            Empresa = ?, CNPJ = ?, Endereco = ?, Cidade = ?, Estado = ?,
            Telefone = ?, Celular = ?, Whatsapp = ?,
            Banco = ?, Agencia = ?, Conta = ?, PIX = ?
            WHERE id_criador = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ssssssssssssi', 
        $empresa, $cnpj, $endereco, $cidade, $estado,
        $telefone, $celular, $whatsapp,
        $banco, $agencia, $conta, $pix,
        $id_usuario
    );

    if ($stmt->execute()) {
        header("Location: minha_empresa.php?msg=sucesso");
    } else {
        die("Erro ao atualizar: " . $stmt->error);
    }

} catch (Exception $e) {
    die("Erro técnico: " . $e->getMessage());
}
