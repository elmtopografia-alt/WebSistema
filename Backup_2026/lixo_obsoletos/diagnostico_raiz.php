<?php
// Diagnóstico v2 (Na Raiz)
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>Diagnóstico Raiz</h1>";
echo "<p>Bem-vindo ao diagnóstico na raiz.</p>";
echo "<p>Caminho atual: " . __DIR__ . "</p>";

// Tenta verificar se a pasta 'propostas' existe
if (is_dir('propostas')) {
    echo "<p style='color:green'>A pasta 'propostas' EXISTE aqui.</p>";
    $files = scandir('propostas');
    echo "<pre>" . print_r($files, true) . "</pre>";
} else {
    echo "<p style='color:red'>A pasta 'propostas' NÃO EXISTE aqui.</p>";
}
?>
