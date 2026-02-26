<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<pre>Iniciando Teste do Premium...\n\n";

register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== null && in_array($error['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR, E_RECOVERABLE_ERROR, E_PARSE])) {
        echo "\n\nCRITICAL FATAL ERROR CAPTURADO:\n";
        print_r($error);
    }
});

try {
    $_GET['id'] = 193; // Força o ID para o teste
    include __DIR__ . '/testes_isolados/crm-propostas/gerar-proposta.php';
} catch (Throwable $e) {
    echo "\n\nEXCEÇÃO CAPTURADA:\n";
    echo $e->getMessage() . "\n";
    echo $e->getFile() . " na linha " . $e->getLine() . "\n";
}

echo "\n\nFinalizado com sucesso (sem fatal error).</pre>";
