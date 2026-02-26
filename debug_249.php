<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/PropostaRepository.php';

$repo = new PropostaRepository();
$dados = $repo->buscarPorId(249);

echo "--- DEBUG PROPOSTA 249 ---\n";
echo "modelo_docx: " . ($dados['modelo_docx'] ?? 'NULL') . "\n";
echo "isDocxMode check:\n";
echo " - modelo_docx empty? " . (empty($dados['modelo_docx']) ? 'SIM' : 'NÃO') . "\n";
echo " - docx_bloco_0_content empty? " . (empty($dados['docx_bloco_0_content']) ? 'SIM' : 'NÃO') . "\n";

if (!empty($dados['modelo_docx'])) {
    $path = __DIR__ . '/modelos_gerados/' . $dados['modelo_docx'] . '.php';
    echo "Caminho esperado: $path\n";
    echo "Arquivo existe? " . (file_exists($path) ? 'SIM' : 'NÃO') . "\n";
}
?>
