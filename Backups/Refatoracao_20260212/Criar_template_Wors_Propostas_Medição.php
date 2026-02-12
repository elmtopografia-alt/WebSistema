<?php

/**
 * criar_template_drone_elegante.php
 * 
 * TEMPLATE REFINADO - Drone / Aerofotogrametria
 * Design sóbrio, profissional, sem excessos visuais.
 */

require_once 'vendor/autoload.php';

use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Shared\Converter;
use PhpOffice\PhpWord\SimpleType\Jc;

// ============================================================
// PALETA SOBRIA (Monocromática Azul Profundo)
// ============================================================

$CORES = [
    'primaria'      => '1F3A5F',    // Azul marinho (só para títulos principais)
    'secundaria'    => '4A6FA5',    // Azul aço (subtítulos)
    'texto'         => '2C3E50',    // Cinza azulado (corpo)
    'subtitulo'     => '5D6D7E',    // Cinza médio (descrições)
    'fundo'         => 'F8F9FA',    // Cinza quase branco (alternância sutil)
    'borda'         => 'D5D8DC',    // Borda cinza claro
    'branco'        => 'FFFFFF',
];

$FONTES = [
    'titulo' => 'Helvetica Neue',   // Ou Arial se não tiver Helvetica
    'corpo'  => 'Calibri Light'
];

// ============================================================
// INICIALIZAÇÃO
// ============================================================

$phpWord = new PhpWord();
$phpWord->setDefaultFontName($FONTES['corpo']);
$phpWord->setDefaultFontSize(11);
$phpWord->setDefaultParagraphStyle(['lineHeight' => 1.2, 'spaceAfter' => 120]);

// Títulos
$phpWord->addTitleStyle(1, [
    'name' => $FONTES['titulo'],
    'size' => 20,
    'bold' => true,
    'color' => $CORES['primaria'],
], ['alignment' => Jc::CENTER, 'spaceAfter' => 100]);

$phpWord->addTitleStyle(2, [
    'name' => $FONTES['titulo'],
    'size' => 12,
    'bold' => true,
    'color' => $CORES['secundaria'],
], ['spaceBefore' => 300, 'spaceAfter' => 80]);

// Estilos de texto
$phpWord->addFontStyle('Corpo', ['name' => $FONTES['corpo'], 'size' => 11, 'color' => $CORES['texto']]);
$phpWord->addFontStyle('Destaque', ['name' => $FONTES['corpo'], 'size' => 11, 'bold' => true, 'color' => $CORES['primaria']]);
$phpWord->addFontStyle('Legenda', ['name' => $FONTES['corpo'], 'size' => 9, 'italic' => true, 'color' => $CORES['subtitulo']]);
$phpWord->addFontStyle('Valor', ['name' => $FONTES['titulo'], 'size' => 16, 'bold' => true, 'color' => $CORES['primaria']]);

// Tabela elegante (bordas finas, sem excesso de cor)
$phpWord->addTableStyle('Elegante', [
    'borderSize' => 4,
    'borderColor' => $CORES['borda'],
    'cellMargin' => 80,
], ['bgColor' => $CORES['primaria']]); // Só cabeçalho tem cor

// ============================================================
// DOCUMENTO
// ============================================================

$section = $phpWord->addSection([
    'marginLeft' => Converter::cmToTwip(2.5),
    'marginRight' => Converter::cmToTwip(2.5),
    'marginTop' => Converter::cmToTwip(2),
    'marginBottom' => Converter::cmToTwip(2),
]);

// Cabeçalho minimalista
$header = $section->addHeader();
$headerTable = $header->addTable();
$headerTable->addRow();
$headerTable->addCell(Converter::cmToTwip(12))->addText('${logo_empresa}', ['size' => 9, 'color' => $CORES['subtitulo']]);
$headerCell = $headerTable->addCell(Converter::cmToTwip(6), ['valign' => 'center']);
$headerRun = $headerCell->addTextRun(['alignment' => Jc::RIGHT]);
$headerRun->addText('Proposta ', ['size' => 9, 'color' => $CORES['subtitulo']]);
$headerRun->addText('${numero_proposta}', ['size' => 11, 'bold' => true, 'color' => $CORES['primaria']]);

