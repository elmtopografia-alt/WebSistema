<?php
/**
 * API para Gerar Proposta em Word (.docx)
 * Endpoint: api/gerar_proposta_docx.php?id_proposta=123
 */

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../classes/PropostaPHPWord.php';
require_once __DIR__ . '/../db.php';

// Sessão
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(['sucesso' => false, 'erro' => 'Não autenticado']);
    exit;
}

$id_usuario = $_SESSION['usuario_id'];
$is_demo = (isset($_SESSION['ambiente']) && $_SESSION['ambiente'] === 'demo');

// Conexão
try {
    $conn = $is_demo ? Database::getDemo() : Database::getProd();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['sucesso' => false, 'erro' => 'Erro de conexão: ' . $e->getMessage()]);
    exit;
}

// Busca dados da proposta
$id_proposta = isset($_GET['id_proposta']) ? (int)$_GET['id_proposta'] : 0;

if (!$id_proposta) {
    echo json_encode(['sucesso' => false, 'erro' => 'ID da proposta não informado']);
    exit;
}

// Busca proposta
$stmt = $conn->prepare("SELECT * FROM Propostas WHERE id_proposta = ? AND id_criador = ?");
$stmt->bind_param("ii", $id_proposta, $id_usuario);
$stmt->execute();
$proposta = $stmt->get_result()->fetch_assoc();

if (!$proposta) {
    http_response_code(404);
    echo json_encode(['sucesso' => false, 'erro' => 'Proposta não encontrada ou sem permissão']);
    exit;
}

// Busca cliente
$stmt = $conn->prepare("SELECT * FROM Clientes WHERE id_cliente = ?");
$stmt->bind_param("i", $proposta['id_cliente']);
$stmt->execute();
$cliente = $stmt->get_result()->fetch_assoc();

// Mescla dados
$dados = array_merge($proposta, $cliente ?? []);

// Gera nome do arquivo
$numero_proposta = $proposta['numero_proposta'] ?? $id_proposta;
$nome_arquivo = 'Proposta_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $numero_proposta) . '.docx';
$pasta_saida = __DIR__ . '/../propostas_geradas/';
$caminho_completo = $pasta_saida . $nome_arquivo;

// Garante que pasta existe
if (!is_dir($pasta_saida)) {
    mkdir($pasta_saida, 0755, true);
}

try {
    $gerador = new PropostaPHPWord($dados);
    $gerador->gerar($caminho_completo);
    
    // Retorna URL para download
    echo json_encode([
        'sucesso' => true,
        'arquivo' => $nome_arquivo,
        'url' => 'propostas_geradas/' . $nome_arquivo,
        'numero_proposta' => $numero_proposta
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['sucesso' => false, 'erro' => 'Erro ao gerar proposta: ' . $e->getMessage()]);
}
