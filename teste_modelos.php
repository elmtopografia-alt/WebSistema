<?php
/**
 * teste_modelos.php
 * Script de Diagnóstico de Templates (Temporários)
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

$dir_prod = __DIR__ . '/modelos_prod/';
$dir_demo = __DIR__ . '/modelos_demo/';

function listarArquivos($pasta) {
    if (!is_dir($pasta)) {
        return ["❌ ERRO: A pasta não existe no servidor."];
    }
    $arquivos = scandir($pasta);
    $lista = [];
    foreach ($arquivos as $arq) {
        if ($arq !== '.' && $arq !== '..') {
            $lista[] = $arq;
        }
    }
    return $lista;
}

$arquivosProd = listarArquivos($dir_prod);
$arquivosDemo = listarArquivos($dir_demo);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Diagnóstico de Modelos Word</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .col-box { border: 1px solid #ddd; padding: 20px; border-radius: 8px; }
        .bg-prod { background-color: #e8f5e9; border-color: #c8e6c9; }
        .bg-demo { background-color: #e3f2fd; border-color: #bbdefb; }
        h3 { border-bottom: 2px solid rgba(0,0,0,0.1); padding-bottom: 10px; margin-bottom: 20px; }
    </style>
</head>
<body class="p-4">
    <div class="container">
        <h2 class="text-center mb-4">🔍 Diagnóstico de Modelos (.docx)</h2>
        <div class="alert alert-warning text-center">
            <strong>Instrução:</strong> Baixe um arquivo de cada lado. Abra no Word. <br>
            Se o arquivo da coluna <strong>DEMO</strong> tiver o cabeçalho da sua empresa oficial, <br>
            significa que você fez o upload do arquivo errado na pasta <code>modelos_demo</code> via FTP.
        </div>

        <div class="row">
            <!-- COLUNA PRODUÇÃO -->
            <div class="col-md-6">
                <div class="col-box bg-prod">
                    <h3 class="text-success">📂 Pasta: modelos_prod</h3>
                    <?php if (isset($arquivosProd[0]) && strpos($arquivosProd[0], 'ERRO') !== false): ?>
                        <div class="alert alert-danger"><?= $arquivosProd[0] ?></div>
                    <?php else: ?>
                        <ul class="list-group">
                            <?php foreach ($arquivosProd as $arq): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <?= $arq ?>
                                    <a href="modelos_prod/<?= $arq ?>" class="btn btn-sm btn-success">Baixar</a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                        <?php if(empty($arquivosProd)) echo "<p class='text-muted'>Pasta vazia.</p>"; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- COLUNA DEMO -->
            <div class="col-md-6">
                <div class="col-box bg-demo">
                    <h3 class="text-primary">📂 Pasta: modelos_demo</h3>
                    <?php if (isset($arquivosDemo[0]) && strpos($arquivosDemo[0], 'ERRO') !== false): ?>
                        <div class="alert alert-danger"><?= $arquivosDemo[0] ?></div>
                    <?php else: ?>
                        <ul class="list-group">
                            <?php foreach ($arquivosDemo as $arq): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <?= $arq ?>
                                    <a href="modelos_demo/<?= $arq ?>" class="btn btn-sm btn-primary">Baixar</a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                        <?php if(empty($arquivosDemo)) echo "<p class='text-muted'>Pasta vazia.</p>"; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="text-center mt-5">
            <a href="index.php" class="btn btn-secondary">Voltar ao Sistema</a>
        </div>
    </div>
</body>

</html>