<?php
// ARQUIVO: salvar_modo_header.php
// FUNÇÃO: Salva o modo de exibição do header (logo completo ou ícone) via AJAX

session_start();
require_once 'config.php';
require_once 'db.php';

header('Content-Type: application/json');

// Verificação de Segurança
if (!isset($_SESSION['usuario_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Acesso negado']);
    exit;
}

// VALIDAÇÃO CSRF
if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    echo json_encode(['success' => false, 'error' => 'Token de segurança inválido']);
    exit;
}

$id_usuario = $_SESSION['usuario_id'];
$is_demo = ($_SESSION['ambiente'] === 'demo');

// Demo não pode alterar
if ($is_demo) {
    echo json_encode(['success' => false, 'error' => 'Alteração bloqueada no modo demo']);
    exit;
}

$conn = Database::getProd();

try {
    $header_mode = trim($_POST['header_logo_mode'] ?? 'full');

    // Validar valor
    if (!in_array($header_mode, ['full', 'icon'])) {
        $header_mode = 'full';
    }

    // Atualizar no banco
    $stmt = $conn->prepare("UPDATE DadosEmpresa SET header_logo_mode = ? WHERE id_criador = ?");
    $stmt->bind_param('si', $header_mode, $id_usuario);

    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Modo do header atualizado com sucesso',
            'mode' => $header_mode
        ]);
    } else {
        throw new Exception("Erro ao atualizar: " . $stmt->error);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
