<?php
/**
 * SCRIPT DE DIAGNÓSTICO DE SOBREVIVÊNCIA
 * Captura erros fatais em tempo de execução
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== NULL && ($error['type'] === E_ERROR || $error['type'] === E_PARSE || $error['type'] === E_COMPILE_ERROR)) {
        echo "<h1>FATAL ERROR DETECTADO</h1>";
        echo "<pre>";
        print_r($error);
        echo "</pre>";
    }
});

try {
    // Tenta incluir o arquivo problemático
    require_once 'gerar-proposta.php';
} catch (Throwable $e) {
    echo "<h1>EXCEÇÃO CAPTURADA</h1>";
    echo "<p><strong>Erro:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>Arquivo:</strong> " . $e->getFile() . "</p>";
    echo "<p><strong>Linha:</strong> " . $e->getLine() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>
