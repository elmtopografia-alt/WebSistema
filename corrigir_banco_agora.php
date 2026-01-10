<?php
// Arquivo: corrigir_banco_agora.php
// Função: Padroniza os status bagunçados no banco de dados.

session_start();
require_once 'config.php';
require_once 'db.php';

// Conecta (Se logado como demo, limpa demo. Se prod, limpa prod)
$conn = (isset($_SESSION['ambiente']) && $_SESSION['ambiente'] === 'demo') ? Database::getDemo() : Database::getProd();

echo "<h2>🧹 Iniciando Higienização de Status...</h2>";

// 1. Corrige ENVIADA (Minúsculo -> Maiúsculo)
$sql1 = "UPDATE Propostas SET status = 'Enviada' WHERE status LIKE 'envia%' OR status = 'enviada'";
$conn->query($sql1);
echo "Correção de 'Enviada': " . $conn->affected_rows . " registros alterados.<br>";

// 2. Corrige ELABORANDO (Variações -> Em elaboração)
$sql2 = "UPDATE Propostas SET status = 'Em elaboração' WHERE status LIKE 'elabor%' OR status LIKE 'rascunho%' OR status = 'elaborando'";
$conn->query($sql2);
echo "Correção de 'Em elaboração': " . $conn->affected_rows . " registros alterados.<br>";

// 3. Corrige APROVADA (Variações -> Aprovada)
$sql3 = "UPDATE Propostas SET status = 'Aprovada' WHERE status LIKE 'aprov%' OR status LIKE 'conclu%' OR status LIKE 'aceit%'";
$conn->query($sql3);
echo "Correção de 'Aprovada': " . $conn->affected_rows . " registros alterados.<br>";

// 4. Corrige CANCELADA
$sql4 = "UPDATE Propostas SET status = 'Cancelada' WHERE status LIKE 'cancel%' OR status LIKE 'perdid%'";
$conn->query($sql4);
echo "Correção de 'Cancelada': " . $conn->affected_rows . " registros alterados.<br>";

echo "<hr>";
echo "<h3 style='color:green'>✅ Banco de dados padronizado!</h3>";
echo "<a href='painel.php'><button>VOLTAR AO PAINEL</button></a>";
?>