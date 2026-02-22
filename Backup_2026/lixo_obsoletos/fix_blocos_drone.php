<?php
/**
 * fix_blocos_drone.php - Atualiza textos do Investimento e Equipamentos para Drone
 * EXECUTAR UMA VEZ e APAGAR depois.
 */
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once 'db.php';
$conn = Database::getProd();

echo "<h1 style='font-family:monospace;'>Fix Blocos Drone (Investimento + Equipamentos)</h1><pre>";

// =====================================================
// 1. INVESTIMENTO - Atualizar texto padrão (Drone id=19)
// =====================================================
echo "\n=== 1. INVESTIMENTO (Drone) ===\n\n";

$novoTextoInvestimento = 'Este investimento traduz-se em produtividade multiplicada: milhões de coordenadas georreferenciadas coletadas em horas, não em dias, eliminando gargalos operacionais e acelerando entregas sem comprometer a precisão.';

$res = $conn->query("SELECT id, default_content FROM service_type_blocks WHERE service_type_id = 19 AND block_slug = 'investimento'");
if ($res && $res->num_rows > 0) {
    $row = $res->fetch_assoc();
    echo "Conteúdo ATUAL:\n" . substr($row['default_content'], 0, 300) . "\n\n";
    
    // Substitui o texto antigo (remove valor e explicação de custo-benefício)
    $conteudo = $row['default_content'];
    
    // Tenta encontrar e substituir o padrão antigo
    // Padrão: "R$ X.XXX,XX (EXTENSO) Este investimento reflete..."
    $conteudo = preg_replace(
        '/R\$\s*[\d.,]+\s*\([^)]+\)\s*Este investimento reflete[^.]*\./s',
        $novoTextoInvestimento,
        $conteudo
    );
    
    // Se não encontrou o padrão exato, tenta outro
    if (strpos($conteudo, 'produtividade multiplicada') === false) {
        $conteudo = preg_replace(
            '/Este investimento reflete[^.]*\./s',
            $novoTextoInvestimento,
            $conteudo
        );
    }
    
    // Se ainda não encontrou, substitui o conteúdo inteiro
    if (strpos($conteudo, 'produtividade multiplicada') === false) {
        echo "⚠️ Padrão não encontrado com regex. Substituindo conteúdo inteiro.\n";
        $conteudo = '<p>${ValorProposta} (${ValorExtenso})</p><p>' . $novoTextoInvestimento . '</p>';
    }
    
    $stmt = $conn->prepare("UPDATE service_type_blocks SET default_content = ? WHERE id = ?");
    $stmt->bind_param('si', $conteudo, $row['id']);
    if ($stmt->execute()) {
        echo "✅ Investimento atualizado!\nNovo conteúdo:\n" . substr($conteudo, 0, 400) . "\n";
    } else {
        echo "❌ ERRO: {$conn->error}\n";
    }
    $stmt->close();
} else {
    echo "⚠️ Bloco 'investimento' não encontrado para Drone (id=19).\n";
}

// =====================================================
// 2. EQUIPAMENTOS - Atualizar lista (Drone id=19)
// =====================================================
echo "\n\n=== 2. EQUIPAMENTOS (Drone) ===\n\n";

$novoTextoEquipamentos = '<ul>
<li>Aeronave: ${Drone} (Câmera de Alta Resolução).</li>
<li>GPS - Geodésia: ${GPS} (Receptor GNSS RTK/PPK para Pontos de Controle).</li>
<li>Estação Total para Apoio: ${Estacao_Total} (Se necessário para áreas de sombra de GPS).</li>
<li>Processamento: Workstations com placas gráficas de alto desempenho.</li>
</ul>';

$res = $conn->query("SELECT id, block_slug, default_content FROM service_type_blocks WHERE service_type_id = 19 AND block_slug = 'equipamentos'");
if ($res && $res->num_rows > 0) {
    $row = $res->fetch_assoc();
    echo "Conteúdo ATUAL:\n" . substr($row['default_content'], 0, 300) . "\n\n";
    
    $stmt = $conn->prepare("UPDATE service_type_blocks SET default_content = ? WHERE id = ?");
    $stmt->bind_param('si', $novoTextoEquipamentos, $row['id']);
    if ($stmt->execute()) {
        echo "✅ Equipamentos atualizado!\nNovo conteúdo:\n$novoTextoEquipamentos\n";
    } else {
        echo "❌ ERRO: {$conn->error}\n";
    }
    $stmt->close();
} else {
    // Talvez o slug seja diferente
    echo "⚠️ Bloco 'equipamentos' não encontrado. Procurando alternativas...\n";
    $alt = $conn->query("SELECT id, block_slug, block_title, default_content FROM service_type_blocks WHERE service_type_id = 19 AND (block_slug LIKE '%equip%' OR block_title LIKE '%equip%')");
    if ($alt && $alt->num_rows > 0) {
        while ($a = $alt->fetch_assoc()) {
            echo "  Encontrado: slug='{$a['block_slug']}', title='{$a['block_title']}'\n";
            echo "  Conteúdo: " . substr($a['default_content'], 0, 200) . "\n\n";
            
            $stmt = $conn->prepare("UPDATE service_type_blocks SET default_content = ? WHERE id = ?");
            $stmt->bind_param('si', $novoTextoEquipamentos, $a['id']);
            if ($stmt->execute()) {
                echo "  ✅ Atualizado!\n";
            }
            $stmt->close();
        }
    } else {
        echo "  ❌ Nenhum bloco de equipamentos encontrado para Drone.\n";
        echo "  Listando todos os blocos do Drone para referência:\n";
        $all = $conn->query("SELECT block_slug, block_title FROM service_type_blocks WHERE service_type_id = 19 ORDER BY display_order");
        while ($b = $all->fetch_assoc()) {
            echo "    - {$b['block_slug']}: {$b['block_title']}\n";
        }
    }
}

echo "\n\n=== CONCLUÍDO ===\n";
echo "⚠️ APAGUE ESTE ARQUIVO após confirmar.\n";
echo "</pre>";
?>
