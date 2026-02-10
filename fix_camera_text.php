<?php
// fix_camera_text.php
require_once 'config.php';
require_once 'db.php';

echo "<h2>Correção de Texto - Equipamentos</h2>";

try {
    $conn = Database::getProd();
    
    // Procura blocos de 'equipamentos' que contenham 'Câmera Fotográfica'
    $sql = "SELECT id, default_content FROM service_type_blocks WHERE block_slug LIKE 'equipamentos%' AND default_content LIKE '%Câmera Fotográfica%'";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $id = $row['id'];
            $content = $row['default_content'];
            
            // Remove a linha da câmera
            // Tenta remover <li><strong>Câmera Fotográfica:</strong> ...</li>
            $newContent = preg_replace('/<li[^>]*>\s*<strong[^>]*>Câmera Fotográfica:<\/strong>[^<]*<\/li>/i', '', $content);
            
            // Se falhar o regex complexo, tenta string simples
            if ($newContent === $content) {
                 $newContent = str_replace('Câmera Fotográfica', '', $content);
            }
            
            // Atualiza
            $stmt = $conn->prepare("UPDATE service_type_blocks SET default_content = ? WHERE id = ?");
            $stmt->bind_param("si", $newContent, $id);
            
            if ($stmt->execute()) {
                echo "<div style='color:green'>Câmera removida do bloco ID {$id}.</div>";
            } else {
                 echo "<div style='color:red'>Erro ao atualizar ID {$id}: " . $conn->error . "</div>";
            }
        }
    } else {
        echo "<div style='color:blue'>Nenhum bloco com 'Câmera Fotográfica' encontrado para correção.</div>";
    }

} catch (Exception $e) {
    echo "<p style='color:red;'>Erro Fatal: " . $e->getMessage() . "</p>";
}

echo "<br><a href='editor_dinamico.php'>Voltar para o Editor</a>";
?>
