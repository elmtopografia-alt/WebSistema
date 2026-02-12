<?php
/**
 * WRAPPER DE SEGURANÇA - Fase 5
 * Arquivo: gerar_documento.php
 * Tipo: Gerador de Documentos Word
 */

// 1. CAPTURA DE BUFFER CRÍTICO (evita output prematuro que corrompe o Word)
ob_start();

try {
    // 2. INICIALIZAÇÃO DO SISTEMA
    require_once __DIR__ . '/bootstrap.php';
    
    use SGT\Security\AuthService;
    use SGT\Security\InputSanitizer;

    // 3. VERIFICAÇÃO DE AUTENTICAÇÃO
    if (!AuthService::check()) {
        ob_end_clean();
        error_log("[AUDIT] Tentativa de acesso não autorizado ao WORD (ID: " . ($_GET['id'] ?? 'n/a') . ") - IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'n/a'));
        header('Location: login.php?msg=sessao_expirada');
        exit;
    }
    
    // 4. LOG DE ACESSO (Auditoria)
    $user = AuthService::user();
    $idProposta = InputSanitizer::int($_GET['id'] ?? $_POST['id'] ?? 0);
    
    error_log(sprintf(
        "[AUDIT] DOCUMENTO_GERAR_INICIADO - User: %s (ID: %d) - Proposta: %d - IP: %s",
        $user['email'],
        $user['id'],
        $idProposta,
        $_SERVER['REMOTE_ADDR']
    ));
    
    // 5. PONTE DE COMPATIBILIDADE (MySQLi)
    global $conn;
    if (!isset($conn)) {
        require_once 'ConnectionManager.php';
        $conn = ConnectionManager::get();
    }
    
    // 6. FALLBACK PARA LEGADO (Durante Observação)
    $arquivoLegado = __DIR__ . '/gerar_documento.LEGADO.php';
    
    if (file_exists($arquivoLegado)) {
        ob_end_clean(); // Libera buffer antes de delegar para não corromper o download
        require_once $arquivoLegado;
        exit;
    } else {
        throw new Exception("Arquivo de geração legado não encontrado.");
    }
    
} catch (Exception $e) {
    ob_end_clean();
    error_log("[ERRO WRAPPER WORD]: " . $e->getMessage());
    http_response_code(500);
    die("Erro interno ao processar documento. O administrador foi notificado.");
}
