<?php
require_once 'config.php';
require_once 'db.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 98;
$conn = Database::getProd();

$res = $conn->query("SELECT * FROM Propostas WHERE id = $id");
$proposta = $res->fetch_assoc();

echo "<h1>Dados Brutos da Proposta $id</h1>";
echo "<pre>";
print_r($proposta);
echo "</pre>";

echo "<h2>Conteúdo JSON Decodificado</h2>";
$json = json_decode($proposta['conteudo_json'] ?? '', true);
echo "<pre>";
print_r($json);
echo "</pre>";
?>
