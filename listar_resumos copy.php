<?php
// Nome da página: listar_resumos.php
// VERSÃO FINAL - SINCRONIZADA

session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

// O caminho agora é relativo à estrutura SaaS, precisa encontrar a pasta do usuário
// Esta lógica precisa ser ajustada quando a estrutura multi-tenant for implementada
// Por enquanto, manteremos a lógica antiga para o sistema de usuário único.
$caminho_resumos = dirname(__DIR__) . '/resumos_propostas/';

if (isset($_GET['arquivo'])) {
    $nome_arquivo = basename($_GET['arquivo']);
    if (strpos($nome_arquivo, '..') !== false || strpos($nome_arquivo, '/') !== false) { die('Acesso negado.'); }
    $caminho_completo = $caminho_resumos . $nome_arquivo;

    if (file_exists($caminho_completo)) {
        $acao = isset($_GET['acao']) ? $_GET['acao'] : 'baixar';
        if ($acao === 'visualizar') {
            header('Content-Type: text/plain; charset=utf-8');
            header('Content-Disposition: inline; filename="' . $nome_arquivo . '"');
        } else {
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . $nome_arquivo . '"');
        }
        header('Content-Length: ' . filesize($caminho_completo));
        readfile($caminho_completo);
        exit;
    } else {
        $_SESSION['mensagem_erro'] = "Arquivo não encontrado.";
        header("Location: listar_resumos.php");
        exit();
    }
}

$lista_arquivos = [];
if (is_dir($caminho_resumos)) {
    $arquivos = scandir($caminho_resumos, SCANDIR_SORT_DESCENDING);
    foreach ($arquivos as $arquivo) {
        if ($arquivo !== '.' && $arquivo !== '..' && pathinfo($arquivo, PATHINFO_EXTENSION) === 'txt') {
            $lista_arquivos[] = $arquivo;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Resumos de Propostas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4 mx-auto" style="max-width: 75%;">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Resumos Financeiros (.txt)</h3>
        <a href="index.php" class="btn btn-secondary">Voltar ao Painel</a>
    </div>

    <?php if (isset($_SESSION['mensagem_erro'])): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <?= $_SESSION['mensagem_erro'] ?><?php unset($_SESSION['mensagem_erro']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <?php if (!empty($lista_arquivos)): ?>
                <div class="list-group">
                    <?php foreach ($lista_arquivos as $arquivo): ?>
                        <div class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <span class="fw-bold text-dark">📄 <?= htmlspecialchars($arquivo) ?></span>
                            <div class="d-flex gap-2">
                                <a href="?arquivo=<?= urlencode($arquivo) ?>&acao=visualizar" target="_blank" class="btn btn-sm btn-outline-primary">👁️ Ler na Tela</a>
                                <a href="?arquivo=<?= urlencode($arquivo) ?>" class="btn btn-sm btn-success">⬇️ Baixar</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="text-center text-muted py-4">Nenhum resumo encontrado.</p>
            <?php endif; ?>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
*Fim arquivo listar_resumos.php*