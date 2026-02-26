<?php
require_once 'db.php';
require_once 'PropostaRepository.php';

try {
    $conn = Database::getProd();
    
    // Busca a última proposta criada
    $res = $conn->query("SELECT id_proposta, numero_proposta, data_criacao, id_cliente, id_servico, modelo_docx FROM Propostas ORDER BY id_proposta DESC LIMIT 1");
    
    if ($res && $row = $res->fetch_assoc()) {
        $id = $row['id_proposta'];
        echo "=== ÚLTIMA PROPOSTA ENCONTRADA ===\n";
        print_r($row);
        echo "\n";

        echo "=== CONTEÚDOS PERSONALIZADOS (Editor) ===\n";
        $res2 = $conn->query("SELECT block_id, SUBSTRING(conteudo_texto, 1, 100) as resumo FROM Proposta_Conteudo_Personalizado WHERE id_proposta = $id");
        while ($row2 = $res2->fetch_assoc()) {
            echo "Bloco: {$row2['block_id']} | Resumo: {$row2['resumo']}...\n";
        }
        
        echo "\n=== DADOS NO REPOSITÓRIO (buscarPorId) ===\n";
        $repo = new PropostaRepository();
        $dadosCompletos = $repo->buscarPorId($id);
        
        // Remove 'itens' para não poluir muito a saída se houver muitos
        $debug = $dadosCompletos;
        unset($debug['itens']);
        print_r($debug);

    } else {
        echo "Nenhuma proposta encontrada no banco de dados.\n";
    }

} catch (Exception $e) {
    echo "ERRO: " . $e->getMessage() . "\n";
}
