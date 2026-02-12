<?php

/**
 * criar_template_drone.php
 * 
 * TEMPLATE ESPECIALIZADO - Drone / Aerofotogrametria
 * Gera um modelo DOCX profissional específico para serviços de mapeamento aéreo.
 */

require_once 'vendor/autoload.php';

use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Shared\Converter;
use PhpOffice\PhpWord\SimpleType\Jc;

// ============================================================
// CONFIGURAÇÕES
// ============================================================

$CORES = [
    'azul_escuro'   => '1B4F72',
    'azul_medio'    => '2E86AB',
    'azul_claro'    => 'D4E6F1',
    'cinza_escuro'  => '333333',
    'cinza_medio'   => '666666',
    'cinza_claro'   => 'F5F5F5',
    'branco'        => 'FFFFFF',
];

$FONTES = ['titulo' => 'Arial', 'corpo' => 'Calibri'];

// ============================================================
// INICIALIZAÇÃO
// ============================================================

$phpWord = new PhpWord();
$phpWord->setDefaultFontName($FONTES['corpo']);
$phpWord->setDefaultFontSize(11);

// Estilos
$phpWord->addTitleStyle(1, [
    'name' => $FONTES['titulo'],
    'size' => 18,
    'bold' => true,
    'color' => $CORES['azul_escuro'],
], ['alignment' => Jc::CENTER, 'spaceAfter' => Converter::pointToTwip(8)]);

$phpWord->addTitleStyle(2, [
    'name' => $FONTES['titulo'],
    'size' => 13,
    'bold' => true,
    'color' => $CORES['azul_medio'],
], ['alignment' => Jc::LEFT, 'spaceAfter' => Converter::pointToTwip(6), 'spaceBefore' => Converter::pointToTwip(12)]);

$phpWord->addParagraphStyle('Corpo', ['alignment' => Jc::BOTH, 'lineHeight' => 1.15, 'spaceAfter' => Converter::pointToTwip(6)]);
$phpWord->addParagraphStyle('Centralizado', ['alignment' => Jc::CENTER]);
$phpWord->addParagraphStyle('ListaItem', ['alignment' => Jc::LEFT, 'indentation' => ['left' => Converter::cmToTwip(0.5)], 'spaceAfter' => Converter::pointToTwip(3)]);

$phpWord->addFontStyle('TextoNormal', ['name' => $FONTES['corpo'], 'size' => 11, 'color' => $CORES['cinza_escuro']]);
$phpWord->addFontStyle('Label', ['name' => $FONTES['corpo'], 'size' => 10, 'bold' => true, 'color' => $CORES['cinza_medio']]);
$phpWord->addFontStyle('Rodape', ['name' => $FONTES['corpo'], 'size' => 9, 'color' => $CORES['cinza_medio']]);
$phpWord->addFontStyle('Bullet', ['name' => $FONTES['corpo'], 'size' => 11, 'color' => $CORES['azul_medio']]);
$phpWord->addFontStyle('Fase', ['name' => $FONTES['titulo'], 'size' => 11, 'bold' => true, 'color' => $CORES['azul_escuro']]);

$tableStyle = 'TabelaProfissional';
$phpWord->addTableStyle($tableStyle, [
    'borderSize' => 6,
    'borderColor' => 'CCCCCC',
    'cellMargin' => Converter::pointToTwip(5),
], ['bgColor' => $CORES['azul_escuro']]);

// ============================================================
// DOCUMENTO
// ============================================================

$section = $phpWord->addSection([
    'marginLeft' => Converter::cmToTwip(2.5),
    'marginRight' => Converter::cmToTwip(2),
    'marginTop' => Converter::cmToTwip(2),
    'marginBottom' => Converter::cmToTwip(2),
]);

// CABEÇALHO
$header = $section->addHeader();
$headerTable = $header->addTable();
$headerTable->addRow();
$headerTable->addCell(Converter::cmToTwip(10))->addText('${logo_empresa}', ['size' => 10]);
$cell2 = $headerTable->addCell(Converter::cmToTwip(6), ['valign' => 'center']);
$run = $cell2->addTextRun(['alignment' => Jc::RIGHT]);
$run->addText('Proposta Nº ', ['size' => 10, 'color' => $CORES['cinza_medio']]);
$run->addText('${numero_proposta}', ['size' => 12, 'bold' => true, 'color' => $CORES['azul_escuro']]);

