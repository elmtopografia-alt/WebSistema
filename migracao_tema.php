<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/ConnectionManager.php';

error_reporting(E_ALL);
ini_set('display_errors', '1');

try {
    $conn = ConnectionManager::get();
    
    // 1. Adicionar coluna cor
    try {
        $conn->query("ALTER TABLE Propostas ADD COLUMN cor VARCHAR(20) DEFAULT 'verde'");
    } catch(Exception $e) {
        // Ignora se a coluna já existir
    }
    
    // 2. Migrar modelos antigos para novo padrão
    $conn->query("
        UPDATE Propostas SET 
            modelo_docx = 'PropostaDrone',
            cor = CASE 
                WHEN modelo_docx LIKE '%Azul%' THEN 'azul'
                WHEN modelo_docx LIKE '%Verde%' THEN 'verde'
                WHEN modelo_docx LIKE '%Laranja%' THEN 'laranja'
                WHEN modelo_docx LIKE '%Cinza%' THEN 'cinza'
                ELSE 'verde'
            END
        WHERE modelo_docx LIKE 'ModeloPropostaDrone%'
    ");
    
    // 3. Verificar migração
    $res = $conn->query("SELECT id_proposta, modelo_docx, cor, data_criacao FROM Propostas ORDER BY id_proposta DESC LIMIT 10");
    $result = [];
    if ($res) {
        while($r = $res->fetch_assoc()) {
            $result[] = $r;
        }
    }
    
    echo "Sucesso!\n";
    print_r($result);
    
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}
