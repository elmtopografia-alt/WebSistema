/**
 * config.php
 * Configuração central do SGT
 */

// ==========================================================
// PROTEÇÃO
// ==========================================================
if (basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
    exit('acesso direto negado');
}

// ==========================================================
// AMBIENTE E AUXILIARES
// ==========================================================
if (!defined('ENVIRONMENT')) define('ENVIRONMENT', 'production'); // development | production
if (!defined('SISTEMA_VERSAO')) define('SISTEMA_VERSAO', '01');

/**
 * Carrega variáveis de ambiente do .env
 */
if (!function_exists('loadEnv')) {
    function loadEnv($var, $default = null) {
        $val = getenv($var);
        if ($val !== false) return $val;
        if (isset($_ENV[$var])) return $_ENV[$var];
        
        static $envCache = null;
        if ($envCache === null) {
            $envFile = __DIR__ . '/.env';
            $envCache = file_exists($envFile) ? parse_ini_file($envFile) : [];
        }
        if (isset($envCache[$var])) return $envCache[$var];
        
        if ($default === null) die("ERRO: Variável $var não configurada");
        return $default;
    }
}

// ==========================================================
// CREDENCIAIS (Carregadas de .env)
// ==========================================================
if (!defined('DB_PROD_HOST')) define('DB_PROD_HOST', loadEnv('DB_PROD_HOST'));
if (!defined('DB_PROD_NAME')) define('DB_PROD_NAME', loadEnv('DB_PROD_NAME'));
if (!defined('DB_PROD_USER')) define('DB_PROD_USER', loadEnv('DB_PROD_USER'));
if (!defined('DB_PROD_PASS')) define('DB_PROD_PASS', loadEnv('DB_PROD_PASS'));

if (!defined('DB_DEMO_HOST')) define('DB_DEMO_HOST', loadEnv('DB_DEMO_HOST'));
if (!defined('DB_DEMO_NAME')) define('DB_DEMO_NAME', loadEnv('DB_DEMO_NAME'));
if (!defined('DB_DEMO_USER')) define('DB_DEMO_USER', loadEnv('DB_DEMO_USER'));
if (!defined('DB_DEMO_PASS')) define('DB_DEMO_PASS', loadEnv('DB_DEMO_PASS'));

if (!defined('SMTP_HOST')) define('SMTP_HOST', loadEnv('SMTP_HOST', 'email-ssl.com.br'));
if (!defined('SMTP_USER')) define('SMTP_USER', loadEnv('SMTP_USER'));
if (!defined('SMTP_PASS')) define('SMTP_PASS', loadEnv('SMTP_PASS'));
if (!defined('SMTP_PORT')) define('SMTP_PORT', intval(loadEnv('SMTP_PORT', 465)));
if (!defined('SMTP_FROM_EMAIL')) define('SMTP_FROM_EMAIL', loadEnv('SMTP_USER')); 
if (!defined('SMTP_FROM_NAME')) define('SMTP_FROM_NAME', 'SGT - Sistema de Gestão');

// E-mail Financeiro (Para uso específico ou Reply-To)
if (!defined('EMAIL_FINANCEIRO')) define('EMAIL_FINANCEIRO', 'financeiro.sgt@elmtopografia.com.br');

// ==========================================================
// CONFIGURAÇÕES ADICIONAIS
// ==========================================================

// Define URL Base automaticamente
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];
$script = $_SERVER['SCRIPT_NAME'];
$path = dirname($script);
// Remove barras invertidas no Windows e garante barra final correta
$path = str_replace('\\', '/', $path);
$base = "$protocol://$host$path";
// Remove barra final se existir para padronizar
$base = rtrim($base, '/');
define('BASE_URL', $base);

if (ENVIRONMENT === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

date_default_timezone_set('america/sao_paulo');
setlocale(LC_ALL, 'pt_BR', 'pt_BR.utf-8', 'portuguese');
mb_internal_encoding('utf-8');

// ==========================================================
// MONITORAMENTO (Ativado após definições vitais)
// ==========================================================
// --- SGT Tracer (Ativação do GPS de Rotas) ---
require_once __DIR__ . '/src/Security/Tracer.php';
\SGT\Security\Tracer::init();
// ---------------------------------------------

// ==========================================================
// FIM DAS CONFIGURAÇÕES
// ==========================================================
// Este arquivo não deve conter lógica de conexão.
// Use db.php para obter a conexão com o banco.


