<?php
// Arquivo: estudo_modelo_usucapiao.php
// Objetivo: Testar a classe PropostaTopografia com dados de Usucapião

require_once 'estudo_modelo_usucapiao_classe_reurilisavel.php';

// --- DADOS SIMULADOS (Viram do banco depois) ---
$numero_proposta = "015/2026";
$cidade_data = "São Paulo, 29 de Janeiro de 2026";
$dados_cliente = [
    'Nome' => 'João da Silva',
    'E-mail' => 'joao@email.com',
    'Telefone' => '(11) 99999-9999',
    'WhatsApp' => '(11) 98888-8888'
];
$dados_obra = [
    'Endereço' => 'Rua das Flores, 123',
    'Bairro' => 'Jardim Primavera',
    'Cidade' => 'São Paulo - SP'
];

$valor_proposta = "R$ 3.500,00";
$valor_extenso = "Três mil e quinhentos reais";

// --- INSTANCIANDO A CLASSE ---
$proposta = new PropostaTopografia();

// 1. Cabeçalho
$proposta->addHeader($numero_proposta, 'Levantamento Topográfico', 'Para fins de Usucapião', $cidade_data);

// 2. Dados do Cliente
$proposta->addSectionTitle('DADOS DO CLIENTE');
$proposta->addInfoBox($dados_cliente);

// 3. Local da Obra
$proposta->addSectionTitle('LOCAL DA OBRA');
$proposta->addInfoBox($dados_obra);

// 4. Apresentação (Amarelo)
$proposta->addPresentation(
    'A Sua Empresa é uma empresa prestadora de serviços técnicos especializada exclusivamente em Engenharia de Agrimensura e Topografia.',
    'Nosso foco é a elaboração precisa das peças técnicas (plantas, memoriais e laudos) que servem como base física para o processo.'
);

// 5. Escopo (Verde/Vermelho)
$incluidos = [
    'Medição em campo com equipamentos de precisão',
    'Confecção de plantas e memoriais descritivos',
    'Transcrição dos dados dos confrontantes',
    'Emissão de ART de serviço topográfico',
    'Arquivos digitais (PDF e DWG)'
];
$nao_incluidos = [
    'Protocolização em cartórios ou prefeituras',
    'Assessoria jurídica ou advocatícia',
    'Coleta de assinaturas de confrontantes',
    'Solicitação de documentos a vizinhos',
    'Garantia de titulação da propriedade'
];
$proposta->addScope($incluidos, $nao_incluidos);

// 6. Investimento (Azul com Dourado)
$proposta->addValorDestaque($valor_proposta, $valor_extenso);

// 7. Pagamento
$proposta->addSectionTitle('CONDIÇÕES DE PAGAMENTO');
// (Poderíamos criar um método específico addPaymentConditions, mas usando InfoBox por enquanto como exemplo simples, ou criar estrutura custom)
$proposta->addInfoBox([
    'Mobilização (50%)' => 'R$ 1.750,00',
    'Entrega (50%)' => 'R$ 1.750,00'
]);

// 8. Rodapé
$proposta->addFooter('SUA EMPRESA TOPOGRAFIA', '📞 (11) 99999-9999 | Engenharia e Topografia de Precisão');

// 9. Gerar Arquivo
$arquivo_saida = 'Proposta_Usucapiao_Teste.docx';
if ($proposta->generate($arquivo_saida)) {
    echo "<h1>Sucesso!</h1>";
    echo "<p>Arquivo gerado: <strong>$arquivo_saida</strong></p>";
    echo "<p>Agora use os outros 5 códigos que você tem para criar mais métodos na classe!</p>";
} else {
    echo "<h1>Erro</h1>";
    echo "<p>Não foi possível gerar o arquivo. Verifique permissões ou biblioteca PHPWord.</p>";
}
?>