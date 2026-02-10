<?php
require_once 'config.php';
require_once 'db.php';

echo "<h1>Debug Propostas Table</h1>";

$conn = Database::getProd();

// 1. Check Column Type
echo "<h2>Table Structure</h2>";
$res = $conn->query("DESCRIBE Propostas");
if($res) {
    echo "<table border=1><tr><th>Field</th><th>Type</th><th>Default</th></tr>";
    while($row = $res->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$row['Field']}</td>";
        echo "<td>{$row['Type']}</td>";
        echo "<td>{$row['Default']}</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "Error describing table: " . $conn->error;
}

// 2. Dump recent data_criacao
echo "<h2>Recent Data</h2>";
$res = $conn->query("SELECT id_proposta, data_criacao FROM Propostas ORDER BY id_proposta DESC LIMIT 10");
if($res) {
    echo "<table border=1><tr><th>ID</th><th>data_criacao (Raw)</th></tr>";
    while($row = $res->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$row['id_proposta']}</td>";
        echo "<td>'" . $row['data_criacao'] . "'</td>";
        echo "</tr>";
    }
    echo "</table>";
}

// 3. Dump data sorted by data_criacao DESC
echo "<h2>Sorted by data_criacao DESC</h2>";
$res = $conn->query("SELECT id_proposta, data_criacao FROM Propostas ORDER BY data_criacao DESC LIMIT 10");
if($res) {
    echo "<table border=1><tr><th>ID</th><th>data_criacao (Raw)</th></tr>";
    while($row = $res->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$row['id_proposta']}</td>";
        echo "<td>'" . $row['data_criacao'] . "'</td>";
        echo "</tr>";
    }
    echo "</table>";
}
?>
