<?php
/**
 * UTILITÁRIO SGT: Limpeza de Modelos DOCX Remotos
 * Como os arquivos deletados no ambiente local não são automaticamente
 * removidos do servidor remoto via SFTP, este script permite deletar
 * todos os modelos gerados antigios diretamente no servidor web.
 */
session_start();
require_once 'config.php';

// Proteção Básica: Apenas Admins
if (!isset($_SESSION['usuario_id']) || $_SESSION['perfil'] !== 'admin') {
    die("Acesso Negado. Você precisa ser administrador.");
}

$dir_modelos = __DIR__ . '/modelos_gerados/';
$mensagem = "";

if (isset($_POST['limpar'])) {
    if (is_dir($dir_modelos)) {
        $arquivos = glob($dir_modelos . '*.php');
        $deletados = 0;
        
        foreach ($arquivos as $arquivo) {
            // Não apaga o index.php (se existir para segurança de diretório)
            if (basename($arquivo) !== 'index.php') {
                if (@unlink($arquivo)) {
                    $deletados++;
                }
            }
        }
        $mensagem = "<div style='color:#0f0; margin-bottom: 20px;'>✅ Sucesso! $deletados modelos foram excluídos do servidor.</div>";
    } else {
        $mensagem = "<div style='color:#f00; margin-bottom: 20px;'>❌ O diretório de modelos gerados não existe.</div>";
    }
}

// Lista os modelos atuais para visualização
$modelos_atuais = [];
if (is_dir($dir_modelos)) {
    $arquivos = glob($dir_modelos . '*.php');
    foreach ($arquivos as $arquivo) {
        if (basename($arquivo) !== 'index.php') {
            $modelos_atuais[] = basename($arquivo);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>SGT - Limpeza de Modelos DOCX</title>
    <style>
        body { background:#111; color:#eee; font-family:sans-serif; padding:40px; line-height: 1.6; }
        .container { max-width: 600px; margin: 0 auto; background: #1f1f1f; padding: 30px; border-radius: 8px; border: 1px solid #333; }
        .btn { background: #dc2626; color: white; border: none; padding: 12px 24px; font-size: 16px; border-radius: 6px; cursor: pointer; font-weight: bold; width: 100%; transition: background 0.3s; }
        .btn:hover { background: #b91c1c; }
        .btn-voltar { background: #2563eb; display: inline-block; margin-top: 20px; text-decoration: none; text-align: center; }
        .btn-voltar:hover { background: #1d4ed8; }
        .lista-modelos { background: #000; padding: 15px; border-radius: 6px; font-family: monospace; max-height: 250px; overflow-y: auto; margin-bottom: 25px; border: 1px solid #333; }
        .lista-modelos li { color: #aaa; margin-bottom: 5px; list-style-type: none; }
        ul { padding-left: 0; margin: 0; }
        hr { border: 0; border-top: 1px solid #333; margin: 25px 0; }
    </style>
</head>
<body>
    <div class="container">
        <h2>🧹 Gerenciador de Modelos DOCX (Servidor)</h2>
        <p>Quando você apaga um modelo <code>.php</code> na sua pasta <code>modelos_gerados</code> localmente, o seu FTP não apaga automaticamente a cópia que já subiu para o site.</p>
        <p>Por isso os modelos velhos continuam aparecendo na Etapa 2.</p>
        
        <?= $mensagem ?>
        
        <h3>Modelos Atualmente no Servidor:</h3>
        <div class="lista-modelos">
            <?php if (empty($modelos_atuais)): ?>
                <i>Nenhum modelo encontrado no servidor.</i>
            <?php else: ?>
                <ul>
                    <?php foreach ($modelos_atuais as $m): ?>
                        <li>📄 <?= htmlspecialchars($m) ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>

        <?php if (!empty($modelos_atuais)): ?>
        <form method="post" onsubmit="return confirm('Tem certeza que deseja APAGAR TODOS os modelos listados acima no servidor remoto? Você terá que gerar o(s) modelo(s) certo(s) novamente através do Gerador.');">
            <button type="submit" name="limpar" class="btn">⚠️ Apagar Todos os Modelos Velhos do Servidor ⚠️</button>
        </form>
        <?php endif; ?>
        
        <hr>
        <a href="painel.php" class="btn btn-voltar">Voltar ao Painel</a>
        <a href="criar_proposta.php" class="btn btn-voltar" style="background:var(--brand);margin-left:10px;">Ir para Criar Proposta</a>
    </div>
</body>
</html>
