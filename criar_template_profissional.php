<?php

/**
 * criar_template_profissional.php
 * 
 * VERSÃO 2.0 - Template Completo
 * Gera um modelo DOCX profissional com TODAS as seções comuns às propostas.
 * 
 * SEÇÕES INCLUÍDAS:
 * 1. Cabeçalho (Logo, Proposta Nº, Cidade, Data)
 * 2. Dados do Cliente
 * 3. Local da Obra
 * 4. Apresentação da Empresa
 * 5. Finalidade
 * 6. Escopo do Serviço (placeholder genérico)
 * 7. Documentação Gerada (Entregáveis)
 * 8. Metodologia
 * 9. Equipamentos
 * 10. Investimento
 * 11. Condições de Pagamento
 * 12. Dados Bancários
 * 13. Considerações Finais
 * 14. Assinatura
 */

require_once 'vendor/autoload.php';

use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Shared\Converter;
use PhpOffice\PhpWord\SimpleType\Jc;

// ============================================================
// CONFIGURAÇÕES DE CORES E TIPOGRAFIA
// ============================================================

$CORES = [
    'azul_escuro'   => '1B4F72',  // Títulos, cabeçalhos
    'azul_medio'    => '2E86AB',  // Subtítulos, links
    'azul_claro'    => 'D4E6F1',  // Fundos de destaque
    'cinza_escuro'  => '333333',  // Texto corpo
    'cinza_medio'   => '666666',  // Texto secundário
    'cinza_claro'   => 'F5F5F5',  // Backgrounds alternados
    'branco'        => 'FFFFFF',
];

$FONTES = [
    'titulo' => 'Arial',
    'corpo'  => 'Calibri',
];

// ============================================================
// INICIALIZAÇÃO DO PHPWORD
// ============================================================

$phpWord = new PhpWord();
$phpWord->setDefaultFontName($FONTES['corpo']);
$phpWord->setDefaultFontSize(11);

// ============================================================
// DEFINIÇÃO DE ESTILOS
// ============================================================

// Título Principal (H1)
$phpWord->addTitleStyle(1, [
    'name' => $FONTES['titulo'],
    'size' => 18,
    'bold' => true,
    'color' => $CORES['azul_escuro'],
], ['alignment' => Jc::CENTER, 'spaceAfter' => Converter::pointToTwip(8)]);

// Subtítulo (H2)
$phpWord->addTitleStyle(2, [
    'name' => $FONTES['titulo'],
    'size' => 13,
    'bold' => true,
    'color' => $CORES['azul_medio'],
], ['alignment' => Jc::LEFT, 'spaceAfter' => Converter::pointToTwip(6), 'spaceBefore' => Converter::pointToTwip(12)]);

// Estilos de Parágrafo
$phpWord->addParagraphStyle('Corpo', [
    'alignment' => Jc::BOTH,
    'lineHeight' => 1.15,
    'spaceAfter' => Converter::pointToTwip(6),
]);
$phpWord->addParagraphStyle('Centralizado', ['alignment' => Jc::CENTER]);
$phpWord->addParagraphStyle('ListaItem', [
    'alignment' => Jc::LEFT,
    'indentation' => ['left' => Converter::cmToTwip(0.5)],
    'spaceAfter' => Converter::pointToTwip(3),
]);

// Estilos de Fonte
$phpWord->addFontStyle('TextoNormal', ['name' => $FONTES['corpo'], 'size' => 11, 'color' => $CORES['cinza_escuro']]);
$phpWord->addFontStyle('Label', ['name' => $FONTES['corpo'], 'size' => 11, 'bold' => true, 'color' => $CORES['cinza_medio']]);
$phpWord->addFontStyle('Rodape', ['name' => $FONTES['corpo'], 'size' => 11, 'color' => $CORES['cinza_medio']]);
$phpWord->addFontStyle('Bullet', ['name' => $FONTES['corpo'], 'size' => 11, 'color' => $CORES['azul_medio']]);

// Estilo de Tabela
$tableStyle = 'TabelaProfissional';
$phpWord->addTableStyle($tableStyle, [
    'borderSize' => 6,
    'borderColor' => 'CCCCCC',
    'cellMargin' => Converter::pointToTwip(4),
], ['bgColor' => $CORES['azul_escuro']]);

// ============================================================
// CRIAÇÃO DO DOCUMENTO
// ============================================================

