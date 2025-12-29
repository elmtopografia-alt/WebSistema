<?php
// Nome do Arquivo: mudar_status.php
// Função: Backend para atualizar o status da proposta (Ex: De 'Em elaboração' para 'Aprovada').

session_start();
require_once 'config.php';
require_once 'db.php';

// 1. Segurança: Verifica se está logado e se é POST
if (!isset($_SESSION['usuario_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit;
}

$id_usuario = $_SESSION['usuario_id'];
$is_demo = (isset($_SESSION['ambiente']) && $_SESSION['ambiente'] === 'demo');
$conn = $is_demo ? Database::getDemo() : Database::getProd();

// 2. Coleta dados do formulário
$id_proposta = intval($_POST['id_proposta']);
$novo_status = $_POST['novo_status'];

// Lista de status permitidos (Segurança contra injeção de valores estranhos)
$permitidos = ['Em elaboração', 'Enviada', 'Aprovada', 'Cancelada'];

if (in_array($novo_status, $permitidos)) {
    try {
        // 3. Atualiza no Banco (Garante que a proposta pertence ao usuário logado)
        $stmt = $conn->prepare("UPDATE Propostas SET status = ? WHERE id_proposta = ? AND id_criador = ?");
        $stmt->bind_param('sii', $novo_status, $id_proposta, $id_usuario);
        
        if ($stmt->execute()) {
            // Sucesso: Volta para o painel com mensagem verde
            header("Location: index.php?msg=status_ok");
            exit;
        } else {
            // Erro de SQL
            header("Location: index.php?msg=erro");
            exit;
        }
    } catch (Exception $e) {
        die("Erro técnico: " . $e->getMessage());
    }
} else {
    // Tentativa de status inválido
    die("Status inválido.");
}