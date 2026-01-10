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
// FIM DAS CONFIGURAÇÕES
// ==========================================================
// Este arquivo não deve conter lógica de conexão.
// Use db.php para obter a conexão com o banco.