$section = $phpWord->addSection([
    'marginLeft' => Converter::cmToTwip(2.5),
    'marginRight' => Converter::cmToTwip(2),
    'marginTop' => Converter::cmToTwip(2),
    'marginBottom' => Converter::cmToTwip(2),
]);

// ============================================================
// CABEÇALHO
// ============================================================

$header = $section->addHeader();
$headerTable = $header->addTable();
$headerTable->addRow();
$headerTable->addCell(Converter::cmToTwip(10))->addText('${logo_empresa}', ['size' => 11]);
$cell2 = $headerTable->addCell(Converter::cmToTwip(6), ['valign' => 'center']);
$run = $cell2->addTextRun(['alignment' => Jc::RIGHT]);
$run->addText('Proposta Nº ', ['size' => 11, 'color' => $CORES['cinza_medio']]);
$run->addText('${numero_proposta}', ['size' => 12, 'bold' => true, 'color' => $CORES['azul_escuro']]);

// ============================================================
// RODAPÉ
// ============================================================

$footer = $section->addFooter();
$footerRun = $footer->addTextRun(['alignment' => Jc::CENTER]);
$footerRun->addText('${Empresa}', 'Rodape');
$footerRun->addText(' • CNPJ: ${CNPJ} • WhatsApp: ${whatsapp}', 'Rodape');
$footer->addTextRun(['alignment' => Jc::RIGHT])->addField('PAGE');

// ============================================================
// TÍTULO PRINCIPAL
// ============================================================

$section->addTitle('PROPOSTA DE SERVIÇOS', 1);
$section->addText('Serviços de Topografia e Georreferenciamento', [
    'size' => 11,
    'italic' => true,
    'color' => $CORES['cinza_medio']
], 'Centralizado');
$section->addTextBreak(1);

// Cidade e Data
$section->addText('${Cidade}, ${DExrenso}', ['size' => 11, 'color' => $CORES['cinza_escuro']], 'Corpo');
$section->addTextBreak(1);

// ============================================================
// 1. DADOS DO CLIENTE
// ============================================================

$section->addTitle('📌 Dados do Cliente', 2);

$tblCliente = $section->addTable($tableStyle);
$tblCliente->addRow(Converter::pointToTwip(14));
$tblCliente->addCell(Converter::cmToTwip(16), ['bgColor' => $CORES['azul_escuro'], 'gridSpan' => 2])
    ->addText('INFORMAÇÕES DO CONTRATANTE', ['bold' => true, 'color' => $CORES['branco'], 'size' => 11], ['alignment' => Jc::CENTER]);

$clienteRows = [
    ['Nome/Razão Social', '${nome_cliente_salvo}'],
    ['E-mail', '${email_salvo}'],
    ['Telefone', '${telefone_salvo}'],
    ['Celular', '${celular_salvo}'],
    ['WhatsApp', '${whatsapp_salvo}'],
];
$zebra = false;
foreach ($clienteRows as $row) {
    $bg = $zebra ? $CORES['cinza_claro'] : $CORES['branco'];
    $tblCliente->addRow();
    $tblCliente->addCell(Converter::cmToTwip(4), ['bgColor' => $bg])->addText($row[0], 'Label');
    $tblCliente->addCell(Converter::cmToTwip(12), ['bgColor' => $bg])->addText($row[1], 'TextoNormal');
    $zebra = !$zebra;
}
$section->addTextBreak(1);

// ============================================================
// 2. LOCAL DA OBRA
// ============================================================

$section->addTitle('📍 Local da Obra', 2);

$tblObra = $section->addTable($tableStyle);
$tblObra->addRow(Converter::pointToTwip(14));
$tblObra->addCell(Converter::cmToTwip(16), ['bgColor' => $CORES['azul_escuro'], 'gridSpan' => 2])
    ->addText('DADOS DO IMÓVEL', ['bold' => true, 'color' => $CORES['branco'], 'size' => 11], ['alignment' => Jc::CENTER]);

