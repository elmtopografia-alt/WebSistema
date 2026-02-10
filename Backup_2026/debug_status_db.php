<?php
// debug_status_db.php
session_start();
require_once 'config.php';
require_once 'db.php';

header('Content-Type: text/html; charset=utf-8');

if (!isset($_SESSION['usuario_id'])) {
    die("Por favor, faça login primeiro.");
}

$id_usuario = $_SESSION['usuario_id'];
$conn = (isset($_SESSION['ambiente']) && $_SESSION['ambiente'] === 'demo') ? Database::getDemo() : Database::getProd();

echo "<h1>Diagnóstico de Status no Banco de Dados</h1>";
echo "<p>Usuário ID: $id_usuario</p>";
echo "<p>Ambiente: " . ($_SESSION['ambiente'] ?? 'producao') . "</p>";

$sql = "SELECT id_proposta, numero_proposta, status, LENGTH(status) as len, HEX(status) as hex_val FROM Propostas WHERE id_criador = ? ORDER BY id_proposta DESC LIMIT 20";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $id_usuario);
$stmt->execute();
$res = $stmt->get_result();

echo "<table border='1' cellpadding='5'>";
echo "<tr><th>ID</th><th>Número</th><th>Status (Visual)</th><th>Tamanho</th><th>HEX (Debug)</th></tr>";

while ($row = $res->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . $row['id_proposta'] . "</td>";
    echo "<td>" . $row['numero_proposta'] . "</td>";
    echo "<td>[" . htmlspecialchars($row['status']) . "]</td>";
    echo "<td>" . $row['len'] . "</td>";
    echo "<td>" . $row['hex_val'] . "</td>";
    echo "</tr>";
}
echo "</table>";
?>
