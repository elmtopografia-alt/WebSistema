<?php
/**
 * config.php
 * Arquivo de Configuração Centralizado - Versão Multi-Banco
 * * Responsabilidade:
 * 1. Guardar credenciais de acesso específicas (PROD e DEMO).
 * 2. Mapear as bases de dados.
 */

// Proteção contra acesso direto
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    die('Acesso direto negado.');
}

// ==========================================================
// 1. AMBIENTE
// ==========================================================
define('ENVIRONMENT', 'development'); // Mude para 'production' ao finalizar

if (ENVIRONMENT === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// ==========================================================
// 2. CREDENCIAIS DO BANCO DE DADOS
// ==========================================================

// --- PROD (Ambiente de Produção / Demanda) ---
define('DB_PROD_HOST', 'demanda.mysql.dbaas.com.br');
define('DB_PROD_USER', 'demanda');
define('DB_PROD_PASS', 'Qtamaqmde5202@');
define('DB_PROD_NAME', 'demanda');

// --- DEMO (Ambiente de Demonstração / Proposta) ---
define('DB_DEMO_HOST', 'proposta.mysql.dbaas.com.br');
define('DB_DEMO_USER', 'proposta');
define('DB_DEMO_PASS', 'Qtamaqmde5202@');
define('DB_DEMO_NAME', 'proposta');

// ==========================================================
// 3. CONFIGURAÇÕES GERAIS
// ==========================================================
define('SITE_NAME', 'SGT - Sistema de Gestão Topográfica');

// Configurações Regionais
date_default_timezone_set('America/Sao_Paulo');
setlocale(LC_MONETARY, 'pt_BR');
mb_internal_encoding("UTF-8");
?>