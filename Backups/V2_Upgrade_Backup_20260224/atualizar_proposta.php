<?php
/**
 * atualizar_proposta.php - Upgrade SGT v2.0
 * Realiza o UPDATE total (Overwrite) da proposta e sua planilha de custos.
 */

require_once 'session_validator.php';
require_once 'PropostaRepository.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Acesso inválido.");
}

$id_usuario = $_SESSION['usuario_id'];
$id_proposta = intval($_POST['id_proposta'] ?? 0);

if (!$id_proposta) {
    die("ID da proposta não informado.");
}

try {
    $repo = new PropostaRepository();
    
    // Verifica se a proposta pertence ao usuário
    $proposta_atual = $repo->buscarPorId($id_proposta);
    if (!$proposta_atual || $proposta_atual['id_criador'] != $id_usuario) {
        throw new Exception("Acesso negado ou proposta não encontrada.");
    }

    // 1. Atualização Completa (v2.0)
    $sucesso = $repo->atualizarCompleto($id_proposta, $_POST, $id_usuario);

    if ($sucesso) {
        $response = [
            'success' => true,
            'message' => 'Proposta atualizada com sucesso!',
            'redirect' => 'painel.php?success=1'
        ];
    } else {
        throw new Exception("Erro ao atualizar proposta no banco de dados.");
    }

} catch (Throwable $e) {
    $response = [
        'success' => false,
        'message' => $e->getMessage()
    ];
}

// Resposta AJAX ou Redirect
if (!empty($_POST['ajax']) || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'xmlhttprequest')) {
    header('Content-Type: application/json');
    echo json_encode($response);
} else {
    if ($response['success']) {
        header("Location: " . $response['redirect']);
    } else {
        die("Erro: " . $response['message']);
    }
}