<?php
/**
 * Migração: Adicionar coluna modelo_docx à tabela Propostas
 */

require_once 'db.php';

$conn = Database::getProd();

echo "Iniciando migração...\n";

$sql = "ALTER TABLE Propostas ADD COLUMN IF NOT EXISTS modelo_docx VARCHAR(255) DEFAULT NULL AFTER id_servico";

if ($conn->query($sql)) {
    echo "Sucesso: Coluna 'modelo_docx' adicionada ou já existente.\n";
} else {
    echo "Erro ao adicionar coluna: " . $conn->error . "\n";
}

$sql2 = "ALTER TABLE Propostas ADD COLUMN IF NOT EXISTS config_docx_json LONGTEXT DEFAULT NULL AFTER modelo_docx";
if ($conn->query($sql2)) {
    echo "Sucesso: Coluna 'config_docx_json' adicionada.\n";
}

echo "Migração concluída.";
