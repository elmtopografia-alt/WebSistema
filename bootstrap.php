<?php
/**
 * Bootstrap do SGT Propostas
 * 
 * Carrega configurações, segurança e autoloader.
 * Deve ser incluído no início de TODO arquivo PHP novo.
 * 
 * @package SGT_Propostas
 */

declare(strict_types=1);

// Previne acesso direto a includes (opcional)
if (!defined('SGT_ACCESS')) define('SGT_ACCESS', true);

// Caminho base do projeto
define('SGT_ROOT', __DIR__);
define('SGT_SRC', SGT_ROOT . '/src');
define('SGT_CONFIG', SGT_ROOT . '/config');

// Carrega configurações base da Fase 3
require_once SGT_CONFIG . '/database.php';
require_once SGT_CONFIG . '/security.php';

use function SGT\Config\loadEnv;
use function SGT\Config\setupSecurityHeaders;
use function SGT\Config\startSecureSession;

// Configura logs de erro para a pasta local
$logPath = SGT_ROOT . '/logs/error_' . date('Y-m-d') . '.log';
ini_set('log_errors', '1');
ini_set('error_log', $logPath);

// Carrega variáveis de ambiente do .env
try {
    loadEnv(SGT_ROOT . '/.env');
} catch (\Exception $e) {
    // Se falhar o carregamento (ex: arquivo ausente), logamos e paramos
    error_log("FALHA BOOTSTRAP: " . $e->getMessage());
    die('Erro crítico de configuração. Verifique o arquivo .env.');
}

// Configura headers de segurança (CSP, XSS, etc)
setupSecurityHeaders();

// Inicia sessão segura e validada
startSecureSession();

/**
 * Autoloader PSR-4 Simplificado
 */
spl_autoload_register(function ($class) {
    $prefix = 'SGT\\';
    $base_dir = SGT_SRC . '/';
    
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    
    if (file_exists($file)) {
        require $file;
    }
});

// Helpers Úteis
if (!function_exists('asset')) {
    function asset(string $path): string {
        return htmlspecialchars($path, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('url')) {
    function url(string $path): string {
        $base = rtrim($_ENV['APP_URL'] ?? '', '/');
        return $base . '/' . ltrim($path, '/');
    }
}

if (!function_exists('old')) {
    function old(string $key, $default = ''): string {
        return htmlspecialchars($_POST[$key] ?? $default, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('flash')) {
    function flash(string $type, string $message): void {
        $_SESSION['flash_messages'][$type] = $message;
    }
}

if (!function_exists('getFlash')) {
    function getFlash(string $type): ?string {
        $message = $_SESSION['flash_messages'][$type] ?? null;
        unset($_SESSION['flash_messages'][$type]);
        return $message;
    }
}
