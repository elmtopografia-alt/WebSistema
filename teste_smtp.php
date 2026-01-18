<?php
// Arquivo: teste_smtp.php
// Objetivo: Testar se as credenciais do config.php estão funcionando

require_once 'config.php';
require_once 'GerenciadorEmail.php';

// Força exibição de erros
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<div style='font-family: Arial, sans-serif; padding: 20px;'>";
echo "<h1>🕵️ Teste de Envio de E-mail (SMTP)</h1>";

echo "<div style='background: #f4f4f4; padding: 15px; border-radius: 5px; margin-bottom: 20px;'>";
echo "<strong>Configuração Carregada:</strong><br>";
echo "Host: " . SMTP_HOST . "<br>";
echo "Porta: " . SMTP_PORT . "<br>";
echo "Usuário: " . SMTP_USER . "<br>";
echo "Remetente: " . SMTP_FROM_EMAIL . "<br>";
echo "</div>";

// Tenta enviar para o próprio e-mail configurado (Admin)
$destinatario = SMTP_USER; 
$assunto = "Teste de Configuração SGT - " . date('d/m/Y H:i:s');
$corpo = "
    <div style='color: #333;'>
        <h2>Teste Bem-Sucedido! ✅</h2>
        <p>Se você está lendo isso, o envio de e-mails pelo SGT está funcionando corretamente.</p>
        <hr>
        <p><small>Enviado em: " . date('d/m/Y H:i:s') . "</small></p>
    </div>
";

echo "Tentando enviar e-mail para <strong>$destinatario</strong>...<br><br>";

if (GerenciadorEmail::enviar($destinatario, 'Admin Teste', $assunto, $corpo)) {
    echo "<h2 style='color: green;'>✅ SUCESSO!</h2>";
    echo "<p>O e-mail foi enviado. Verifique sua caixa de entrada (e spam) de: <strong>$destinatario</strong></p>";
} else {
    echo "<h2 style='color: red;'>❌ ERRO!</h2>";
    echo "<p>Não foi possível enviar o e-mail.</p>";
    echo "<p><strong>Possíveis causas:</strong></p>";
    echo "<ul>";
    echo "<li>Senha incorreta no config.php</li>";
    echo "<li>Bloqueio de segurança na Locaweb (verifique se o SMTP está ativo no painel deles)</li>";
    echo "<li>Porta 465 bloqueada no seu ambiente local (firewall/antivírus)</li>";
    echo "</ul>";
}

echo "</div>";
?>
