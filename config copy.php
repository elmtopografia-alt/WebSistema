<?php
// ARQUIVO: config.php
// FUNÇÃO: Centralizar credenciais e configurações globais

// Evita acesso direto ao arquivo
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    header("HTTP/1.0 403 Forbidden");
    exit;
}

// Configurações de Fuso Horário e Localidade
setlocale(LC_ALL, 'pt_BR.utf-8', 'pt_BR', 'portuguese');
date_default_timezone_set('America/Sao_Paulo');

// Definição de Credenciais do Banco de Dados
// PROD (Ambiente de Produção)
define('DB_PROD_HOST', 'demanda.mysql.dbaas.com.br');
define('DB_PROD_USER', 'demanda');
define('DB_PROD_PASS', 'Qtamaqmde5202@');
define('DB_PROD_NAME', 'demanda');

// DEMO (Ambiente de Demonstração)
define('DB_DEMO_HOST', 'proposta.mysql.dbaas.com.br');
define('DB_DEMO_USER', 'proposta');
define('DB_DEMO_PASS', 'Qtamaqmde5202@');
define('DB_DEMO_NAME', 'proposta');
?>