<?php
/**
 * update_drone_resources.php
 * Atualiza o bloco de 'Metodologia' ou cria um novo bloco 'Recursos e Equipamentos'
 * com o texto rico fornecido pelo usuário.
 */

require_once 'db.php';

// TEXTO FORNECIDO PELO USUÁRIO (Já com variáveis ajustadas se necessário)
// ${Drone}, ${GPS}, ${Estacao_Total}, ${Veiculo}, ${ClienteBairro}
$conteudoRecursos = "<h3>Recursos e Equipamentos Alocados</h3>
<ul>
    <li><strong>Drone VANT:</strong> \${Drone} (câmera de alta resolução, sistema RTK);</li>
    <li><strong>GPS de Apoio (RTK):</strong> \${GPS} (coleta de GCPs com precisão centimétrica);</li>
    <li><strong>Estação Total:</strong> \${Estacao_Total} (verificação de pontos de controle);</li>
    <li><strong>Veículo:</strong> \${Veiculo} (acesso fácil – via asfaltada ao local em \${ClienteBairro}).</li>
</ul>
<p><strong>Acessórios Específicos:</strong></p>
<ul>
    <li>Alvos para GCPs (considerando acesso \${AcessoLocal});</li>
    <li>Baterias extras (autonomia para área de \${AreaEstimada});</li>
    <li>Kit de limpeza (para poeira/lama em terreno \${TipoTerreno});</li>
    <li>Software Fotogramétrico: Licenciado para processamento de imagens;</li>
    <li>Workstation: Computador de alta performance.</li>
</ul>";

// LÓGICA DE ATUALIZAÇÃO
echo "<h1>Atualizando Bloco de Recursos (Drone)</h1>";

try {
    $conn = Database::getProd();
    
    // Identificar ID do serviço de Drone
    $res = $conn->query("SELECT id_servico, nome FROM Tipo_Servicos WHERE nome LIKE '%Drone%' OR nome LIKE '%Aero%' LIMIT 1");
    $servico = $res->fetch_assoc();
    
    if (!$servico) {
        die("<h3 style='color:red'>Serviço de Drone não encontrado!</h3>");
    }
    
    $idServico = $servico['id_servico'];
    echo "<p>Serviço Alvo: <strong>{$servico['nome']} (ID: $idServico)</strong></p>";

    $table = "service_type_blocks";
    $blockSlug = "recursos_equipamentos";
    
    // Configuração do Bloco
    $title = "Recursos e Equipamentos";
    $cat = "technical";
    $order = 35; // Entre metodologia e cronograma

    // Verifica se registro existe
    $check = $conn->query("SELECT id FROM $table WHERE service_type_id = $idServico AND block_slug = '$blockSlug'");
    
    if ($check && $check->num_rows > 0) {
        $stmt = $conn->prepare("UPDATE $table SET default_content = ? WHERE service_type_id = ? AND block_slug = ?");
        $stmt->bind_param("sis", $conteudoRecursos, $idServico, $blockSlug);
        $stmt->execute();
        echo "<li>Bloco '$blockSlug': <span style='color:blue'>Atualizado</span></li>";
    } else {
        $stmt = $conn->prepare("INSERT INTO $table (service_type_id, block_slug, block_title, category, display_order, is_required, default_content, is_active, allowed_vars) VALUES (?, ?, ?, ?, ?, 0, ?, 1, '[]')");
        $stmt->bind_param("isssis", $idServico, $blockSlug, $title, $cat, $order, $conteudoRecursos);
        
        if ($stmt->execute()) {
            echo "<li>Bloco '$blockSlug': <span style='color:green'>Criado</span></li>";
        } else {
             echo "<li>Bloco '$blockSlug': <span style='color:red'>Erro ao criar: " . $stmt->error . "</span></li>";
        }
    }
    
    echo "</ul>";
    echo "<h3 style='color:green'>Sucesso! Template de Recursos atualizado.</h3>";

} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}
?>
