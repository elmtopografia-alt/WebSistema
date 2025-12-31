<?php
// MODO DE DIAGNÓSTICO DE VENDOR

ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>Diagnóstico da Pasta 'vendor'</h1>";

// --- TESTE 1: VERIFICAR SE O ARQUIVO AUTOLOAD EXISTE ---

$caminho_autoload = __DIR__ . '/vendor/autoload.php';
echo "<p>Procurando pelo arquivo de autoload em: <strong>" . $caminho_autoload . "</strong></p>";

if (file_exists($caminho_autoload)) {
    echo "<p style='color:green; font-weight:bold;'>[SUCESSO] O arquivo 'vendor/autoload.php' foi encontrado.</p>";
} else {
    echo "<p style='color:red; font-weight:bold;'>[FALHA CRÍTICA] O arquivo 'vendor/autoload.php' NÃO foi encontrado. A estrutura da pasta vendor está incorreta ou o arquivo está faltando.</p>";
    die("<h2>Diagnóstico Interrompido.</h2>");
}

// --- TESTE 2: TENTAR INCLUIR O AUTOLOAD ---

try {
    require_once $caminho_autoload;
    echo "<p style='color:green; font-weight:bold;'>[SUCESSO] O arquivo 'vendor/autoload.php' foi incluído sem erros.</p>";
} catch (Exception $e) {
    echo "<p style='color:red; font-weight:bold;'>[FALHA CRÍTICA] Ocorreu um erro ao tentar incluir 'vendor/autoload.php': " . htmlspecialchars($e->getMessage()) . "</p>";
    die("<h2>Diagnóstico Interrompido.</h2>");
}

// --- TESTE 3: VERIFICAR SE A CLASSE DO PHPWORD PODE SER ENCONTRADA ---

echo "<p>Verificando se a classe principal do PHPWord (\\PhpOffice\\PhpWord\\PhpWord) está disponível...</p>";

if (class_exists('\\PhpOffice\\PhpWord\\PhpWord')) {
    echo "<p style='color:green; font-weight:bold;'>[SUCESSO] A classe do PHPWord foi encontrada com sucesso!</p>";
    echo "<h2>Diagnóstico Concluído: A sua pasta 'vendor' e a instalação do PHPWord parecem estar funcionando corretamente.</h2>";
} else {
    echo "<p style='color:red; font-weight:bold;'>[FALHA CRÍTICA] A classe do PHPWord NÃO foi encontrada. Isso indica que a instalação dentro da pasta 'vendor' está corrompida ou incompleta.</p>";
    die("<h2>Diagnóstico Interrompido.</h2>");
}

?>