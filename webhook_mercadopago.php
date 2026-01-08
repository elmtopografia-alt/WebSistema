<?php
/**
 * webhook_mercadopago.php
 * Recebe notificações do Mercado Pago e salva no banco de dados.
 */

require_once 'config.php';

// 1. Captura o corpo da requisição
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// 2. Identifica o tipo de evento (se possível)
$tipo_evento = $data['type'] ?? ($data['action'] ?? 'unknown');

// 3. Salva no banco de dados
try {
    $pdo = conectarBanco();
    
    $sql = "INSERT INTO Logs_Webhook (tipo_evento, payload_json, status_processamento) VALUES (?, ?, 'pendente')";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $tipo_evento,
        $input // Salva o JSON bruto original
    ]);
    
    // 4. Retorna 200 OK para o Mercado Pago não reenviar imediatamente
    http_response_code(200);
    echo "OK";

} catch (Exception $e) {
    // Log de erro silencioso (para não expor erro ao MP, mas idealmente deveria logar em arquivo)
    http_response_code(500);
    echo "Erro interno";
}
