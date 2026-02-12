<?php
require_once 'db.php';

try {
    $conn = Database::getProd();
    echo "Conectado ao banco de dados.\n";

    $sql = file_get_contents(__DIR__ . '/setup_crm_update.sql');

    // Executa múltiplas queries se necessário (embora o driver PDO padrão não suporte múltiplas queries em uma chamada prepare/execute, vamos tentar exec direto ou dividir)
    // Para garantir compatibilidade, vamos dividir por ";"
    
    $queries = explode(';', $sql);

    foreach ($queries as $query) {
        $query = trim($query);
        if (!empty($query)) {
            try {
                $conn->exec($query);
                echo "Sucesso: " . substr($query, 0, 50) . "...\n";
            } catch (PDOException $e) {
                // Ignora erro de coluna duplicada (1060)
                if (strpos($e->getMessage(), "Duplicate column name") !== false) {
                     echo "Aviso: Coluna já existe. Ignorando.\n";
                } else {
                     echo "Erro: " . $e->getMessage() . "\n";
                }
            }
        }
    }

    echo "Atualização de CRM concluída!";

} catch (Exception $e) {
    echo "Erro fatal: " . $e->getMessage();
}
?>
