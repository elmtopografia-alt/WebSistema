<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/ConnectionManager.php';

try {
    $conn = ConnectionManager::get();
    echo "<h1>TESTE DE BANCO (JSON EXTRACT)</h1>";
    
    // Tenta usar JSON_UNQUOTE
    $sql = "SELECT JSON_UNQUOTE(JSON_EXTRACT('{\"teste\": \"sucesso\"}', '$.teste')) as prop";
    $res = $conn->query($sql);
    
    if (!$res) {
        throw new Exception("Falha na execução: " . $conn->error);
    }
    
    $row = $res->fetch_assoc();
    echo "<p style='color:green'>Suporte JSON OK: " . print_r($row, true) . "</p>";
    
    // Testa pegar Proposta especifica
    $id = 203;
    $sql2 = "SELECT p.id_proposta, c.nome_cliente, p.modelo_docx, p.docx_conteudo, 
                    JSON_UNQUOTE(JSON_EXTRACT(p.docx_conteudo, '$')) as docx_blocos_array
            FROM Propostas p 
            LEFT JOIN Clientes c ON p.id_cliente = c.id_cliente 
            WHERE p.id_proposta = $id";
            
    $res2 = $conn->query($sql2);
    if (!$res2) {
        throw new Exception("Falha ao buscar Proposta 203: " . $conn->error);
    }
    
    echo "<p style='color:green'>Busca por ID 203 com JSON OK. Resultados: " . $res2->num_rows . "</p>";

} catch (Exception $e) {
    echo "<h3>Erro Encontrado (Fatal Pelo DB):</h3><pre style='color:red'>" . $e->getMessage() . "</pre>";
}
echo "<p>Fim do roteiro de teste DB</p>";
?>
