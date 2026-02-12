<?php
/**
 * Script de Verificação de Extensões PHP
 * Sistema: SGT Proposta
 * Verifica se todas as extensões necessárias estão instaladas
 */

$extensoes_obrigatorias = [
    'mysqli'   => 'Conexão com banco de dados MySQL',
    'json'     => 'Encode/decode de dados JSON',
    'session'  => 'Gerenciamento de sessões',
    'mbstring' => 'Strings multibyte (UTF-8)',
    'iconv'    => 'Conversão de caracteres (acentos)',
    'zip'      => 'Geração de arquivos DOCX (PHPWord)',
    'openssl'  => 'Envio de e-mails SMTP com SSL'
];

$extensoes_recomendadas = [
    'fileinfo' => 'Detecção de tipo MIME em uploads',
    'gd'       => 'Manipulação de imagens (PHPWord)',
    'xml'      => 'Processamento XML interno do DOCX',
    'dom'      => 'Manipulação de documentos XML',
    'intl'     => 'Formatação de datas/moedas BR'
];

$php_minimo = '7.4.0';

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Verificação de Extensões - SGT Proposta</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body { background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%); min-height: 100vh; }
        .card { border: none; box-shadow: 0 10px 40px rgba(0,0,0,.3); }
        .status-ok { color: #28a745; }
        .status-fail { color: #dc3545; }
        .status-warn { color: #ffc107; }
    </style>
</head>
<body class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header bg-dark text-white text-center py-4">
                        <h3><i class="bi bi-gear-wide-connected me-2"></i>Verificação de Ambiente PHP</h3>
                        <small class="text-muted">Sistema SGT Proposta</small>
                    </div>
                    <div class="card-body p-4">
                        
                        <!-- Versão PHP -->
                        <div class="alert <?= version_compare(PHP_VERSION, $php_minimo, '>=') ? 'alert-success' : 'alert-danger' ?> d-flex align-items-center">
                            <i class="bi <?= version_compare(PHP_VERSION, $php_minimo, '>=') ? 'bi-check-circle-fill' : 'bi-x-circle-fill' ?> fs-4 me-3"></i>
                            <div>
                                <strong>PHP <?= PHP_VERSION ?></strong><br>
                                <small>Mínimo necessário: <?= $php_minimo ?></small>
                            </div>
                        </div>

                        <!-- Extensões Obrigatórias -->
                        <h5 class="mt-4 mb-3"><i class="bi bi-exclamation-diamond-fill text-danger me-2"></i>Extensões Obrigatórias</h5>
                        <table class="table table-bordered">
                            <thead class="table-dark">
                                <tr>
                                    <th>Extensão</th>
                                    <th>Função</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($extensoes_obrigatorias as $ext => $desc): ?>
                                <tr>
                                    <td><code><?= $ext ?></code></td>
                                    <td><?= $desc ?></td>
                                    <td class="text-center">
                                        <?php if (extension_loaded($ext)): ?>
                                            <span class="badge bg-success"><i class="bi bi-check-lg"></i> Instalada</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger"><i class="bi bi-x-lg"></i> AUSENTE</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>

                        <!-- Extensões Recomendadas -->
                        <h5 class="mt-4 mb-3"><i class="bi bi-info-circle-fill text-warning me-2"></i>Extensões Recomendadas</h5>
                        <table class="table table-bordered">
                            <thead class="table-secondary">
                                <tr>
                                    <th>Extensão</th>
                                    <th>Função</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($extensoes_recomendadas as $ext => $desc): ?>
                                <tr>
                                    <td><code><?= $ext ?></code></td>
                                    <td><?= $desc ?></td>
                                    <td class="text-center">
                                        <?php if (extension_loaded($ext)): ?>
                                            <span class="badge bg-success"><i class="bi bi-check-lg"></i> Instalada</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning text-dark"><i class="bi bi-dash-lg"></i> Ausente</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>

                        <!-- Resumo -->
                        <?php
                        $faltando_obrigatorias = [];
                        foreach ($extensoes_obrigatorias as $ext => $desc) {
                            if (!extension_loaded($ext)) $faltando_obrigatorias[] = $ext;
                        }
                        ?>

                        <?php if (empty($faltando_obrigatorias)): ?>
                            <div class="alert alert-success mt-4">
                                <i class="bi bi-patch-check-fill me-2 fs-5"></i>
                                <strong>Ambiente OK!</strong> Todas as extensões obrigatórias estão instaladas. O sistema está pronto para funcionar.
                            </div>
                        <?php else: ?>
                            <div class="alert alert-danger mt-4">
                                <i class="bi bi-shield-x me-2 fs-5"></i>
                                <strong>Atenção!</strong> As seguintes extensões estão faltando: 
                                <code><?= implode(', ', $faltando_obrigatorias) ?></code>
                                <hr>
                                <strong>Como instalar:</strong><br>
                                <small>
                                    <strong>Windows (XAMPP):</strong> Edite <code>php.ini</code> e remova o <code>;</code> das linhas <code>extension=nome</code><br>
                                    <strong>Linux:</strong> <code>sudo apt install php-<?= implode(' php-', $faltando_obrigatorias) ?></code>
                                </small>
                            </div>
                        <?php endif; ?>

                        <div class="text-center mt-4">
                            <a href="index.php" class="btn btn-primary">
                                <i class="bi bi-house me-2"></i>Voltar ao Sistema
                            </a>
                            <button onclick="location.reload()" class="btn btn-outline-secondary ms-2">
                                <i class="bi bi-arrow-clockwise me-2"></i>Verificar Novamente
                            </button>
                        </div>

                    </div>
                    <div class="card-footer text-muted text-center small">
                        Verificação executada em <?= date('d/m/Y H:i:s') ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
