<?php
require_once 'config.php';
require_once 'db.php';
session_start();

$id_usuario = $_SESSION['usuario_id'] ?? 1; // Default to 1 if no session for testing
$conn = Database::getProd();

echo "<h1>Debug KPI Breakdown</h1>";
echo "User ID: $id_usuario<br><hr>";

// 1. Total All
$sqlAll = "SELECT 
    COUNT(*) as qtd, 
    SUM(valor_final_proposta) as total_valor 
    FROM Propostas WHERE id_criador = $id_usuario";
$rowAll = $conn->query($sqlAll)->fetch_assoc();

echo "<h3>TOTAL GERAL (All Statuses)</h3>";
echo "Qtd: " . $rowAll['qtd'] . "<br>";
echo "Valor: " . number_format($rowAll['total_valor'], 2, ',', '.') . "<br><hr>";

// 2. Breakdown by Status
$sqlGroup = "SELECT status, COUNT(*) as qtd, SUM(valor_final_proposta) as total_valor 
             FROM Propostas 
             WHERE id_criador = $id_usuario 
             GROUP BY status";
$res = $conn->query($sqlGroup);

echo "<h3>POR STATUS</h3>";
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Status</th><th>Qtd</th><th>Valor Total</th></tr>";

$somaAprovada = 0;
$qtdAprovada = 0;

while($row = $res->fetch_assoc()) {
    echo "<tr>";
    echo "<td>{$row['status']}</td>";
    echo "<td>{$row['qtd']}</td>";
    echo "<td>" . number_format($row['total_valor'], 2, ',', '.') . "</td>";
    echo "</tr>";
    
    if (stripos($row['status'], 'aprov') !== false) {
        $somaAprovada += $row['total_valor'];
        $qtdAprovada += $row['qtd'];
    }
}
echo "</table>";

echo "<hr><h3>Recalculo 'Aprovadas' (Calculated PHP)</h3>";
echo "Qtd Aprovadas: $qtdAprovada<br>";
echo "Valor Aprovadas: " . number_format($somaAprovada, 2, ',', '.') . "<br>";

?>
