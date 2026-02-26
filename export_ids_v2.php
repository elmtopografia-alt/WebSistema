<?php
require_once __DIR__ . '/ConnectionManager.php';
$conn = ConnectionManager::get();
$out = "";

$out .= "=== TIPOS DE LOCACAO ===\n";
$res = $conn->query("SELECT id_locacao, nome FROM Tipo_Locacao");
while($row = $res->fetch_assoc()) $out .= "ID: {$row['id_locacao']} - Nome: {$row['nome']}\n";

$out .= "\n=== MARCAS ===\n";
$res = $conn->query("SELECT id_marca, id_locacao, nome_marca FROM Marcas");
while($row = $res->fetch_assoc()) $out .= "ID: {$row['id_marca']} - Tipo Ref: {$row['id_locacao']} - Marca: {$row['nome_marca']}\n";

file_put_contents(__DIR__ . '/db_dump_ids.txt', $out);
echo "Dados salvos em db_dump_ids.txt";
?>
