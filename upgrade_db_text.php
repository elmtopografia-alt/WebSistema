<?php
// upgrade_db_text.php
// Atualiza a coluna para suportar textos longos (LONGTEXT)

header('Content-Type: text/plain; charset=utf-8');
require_once 'vendor/autoload.php';
require_once 'db.php';

try {
    if (!isset($conn) || !($conn instanceof mysqli)) {
        $conn = Database::getProd();
    }

    echo "=== Atualização de Estrutura do Banco ===\n";
    echo "Objetivo: Aumentar capacidade de texto para LONGTEXT (4GB)\n\n";

    // Altera a coluna default_content_json
    $sql = "ALTER TABLE `proposal_block_templates` MODIFY `default_content_json` LONGTEXT COLLATE utf8mb4_unicode_ci";

    if ($conn->query($sql)) {
        echo "✅ Sucesso! Coluna 'default_content_json' atualizada para LONGTEXT.\n";
        echo "Agora você pode salvar textos jurídicos gigantes sem cortar nada.";
    } else {
        echo "❌ Erro: " . $conn->error . "\n";
    }
} catch (Exception $e) {
    echo "Erro Fatal: " . $e->getMessage();
}
