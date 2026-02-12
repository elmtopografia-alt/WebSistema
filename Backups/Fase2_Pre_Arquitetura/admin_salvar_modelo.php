<?php
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: admin_modelos.php");
    exit;
}

$env = $_POST['env'] ?? 'prod';
$slug = $_POST['slug'] ?? '';
$id_variation = $_POST['id_variation'] ?? 0;
$content = $_POST['content_text'] ?? '';

if (empty($slug) || empty($id_variation)) {
    die("Erro: Dados do bloco incompletos.");
}

try {
    $conn = ($env === 'demo') ? Database::getDemo() : Database::getProd();

    // UPDATE Direto no ID específico
    $stmt = $conn->prepare("UPDATE proposal_content_variations SET content_text = ? WHERE id_variation = ?");
    $stmt->bind_param("si", $content, $id_variation);

    if ($stmt->execute()) {
        $msg = "Conteúdo atualizado com sucesso!";
        // Mantém na mesma variação
        header("Location: admin_editar_modelo.php?slug={$slug}&env={$env}&id_variation={$id_variation}");
        exit;
    } else {
        die("Erro ao salvar no banco de dados: " . $conn->error);
    }

    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    die("Erro crítico: " . $e->getMessage());
}
