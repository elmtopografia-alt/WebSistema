<?php
// debug_check_services.php
// Script de diagnóstico para listar serviços e tabelas

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Carregando db.php...\n";
require_once 'db.php';

if (!isset($conn) || $conn->connect_error) {
    die("Erro de conexão: " . ($conn->connect_error ?? 'Variável $conn não definida'));
}

echo "Conectado ao DB. Host info: " . $conn->host_info . "\n";

// Tenta listar Tipo_Servicos
echo "\n--- Consultando Tipo_Servicos ---\n";
$sql = "SELECT * FROM Tipo_Servicos";
$result = $conn->query($sql);

if ($result) {
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            // Tenta adivinhar o nome da coluna de descrição
            $nome = $row['nome'] ?? $row['descricao'] ?? $row['titulo'] ?? 'Sem Nome';
            $id = $row['id'] ?? $row['ID'] ?? 'Sem ID';
            echo "ID: $id | Serviço: $nome\n";
        }
    } else {
        echo "Tabela vazia.\n";
    }
} else {
    echo "Erro ao consultar Tipo_Servicos: " . $conn->error . "\n";
    
    // Se falhar, lista todas as tabelas para conferir o nome
    echo "\n--- Listando Tabelas do Banco ---\n";
    $tables = $conn->query("SHOW TABLES");
    if ($tables) {
        while ($t = $tables->fetch_row()) {
            echo " - " . $t[0] . "\n";
        }
    }
}
echo "\nDiagnóstico concluído.\n";
?>