$obraRows = [
    ['Endereço', '${endereco_obra}'],
    ['Bairro', '${bairro_obra}'],
    ['Cidade', '${cidade_obra}'],
    ['Estado', '${estado_obra}'],
    ['Área do Imóvel', '${area_obra}'],
    ['Tipo de Levantamento', '${tipo_levantamento}'],
];
$zebra = false;
foreach ($obraRows as $row) {
    $bg = $zebra ? $CORES['cinza_claro'] : $CORES['branco'];
    $tblObra->addRow();
    $tblObra->addCell(Converter::cmToTwip(4), ['bgColor' => $bg])->addText($row[0], 'Label');
    $tblObra->addCell(Converter::cmToTwip(12), ['bgColor' => $bg])->addText($row[1], 'TextoNormal');
    $zebra = !$zebra;
}
$section->addTextBreak(1);

// ============================================================
// 3. APRESENTAÇÃO DA EMPRESA
// ============================================================

$section->addTitle('1. Apresentação', 2);

$section->addText(
    'A ${Empresa} é referência em serviços topográficos de alta precisão. Com vasta experiência e histórico sólido de projetos concluídos, garantimos segurança e exatidão em cada medição.',
    'TextoNormal',
    'Corpo'
);
$section->addText(
    'Nosso compromisso é assegurar a conformidade das medidas reais do terreno com a documentação legal e a realidade física. Utilizamos tecnologia de ponta e equipe altamente qualificada para oferecer base técnica sólida para projetos de engenharia e arquitetura.',
    'TextoNormal',
    'Corpo'
);
$section->addTextBreak(1);

// ============================================================
// 4. FINALIDADE
// ============================================================

$section->addTitle('2. Finalidade', 2);
$section->addText('${finalidade}', 'TextoNormal', 'Corpo');
$section->addTextBreak(1);

// ============================================================
// 5. ESCOPO DO SERVIÇO (Placeholder para conteúdo específico)
// ============================================================

$section->addTitle('3. Escopo do Serviço', 2);

$section->addText('${escopo_servico}', 'TextoNormal', 'Corpo');

// Texto padrão caso não haja escopo customizado
$section->addText(
    'O serviço contempla todas as atividades de campo e escritório necessárias para a execução do levantamento topográfico conforme especificações técnicas e normas vigentes.',
    ['size' => 11, 'italic' => true, 'color' => $CORES['cinza_medio']],
    'Corpo'
);
$section->addTextBreak(1);

// ============================================================
// 6. DOCUMENTAÇÃO GERADA (ENTREGÁVEIS)
// ============================================================

$section->addTitle('4. Documentação Gerada (Entregáveis)', 2);

$entregaveis = [
    'Planta Topográfica em formato DWG e PDF',
    'Memorial Descritivo do Terreno',
    'ART (Anotação de Responsabilidade Técnica) registrada no CREA',
    'Relatório Técnico (quando aplicável)',
];

foreach ($entregaveis as $item) {
    $run = $section->addTextRun('ListaItem');
    $run->addText('● ', 'Bullet');
    $run->addText($item, 'TextoNormal');
}
$section->addTextBreak(1);

// ============================================================
// 7. METODOLOGIA
// ============================================================

$section->addTitle('5. Metodologia', 2);

$section->addText('Etapa 1: Geodésia e Amarração (GPS/GNSS)', ['bold' => true, 'color' => $CORES['azul_escuro']], 'Corpo');
$section->addText(
    'Implantação da Rede de Apoio Geodésica com rastreadores GNSS de dupla frequência para georreferenciamento ao Sistema Geodésico Brasileiro (SIRGAS2000).',
    'TextoNormal',
    'Corpo'
);

$section->addText('Etapa 2: Topografia de Detalhe (Estação Total)', ['bold' => true, 'color' => $CORES['azul_escuro']], 'Corpo');
$section->addText(
    'Levantamento irradiado de todos os detalhes internos e externos listados no escopo e coleta da altimetria para geração do modelo digital do terreno.',
    'TextoNormal',
    'Corpo'
);

$section->addText('Etapa 3: Escritório', ['bold' => true, 'color' => $CORES['azul_escuro']], 'Corpo');
$section->addText(
    'Processamento dos vetores GNSS, unificação dos dados com a topografia clássica, desenho técnico e elaboração dos memoriais.',
    'TextoNormal',
    'Corpo'
);

$section->addTextBreak(1);
$section->addText('Normas Técnicas Aplicáveis:', ['bold' => true, 'size' => 11], 'Corpo');
$normas = ['ABNT NBR 13.133 (Execução de Levantamento Topográfico)', 'Sistema de Referência: SIRGAS2000 UTM'];
foreach ($normas as $n) {
    $run = $section->addTextRun('ListaItem');
    $run->addText('● ', 'Bullet');
    $run->addText($n, ['size' => 11]);
}
$section->addTextBreak(1);

