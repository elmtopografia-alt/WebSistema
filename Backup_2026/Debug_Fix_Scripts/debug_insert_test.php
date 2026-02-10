<?php
// debug_insert_test.php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require 'config.php';
require 'db.php';

echo "<h1>Debug Insert Propostas</h1>";

try {
    // 1. Conexão
    $conn = Database::getProd(); // Força PROD para teste
    echo "Conexão OK.<br>";

    // 2. Dados Dummy
    $num = 'TEST-' . date('His');
    $id_criador = 3; // ID Fixo para teste ou pegar de $_SESSION se disponivel
    if(session_status() === PHP_SESSION_NONE) session_start();
    if(isset($_SESSION['usuario_id'])) $id_criador = $_SESSION['usuario_id'];

    // 3. Query (Cópia Exata do salvar_proposta.php)
    $sql = "INSERT INTO Propostas (
        numero_proposta, id_cliente, id_criador, 
        nome_cliente_salvo, email_salvo, status,
        valor_final_proposta, data_criacao
    ) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";

    // Nota: Estou testando um INSERT simplificado primeiro para ver se a tabela aceita
    // Se falhar aqui, o problema é permissão ou tabela corrompida/inexistente

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Erro Prepare: " . $conn->error);
    }

    $cliente = 1; // ID Cliente Fixo
    $nome = "Cliente Teste Debug";
    $email = "teste@debug.com";
    $status = "Em elaboração";
    $valor = 1234.56;

    $stmt->bind_param("siisssd", $num, $cliente, $id_criador, $nome, $email, $status, $valor);

    if ($stmt->execute()) {
        echo "✅ INSERT Básico Sucesso! ID: " . $conn->insert_id . "<br>";
        
        // Agora vamos testar listar as colunas para comparar com o salvar_proposta.php
        $res = $conn->query("SHOW COLUMNS FROM Propostas");
        echo "<h3>Colunas da Tabela:</h3><ul>";
        while($row = $res->fetch_assoc()) {
            echo "<li>" . $row['Field'] . " (" . $row['Type'] . ")</li>";
        }
        echo "</ul>";

    } else {
        throw new Exception("Erro Execute: " . $stmt->error);
    }

} catch (Exception $e) {
    echo "❌ FALHA: " . $e->getMessage();
}
