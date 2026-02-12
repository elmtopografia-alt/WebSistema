<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once 'db.php';

echo "<h1>Conteúdo Atual no Banco de Dados</h1>";
echo "<table border='1' cellpadding='10'>";
echo "<tr><th>Bloco (Tópico)</th><th>Variação</th><th>Início do Texto (50 chars)</th></tr>";

try {
    $conn = Database::getProd();
    $sql = "SELECT b.name as topo, v.variation_name, v.content_text 
            FROM proposal_content_variations v 
            JOIN proposal_block_templates b ON v.block_slug = b.slug 
            ORDER BY b.order ASC, v.variation_name ASC";
    
    $res = $conn->query($sql);
    
    if ($res && $res->num_rows > 0) {
        while($row = $res->fetch_assoc()) {
            $inicio = mb_substr(strip_tags($row['content_text']), 0, 100) . '...';
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['topo']) . "</td>";
            echo "<td>" . htmlspecialchars($row['variation_name']) . "</td>";
            echo "<td>" . htmlspecialchars($inicio) . "</td>";
            echo "</tr>";
        }
    } else {
        echo "<tr><td colspan='3'>NENHUM CONTEÚDO ENCONTRADO. PODE NECESSITAR RODAR O SCRIT DE FIX.</td></tr>";
    }
} catch (Exception $e) {
    echo "<tr><td colspan='3'>Erro: " . $e->getMessage() . "</td></tr>";
}
echo "</table>";
?>
