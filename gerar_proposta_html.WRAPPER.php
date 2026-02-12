<?php
/**
 * WRAPPER DE COMPATIBILIDADE - Gerador de Proposta HTML
 * 
 * Este snippet garante segurança e compatibilidade com o novo sistema
 * preservando o funcionamento do gerador legado.
 */

// 1. Inicializa o sistema novo (Fase 4/5)
require_once __DIR__ . '/bootstrap.php';

// 2. Segurança: Apenas usuários autenticados podem gerar propostas
\SGT\Security\AuthService::requireAuth();

// 3. Ponte de Dados para o legado ($conn mysqli)
global $conn;
if (!isset($conn)) {
    // Busca a conexão mysqli via ConnectionManager da Fase 1/2
    require_once 'ConnectionManager.php';
    $conn = ConnectionManager::get();
}

// 4. Log de Geração (Auditoria)
error_log("Geração de Proposta HTML (ID: " . ($_GET['id'] ?? 'n/a') . ") por Usuário ID: " . ($_SESSION['usuario_id'] ?? 'n/a'));

// 5. Continua para o código original
// require_once 'gerar_proposta_html.php'; (Isso será inserido no topo do arquivo original)
