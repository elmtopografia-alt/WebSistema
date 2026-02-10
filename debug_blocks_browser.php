<?php
// debug_blocks_browser.php
require_once 'config.php';
require_once 'db.php';

echo "<h2>Debug: Conteúdo dos Blocos de Equipamentos</h2>";
echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
echo "<tr><th>ID</th><th>Slug</th><th>Conteúdo (HTML cru)</th></tr>";

try {
    $conn = Database::getProd();
    $res = $conn->query("SELECT id, block_slug, default_content FROM service_type_blocks WHERE block_slug LIKE 'equipamentos%'");
    
    while ($row = $res->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$row['id']}</td>";
        echo "<td>{$row['block_slug']}</td>";
        echo "<td><pre>" . htmlspecialchars($row['default_content']) . "</pre></td>";
        echo "</tr>";
    }
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}

echo "</table>";
?>
