<?php
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Acesso inválido.");
}

$env = $_POST['env'] ?? 'prod';
$slug = $_POST['slug'] ?? '';
$name = trim($_POST['new_variation_name'] ?? '');

if (empty($slug) || empty($name)) {
    header("Location: admin_modelos.php?msg=" . urlencode("Dados inválidos."));
    exit;
}

try {
    $conn = ($env === 'demo') ? Database::getDemo() : Database::getProd();

    // Insere nova variação (copia conteúdo padrão se existir, ou vazio)
    // 1. Pega conteúdo padrão
    $content = "";
    $stmtGet = $conn->prepare("SELECT content_text FROM proposal_content_variations WHERE block_slug = ? AND is_default = 1");
    $stmtGet->bind_param("s", $slug);
    $stmtGet->execute();
    $res = $stmtGet->get_result();
    if ($row = $res->fetch_assoc()) {
        $content = $row['content_text']; // Copia do padrão para facilitar edição
    }
    $stmtGet->close();

    // 2. Cria nova variação
    $stmt = $conn->prepare("INSERT INTO proposal_content_variations (block_slug, variation_name, content_text, is_default) VALUES (?, ?, ?, 0)");
    $stmt->bind_param("sss", $slug, $name, $content);

    if ($stmt->execute()) {
        $newId = $stmt->insert_id;
        header("Location: admin_editar_modelo.php?slug={$slug}&env={$env}&id_variation={$newId}");
        exit;
    } else {
        die("Erro ao criar variação: " . $conn->error);
    }
} catch (Exception $e) {
    die("Erro: " . $e->getMessage());
}
