<?php
/**
 * Proteção CSRF (Cross-Site Request Forgery)
 * 
 * Gera e valida tokens CSRF para formulários
 * 
 * @package SGT_Propostas
 * @subpackage Security
 */

declare(strict_types=1);

namespace SGT\Security;

require_once __DIR__ . '/../../config/security.php';
require_once __DIR__ . '/../../config/database.php';

use function SGT\Config\startSecureSession;
use function SGT\Config\getAppConfig;

class CSRFProtection
{
    private const TOKEN_KEY = 'csrf_token';
    private const TOKEN_TIME = 'csrf_created';
    private const TOKEN_LIFETIME = 3600; // 1 hora
    
    /**
     * Inicializa e retorna token CSRF
     */
    public static function getToken(): string
    {
        startSecureSession();
        
        // Gera novo token se não existir ou expirou
        if (empty($_SESSION[self::TOKEN_KEY]) || 
            empty($_SESSION[self::TOKEN_TIME]) ||
            (time() - $_SESSION[self::TOKEN_TIME]) > self::TOKEN_LIFETIME) {
            
            $_SESSION[self::TOKEN_KEY] = self::generateToken();
            $_SESSION[self::TOKEN_TIME] = time();
        }
        
        return $_SESSION[self::TOKEN_KEY];
    }
    
    /**
     * Gera token criptograficamente seguro
     */
    private static function generateToken(): string
    {
        $config = getAppConfig();
        $salt = $config['csrf_salt'] ?? 'default_salt_change_in_production';
        
        $random = bin2hex(random_bytes(32));
        $hash = hash_hmac('sha256', $random . session_id(), $salt);
        
        return $hash;
    }
    
    /**
     * Valida token recebido
     */
    public static function validateToken(?string $token): bool
    {
        startSecureSession();
        
        if (empty($token) || empty($_SESSION[self::TOKEN_KEY])) {
            return false;
        }
        
        // Timing-safe comparison
        return hash_equals($_SESSION[self::TOKEN_KEY], $token);
    }
    
    /**
     * Invalida token atual (após uso bem-sucedido)
     */
    public static function invalidateToken(): void
    {
        startSecureSession();
        unset($_SESSION[self::TOKEN_KEY], $_SESSION[self::TOKEN_TIME]);
    }
    
    /**
     * Retorna HTML do input hidden para formulários
     */
    public static function getInputField(): string
    {
        $token = self::getToken();
        return sprintf(
            '<input type="hidden" name="csrf_token" value="%s">',
            htmlspecialchars($token, ENT_QUOTES, 'UTF-8')
        );
    }
    
    /**
     * Verifica token e lança exceção se inválido
     * @throws \Exception
     */
    public static function verifyOrFail(): void
    {
        $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
        
        if (!self::validateToken($token)) {
            http_response_code(403);
            header('Content-Type: application/json');
            die(json_encode(['erro' => 'Token CSRF inválido. Recarregue a página.']));
        }
    }
}
