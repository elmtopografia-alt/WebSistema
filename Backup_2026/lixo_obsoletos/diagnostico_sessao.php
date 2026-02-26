<?php
/**
 * Script de Diagnóstico de Sessão (Temporário)
 * Use para verificar por que o acesso está sendo negado.
 */
session_start();

echo "<h1>Diagnóstico de Sessão SGT</h1>";
echo "<ul>";
echo "<li><strong>ID Usuário:</strong> " . ($_SESSION['usuario_id'] ?? 'Não definido') . "</li>";
echo "<li><strong>Nome:</strong> " . ($_SESSION['usuario_nome'] ?? 'Não definido') . "</li>";
echo "<li><strong>Perfil Atual:</strong> <span style='color: " . (($_SESSION['perfil'] ?? '') === 'admin' ? 'green' : 'red') . "'>" . ($_SESSION['perfil'] ?? 'Não definido') . "</span></li>";
echo "<li><strong>Ambiente:</strong> " . ($_SESSION['ambiente'] ?? 'Não definido') . "</li>";
echo "<li><strong>Origem Login:</strong> " . ($_SESSION['origem_login'] ?? 'Não definido') . "</li>";
echo "</ul>";

if (($_SESSION['perfil'] ?? '') !== 'admin') {
    echo "<p style='color: red;'>⚠️ Seu perfil não é 'admin'. O gerador DOCX exige perfil administrativo.</p>";
} else {
    echo "<p style='color: green;'>✅ Seu perfil é 'admin'. Você deveria ter acesso.</p>";
}

echo "<hr>";
echo "<a href='gerador_upload_docx.php'>Tentar acessar o Gerador</a> | ";
echo "<a href='painel.php'>Voltar ao Painel</a>";
