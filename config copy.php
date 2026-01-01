<?php
/**
 * config.php
 * Configuração central + conexão PDO
 * Código pleno e determinístico
 */

// ==========================================================
// BLOQUEIO DE ACESSO DIRETO
// ==========================================================
if (basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
    exit('acesso direto negado');
}

// ==========================================================
// AMBIENTE
// ==========================================================
define('ENVIRONMENT', 'development'); // development | production

if (ENVIRONMENT === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// ==========================================================
// CONFIGURAÇÕES REGIONAIS
// ==========================================================
date_default_timezone_set('America/Sao_Paulo');
mb_internal_encoding('UTF-8');

// ==========================================================
// BASES DE DADOS
// ==========================================================

// PRODUÇÃO (DEMANDA)
define('DB_PROD_HOST', 'demanda.mysql.dbaas.com.br');
define('DB_PROD_USER', 'demanda');
define('DB_PROD_PASS', 'Qtamaqmde5202@');
define('DB_PROD_NAME', 'demanda');

// DEMO (PROPOSTA)
define('DB_DEMO_HOST', 'proposta.mysql.dbaas.com.br');
define('DB_DEMO_USER', 'proposta');
define('DB_DEMO_PASS', 'Qtamaqmde5202@');
define('DB_DEMO_NAME', 'proposta');

// ==========================================================
// DEFINIÇÃO DO AMBIENTE ATIVO
// ==========================================================
// valores possíveis: prod | demo
$AMBIENTE_ATIVO = 'prod';

// ==========================================================
// CONEXÃO PDO
// ==========================================================
try {

    if ($AMBIENTE_ATIVO === 'prod') {
        $dsn  = "mysql:host=" . DB_PROD_HOST . ";dbname=" . DB_PROD_NAME . ";charset=utf8mb4";
        $user = DB_PROD_USER;
        $pass = DB_PROD_PASS;
    } else {
        $dsn  = "mysql:host=" . DB_DEMO_HOST . ";dbname=" . DB_DEMO_NAME . ";charset=utf8mb4";
        $user = DB_DEMO_USER;
        $pass = DB_DEMO_PASS;
    }

    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);

} catch (PDOException $e) {
    exit('erro de conexão com banco de dados');
}

// ==========================================================
// CONFIGURAÇÕES GERAIS
// ==========================================================
define('SITE_NAME', 'SGT - Sistema de Gestão Topográfica');
