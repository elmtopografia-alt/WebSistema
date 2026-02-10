<?php
/**
 * debug_session.php
 * Arquivo temporário para debug - APAGAR DEPOIS
 */

session_start();

echo "<h2>Debug de Sessão</h2>";
echo "<pre>";
echo "SESSION:\n";
print_r($_SESSION);
echo "\n\nVARIÁVEIS RELEVANTES:\n";
echo "usuario_id: " . ($_SESSION['usuario_id'] ?? 'NÃO EXISTE') . "\n";
echo "ambiente: " . ($_SESSION['ambiente'] ?? 'NÃO EXISTE') . "\n";
echo "perfil: " . ($_SESSION['perfil'] ?? 'NÃO EXISTE') . "\n";
echo "</pre>";

echo "<br><br>";
echo "<a href='index.php'>Ir para index.php</a> | ";
echo "<a href='Cli_Pro.php'>Ir para Cli_Pro.php</a> | ";
echo "<a href='Cli_demo.php'>Ir para Cli_demo.php</a> | ";
echo "<a href='painel.php'>Ir para painel.php</a>";
?>
