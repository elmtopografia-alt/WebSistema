<?php
/**
 * api_logs_mp.php
 * Retorna os últimos logs do Mercado Pago em JSON
 */

require_once 'config.php';
session_start();

// Segurança: Apenas Admin
if (!isset($_SESSION['usuario_id']) || $_SESSION['perfil'] !== 'admin') {
    http_response_code(403);
    exit(json_encode(['erro' => 'Acesso negado']));
}

try {
    $pdo = conectarBanco();
    $stmt = $pdo->query("SELECT id_log, data_recebimento, tipo_evento, status_processamento FROM Logs_Webhook ORDER BY id_log DESC LIMIT 20");
    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    header('Content-Type: application/json');
    echo json_encode($logs);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['erro' => $e->getMessage()]);
}
