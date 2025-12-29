<?php
// Nome do Arquivo: setup_geral.php
// Função: Cria a coluna 'arquivo_modelo' e define os nomes nos DOIS BANCOS (Prod e Demo).

require_once 'config.php';
require_once 'db.php';

// Mapa de Arquivos (Nome no Banco => Nome do Arquivo)
$mapa = [
    'Avulso' => 'ModeloPropostaAvulso.docx',
    'Conferência' => 'ModeloPropostaConferencia.docx',
    'Desdobramento' => 'ModeloPropostaDesdobramento.docx',
    'Drone' => 'ModeloPropostaDrone.docx',
    'Georreferenciamento' => 'ModeloPropostaGeorreferenciamentoINCRA.docx',
    'Locação de Obra' => 'ModeloPropostaLocacaodeObra.docx',
    'Locação Terraplenagem' => 'ModeloPropostaLocacaoTerraplenagem.docx',
    'Obra Civil' => 'ModeloPropostaObraCivil.docx',
    'Obra Industrial' => 'ModeloPropostaObraIndustrial.docx',
    'Obra Terraplanagem' => 'ModeloPropostaObraTerraplanagem.docx',
    'Planialtimétrico' => 'ModeloPropostaPlanialtimetrico.docx',
    'Planimétrico' => 'ModeloPropostaPlanimetrico.docx',
    'Retificação' => 'ModeloPropostaRetificacaodeArea.docx',
    'Revisão' => 'ModeloPropostaRevisaodeServico.docx',
    'Usucapião' => 'ModeloPropostaUsucapiao.docx'
];

function atualizarBanco($conn, $nomeBanco, $mapa) {
    echo "<h2>Processando Banco: $nomeBanco</h2>";
    
    // 1. Cria Coluna
    try {
        $conn->query("ALTER TABLE Tipo_Servicos ADD COLUMN arquivo_modelo VARCHAR(255) NULL");
        echo "<p style='color:green'>+ Coluna 'arquivo_modelo' criada.</p>";
    } catch (Exception $e) {
        echo "<p style='color:blue'>= Coluna já existia.</p>";
    }

    // 2. Atualiza Nomes
    $count = 0;
    foreach ($mapa as $chave => $arquivo) {
        $sql = "UPDATE Tipo_Servicos SET arquivo_modelo = '$arquivo' WHERE nome LIKE '%$chave%'";
        if ($conn->query($sql)) {
            $count += $conn->affected_rows;
        }
    }
    echo "<p>✅ <strong>$count</strong> serviços atualizados com o nome do arquivo correto.</p>";
    echo "<hr>";
}

echo "<h1>🔄 Sincronização de Estrutura</h1>";

// Executa na Produção
try {
    atualizarBanco(Database::getProd(), "PRODUÇÃO (demanda)", $mapa);
} catch (Exception $e) { echo "Erro Prod: " . $e->getMessage(); }

// Executa na Demo
try {
    atualizarBanco(Database::getDemo(), "DEMO (proposta)", $mapa);
} catch (Exception $e) { echo "Erro Demo: " . $e->getMessage(); }

echo "<h3>Concluído! Ambos os sistemas estão compatíveis.</h3>";
?>