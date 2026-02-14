<?php
/**
 * WRAPPER DE COMPATIBILIDADE - Gerador de Proposta HTML
 * 
 * Este snippet garante segurança e compatibilidade com o novo sistema
 * preservando o funcionamento do gerador legado.
 * 
 * NOTA: Usa session_validator.php (sistema legado) em vez de bootstrap.php
 * porque o bootstrap usa session_name('sgt_session') que é incompatível
 * com a sessão PHPSESSID usada pelo resto do sistema.
 */

// 1. Segurança via session_validator (compatível com sessão PHPSESSID do sistema legado)
require_once __DIR__ . '/session_validator.php';

// 2. Ponte de Dados para o legado ($conn mysqli)
global $conn;
if (!isset($conn)) {
    require_once 'ConnectionManager.php';
    $conn = ConnectionManager::get();
}

// 3. Log de Geração (Auditoria)
error_log("Geração de Proposta HTML (ID: " . ($_GET['id'] ?? 'n/a') . ") por Usuário ID: " . ($_SESSION['usuario_id'] ?? 'n/a'));

// 4. Continua para o código original

