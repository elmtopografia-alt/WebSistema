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
mb_internal_encoding('utf-8');

// ==========================================================
// CREDENCIAIS
// ==========================================================

// PRODUÇÃO
define('DB_PROD_HOST', 'demanda.mysql.dbaas.com.br');
define('DB_PROD_NAME', 'demanda');
define('DB_PROD_USER', 'demanda');
define('DB_PROD_PASS', 'Qtamaqmde5202@');

// DEMO
define('DB_DEMO_HOST', 'proposta.mysql.dbaas.com.br');
define('DB_DEMO_NAME', 'proposta');
define('DB_DEMO_USER', 'proposta');
define('DB_DEMO_PASS', 'Qtamaqmde5202@');

// ==========================================================
// FUNÇÃO DE CONEXÃO (ÚNICO PONTO PDO)
// ==========================================================
function conectarBanco(string $ambiente = 'prod'): PDO
{
    if ($ambiente === 'demo') {
        $host = DB_DEMO_HOST;
        $db   = DB_DEMO_NAME;
        $user = DB_DEMO_USER;
        $pass = DB_DEMO_PASS;
    } else {
        $host = DB_PROD_HOST;
        $db   = DB_PROD_NAME;
        $user = DB_PROD_USER;
        $pass = DB_PROD_PASS;
    }

    $dsn = "mysql:host={$host};dbname={$db};charset=utf8mb4";

    return new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
}