// Rodapé discreto
$footer = $section->addFooter();
$footer->addText(
    '${Empresa} • CNPJ ${CNPJ} • ${whatsapp}',
    ['size' => 8, 'color' => $CORES['subtitulo']],
    ['alignment' => Jc::CENTER]
);

// ============================================================
// CAPA / TÍTULO
// ============================================================

$section->addTitle('Proposta de Serviços', 1);
$section->addText(
    'Topografia por Aerofotogrametria com Drone',
    ['size' => 12, 'italic' => true, 'color' => $CORES['secundaria']],
    ['alignment' => Jc::CENTER, 'spaceAfter' => 400]
);

// Data e referência em uma linha só
$dataRun = $section->addTextRun(['alignment' => Jc::RIGHT]);
$dataRun->addText('${Cidade}, ', ['color' => $CORES['texto']]);
$dataRun->addText('${DExtenso}', ['color' => $CORES['texto']]);

$section->addTextBreak(1);

// ============================================================
// DADOS DO CLIENTE (Tabela compacta)
// ============================================================

$section->addTitle('Dados do Cliente', 2);

$tblCliente = $section->addTable('Elegante');
$tblCliente->addRow();
$tblCliente->addCell(Converter::cmToTwip(16), ['gridSpan' => 2, 'bgColor' => $CORES['primaria']])
    ->addText('Informações do Contratante', ['bold' => true, 'color' => $CORES['branco'], 'size' => 10]);

$clienteDados = [
    ['Nome/Razão Social', '${nome_cliente_salvo}'],
    ['Contato', '${telefone_salvo} • ${email_salvo}'],
    ['Endereço da Obra', '${endereco_obra}, ${bairro_obra}'],
    ['Localização', '${cidade_obra} - ${estado_obra}'],
];

foreach ($clienteDados as $i => $row) {
    $bg = ($i % 2 == 0) ? $CORES['branco'] : $CORES['fundo'];
    $tblCliente->addRow();
    $tblCliente->addCell(Converter::cmToTwip(4), ['bgColor' => $bg])->addText($row[0], ['bold' => true, 'size' => 10, 'color' => $CORES['secundaria']]);
    $tblCliente->addCell(Converter::cmToTwip(12), ['bgColor' => $bg])->addText($row[1], ['size' => 10]);
}

$section->addTextBreak(1);

// ============================================================
// ESCOPO RESUMIDO (Em vez de metodologia extensa)
// ============================================================

$section->addTitle('Escopo do Serviço', 2);

$section->addText(
    'Levantamento topográfico planialtimétrico por aerofotogrametria, compreendendo voo automatizado, 
    coleta de coordenadas geodésicas de controle, processamento fotogramétrico e vetorização cadastral. 
    O resultado será entregue em sistema georreferenciado de coordenadas geográficas, com precisão 
    compatível à escala de trabalho.',
    'Corpo'
);

$section->addTextBreak(1);

// Produtos entregues (lista compacta, sem emojis)
$section->addText('Entregáveis:', 'Destaque');

$entregaveis = [
    'Ortofoto de alta resolução georreferenciada (RGB)',
    'Modelo Digital de Terreno (MDT) e Curvas de Nível',
    'Planta topográfica em formato DWG/DXF',
    'Relatório técnico de precisão e processamento',
    'ART de responsabilidade técnica'
];

foreach ($entregaveis as $item) {
    $run = $section->addTextRun(['indentation' => ['left' => 360], 'spaceAfter' => 60]);
    $run->addText('• ', ['color' => $CORES['secundaria']]);
    $run->addText($item, ['size' => 10.5]);
}

$section->addTextBreak(1);

// ============================================================
// CRONOGRAMA (Simplificado)
// ============================================================

$section->addTitle('Prazo de Execução', 2);

$section->addText(
    'Prazo estimado de ${prazo_total} dias úteis, contados a partir da data de aprovação da proposta 
    e condições climáticas favoráveis.',
    ['size' => 10.5, 'color' => $CORES['texto']]
);

$section->addTextBreak(0.5);

// Tabela de etapas (sem alternância de cor agressiva)
$tblPrazo = $section->addTable('Elegante');
$tblPrazo->addRow();
$tblPrazo->addCell(Converter::cmToTwip(12), ['bgColor' => $CORES['primaria']])->addText('Etapa', ['bold' => true, 'color' => $CORES['branco'], 'size' => 10]);
$tblPrazo->addCell(Converter::cmToTwip(4), ['bgColor' => $CORES['primaria']])->addText('Duração', ['bold' => true, 'color' => $CORES['branco'], 'size' => 10]);

