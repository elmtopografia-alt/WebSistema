<?php
require_once 'ConnectionManager.php';

$serviceId = 19; // Drone
try {
    $conn = ConnectionManager::get('producao');
    $res = $conn->query("SELECT * FROM service_type_blocks WHERE service_type_id = $serviceId ORDER BY display_order ASC");
    echo "--- Blocks for Service $serviceId (PROD) ---\n";
    while($row = $res->fetch_assoc()) {
        echo "[{$row['block_slug']}] {$row['block_title']} ({$row['category']})\n";
        echo "Required: " . ($row['is_required'] ? 'Yes' : 'No') . "\n";
        echo "Default Content Preview: " . mb_substr($row['default_content'], 0, 100) . "...\n";
        echo "-------------------\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
