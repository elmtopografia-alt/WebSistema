<?php
// verificar_arquivos.php
// Script para listar arquivos no servidor e verificar se o painel está lá
header('Content-Type: text/html; charset=utf-8');

echo "<h1>Diagnóstico de Arquivos (Locaweb)</h1>";
echo "<p>Este script está rodando em: <strong>" . __DIR__ . "</strong></p>";

$arquivos = scandir(__DIR__);

echo "<table border='1' cellpadding='10'>";
echo "<tr><th>Arquivo</th><th>Tamanho</th><th>Permissões</th><th>Link (Clique para testar)</th></tr>";

foreach ($arquivos as $arquivo) {
    if ($arquivo == '.' || $arquivo == '..') continue;
    
    $caminho = __DIR__ . DIRECTORY_SEPARATOR . $arquivo;
    $perms = substr(sprintf('%o', fileperms($caminho)), -4);
    $tamanho = filesize($caminho) . ' bytes';
    
    // Cor destaca o arquivo problemático
    $cor = ($arquivo == 'painel_prospeccao.php') ? 'style="background-color: yellow; font-weight: bold;"' : '';
    
    echo "<tr $cor>";
    echo "<td>$arquivo</td>";
    echo "<td>$tamanho</td>";
    echo "<td>$perms</td>";
    echo "<td><a href='$arquivo' target='_blank'>Abrir $arquivo</a></td>";
    echo "</tr>";
}
echo "</table>";
?>
