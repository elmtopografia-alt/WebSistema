<?php
// Limpa OPcache para garantir execução do código novo
if (function_exists('opcache_reset')) opcache_reset();

echo "<pre style='background:#111; color:#0f0; padding:20px; font-family:monospace;'>";
echo "=== INICIANDO CONVERSOR DIRETO ===\n";

$origem = __DIR__ . '/modelos_prod';
$destino = __DIR__ . '/modelos_unificados';

require_once __DIR__ . '/scripts/conversor_docx_v3.php';

// Limpa cache do conversor antes de instanciar
if (class_exists('ConversorDocxV3')) {
    $conversor = new ConversorDocxV3($origem, $destino);
    $conversor->executar();
    
    echo "\n\n=== VERIFICANDO RESULTADOS ===\n";
    if (file_exists(__DIR__ . '/scripts/validador_v3.php')) {
        require_once __DIR__ . '/scripts/validador_v3.php';
        if (class_exists('ValidadorV3')) {
            $validador = new ValidadorV3();
            $validador->validarDiretorio($destino);
        }
    }
}
echo "</pre>";
