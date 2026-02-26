<?php
require_once 'db.php';
$conn = Database::getProd();

echo "<h1>Diagnóstico de Conteúdo - Drone</h1>";

// Helper function to show block content
function mostrarBloco($conn, $block_id) {
    echo "<h2>Bloco: $block_id</h2>";
    
    // Check service_type_blocks
    $sql1 = "SELECT id, default_content FROM service_type_blocks 
             WHERE block_id = '$block_id' 
             AND service_type_id = (SELECT id_servico FROM Tipo_Servicos WHERE nome = 'Drone' LIMIT 1)";
    $res1 = $conn->query($sql1);
    
    echo "<h3>Tabela: service_type_blocks</h3>";
    if ($res1 && $res1->num_rows > 0) {
        while ($row = $res1->fetch_assoc()) {
            echo "<pre style='background:#eee; padding:10px; border:1px solid #ccc'>" . htmlspecialchars($row['default_content']) . "</pre>";
        }
    } else {
        echo "<p>Nenhum registro encontrado para Drone.</p>";
    }

    // Check proposal_block_templates (generic fallback)
    $sql2 = "SELECT id, default_content FROM proposal_block_templates WHERE block_id = '$block_id'";
    $res2 = $conn->query($sql2);
    
    echo "<h3>Tabela: proposal_block_templates (Genérico)</h3>";
    if ($res2 && $res2->num_rows > 0) {
        while ($row = $res2->fetch_assoc()) {
            echo "<pre style='background:#f9f9db; padding:10px; border:1px solid #ccc'>" . htmlspecialchars($row['default_content']) . "</pre>";
        }
    } else {
        echo "<p>Nenhum registro encontrado.</p>";
    }
}

// 1. Investimento (para verificar se ficou correto)
mostrarBloco($conn, 'investimento');

// 2. Cronograma (onde o usuário diz que o texto aparece errado "Em Observações")
mostrarBloco($conn, 'cronograma');

// 3. Equipamentos (onde o usuário diz que não atualizou)
mostrarBloco($conn, 'equipamentos_previstos');
mostrarBloco($conn, 'equipamentos'); // Tentando outro ID possível

$conn->close();
?>
