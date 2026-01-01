<?php
/**
 * config.php
 * Configuração central do sistema
 * NÃO inicia sessão
 * NÃO cria conexão
 */

// Bloqueia acesso direto
if (basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
    http_response_code(403);
    exit('Acesso negado.');
}

// ==========================================================
// AMBIENTE
// ==========================================================
define('ENVIRONMENT', 'production'); // development | production

if (ENVIRONMENT === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
}

// ==========================================================
// TIMEZONE / LOCALE
// ==========================================================
date_default_timezone_set('America/Sao_Paulo');
mb_internal_encoding('UTF-8');

// ==========================================================
// BASE URL (AJUSTE SE NECESSÁRIO)
// ==========================================================
define('BASE_URL', '/Orcamento');

// ==========================================================
// BANCO DE DADOS
// ==========================================================

// PRODUÇÃO (DEMANDA)
define('DB_PROD_HOST', 'demanda.mysql.dbaas.com.br');
define('DB_PROD_NAME', 'demanda');
define('DB_PROD_USER', 'demanda');
define('DB_PROD_PASS', 'Qtamaqmde5202@');

// DEMO (PROPOSTA)
define('DB_DEMO_HOST', 'proposta.mysql.dbaas.com.br');
define('DB_DEMO_NAME', 'proposta');
define('DB_DEMO_USER', 'proposta');
define('DB_DEMO_PASS', 'Qtamaqmde5202@');

// ==========================================================
// SISTEMA
// ==========================================================
define('SITE_NAME', 'SGT - Sistema de Gestão Topográfica');
define('CNPJ_PADRAO', 'ELM Serviços Topográficos Ltda');
