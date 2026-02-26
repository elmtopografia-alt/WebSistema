<?php
require_once __DIR__ . '/ConnectionManager.php';
$conn = ConnectionManager::get();

ob_start();
echo "--- TIPO_LOCACAO ---\n";
$res = $conn->query("SELECT * FROM Tipo_Locacao");
while($row = $res->fetch_assoc()) {
    echo "ID: {$row['id_locacao']} | Nome: '{$row['nome']}'\n";
}

echo "\n--- MARCAS ---\n";
$res = $conn->query("SELECT m.id_marca, m.id_locacao, m.nome_marca, tl.nome as tipo 
                     FROM Marcas m 
                     JOIN Tipo_Locacao tl ON m.id_locacao = tl.id_locacao 
                     LIMIT 50");
while($row = $res->fetch_assoc()) {
    echo "ID: {$row['id_marca']} | Tipo: '{$row['tipo']}' | Marca: '{$row['nome_marca']}'\n";
}
$output = ob_get_clean();
file_put_contents(__DIR__ . '/debug_output.txt', $output);
echo "Dados salvos em debug_output.txt";
?>
