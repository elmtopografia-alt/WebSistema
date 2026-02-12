<?php
// debug_docx_content.php
require_once 'vendor/autoload.php';

function getPlaceholders($filePath) {
    if (!file_exists($filePath)) return "Arquivo não encontrado: $filePath";
    
    $template = new \PhpOffice\PhpWord\TemplateProcessor($filePath);
    $variables = $template->getVariables();
    return $variables;
}

$files = [
    'modelos_prod/ModeloProfissionalV2.docx',
    'modelos_prod/ModeloPropostaDrone.docx',
    'modelos_prod/ModeloPropostaPadrao.docx' // Verificando se existe
];

echo "<h1>Variáveis Encontradas nos Modelos</h1>";

foreach ($files as $file) {
    echo "<h2>Arquivo: $file</h2>";
    if (file_exists($file)) {
        $vars = getPlaceholders($file);
        echo "<pre>";
        print_r($vars);
        echo "</pre>";
    } else {
        echo "<p style='color:red'>Arquivo não encontrado.</p>";
    }
    echo "<hr>";
}
?>
