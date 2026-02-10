<?php
// debug_locacao.php
require_once 'db.php';
$conn = Database::getProd();

$id_proposta = isset($_GET['id']) ? intval($_GET['id']) : 0;

echo "<h2>Debug Locação</h2>";
echo "<h3>Conteúdo de Proposta_Locacao para ID: $id_proposta (ou últimas 5 se 0)</h3>";

$sql = "SELECT pl.*, tl.nome as nome_equipamento, m.nome_marca 
        FROM Proposta_Locacao pl 
        LEFT JOIN Tipo_Locacao tl ON pl.id_locacao = tl.id_locacao 
        LEFT JOIN Marcas m ON pl.id_marca = m.id_marca";

if ($id_proposta) {
    $sql .= " WHERE pl.id_proposta = $id_proposta";
} else {
    $sql .= " ORDER BY pl.id_proposta_locacao DESC LIMIT 20";
}

$res = $conn->query($sql);

echo "<table border='1'><tr><th>ID Prop</th><th>Equipamento</th><th>Marca (ID)</th><th>Nome Marca</th></tr>";
while ($row = $res->fetch_assoc()) {
    echo "<tr>";
    echo "<td>{$row['id_proposta']}</td>";
    echo "<td>{$row['nome_equipamento']}</td>";
    echo "<td>{$row['id_marca']}</td>";
    echo "<td>{$row['nome_marca']}</td>";
    echo "</tr>";
}
echo "</table>";
?>
