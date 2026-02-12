<?php
/**
 * WRAPPER DE COMPATIBILIDADE - Painel CRM
 * 
 * Este snippet deve ser incluído no topo do painel_crm.php
 * Ele garante que a nova conexão PDO e as configurações de segurança
 * estejam ativas, enquanto mantém compatibilidade com o código legado.
 */

// Se o novo bootstrap já foi carregado, garantimos a ponte
require_once __DIR__ . '/bootstrap.php';

// Ativa Headers de Segurança
\SGT\Config\setupSecurityHeaders();

// Ponte de Compatibilidade para scripts que esperam $conn (mysqli)
// NOTA: O ConnectionManager (Fase 1) já faz o singleton do mysqli.
// Vamos garantir que a sessão e segurança novas não quebrem o CRM.

if (!isset($_SESSION['usuario_id']) && isset($_SESSION['user_id'])) {
    // Sincroniza logins entre sistemas se necessário
    $_SESSION['usuario_id'] = $_SESSION['user_id'];
}
