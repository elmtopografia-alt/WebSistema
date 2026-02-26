<?php
/**
 * diagnostico_schema_propostas.php - Lista colunas para conferência de CRUD
 */
require_once 'config.php';
require_once 'ConnectionManager.php';

header('Content-Type: text/plain');
echo "--- ESTRUTURA DA TABELA PROPOSTAS ---\n\n";

$conn = ConnectionManager::get();
$res = $conn->query("DESCRIBE Propostas");

if ($res) {
    echo str_pad("Campo", 30) . " | " . str_pad("Tipo", 20) . " | Nulo | Chave | Padrão | Extra\n";
    echo str_repeat("-", 100) . "\n";
    while ($row = $res->fetch_assoc()) {
        echo str_pad($row['Field'], 30) . " | " . 
             str_pad($row['Type'], 20) . " | " . 
             str_pad($row['Null'], 4) . " | " . 
             str_pad($row['Key'], 5) . " | " . 
             str_pad($row['Default'] ?? 'NULL', 10) . " | " . 
             $row['Extra'] . "\n";
    }
} else {
    echo "ERRO ao descrever tabela: " . $conn->error;
}

echo "\n--- FIM ---";
