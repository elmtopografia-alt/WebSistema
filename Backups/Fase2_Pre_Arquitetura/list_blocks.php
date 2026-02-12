<?php
// ARQUIVO: list_blocks.php
require_once 'config.php';
require_once 'db.php';
$conn = Database::getProd();
$res = $conn->query("SELECT slug, name FROM proposal_block_templates ORDER BY `order`");
while($row = $res->fetch_assoc()) {
    echo $row['slug'] . " - " . $row['name'] . "\n";
}
?>
