<?php
// Nome do Arquivo: setup_modelos.php
// Função: Cria a coluna de arquivo no banco e define os nomes corretos.
// EXECUTE UMA VEZ E DEPOIS APAGUE.

require_once 'config.php';
require_once 'db.php';

$conn = Database::getProd();

echo "<h1>⚙️ Configuração de Modelos</h1>";

// 1. Adiciona a coluna se não existir
try {
    $conn->query("ALTER TABLE Tipo_Servicos ADD COLUMN arquivo_modelo VARCHAR(255) NULL");
    echo "<p style='color:green'>Coluna 'arquivo_modelo' criada com sucesso!</p>";
} catch (Exception $e) {
    echo "<p style='color:orange'>Coluna já existia ou erro menor.</p>";
}

// 2. Mapa Definitivo (Nome do Serviço no Banco => Nome do Arquivo na Pasta)
$mapa = [
    'Avulso' => 'ModeloPropostaAvulso.docx',
    'Conferência' => 'ModeloPropostaConferencia.docx',
    'Desdobramento' => 'ModeloPropostaDesdobramento.docx',
    'Drone' => 'ModeloPropostaDrone.docx',
    'Georreferenciamento' => 'ModeloPropostaGeorreferenciamentoINCRA.docx', // Pega por parte do nome
    'Locação de Obra' => 'ModeloPropostaLocacaodeObra.docx',
    'Locação Terraplenagem' => 'ModeloPropostaLocacaoTerraplenagem.docx',
    'Obra Civil' => 'ModeloPropostaObraCivil.docx',
    'Obra Industrial' => 'ModeloPropostaObraIndustrial.docx',
    'Obra Terraplanagem' => 'ModeloPropostaObraTerraplanagem.docx',
    'Planialtimétrico' => 'ModeloPropostaPlanialtimetrico.docx',
    'Planimétrico' => 'ModeloPropostaPlanimetrico.docx',
    'Retificação' => 'ModeloPropostaRetificacaodeArea.docx', // Pega por parte do nome
    'Revisão' => 'ModeloPropostaRevisaodeServico.docx',
    'Usucapião' => 'ModeloPropostaUsucapiao.docx'
];

echo "<h3>Atualizando Nomes de Arquivos...</h3>";

foreach ($mapa as $chave => $arquivo) {
    // Atualiza onde o nome do serviço CONTÉM a chave (ex: "Retificação de Área" contém "Retificação")
    $sql = "UPDATE Tipo_Servicos SET arquivo_modelo = '$arquivo' WHERE nome LIKE '%$chave%'";
    if ($conn->query($sql)) {
        if ($conn->affected_rows > 0) {
            echo "<p>✅ Serviço contendo '<b>$chave</b>' vinculado a: <strong>$arquivo</strong></p>";
        }
    }
}

echo "<hr><h3>Configuração Concluída. Agora o sistema lerá direto do banco!</h3>";
?>