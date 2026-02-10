<?php
require_once 'db.php';

$conn = Database::getProd();
$result = $conn->query("SELECT * FROM proposal_block_templates WHERE is_active = 1 ORDER BY id ASC");

echo "<h1>Blocos no Banco de Dados</h1>";
echo "<table border='1'><tr><th>ID</th><th>Slug</th><th>Name</th><th>Category</th><th>JSON</th></tr>";
while ($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>{$row['id']}</td>";
    echo "<td>{$row['slug']}</td>";
    echo "<td>{$row['name']}</td>";
    echo "<td>{$row['category']}</td>";
    echo "<td>" . htmlspecialchars($row['default_content_json']) . "</td>";
    echo "</tr>";
}
echo "</table>";
