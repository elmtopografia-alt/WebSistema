<?php
// ARQUIVO: check_drone_service.php
require_once 'config.php';
require_once 'db.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

try {
    $conn = Database::getProd();
    
    // 1. Find Service ID
    echo "=== BUSCANDO SERVIÇO DE DRONE ===\n";
    $sql = "SELECT * FROM Tipo_Servicos WHERE nome LIKE '%Drone%' OR nome LIKE '%Aero%' OR nome LIKE '%VANT%'";
    $res = $conn->query($sql);
    
    $droneServiceId = null;
    if ($res->num_rows > 0) {
        while ($row = $res->fetch_assoc()) {
            echo "ID: " . $row['id_servico'] . " | Nome: " . $row['nome'] . "\n";
            if (stripos($row['nome'], 'Drone') !== false || stripos($row['nome'], 'Aero') !== false) {
                $droneServiceId = $row['id_servico'];
            }
        }
    } else {
        echo "Nenhum serviço encontrado com termos Drone/Aero/VANT.\n";
    }
    
    if ($droneServiceId) {
        echo "\n=== CONTEÚDO ATUAL (Variations) para Serviço ID $droneServiceId ===\n";
        $sqlVar = "SELECT v.id_variation, b.block_name, v.content_text 
                   FROM proposal_content_variations v
                   JOIN proposal_block_templates b ON v.block_id = b.id_block
                   WHERE v.service_ids LIKE '%\"$droneServiceId\"%' OR v.service_ids LIKE '%[$droneServiceId]%' OR v.service_ids = '$droneServiceId'";
        
        // Note: service_ids logic relies on how it's stored. If JSON array, simple string match might fail if strict.
        // Let's list ALL variations and filter in PHP used logic if needed, or simple JOIN if table structure is normalized (it's likely not based on 'service_ids LIKE').
        // Let's try simple query first assuming variations link via `service_id` column or `service_ids` JSON
        
        // Checking table structure first for variations
        $cols = $conn->query("SHOW COLUMNS FROM proposal_content_variations");
        $hasServiceIds = false;
        while($c = $cols->fetch_assoc()) {
             if($c['Field'] == 'service_ids') $hasServiceIds = true;
        }
        
        if($hasServiceIds) {
             $sqlVar = "SELECT v.id_variation, b.block_name, v.title, v.content_text 
                   FROM proposal_content_variations v
                   LEFT JOIN proposal_block_templates b ON v.block_id = b.id_block
                   WHERE JSON_CONTAINS(v.service_ids, '\"$droneServiceId\"')";
             // Fallback for non-JSON or string matching
             try {
                $resVar = $conn->query($sqlVar); 
             } catch(Exception $e) {
                 $sqlVar = "SELECT v.id_variation, b.block_name, v.title, v.content_text 
                   FROM proposal_content_variations v
                   LEFT JOIN proposal_block_templates b ON v.block_id = b.id_block
                   WHERE v.service_ids LIKE '%\"$droneServiceId\"%'";
                 $resVar = $conn->query($sqlVar);
             }
        } else {
            // Maybe it uses a mapping table? OR single `id_servico`
             $sqlVar = "SELECT v.id_variation, b.block_name, v.title, v.content_text 
                   FROM proposal_content_variations v
                   LEFT JOIN proposal_block_templates b ON v.block_id = b.id_block
                   WHERE v.id_servico = $droneServiceId"; // Guessing
             $resVar = $conn->query($sqlVar);
        }

        if ($resVar && $resVar->num_rows > 0) {
            while ($row = $resVar->fetch_assoc()) {
                echo "Block: " . str_pad($row['block_name'], 15) . " | Title: " . $row['title'] . " | Content Legth: " . strlen($row['content_text']) . "\n";
            }
        } else {
            echo "Nenhum conteúdo específico encontrado para este serviço.\n";
        }
    }

} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}
?>
