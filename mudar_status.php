SistemaWeb/mudar_status.phpSistemaWeb/mudar_status.php<?php
// Nome do Arquivo: mudar_status.php
// Função: Backend para atualizar status.
// Correção: Redireciona para PAINEL.PHP (e não index.php).

session_start();
require_once 'config.php';
require_once 'db.php';

// 1. Segurança
if (!isset($_SESSION['usuario_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: painel.php");
    exit;
}

$id_usuario = $_SESSION['usuario_id'];
$is_demo = (isset($_SESSION['ambiente']) && $_SESSION['ambiente'] === 'demo');
$conn = $is_demo ? Database::getDemo() : Database::getProd();

// 2. Coleta dados
$id_proposta = intval($_POST['id_proposta']);
$novo_status = $_POST['novo_status'];

// Lista de status permitidos
$permitidos = ['Em elaboração', 'Enviada', 'Aprovada', 'Cancelada'];

if (in_array($novo_status, $permitidos)) {
    try {
        // 3. Atualiza no Banco
        $stmt = $conn->prepare("UPDATE Propostas SET status = ? WHERE id_proposta = ? AND id_criador = ?");
        $stmt->bind_param('sii', $novo_status, $id_proposta, $id_usuario);
        
        if ($stmt->execute()) {
            // SUCESSO: Vai para o PAINEL
            header("Location: painel.php?msg=status_ok");
            exit;
        } else {
            header("Location: painel.php?msg=erro");
            exit;
        }
    } catch (Exception $e) {
        die("Erro técnico: " . $e->getMessage());
    }
} else {
    die("Status inválido.");
}