$etapas = [
    ['Mobilização e voo de campo', '1-2 dias'],
    ['Processamento fotogramétrico', '3-5 dias'],
    ['Vetorização e desenho técnico', '3-5 dias'],
    ['Revisão e entrega', '1 dia'],
];

foreach ($etapas as $etapa) {
    $tblPrazo->addRow();
    $tblPrazo->addCell(Converter::cmToTwip(12))->addText($etapa[0], ['size' => 10]);
    $tblPrazo->addCell(Converter::cmToTwip(4))->addText($etapa[1], ['size' => 10, 'alignment' => Jc::CENTER], ['alignment' => Jc::CENTER]);
}

$section->addTextBreak(1);

// ============================================================
// INVESTIMENTO (Destaque visual só aqui)
// ============================================================

$section->addTitle('Investimento', 2);

// Box de valor destacado mas elegante
$valorTable = $section->addTable([
    'borderSize' => 6,
    'borderColor' => $CORES['secundaria'],
    'cellMargin' => 120
]);

$valorTable->addRow();
$valorCell = $valorTable->addCell(Converter::cmToTwip(16), ['bgColor' => $CORES['fundo']]);

$valorCell->addText('Valor Total', ['size' => 10, 'color' => $CORES['subtitulo'], 'alignment' => Jc::CENTER]);
$valorCell->addText('R$ ${ValorProposta}', 'Valor', ['alignment' => Jc::CENTER, 'spaceAfter' => 60]);
$valorCell->addText('${ValorExtenso}', ['size' => 9, 'italic' => true, 'color' => $CORES['subtitulo']], ['alignment' => Jc::CENTER]);

$section->addTextBreak(1);

// ============================================================
// CONDIÇÕES COMERCIAIS (Texto corrido em vez de tabela)
// ============================================================

$section->addTitle('Condições de Pagamento', 2);

$section->addText(
    '• Sinal de ${mobilizacao_percentual}% na aprovação da proposta: R$ ${mobilizacao_valor}',
    ['size' => 10.5]
);
$section->addText(
    '• Saldo de ${restante_percentual}% na entrega dos produtos: R$ ${restante_valor}',
    ['size' => 10.5]
);

$section->addTextBreak(0.5);

// Dados bancários em formato texto, não tabela
$section->addText('Dados para pagamento:', ['bold' => true, 'size' => 10, 'color' => $CORES['secundaria']]);
$section->addText('Banco ${Banco} • Ag. ${Agencia} • CC ${Conta}', ['size' => 10]);
$section->addText('Titular: ${Empresa} • CNPJ: ${CNPJ}', ['size' => 10]);
$section->addText('PIX: ${PIX}', ['size' => 10, 'bold' => true, 'color' => $CORES['primaria']]);

$section->addTextBreak(1);

// ============================================================
// VALIDADE E FECHAMENTO
// ============================================================

$section->addText(
    'Esta proposta tem validade de 15 (quinze) dias. Ficamos à disposição para esclarecimentos técnicos.',
    ['size' => 10, 'italic' => true, 'color' => $CORES['subtitulo']]
);

$section->addTextBreak(2);

// Assinatura centralizada e limpa
$section->addText('Atenciosamente,', ['size' => 10]);
$section->addTextBreak(1.5);
$section->addText('_______________________________', ['alignment' => Jc::LEFT]);
$section->addText('${Empresa}', ['bold' => true, 'color' => $CORES['primaria']]);
$section->addText('Engenharia e Topografia', ['size' => 9, 'color' => $CORES['subtitulo']]);

// ============================================================
// SALVAR
// ============================================================

$diretorioSaida = __DIR__ . '/modelos_prod/';
$nomeArquivo = 'PropostaDrone_Elegante.docx';

if (!is_dir($diretorioSaida)) mkdir($diretorioSaida, 0755, true);

$caminhoCompleto = $diretorioSaida . $nomeArquivo;

try {
    $objWriter = IOFactory::createWriter($phpWord, 'Word2007');
    $objWriter->save($caminhoCompleto);
    echo "Template refinado gerado com sucesso: $caminhoCompleto";
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}
