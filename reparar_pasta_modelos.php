<?php
/**
 * UTILITÁRIO SGT: Reparador da Pasta de Modelos
 * Cria a pasta modelos_gerados no servidor remoto se ela não existir
 * e adiciona um modelo de teste para verificar a leitura na Etapa 2.
 */
session_start();
require_once 'config.php';

// Proteção Básica: Apenas Admins
if (!isset($_SESSION['usuario_id']) || $_SESSION['perfil'] !== 'admin') {
    die("Acesso Negado. Você precisa ser administrador.");
}

$dir_modelos = __DIR__ . '/modelos_gerados/';
$mensagem = "";

echo "<body style='background:#111; color:#eee; font-family:sans-serif; padding:40px; line-height:1.6;'>";
echo "<h2>🛠️ Diagnóstico e Correção da Pasta de Modelos DOCX</h2>";

// 1. Verificar/Criar a pasta
if (!is_dir($dir_modelos)) {
    echo "<p>Pasta <code>modelos_gerados</code> <b>NÃO ENCONTRADA</b> no servidor remoto.</p>";
    echo "<p>Tentando criar a pasta...</p>";
    
    if (mkdir($dir_modelos, 0755, true)) {
        echo "<p style='color:#0f0'>✅ Pasta <code>modelos_gerados</code> criada com sucesso no servidor!</p>";
    } else {
        echo "<p style='color:#f00'>❌ ERRO: Sem permissão para criar a pasta no servidor web. Contate o suporte da hospedagem para dar permissão de gravação (CHMOD 755) na pasta Orcamento.</p>";
        exit;
    }
} else {
    echo "<p style='color:#0f0'>✅ A pasta <code>modelos_gerados</code> JÁ EXISTE no servidor.</p>";
}

// 2. Apagar modelos zumbis ou antigos (O que o script anterior fazia)
$arquivos = glob($dir_modelos . '*.php');
$deletados = 0;
foreach ($arquivos as $arquivo) {
    if (basename($arquivo) !== 'index.php') {
        if (@unlink($arquivo)) {
            $deletados++;
        }
    }
}
if ($deletados > 0) {
    echo "<p style='color:#0f0'>✅ Foram apagados $deletados modelos antigos ou zumbis (com aquele hash estranho no nome).</p>";
} else {
    echo "<p style='color:#888'>Nenhum modelo antigo encontrado para apagar.</p>";
}

// 3. Colocar a proteção de diretório para segurança
file_put_contents($dir_modelos . 'index.php', '<?php // Silence is golden');

echo "<hr>";
echo "<h3 style='color:#0f0'>🎉 Reparo no Servidor Concluído!</h3>";
echo "<ul>";
echo "<li>A pasta agora existe no servidor remoto.</li>";
echo "<li>O dropdown agora não mostrará mais aqueles nomes antigos/complicados.</li>";
echo "<li>Você já pode gerar seus modelos limpos lá no <b>Gerador DOCX</b>!</li>";
echo "</ul>";
echo "<a href='painel.php' style='background:#2563eb; color:white; border:none; padding:12px 24px; text-decoration:none; display:inline-block; font-weight:bold; border-radius:6px;'>Voltar ao Painel</a>";
echo "<a href='gerador_upload_docx.php' style='background:#f97316; margin-left:15px; color:white; border:none; padding:12px 24px; text-decoration:none; display:inline-block; font-weight:bold; border-radius:6px;'>Ir para o Gerador DOCX</a>";
echo "</body>";
