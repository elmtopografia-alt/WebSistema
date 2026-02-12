<?php
/**
 * WRAPPER DE SEGURANÇA - Editor Avançado
 * Versão: 2.1 (Fase 5 Refatoração)
 */

ob_start();

try {
    // 1. Inicializa o sistema (Caminho corrigido para Raiz)
    require_once __DIR__ . '/bootstrap.php';
    
    // O bootstrap já inicia a sessão e define o autoloader PSR-4
    
    // 2. Verificação de Autenticação (Redirecionamento se falhar)
    if (empty($_SESSION['usuario_id']) && empty($_SESSION['user_id'])) {
        if (ob_get_length()) ob_end_clean();
        header('Location: login.php?erro=sessao_expirada&origem=editor');
        exit;
    }
    
    // 3. Log de Auditoria
    error_log("[AUDIT] EDITOR_AVANCADO_ACESSO - User: " . ($_SESSION['usuario_id'] ?? 'n/a') . " - IP: " . $_SERVER['REMOTE_ADDR']);

    // 4. Delegação para o arquivo de lógica (editor_dinamico.php)
    if (ob_get_length()) ob_end_clean();
    
    require_once __DIR__ . '/editor_dinamico.php';
    exit;
    
} catch (Throwable $e) {
    if (ob_get_level()) ob_end_clean();
    
    error_log("[ERRO WRAPPER EDITOR]: " . $e->getMessage());
    
    // Fallback de Emergência: Tenta carregar a versão v1 backup se a nova falhar dramaticamente
    if (file_exists('editor_dinamico_v1_backup.php')) {
        require_once 'editor_dinamico_v1_backup.php';
        exit;
    }
    
    http_response_code(500);
    die("Erro interno no Editor Avançado. O administrador foi notificado.");
}
