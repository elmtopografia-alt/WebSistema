<?php
require_once __DIR__ . '/ConnectionManager.php';
$conn = ConnectionManager::get();

echo "=== TIPOS DE LOCACAO ===\n";
$res = $conn->query("SELECT id_locacao, nome FROM Tipo_Locacao LIMIT 5");
while($row = $res->fetch_assoc()) echo "ID: {$row['id_locacao']} - Nome: {$row['nome']}\n";

echo "\n=== MARCAS ===\n";
$res = $conn->query("SELECT id_marca, id_locacao, nome_marca FROM Marcas LIMIT 10");
while($row = $res->fetch_assoc()) echo "ID: {$row['id_marca']} - Tipo Ref: {$row['id_locacao']} - Marca: {$row['nome_marca']}\n";
?>
