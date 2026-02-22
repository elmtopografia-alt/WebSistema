<?php
/**
 * fix_db_coluna_modelo.php
 * Script para adicionar a coluna faltante 'modelo_docx' na tabela Propostas.
 */
require_once __DIR__ . '/ConnectionManager.php';

header('Content-Type: text/plain; charset=utf-8');

try {
    $conn = ConnectionManager::get();
    echo "=== Corrigindo Estrutura do Banco de Dados ===\n\n";
    
    // SQL para adicionar a coluna
    $sql = "ALTER TABLE Propostas ADD COLUMN modelo_docx VARCHAR(100) DEFAULT NULL AFTER coordenadas_gps";
    
    if ($conn->query($sql)) {
        echo "✅ Coluna 'modelo_docx' adicionada com sucesso!\n";
    } else {
        // Verifica se o erro é porque a coluna já existe (previne erro em execuções duplicadas)
        if ($conn->errno == 1060) {
            echo "ℹ️ A coluna 'modelo_docx' já existe.\n";
        } else {
            echo "❌ Erro ao adicionar coluna: " . $conn->error . "\n";
        }
    }
    
    echo "\n=== Verificação Final ===\n";
    $res = $conn->query("SHOW COLUMNS FROM Propostas LIKE 'modelo_docx'");
    if ($res && $res->num_rows > 0) {
        echo "✅ A tabela Propostas está agora atualizada e pronta.\n";
    } else {
        echo "❌ Falha na verificação. A coluna ainda não foi detectada.\n";
    }

} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}
