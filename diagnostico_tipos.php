<?php
require_once __DIR__ . '/ConnectionManager.php';
$conn = ConnectionManager::get();

$out = "=== TIPOS DE LOCACAO ===\n";
$res = $conn->query("SELECT id_locacao, nome FROM Tipo_Locacao");
while($row = $res->fetch_assoc()) $out .= "ID: {$row['id_locacao']} - Nome: {$row['nome']}\n";

$out .= "\n=== MARCAS (AMOSTRA) ===\n";
$res = $conn->query("SELECT id_marca, id_locacao, nome_marca FROM Marcas LIMIT 20");
while($row = $res->fetch_assoc()) $out .= "ID: {$row['id_marca']} - Tipo: {$row['id_locacao']} - Nome: {$row['nome_marca']}\n";

file_put_contents(__DIR__ . '/diagnostico_tipos.txt', $out);
echo "Dados salvos em diagnostico_tipos.txt";
?>
