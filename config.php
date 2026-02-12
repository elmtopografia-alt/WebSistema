<?php
/**
 * config.php
 * Configuração central do SGT
 * NÃO inicia sessão
 * NÃO imprime nada
 */

// ==========================================================
// PROTEÇÃO
// ==========================================================
if (basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
    exit('acesso direto negado');
}

// ==========================================================
// AMBIENTE
// ==========================================================
define('ENVIRONMENT', 'production'); // development | production
define('SISTEMA_VERSAO', '01');

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

// ==========================================================
// TIMEZONE / LOCALE
// ==========================================================
date_default_timezone_set('america/sao_paulo');
setlocale(LC_ALL, 'pt_BR', 'pt_BR.utf-8', 'portuguese');
mb_internal_encoding('utf-8');

// ==========================================================
// CREDENCIAIS (Carregadas de .env) - 2026-02-12
// ==========================================================

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

define('DB_PROD_HOST', loadEnv('DB_PROD_HOST'));
define('DB_PROD_NAME', loadEnv('DB_PROD_NAME'));
define('DB_PROD_USER', loadEnv('DB_PROD_USER'));
define('DB_PROD_PASS', loadEnv('DB_PROD_PASS'));
define('DB_DEMO_HOST', loadEnv('DB_DEMO_HOST'));
define('DB_DEMO_NAME', loadEnv('DB_DEMO_NAME'));
define('DB_DEMO_USER', loadEnv('DB_DEMO_USER'));
define('DB_DEMO_PASS', loadEnv('DB_DEMO_PASS'));
define('SMTP_HOST', loadEnv('SMTP_HOST', 'email-ssl.com.br'));
define('SMTP_USER', loadEnv('SMTP_USER'));
define('SMTP_PASS', loadEnv('SMTP_PASS'));
define('SMTP_PORT', intval(loadEnv('SMTP_PORT', 465)));
define('SMTP_FROM_EMAIL', loadEnv('SMTP_USER')); 
define('SMTP_FROM_NAME', 'SGT - Sistema de Gestão');

// E-mail Financeiro (Para uso específico ou Reply-To)
define('EMAIL_FINANCEIRO', 'financeiro.sgt@elmtopografia.com.br');

// ==========================================================
// FIM DAS CONFIGURAÇÕES
// ==========================================================
// Este arquivo não deve conter lógica de conexão.
// Use db.php para obter a conexão com o banco.