// ============================================================
// 8. EQUIPAMENTOS
// ============================================================

$section->addTitle('6. Equipamentos Previstos', 2);

$tblEquip = $section->addTable($tableStyle);
$tblEquip->addRow(Converter::pointToTwip(14));
$tblEquip->addCell(Converter::cmToTwip(8), ['bgColor' => $CORES['azul_escuro']])
    ->addText('EQUIPAMENTO', ['bold' => true, 'color' => $CORES['branco'], 'size' => 11], ['alignment' => Jc::CENTER]);
$tblEquip->addCell(Converter::cmToTwip(8), ['bgColor' => $CORES['azul_escuro']])
    ->addText('DISPONIBILIDADE', ['bold' => true, 'color' => $CORES['branco'], 'size' => 11], ['alignment' => Jc::CENTER]);

$equipRows = [
    ['Veículo', '${Veiculo}'],
    ['Estação Total', '${Estacao_Total}'],
    ['GPS/GNSS', '${GPS}'],
    ['Drone (VANT)', '${Drone}'],
];
$zebra = false;
foreach ($equipRows as $row) {
    $bg = $zebra ? $CORES['cinza_claro'] : $CORES['branco'];
    $tblEquip->addRow();
    $tblEquip->addCell(Converter::cmToTwip(8), ['bgColor' => $bg])->addText($row[0], 'TextoNormal');
    $tblEquip->addCell(Converter::cmToTwip(8), ['bgColor' => $bg])
        ->addText($row[1], ['bold' => true, 'color' => $CORES['azul_medio']], ['alignment' => Jc::CENTER]);
    $zebra = !$zebra;
}
$section->addTextBreak(1);

// ============================================================
// 9. INVESTIMENTO (VALOR)
// ============================================================

$section->addTitle('7. Investimento', 2);

// Caixa de destaque
$valorTable = $section->addTable(['borderSize' => 10, 'borderColor' => $CORES['azul_escuro'], 'cellMargin' => Converter::pointToTwip(8)]);
$valorTable->addRow(Converter::pointToTwip(50));
$valorCell = $valorTable->addCell(Converter::cmToTwip(16), ['bgColor' => $CORES['azul_claro'], 'valign' => 'center']);

$valorCell->addText('VALOR TOTAL DA PROPOSTA', [
    'size' => 11,
    'bold' => true,
    'color' => $CORES['azul_escuro']
], ['alignment' => Jc::CENTER, 'spaceAfter' => Converter::pointToTwip(4)]);

$valorCell->addText('R$ ${ValorProposta}', [
    'name' => $FONTES['titulo'],
    'size' => 18,
    'bold' => true,
    'color' => $CORES['azul_escuro']
], ['alignment' => Jc::CENTER, 'spaceAfter' => Converter::pointToTwip(4)]);

$valorCell->addText('(${ValorExtenso})', [
    'size' => 11,
    'italic' => true,
    'color' => $CORES['cinza_medio']
], ['alignment' => Jc::CENTER]);

$section->addTextBreak(1);
$section->addText(
    'Este investimento reflete o custo de uma topografia completa, essencial para projetos de arquitetura e engenharia, evitando erros de nível e compatibilização futura.',
    ['size' => 11, 'italic' => true, 'color' => $CORES['cinza_medio']],
    'Corpo'
);
$section->addTextBreak(1);

// ============================================================
// 10. CONDIÇÕES DE PAGAMENTO
// ============================================================

$section->addTitle('8. Condições de Pagamento', 2);

$section->addText('Prazo de Execução: ${prazo_execucao}', ['bold' => true, 'color' => $CORES['azul_escuro']], 'Corpo');
$section->addTextBreak(1);

$tblPagto = $section->addTable($tableStyle);
$tblPagto->addRow(Converter::pointToTwip(14));
$tblPagto->addCell(Converter::cmToTwip(6), ['bgColor' => $CORES['azul_escuro']])
    ->addText('ETAPA', ['bold' => true, 'color' => $CORES['branco'], 'size' => 11], ['alignment' => Jc::CENTER]);
