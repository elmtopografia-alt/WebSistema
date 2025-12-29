<?php
// ARQUIVO: atualizar_status.php
// VERSÃO: ALTERAÇÃO DE STATUS SEGURA

session_start();
require_once 'db.php';

// Verifica login
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

// Verifica parâmetros
if (isset($_GET['id']) && isset($_GET['status'])) {
    $id_proposta = intval($_GET['id']);
    $novo_status = $_GET['status'];

    // Lista de status permitidos (Segurança)
    $status_validos = ['Em elaboração', 'Enviada', 'Aprovada', 'Recusada', 'Cancelada', 'Concluída'];

    if (in_array($novo_status, $status_validos)) {
        // Atualiza no banco
        $stmt = $conn->prepare("UPDATE Propostas SET status = ? WHERE id_proposta = ?");
        $stmt->bind_param('si', $novo_status, $id_proposta);
        
        if ($stmt->execute()) {
            $_SESSION['mensagem_sucesso'] = "Status atualizado para: <strong>$novo_status</strong>";
        } else {
            $_SESSION['mensagem_erro'] = "Erro ao atualizar no banco de dados.";
        }
        $stmt->close();
    } else {
        $_SESSION['mensagem_erro'] = "Status inválido.";
    }
} else {
    $_SESSION['mensagem_erro'] = "Parâmetros inválidos.";
}

// Redireciona de volta para a lista
header("Location: listar_propostas.php");
exit;
?>