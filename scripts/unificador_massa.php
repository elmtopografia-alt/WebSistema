<?php
/**
 * UNIFICADOR EM MASSA - SGT PROPOSTAS
 * Varre a pasta modelos_prod e aplica a unificação v3.0 em todos os arquivos.
 */

require_once __DIR__ . '/unificador_docx.php';

$diretorioOrigem = __DIR__ . '/../modelos_prod';
$diretorioDestino = __DIR__ . '/../modelos_unificados';

if (!is_dir($diretorioDestino)) {
    mkdir($diretorioDestino, 0755, true);
}

$unificador = new UnificadorDocx();
$arquivos = glob($diretorioOrigem . "/*.docx");

echo "========================================\n";
echo "INICIANDO UNIFICAÇÃO EM MASSA (CONTRATO v3.0)\n";
echo "========================================\n";

foreach ($arquivos as $arquivo) {
    if (strpos(basename($arquivo), '~$') === 0) continue; // Ignora temporários do Word
    
    $nomeArquivo = basename($arquivo);
    $destino = $diretorioDestino . '/' . $nomeArquivo;
    
    echo "Processando: $nomeArquivo... ";
    if ($unificador->processar($arquivo, $destino)) {
        echo "✓ OK\n";
    } else {
        echo "✗ ERRO\n";
    }
}

echo "========================================\n";
echo "PROCESSO CONCLUÍDO!\n";
echo "Os arquivos unificados estão em: /modelos_unificados\n";
echo "========================================\n";
