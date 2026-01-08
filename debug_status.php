<?php
require_once 'config.php';
require_once 'db.php';
session_start();

$id_usuario = $_SESSION['usuario_id'] ?? 1;
$conn = Database::getProd();

echo "<h1>Debug Status DB</h1>";

// 1. Check Distinct Statuses
$sql = "SELECT status, COUNT(*) as qtd, SUM(valor_final_proposta) as total FROM Propostas WHERE id_criador = ? GROUP BY status";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $id_usuario);
$stmt->execute();
$res = $stmt->get_result();

echo "<table border='1'><tr><th>Status (Raw)</th><th>Qtd</th><th>Total</th></tr>";
while($row = $res->fetch_assoc()){
    echo "<tr>";
    echo "<td>'" . $row['status'] . "'</td>";
    echo "<td>" . $row['qtd'] . "</td>";
    echo "<td>" . $row['total'] . "</td>";
    echo "</tr>";
}
echo "</table>";
?>
