<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'db.php';
header('Content-Type: text/plain; charset=utf-8');

try {
    $conn = Database::getProd();
    echo "Conexão OK\n";
    
    $text = "Teste de conteúdo";
    $sql = "INSERT INTO proposal_content_variations (template_slug, service_type_id, content_text, allowed_vars, is_active) 
            VALUES ('test_slug', 999, '$text', '[]', 1)";
            
    if ($conn->query($sql)) {
        echo "Inserção de TESTE OK\n";
        $conn->query("DELETE FROM proposal_content_variations WHERE service_type_id = 999");
    } else {
        echo "Erro na inserção: " . $conn->error . "\n";
    }
} catch (Exception $e) {
    echo "Exceção: " . $e->getMessage() . "\n";
}
?>
