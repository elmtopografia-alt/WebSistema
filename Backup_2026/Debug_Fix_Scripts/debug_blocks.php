<?php
require_once 'db.php';
$conn = Database::getProd();
$res = $conn->query("SELECT id, block_slug, default_content FROM service_type_blocks WHERE block_slug LIKE 'equipamentos%'");
while ($row = $res->fetch_assoc()) {
    echo "ID: " . $row['id'] . " | Slug: " . $row['block_slug'] . "\n";
    echo "Content: " . strip_tags($row['default_content']) . "\n";
    echo "--------------------------------------------------\n";
}
?>
