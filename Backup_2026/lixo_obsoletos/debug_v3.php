<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require 'config.php';
require 'db.php';
$conn = Database::getProd();
$id_proposta = 177;

echo "<pre>";
$res = $conn->query("SELECT block_id, conteudo_texto FROM Proposta_Conteudo_Personalizado WHERE id_proposta = $id_proposta AND block_id IN ('investimento','condicoes_pagamento')");
while ($row = $res->fetch_assoc()) {
    echo "<h1>BLOCK: " . $row['block_id'] . "</h1>";
    echo htmlspecialchars($row['conteudo_texto']);
    echo "\n--------------------------------\n";
}
echo "</pre>";
?>