$tblPagto->addCell(Converter::cmToTwip(4), ['bgColor' => $CORES['azul_escuro']])
    ->addText('%', ['bold' => true, 'color' => $CORES['branco'], 'size' => 11], ['alignment' => Jc::CENTER]);
$tblPagto->addCell(Converter::cmToTwip(6), ['bgColor' => $CORES['azul_escuro']])
    ->addText('VALOR', ['bold' => true, 'color' => $CORES['branco'], 'size' => 11], ['alignment' => Jc::CENTER]);

$tblPagto->addRow();
$tblPagto->addCell(Converter::cmToTwip(6), ['bgColor' => $CORES['branco']])->addText('Mobilização (Aceite)', 'TextoNormal');
$tblPagto->addCell(Converter::cmToTwip(4), ['bgColor' => $CORES['branco']])
    ->addText('${mobilizacao_percentual}%', ['bold' => true], ['alignment' => Jc::CENTER]);
$tblPagto->addCell(Converter::cmToTwip(6), ['bgColor' => $CORES['branco']])
    ->addText('R$ ${mobilizacao_valor}', ['bold' => true, 'color' => $CORES['azul_medio']], ['alignment' => Jc::RIGHT]);

$tblPagto->addRow();
$tblPagto->addCell(Converter::cmToTwip(6), ['bgColor' => $CORES['cinza_claro']])->addText('Entrega Final', 'TextoNormal');
$tblPagto->addCell(Converter::cmToTwip(4), ['bgColor' => $CORES['cinza_claro']])
    ->addText('${restante_percentual}%', ['bold' => true], ['alignment' => Jc::CENTER]);
$tblPagto->addCell(Converter::cmToTwip(6), ['bgColor' => $CORES['cinza_claro']])
    ->addText('R$ ${restante_valor}', ['bold' => true, 'color' => $CORES['azul_medio']], ['alignment' => Jc::RIGHT]);

$section->addTextBreak(1);

// ============================================================
// 11. DADOS BANCÁRIOS
// ============================================================

$section->addTitle('9. Dados Bancários', 2);

$tblBanco = $section->addTable($tableStyle);
$tblBanco->addRow(Converter::pointToTwip(14));
$tblBanco->addCell(Converter::cmToTwip(16), ['bgColor' => $CORES['azul_escuro'], 'gridSpan' => 2])
    ->addText('INFORMAÇÕES PARA PAGAMENTO', ['bold' => true, 'color' => $CORES['branco'], 'size' => 11], ['alignment' => Jc::CENTER]);

$bancoRows = [
    ['Banco', '${Banco}'],
    ['Agência', '${Agencia}'],
    ['Conta Corrente', '${Conta}'],
    ['Titular', '${Empresa}'],
    ['CNPJ', '${CNPJ}'],
    ['Chave PIX', '${PIX}'],
];
$zebra = false;
foreach ($bancoRows as $row) {
    $bg = $zebra ? $CORES['cinza_claro'] : $CORES['branco'];
    $tblBanco->addRow();
    $tblBanco->addCell(Converter::cmToTwip(4), ['bgColor' => $bg])->addText($row[0], 'Label');
    $tblBanco->addCell(Converter::cmToTwip(12), ['bgColor' => $bg])->addText($row[1], 'TextoNormal');
    $zebra = !$zebra;
}
$section->addTextBreak(1);

// ============================================================
// 12. CONSIDERAÇÕES FINAIS
// ============================================================

$section->addTitle('10. Considerações Finais', 2);

$section->addText(
    'Agradecemos a oportunidade de apresentar nossa proposta. Temos a certeza de que nossa solução técnica fornecerá a base exata necessária para o desenvolvimento do seu projeto.',
    'TextoNormal',
    'Corpo'
);
$section->addText(
    'Permanecemos à disposição para esclarecimentos adicionais e negociação das condições comerciais.',
    'TextoNormal',
    'Corpo'
);
$section->addTextBreak(2);

// ============================================================
// 13. ASSINATURA
// ============================================================

$section->addText('Atenciosamente,', 'TextoNormal', 'Corpo');
$section->addTextBreak(2);

$section->addText('_______________________________________', 'TextoNormal', 'Centralizado');
$section->addText('${Empresa}', ['bold' => true, 'color' => $CORES['azul_escuro']], 'Centralizado');
$section->addText('${empresa_proponente_cidade}', 'Rodape', 'Centralizado');
$section->addTextBreak(1);
$section->addText('📞 Contato: ${whatsapp} (WhatsApp)', ['size' => 11, 'color' => $CORES['azul_medio']], 'Centralizado');

