<?php
/**
 * setup_webhook_logs.php
 * Cria a tabela para armazenar logs do Mercado Pago
 */

require_once 'config.php';

try {
    $pdo = conectarBanco();
    
    $sql = "CREATE TABLE IF NOT EXISTS Logs_Webhook (
        id_log INT AUTO_INCREMENT PRIMARY KEY,
        data_recebimento DATETIME DEFAULT CURRENT_TIMESTAMP,
        tipo_evento VARCHAR(100),
        payload_json TEXT,
        status_processamento VARCHAR(50) DEFAULT 'pendente'
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    
    $pdo->exec($sql);
    
    echo "Tabela Logs_Webhook criada/verificada com sucesso.";
    
} catch (Exception $e) {
    echo "Erro ao criar tabela: " . $e->getMessage();
}
