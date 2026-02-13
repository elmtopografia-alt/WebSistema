<?php
require_once 'db.php';
$conn = Database::getProd();
$res = $conn->query("SELECT id_servico, nome FROM Tipo_Servicos");
while($row = $res->fetch_assoc()) {
    echo $row['id_servico'] . " - " . $row['nome'] . "\n";
}
?>