// ============================================================
// SALVAR DOCUMENTO
// ============================================================

$diretorioSaida = __DIR__ . '/modelos_prod/';
$nomeArquivo = 'ModeloProfissionalV2.docx';

if (!is_dir($diretorioSaida)) mkdir($diretorioSaida, 0755, true);

$caminhoCompleto = $diretorioSaida . $nomeArquivo;

// ============================================================
// DEBUG: Verificar salvamento
// ============================================================
$debugInfo = [];
$debugInfo['diretorio'] = $diretorioSaida;
$debugInfo['arquivo'] = $nomeArquivo;
$debugInfo['caminho_completo'] = $caminhoCompleto;
$debugInfo['diretorio_existe'] = is_dir($diretorioSaida) ? 'SIM' : 'NÃO';
$debugInfo['diretorio_gravavel'] = is_writable($diretorioSaida) ? 'SIM' : 'NÃO';

try {
    $objWriter = IOFactory::createWriter($phpWord, 'Word2007');
    $objWriter->save($caminhoCompleto);
    $debugInfo['salvou'] = 'SUCESSO';
} catch (Exception $e) {
    $debugInfo['salvou'] = 'ERRO: ' . $e->getMessage();
}

// Verificar arquivo após salvar
if (file_exists($caminhoCompleto)) {
    $debugInfo['arquivo_existe'] = 'SIM';
    $debugInfo['tamanho_bytes'] = filesize($caminhoCompleto);
    $debugInfo['tamanho_kb'] = round(filesize($caminhoCompleto) / 1024, 2) . ' KB';
    $debugInfo['data_modificacao'] = date('d/m/Y H:i:s', filemtime($caminhoCompleto));
} else {
    $debugInfo['arquivo_existe'] = 'NÃO';
}

// Copia para modelos_demo
$diretorioDemo = __DIR__ . '/modelos_demo/';
if (is_dir($diretorioDemo)) {
    copy($caminhoCompleto, $diretorioDemo . $nomeArquivo);
    $debugInfo['copiou_demo'] = 'SIM';
} else {
    $debugInfo['copiou_demo'] = 'Pasta demo não existe';
}

// ============================================================
// FEEDBACK VISUAL
// ============================================================
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Template Profissional v2.0</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }

        .card {
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }
    </style>
</head>

<body>
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-success text-white text-center py-4">
                        <i class="bi bi-check-circle-fill fs-1"></i>
                        <h3 class="mt-2 mb-0">Template v2.0 Gerado!</h3>
                    </div>
                    <div class="card-body p-4">
                        <div class="alert alert-info">
                            <strong>📁 Arquivo:</strong><br>
                            <code><?php echo $caminhoCompleto; ?></code>
                        </div>

                        <!-- DEBUG INFO -->
                        <div class="alert alert-warning">
                            <strong>🔍 Debug Info:</strong>
                            <table class="table table-sm table-bordered mt-2 mb-0" style="font-size: 12px;">
                                <?php foreach ($debugInfo as $key => $value): ?>
                                    <tr>
                                        <td><strong><?php echo $key; ?></strong></td>
                                        <td><?php echo $value; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </table>
                        </div>

                        <h6>✅ Seções Incluídas:</h6>
                        <div class="row">
                            <div class="col-6">
                                <small>
                                    1. Dados do Cliente<br>
                                    2. Local da Obra<br>
                                    3. Apresentação<br>
                                    4. Finalidade<br>
                                    5. Escopo do Serviço<br>
                                    6. Entregáveis<br>
                                    7. Metodologia
                                </small>
                            </div>
                            <div class="col-6">
                                <small>
                                    8. Equipamentos<br>
                                    9. Investimento<br>
                                    10. Pagamento<br>
                                    11. Dados Bancários<br>
                                    12. Considerações Finais<br>
                                    13. Assinatura
                                </small>
                            </div>
                        </div>

                        <div class="d-flex gap-2 mt-4">
                            <a href="modelos_prod/<?php echo $nomeArquivo; ?>" class="btn btn-success flex-fill" download>
                                <i class="bi bi-download me-2"></i>Baixar Template
                            </a>
                            <a href="painel.php" class="btn btn-outline-secondary">
                                <i class="bi bi-house"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>