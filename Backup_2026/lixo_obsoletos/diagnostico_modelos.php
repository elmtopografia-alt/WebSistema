<?php
require_once 'ConnectionManager.php';

function checkEnv($env) {
    echo "--- Checking Environment: $env ---\n";
    try {
        $conn = ConnectionManager::get($env);
        
        // 1. Check Tipo_Servicos
        $res = $conn->query("SELECT id_servico, nome FROM Tipo_Servicos");
        echo "Tipo_Servicos:\n";
        while($row = $res->fetch_assoc()) {
            echo "  ID: {$row['id_servico']} - Nome: {$row['nome']}\n";
        }

        // 2. Check service_type_blocks
        $res = $conn->query("SHOW TABLES LIKE 'service_type_blocks'");
        if ($res->num_rows > 0) {
            $res = $conn->query("SELECT service_type_id, COUNT(*) as total FROM service_type_blocks GROUP BY service_type_id");
            echo "service_type_blocks counts per service:\n";
            while($row = $res->fetch_assoc()) {
                echo "  ServiceID: {$row['service_type_id']} - Block Count: {$row['total']}\n";
            }
        } else {
            echo "Table 'service_type_blocks' DOES NOT EXIST.\n";
        }

        // 3. Check proposal_block_templates
        $res = $conn->query("SHOW TABLES LIKE 'proposal_block_templates'");
        if ($res->num_rows > 0) {
            $res = $conn->query("SELECT COUNT(*) as total FROM proposal_block_templates");
            $row = $res->fetch_assoc();
            echo "proposal_block_templates: Total {$row['total']} generic blocks.\n";
        } else {
            echo "Table 'proposal_block_templates' DOES NOT EXIST.\n";
        }

    } catch (Exception $e) {
        echo "Error in $env: " . $e->getMessage() . "\n";
    }
    echo "\n";
}

checkEnv('producao');
checkEnv('demo');