// RODAPÉ
$footer = $section->addFooter();
$footerRun = $footer->addTextRun(['alignment' => Jc::CENTER]);
$footerRun->addText('${Empresa}', 'Rodape');
$footerRun->addText(' • CNPJ: ${CNPJ} • WhatsApp: ${whatsapp}', 'Rodape');
$footer->addTextRun(['alignment' => Jc::RIGHT])->addField('PAGE');

// ============================================================
// TÍTULO
// ============================================================

$section->addTitle('PROPOSTA DE SERVIÇOS', 1);
$section->addText('Topografia e Mapeamento Aéreo com Drone', [
    'size' => 12,
    'italic' => true,
    'color' => $CORES['cinza_medio']
], 'Centralizado');
$section->addTextBreak(1);
$section->addText('${Cidade}, ${DExrenso}', ['size' => 11, 'color' => $CORES['cinza_escuro']], 'Corpo');
$section->addTextBreak(1);

// ============================================================
// DADOS DO CLIENTE
// ============================================================

$section->addTitle('📌 Dados do Cliente', 2);

$tblCliente = $section->addTable($tableStyle);
$tblCliente->addRow();
$tblCliente->addCell(Converter::cmToTwip(16), ['bgColor' => $CORES['azul_escuro'], 'gridSpan' => 2])
    ->addText('INFORMAÇÕES DO CONTRATANTE', ['bold' => true, 'color' => $CORES['branco'], 'size' => 10], ['alignment' => Jc::CENTER]);

