<?php
/**
 * CHECK_SYNC.php - Verificador de versão remota
 */
require_once 'PropostaRepository.php';

$reflector = new ReflectionClass('PropostaRepository');
$file = $reflector->getFileName();
$content = file_get_contents($file);

echo "<h1>Verificação de Sincronização SGT</h1>";
echo "<strong>Arquivo detectado:</strong> $file<br>";

// Procura por traços da refatoração
$hasSchemaAware = strpos($content, 'autoHealSchema') !== false;
$hasDynamicMap = strpos($content, '$map = [') !== false;

if ($hasSchemaAware && $hasDynamicMap) {
    echo "<h2 style='color:green;'>✓ Sincronização OK: Versão Schema-Aware detectada.</h2>";
} else {
    echo "<h2 style='color:red;'>✗ Sincronização FALHOU: O servidor ainda está rodando a versão LEGADA.</h2>";
    echo "<strong>Dica:</strong> Verifique as permissões de escrita do SFTP ou reinicie a sincronização.";
}

echo "<h3>Prévia da Linha 574:</h3>";
$lines = explode("\n", $content);
echo "<pre style='background:#f0f0f0; padding:10px; border:1px solid #ccc;'>";
echo "574: " . htmlspecialchars($lines[573] ?? 'Linha inexistente');
echo "</pre>";
