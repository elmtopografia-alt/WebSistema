<?php
require_once 'db.php';

$env = $_GET['env'] ?? 'prod';
$slug = $_GET['slug'] ?? '';
$id = $_GET['id'] ?? 0;

if (empty($slug) || empty($id)) {
    die("Dados inválidos.");
}

try {
    $conn = ($env === 'demo') ? Database::getDemo() : Database::getProd();

    // Verifica se é default (não pode excluir o padrão por seguranca simples aqui)
    $stmtCheck = $conn->prepare("SELECT is_default FROM proposal_content_variations WHERE id_variation = ?");
    $stmtCheck->bind_param("i", $id);
    $stmtCheck->execute();
    $res = $stmtCheck->get_result();
    $row = $res->fetch_assoc();

    if ($row && $row['is_default'] == 1) {
        die("Não é permitido excluir a variação Padrão.");
    }

    // Exclui
    $stmt = $conn->prepare("DELETE FROM proposal_content_variations WHERE id_variation = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        header("Location: admin_editar_modelo.php?slug={$slug}&env={$env}"); // Volta para o default do slug
        exit;
    } else {
        die("Erro ao excluir: " . $conn->error);
    }
} catch (Exception $e) {
    die("Erro: " . $e->getMessage());
}
