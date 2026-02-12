<?php
/**
 * Configurações de Segurança
 * 
 * Headers HTTP, configurações de sessão e políticas de segurança
 * 
 * @package SGT_Propostas
 * @subpackage Config
 */

declare(strict_types=1);

namespace SGT\Config;

/**
 * Configura headers de segurança HTTP
 * Deve ser chamado no início de cada requisição
 */
function setupSecurityHeaders(): void
{
    // Previne clickjacking
    header('X-Frame-Options: DENY');
    
    // Previne MIME sniffing
    header('X-Content-Type-Options: nosniff');
    
    // XSS Protection (legado, mas ainda útil)
    header('X-XSS-Protection: 1; mode=block');
    
    // Referrer Policy
    header('Referrer-Policy: strict-origin-when-cross-origin');
    
    // Content Security Policy (ajuste conforme necessidade)
    $csp = "default-src 'self'; ";
    $csp .= "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net; ";
    $csp .= "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://fonts.googleapis.com; ";
    $csp .= "font-src 'self' https://fonts.gstatic.com; ";
    $csp .= "img-src 'self' data: https:; ";
    $csp .= "connect-src 'self';";
    header("Content-Security-Policy: {$csp}");
    
    // Strict Transport Security (descomente em produção com HTTPS)
    // header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
    
    // Remove headers que expõem informações
    header_remove('X-Powered-By');
    header_remove('Server');
}

/**
 * Configura sessão segura
 */
function setupSecureSession(): void
{
    $config = getAppConfig();
    
    // Configurações de cookie de sessão
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_secure', $config['env'] === 'production' ? '1' : '0');
    ini_set('session.cookie_samesite', 'Strict');
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.gc_maxlifetime', (int)($_ENV['SESSION_LIFETIME'] ?? 120) * 60);
    
    // Nome personalizado da sessão (não expõe que é PHP)
    session_name('sgt_session');
    
    // Regenera ID periodicamente
    if (session_status() === PHP_SESSION_ACTIVE) {
        if (!isset($_SESSION['last_regeneration'])) {
            $_SESSION['last_regeneration'] = time();
        } elseif (time() - $_SESSION['last_regeneration'] > 300) { // 5 minutos
            session_regenerate_id(true);
            $_SESSION['last_regeneration'] = time();
        }
    }
}

/**
 * Inicializa sessão de forma segura
 */
function startSecureSession(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        setupSecureSession();
        session_start();
        
        // Proteção contra fixation
        if (!isset($_SESSION['initialized'])) {
            session_regenerate_id(true);
            $_SESSION['initialized'] = true;
            $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'] ?? '';
            $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? '';
        }
        
        // Validação de integridade da sessão
        validateSessionIntegrity();
    }
}

/**
 * Valida se a sessão não foi roubada
 */
function validateSessionIntegrity(): void
{
    if (!isset($_SESSION['ip_address']) || !isset($_SESSION['user_agent'])) {
        session_destroy();
        startSecureSession();
        return;
    }
    
    $currentIp = $_SERVER['REMOTE_ADDR'] ?? '';
    $currentUa = $_SERVER['HTTP_USER_AGENT'] ?? '';
    
    // Permite mudança de IP apenas na mesma faixa (para usuários móveis)
    $sessionIpPrefix = substr($_SESSION['ip_address'], 0, 7);
    $currentIpPrefix = substr($currentIp, 0, 7);
    
    if ($sessionIpPrefix !== $currentIpPrefix || $_SESSION['user_agent'] !== $currentUa) {
        // Possível hijacking - destroi sessão
        session_destroy();
        startSecureSession();
        $_SESSION['security_alert'] = 'Sessão invalidada por segurança';
    }
}