$clienteRows = [
    ['Nome/Razão Social', '${nome_cliente_salvo}'],
    ['E-mail', '${email_salvo}'],
    ['Telefone/Celular', '${telefone_salvo} / ${celular_salvo}'],
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
// LOCAL DA OBRA
// ============================================================

$section->addTitle('📍 Local da Obra', 2);

$tblObra = $section->addTable($tableStyle);
$tblObra->addRow();
$tblObra->addCell(Converter::cmToTwip(16), ['bgColor' => $CORES['azul_escuro'], 'gridSpan' => 2])
    ->addText('DADOS DO IMÓVEL', ['bold' => true, 'color' => $CORES['branco'], 'size' => 10], ['alignment' => Jc::CENTER]);

$obraRows = [
    ['Endereço', '${endereco_obra}'],
    ['Bairro', '${bairro_obra}'],
    ['Cidade/Estado', '${cidade_obra} - ${estado_obra}'],
    ['Área Estimada', '${area_obra}'],
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
// 1. APRESENTAÇÃO
// ============================================================

$section->addTitle('1. Apresentação e Entendimento do Serviço', 2);

$section->addText(
    'A ${Empresa} apresenta esta proposta técnica visando a execução de levantamento topográfico planialtimétrico através de Aerofotogrametria com Drones (VANTs).',
    'TextoNormal',
    'Corpo'
);
$section->addText(
    'Diferente de simples filmagens aéreas, este serviço trata-se de Engenharia de Precisão. O objetivo é gerar uma representação digital fiel do terreno, com coordenadas exatas (Latitude, Longitude e Altitude), servindo de base legal e técnica para projetos de arquitetura, loteamentos, regularização fundiária e cálculos de volume.',
    'TextoNormal',
    'Corpo'
);
$section->addTextBreak(1);

// ============================================================
// 2. METODOLOGIA
// ============================================================

$section->addTitle('2. Metodologia: O Passo a Passo do Mapeamento', 2);

$section->addText(
    'Para garantir que o produto final tenha validade topográfica, seguimos um rigoroso fluxo de trabalho dividido em etapas de campo e escritório:',
    'TextoNormal',
    'Corpo'
);
$section->addTextBreak(1);

// FASE 1
$section->addText('FASE 1: Planejamento e Configuração de Voo (Escritório)', 'Fase', 'Corpo');
$section->addText('Antes de ir a campo, realizamos o estudo da área via satélite. Definimos a altura de voo para garantir a resolução desejada (GSD) e a área de abrangência. O drone segue uma "grade" programada via GPS, garantindo cobertura total do terreno.', 'TextoNormal', 'Corpo');
$section->addTextBreak(1);

// FASE 2
$section->addText('FASE 2: Apoio Terrestre - Pontos de Controle (Campo)', 'Fase', 'Corpo');
$section->addText('Esta é a etapa que diferencia uma foto comum de um mapa topográfico. Nossa equipe distribui e pinta alvos no chão. As coordenadas exatas são coletadas com GPS Geodésico de Alta Precisão (RTK). Esses pontos servem como "âncoras" garantindo precisão centimétrica.', 'TextoNormal', 'Corpo');
$section->addTextBreak(1);

// FASE 3
$section->addText('FASE 3: Execução do Voo e Captura de Dados (Campo)', 'Fase', 'Corpo');
$section->addText('Checklist de segurança: verificação de baterias, hélices, interferência magnética e autorizações DECEA. O drone percorre rota autônoma capturando centenas de fotos em ângulos verticais (nadir) e oblíquos.', 'TextoNormal', 'Corpo');
$section->addTextBreak(1);

// FASE 4
$section->addText('FASE 4: Processamento Fotogramétrico (Escritório)', 'Fase', 'Corpo');
$section->addText('Utilizamos Workstations e softwares específicos: (1) Alinhamento das fotos, (2) Criação da Nuvem de Pontos Densa com milhões de pontos 3D, (3) Georreferenciamento com os Pontos de Controle para precisão milimétrica.', 'TextoNormal', 'Corpo');
$section->addTextBreak(1);

// FASE 5
$section->addText('FASE 5: Vetorização e Desenho Técnico (Escritório - CAD)', 'Fase', 'Corpo');
$section->addText('Desenhista técnico utiliza o modelo 3D para "desenhar" o mapa final em CAD. Vetorização de guias, cercas, edificações, postes, árvores e geração das Curvas de Nível.', 'TextoNormal', 'Corpo');
$section->addTextBreak(1);

// ============================================================
// 3. PRODUTOS ENTREGUES
// ============================================================

$section->addTitle('3. Produtos Entregues (Entregáveis)', 2);

$entregaveis = [
    'Ortomosaico Georreferenciado (TIF/JPG) - "Foto" gigante da área em escala real',
    'MDT (Modelo Digital de Terreno) - Representação 3D do solo para terraplenagem',
    'Curvas de Nível (DWG/DXF) - Arquivo CAD com topografia do terreno',
    'Planta Topográfica Planialtimétrica (PDF) - Mapa finalizado com legendas',
    'Relatório de Processamento - Comprovação da precisão alcançada',
    'ART (Anotação de Responsabilidade Técnica) - Registro no CREA',
];
foreach ($entregaveis as $item) {
    $run = $section->addTextRun('ListaItem');
    $run->addText('● ', 'Bullet');
    $run->addText($item, 'TextoNormal');
}
$section->addTextBreak(1);

// ============================================================
// 4. PRAZOS
// ============================================================

$section->addTitle('4. Prazos Estimados', 2);

$section->addText('O cumprimento dos prazos depende de condições climáticas favoráveis (ausência de chuva e ventos fortes).', ['size' => 10, 'italic' => true, 'color' => $CORES['cinza_medio']], 'Corpo');
$section->addTextBreak(1);

$tblPrazos = $section->addTable($tableStyle);
$tblPrazos->addRow();
$tblPrazos->addCell(Converter::cmToTwip(4), ['bgColor' => $CORES['azul_escuro']])->addText('ETAPA', ['bold' => true, 'color' => $CORES['branco'], 'size' => 10], ['alignment' => Jc::CENTER]);
$tblPrazos->addCell(Converter::cmToTwip(8), ['bgColor' => $CORES['azul_escuro']])->addText('DESCRIÇÃO', ['bold' => true, 'color' => $CORES['branco'], 'size' => 10], ['alignment' => Jc::CENTER]);
$tblPrazos->addCell(Converter::cmToTwip(4), ['bgColor' => $CORES['azul_escuro']])->addText('PRAZO', ['bold' => true, 'color' => $CORES['branco'], 'size' => 10], ['alignment' => Jc::CENTER]);

$prazos = [
    ['1. Mobilização', 'Planejamento e ida a campo', 'Até 02 dias'],
    ['2. Campo', 'Instalação de pontos e Voo', '01 dia'],
    ['3. Processamento', 'Geração da nuvem e modelos', '03 a 05 dias'],
    ['4. Desenho (CAD)', 'Vetorização e Planta Final', '03 a 05 dias'],
];
$zebra = false;
foreach ($prazos as $p) {
    $bg = $zebra ? $CORES['cinza_claro'] : $CORES['branco'];
    $tblPrazos->addRow();
    $tblPrazos->addCell(Converter::cmToTwip(4), ['bgColor' => $bg])->addText($p[0], ['bold' => true, 'size' => 10]);
    $tblPrazos->addCell(Converter::cmToTwip(8), ['bgColor' => $bg])->addText($p[1], 'TextoNormal');
    $tblPrazos->addCell(Converter::cmToTwip(4), ['bgColor' => $bg])->addText($p[2], ['bold' => true, 'color' => $CORES['azul_medio']], ['alignment' => Jc::CENTER]);
    $zebra = !$zebra;
}

// Total
$tblPrazos->addRow();
$tblPrazos->addCell(Converter::cmToTwip(12), ['bgColor' => $CORES['azul_claro'], 'gridSpan' => 2])->addText('TOTAL ESTIMADO', ['bold' => true, 'color' => $CORES['azul_escuro']]);
$tblPrazos->addCell(Converter::cmToTwip(4), ['bgColor' => $CORES['azul_claro']])->addText('07 a 12 dias', ['bold' => true, 'color' => $CORES['azul_escuro']], ['alignment' => Jc::CENTER]);

$section->addTextBreak(1);

// ============================================================
// 5. INVESTIMENTO
// ============================================================

$section->addTitle('5. Investimento', 2);

$valorTable = $section->addTable(['borderSize' => 10, 'borderColor' => $CORES['azul_escuro'], 'cellMargin' => Converter::pointToTwip(8)]);
$valorTable->addRow(Converter::pointToTwip(35));
$valorCell = $valorTable->addCell(Converter::cmToTwip(16), ['bgColor' => $CORES['azul_claro'], 'valign' => 'center']);

$valorCell->addText('VALOR TOTAL DA PROPOSTA', ['size' => 10, 'bold' => true, 'color' => $CORES['azul_escuro']], ['alignment' => Jc::CENTER, 'spaceAfter' => Converter::pointToTwip(4)]);
$valorCell->addText('R$ ${ValorProposta}', ['name' => $FONTES['titulo'], 'size' => 18, 'bold' => true, 'color' => $CORES['azul_escuro']], ['alignment' => Jc::CENTER, 'spaceAfter' => Converter::pointToTwip(4)]);
$valorCell->addText('(${ValorExtenso})', ['size' => 10, 'italic' => true, 'color' => $CORES['cinza_medio']], ['alignment' => Jc::CENTER]);

$section->addTextBreak(1);
$section->addText('Este investimento reflete o custo-benefício da tecnologia: maior riqueza de dados (milhões de pontos) em menor tempo de execução comparado à topografia tradicional.', ['size' => 10, 'italic' => true, 'color' => $CORES['cinza_medio']], 'Corpo');
$section->addTextBreak(1);

// ============================================================
// 6. CONDIÇÕES DE PAGAMENTO
// ============================================================

$section->addTitle('6. Condições de Pagamento', 2);

$tblPagto = $section->addTable($tableStyle);
$tblPagto->addRow();
$tblPagto->addCell(Converter::cmToTwip(6), ['bgColor' => $CORES['azul_escuro']])->addText('ETAPA', ['bold' => true, 'color' => $CORES['branco'], 'size' => 10], ['alignment' => Jc::CENTER]);
$tblPagto->addCell(Converter::cmToTwip(4), ['bgColor' => $CORES['azul_escuro']])->addText('%', ['bold' => true, 'color' => $CORES['branco'], 'size' => 10], ['alignment' => Jc::CENTER]);
$tblPagto->addCell(Converter::cmToTwip(6), ['bgColor' => $CORES['azul_escuro']])->addText('VALOR', ['bold' => true, 'color' => $CORES['branco'], 'size' => 10], ['alignment' => Jc::CENTER]);

$tblPagto->addRow();
$tblPagto->addCell(Converter::cmToTwip(6), ['bgColor' => $CORES['branco']])->addText('Mobilização (Aceite)', 'TextoNormal');
$tblPagto->addCell(Converter::cmToTwip(4), ['bgColor' => $CORES['branco']])->addText('${mobilizacao_percentual}%', ['bold' => true], ['alignment' => Jc::CENTER]);
$tblPagto->addCell(Converter::cmToTwip(6), ['bgColor' => $CORES['branco']])->addText('R$ ${mobilizacao_valor}', ['bold' => true, 'color' => $CORES['azul_medio']], ['alignment' => Jc::RIGHT]);

$tblPagto->addRow();
$tblPagto->addCell(Converter::cmToTwip(6), ['bgColor' => $CORES['cinza_claro']])->addText('Entrega Final', 'TextoNormal');
$tblPagto->addCell(Converter::cmToTwip(4), ['bgColor' => $CORES['cinza_claro']])->addText('${restante_percentual}%', ['bold' => true], ['alignment' => Jc::CENTER]);
$tblPagto->addCell(Converter::cmToTwip(6), ['bgColor' => $CORES['cinza_claro']])->addText('R$ ${restante_valor}', ['bold' => true, 'color' => $CORES['azul_medio']], ['alignment' => Jc::RIGHT]);

$section->addTextBreak(1);

// Dados Bancários
$section->addText('Dados Bancários:', ['bold' => true, 'color' => $CORES['azul_escuro']], 'Corpo');

$tblBanco = $section->addTable($tableStyle);
$bancoRows = [
    ['Banco', '${Banco}'],
    ['Agência / Conta', '${Agencia} / ${Conta}'],
    ['Titular', '${Empresa}'],
    ['CNPJ', '${CNPJ}'],
    ['PIX', '${PIX}'],
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
// 7. EQUIPAMENTOS
// ============================================================

$section->addTitle('7. Equipamentos Previstos', 2);

$tblEquip = $section->addTable($tableStyle);
$tblEquip->addRow();
$tblEquip->addCell(Converter::cmToTwip(5), ['bgColor' => $CORES['azul_escuro']])->addText('EQUIPAMENTO', ['bold' => true, 'color' => $CORES['branco'], 'size' => 10], ['alignment' => Jc::CENTER]);
$tblEquip->addCell(Converter::cmToTwip(11), ['bgColor' => $CORES['azul_escuro']])->addText('DESCRIÇÃO', ['bold' => true, 'color' => $CORES['branco'], 'size' => 10], ['alignment' => Jc::CENTER]);

$equipRows = [
    ['Aeronave (Drone)', '${Drone} - Câmera de Alta Resolução'],
    ['GPS Geodésico', '${GPS} - Receptor GNSS RTK/PPK'],
    ['Estação Total', '${Estacao_Total} - Apoio para áreas de sombra'],
    ['Processamento', 'Workstations com placas gráficas de alto desempenho'],
];
$zebra = false;
foreach ($equipRows as $row) {
    $bg = $zebra ? $CORES['cinza_claro'] : $CORES['branco'];
    $tblEquip->addRow();
    $tblEquip->addCell(Converter::cmToTwip(5), ['bgColor' => $bg])->addText($row[0], ['bold' => true, 'size' => 10]);
    $tblEquip->addCell(Converter::cmToTwip(11), ['bgColor' => $bg])->addText($row[1], 'TextoNormal');
    $zebra = !$zebra;
}
$section->addTextBreak(1);

// ============================================================
// 8. CONSIDERAÇÕES FINAIS
// ============================================================

$section->addTitle('8. Considerações Finais', 2);

$section->addText('Esta proposta tem validade de 15 dias. A ${Empresa} coloca-se à disposição para sanar quaisquer dúvidas técnicas. Garantimos que o produto final entregue será uma ferramenta robusta para o desenvolvimento do seu projeto.', 'TextoNormal', 'Corpo');
$section->addTextBreak(2);

// ============================================================
// ASSINATURA
// ============================================================

$section->addText('Atenciosamente,', 'TextoNormal', 'Corpo');
$section->addTextBreak(2);
$section->addText('_______________________________________', 'TextoNormal', 'Centralizado');
$section->addText('${Empresa}', ['bold' => true, 'color' => $CORES['azul_escuro']], 'Centralizado');
$section->addText('Engenheiro Responsável', 'Rodape', 'Centralizado');
$section->addTextBreak(1);
$section->addText('📞 Contato: ${whatsapp} (WhatsApp)', ['size' => 10, 'color' => $CORES['azul_medio']], 'Centralizado');

// ============================================================
// SALVAR
// ============================================================

$diretorioSaida = __DIR__ . '/modelos_prod/';
$nomeArquivo = 'ModeloPropostaDroneV2.docx';

if (!is_dir($diretorioSaida)) mkdir($diretorioSaida, 0755, true);

$caminhoCompleto = $diretorioSaida . $nomeArquivo;

$debugInfo = [];
$debugInfo['arquivo'] = $nomeArquivo;
$debugInfo['caminho'] = $caminhoCompleto;

try {
    $objWriter = IOFactory::createWriter($phpWord, 'Word2007');
    $objWriter->save($caminhoCompleto);
    $debugInfo['salvou'] = 'SUCESSO';
} catch (Exception $e) {
    $debugInfo['salvou'] = 'ERRO: ' . $e->getMessage();
}

if (file_exists($caminhoCompleto)) {
    $debugInfo['tamanho'] = round(filesize($caminhoCompleto) / 1024, 2) . ' KB';
    $debugInfo['data'] = date('d/m/Y H:i:s', filemtime($caminhoCompleto));
}

// Copia para demo
$diretorioDemo = __DIR__ . '/modelos_demo/';
if (is_dir($diretorioDemo)) copy($caminhoCompleto, $diretorioDemo . $nomeArquivo);

?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Template Drone v2.0</title>
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
                    <div class="card-header bg-primary text-white text-center py-4">
                        <i class="bi bi-airplane fs-1"></i>
                        <h3 class="mt-2 mb-0">Template Drone Gerado!</h3>
                    </div>
                    <div class="card-body p-4">
                        <div class="alert alert-info">
                            <strong>📁 Arquivo:</strong><br>
                            <code><?php echo $caminhoCompleto; ?></code>
                        </div>

                        <div class="alert alert-warning">
                            <strong>🔍 Debug:</strong>
                            <table class="table table-sm table-bordered mt-2 mb-0" style="font-size: 12px;">
                                <?php foreach ($debugInfo as $key => $value): ?>
                                    <tr>
                                        <td><strong><?php echo $key; ?></strong></td>
                                        <td><?php echo $value; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </table>
                        </div>

                        <h6>✅ Conteúdo Incluído:</h6>
                        <ul class="small">
                            <li>Apresentação para Aerofotogrametria</li>
                            <li>Metodologia 5 Fases (Planejamento, Pontos de Controle, Voo, Processamento, CAD)</li>
                            <li>Entregáveis: Ortomosaico, MDT, Curvas de Nível, Planta, Relatório, ART</li>
                            <li>Tabela de Prazos</li>
                            <li>Equipamentos específicos de Drone</li>
                        </ul>

                        <div class="d-flex gap-2 mt-4">
                            <a href="modelos_prod/<?php echo $nomeArquivo; ?>" class="btn btn-primary flex-fill" download>
                                <i class="bi bi-download me-2"></i>Baixar Template Drone
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