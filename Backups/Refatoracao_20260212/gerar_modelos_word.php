<?php
/**
 * GERADOR DE MODELOS WORD (.docx)
 * Cria modelo_curto.docx e modelo_longo.docx com todas as variáveis
 */

require 'vendor/autoload.php';

use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Style\Font;
use PhpOffice\PhpWord\Style\Paragraph;
use PhpOffice\PhpWord\Style\Table;

define('TEMPLATE_PATH', __DIR__ . '/templates/');

// Criar diretório se não existir
if (!is_dir(TEMPLATE_PATH)) {
    mkdir(TEMPLATE_PATH, 0755, true);
}

// ============================================
// MODELO CURTO (Proposta Resumida - 2-3 páginas)
// ============================================
function criarModeloCurto() {
    $phpWord = new PhpWord();
    
    // Configurações globais
    $phpWord->setDefaultFontName('Calibri');
    $phpWord->setDefaultFontSize(11);
    
    // Seção principal
    $section = $phpWord->addSection([
        'marginLeft' => 1134,  // 2cm
        'marginRight' => 1134,
        'marginTop' => 1418,   // 2.5cm
        'marginBottom' => 1418,
    ]);
    
    // CABEÇALHO
    $section->addText('${Empresa}', ['bold' => true, 'size' => 16, 'color' => '2C3E50']);
    $section->addText('Proposta Técnica Comercial', ['bold' => true, 'size' => 14]);
    $section->addText('Proposta Nº: ${NumeroProposta}', ['size' => 11]);
    $section->addTextBreak();
    
    // DATA E LOCAL
    $section->addText('BELO HORIZONTE, ${DataExtenso}', ['bold' => true]);
    $section->addTextBreak();
    
    // DADOS DO CLIENTE
    $section->addText('DADOS DO CLIENTE', ['bold' => true, 'size' => 12, 'bgColor' => 'F8F9FA']);
    $table = $section->addTable();
    $table->addRow();
    $table->addCell(3000)->addText('NOME:');
    $table->addCell(6000)->addText('${ClienteNome}');
    $table->addRow();
    $table->addCell(3000)->addText('E-MAIL:');
    $table->addCell(6000)->addText('${ClienteEmail}');
    $table->addRow();
    $table->addCell(3000)->addText('CONTATO:');
    $table->addCell(6000)->addText('${ClienteTelefone} / ${ClienteCelular}');
    $section->addTextBreak();
    
    // LOCAL DA OBRA
    $section->addText('LOCAL DA OBRA', ['bold' => true, 'size' => 12, 'bgColor' => 'F8F9FA']);
    $table = $section->addTable();
    $table->addRow();
    $table->addCell(3000)->addText('ENDEREÇO:');
    $table->addCell(6000)->addText('${EnderecoObra}');
    $table->addRow();
    $table->addCell(3000)->addText('BAIRRO:');
    $table->addCell(6000)->addText('${BairroObra}');
    $table->addRow();
    $table->addCell(3000)->addText('CIDADE/UF:');
    $table->addCell(6000)->addText('${CidadeObra}');
    $table->addRow();
    $table->addCell(3000)->addText('ÁREA ESTIMADA:');
    $table->addCell(6000)->addText('${AreaEstimada} m²');
    $section->addTextBreak();
    
    // 1. APRESENTAÇÃO
    $section->addText('1. APRESENTAÇÃO', ['bold' => true, 'size' => 12, 'bgColor' => 'F8F9FA']);
    $section->addText(
        'A ${Empresa} apresenta esta proposta técnica visando a execução de ' .
        'levantamento topográfico planialtimétrico através de Aerofotogrametria com Drones. ' .
        'Diferente de simples filmagens aéreas, este serviço trata-se de Engenharia de Precisão. ' .
        'O objetivo é gerar uma representação digital fiel do terreno, com coordenadas exatas ' .
        '(Latitude, Longitude e Altitude), servindo de base legal e técnica para projetos de ' .
        'arquitetura, loteamentos, regularização fundiária e cálculos de volume.'
    );
    $section->addTextBreak();
    
    // 2. FINALIDADE
    $section->addText('2. FINALIDADE', ['bold' => true, 'size' => 12, 'bgColor' => 'F8F9FA']);
    $section->addText(
        'Obter mapeamento aéreo detalhado, ortomosaicos, modelos digitais e nuvens de ' .
        'pontos para análise, planejamento, acompanhamento de obras e levantamento de grandes áreas.'
    );
    $section->addTextBreak();
    
    // 3. ESCOPO DO SERVIÇO - DESTAQUE ESPECIAL
    $section->addText('3. ESCOPO DO SERVIÇO', ['bold' => true, 'size' => 12, 'bgColor' => 'F8F9FA']);
    
    // AQUI ESTÁ A VARIÁVEL DO SERVIÇO COM NEGRITO E FONTE MAIOR
    // No Word, selecione esta variável e aplique: Negrito + Tamanho 14
    $servico = $section->addText('${EscopoServico}', ['bold' => true, 'size' => 14]);
    $section->addTextBreak();
    
    // 4. DOCUMENTAÇÃO GERADA
    $section->addText('4. DOCUMENTAÇÃO GERADA', ['bold' => true, 'size' => 12, 'bgColor' => 'F8F9FA']);
    $section->addText('Serão entregues os seguintes produtos técnicos:');
    $section->addText('• Ortomosaico Georreferenciado (TIF/JPG): "Foto" gigante da área em escala real;');
    $section->addText('• MDT (Modelo Digital de Terreno): Representação 3D do solo para terraplenagem;');
    $section->addText('• Curvas de Nível (DWG/DXF): Arquivo CAD com topografia do terreno;');
    $section->addText('• Planta Topográfica Planialtimétrica (PDF): Mapa finalizado com legendas;');
    $section->addText('• Relatório de Processamento: Comprovação da precisão alcançada;');
    $section->addText('• ART (Anotação de Responsabilidade Técnica): Registro no CREA.');
    $section->addTextBreak();
    
    // 5. METODOLOGIA (Resumida)
    $section->addText('5. METODOLOGIA', ['bold' => true, 'size' => 12, 'bgColor' => 'F8F9FA']);
    $section->addText('1. Planejamento e Configuração de Voo (Escritório)');
    $section->addText('2. Apoio Terrestre - Pontos de Controle (Campo)');
    $section->addText('3. Execução do Voo e Captura de Dados (Campo)');
    $section->addText('4. Processamento Fotogramétrico (Escritório)');
    $section->addText('5. Vetorização e Desenho Técnico (Escritório - CAD)');
    $section->addTextBreak();
    
    // 6. EQUIPAMENTOS - COM MARCAS
    $section->addText('6. EQUIPAMENTOS', ['bold' => true, 'size' => 12, 'bgColor' => 'F8F9FA']);
    $section->addText('${Equipamentos}');
    // Ou individualmente:
    // $section->addText('• Drone: ${DroneMarca}');
    // $section->addText('• GPS: ${GPSMarca}');
    // $section->addText('• Veiculo: ${VeiculoMarca}');
    $section->addTextBreak();
    
    // 7. INVESTIMENTO
    $section->addText('7. INVESTIMENTO', ['bold' => true, 'size' => 12, 'bgColor' => 'F8F9FA']);
    $section->addText('O valor total para execução dos serviços descritos é de:', ['bold' => true]);
    $section->addText('${ValorTotal}', ['bold' => true, 'size' => 16, 'color' => '2C3E50']);
    $section->addText('(${ValorExtenso})', ['italic' => true]);
    $section->addTextBreak();
    
    // 8. CONDIÇÕES DE PAGAMENTO
    $section->addText('8. CONDIÇÕES DE PAGAMENTO', ['bold' => true, 'size' => 12, 'bgColor' => 'F8F9FA']);
    $table = $section->addTable();
    $table->addRow();
    $table->addCell(4000)->addText('Mobilização (Sinal):', ['bold' => true]);
    $table->addCell(5000)->addText('${Sinal} (30% no aceite)');
    $table->addRow();
    $table->addCell(4000)->addText('Entrega Final:', ['bold' => true]);
    $table->addCell(5000)->addText('${ValorFinal} (70% na entrega)');
    $section->addTextBreak();
    
    // DADOS BANCÁRIOS - COMPLETOS
    $section->addText('DADOS BANCÁRIOS', ['bold' => true, 'size' => 12, 'bgColor' => 'F8F9FA']);
    $section->addText('${DadosBancarios}');
    // Ou individualmente:
    // $section->addText('Banco: ${Banco}');
    // $section->addText('Ag/Conta: ${AgenciaConta}');
    // $section->addText('Pix: ${Pix}');
    // $section->addText('Favorecido: ${Favorecido}');
    $section->addTextBreak();
    
    // 9. CONSIDERAÇÕES FINAIS
    $section->addText('9. CONSIDERAÇÕES FINAIS', ['bold' => true, 'size' => 12, 'bgColor' => 'F8F9FA']);
    $section->addText('Validade da Proposta: ${Validade}');
    $section->addText('Prazo de Início: Imediato após o aceite e disponibilidade da área.');
    $section->addText('Acesso à Obra: O contratante deverá garantir o livre acesso da equipe ao local do serviço.');
    $section->addTextBreak(2);
    
    // ASSINATURA
    $section->addText('Atenciosamente,', ['bold' => true]);
    $section->addTextBreak(2);
    $section->addText('_________________________________', ['bold' => true]);
    $section->addText('${Engenheiro}', ['bold' => true]);
    $section->addText('Engenheiro Responsável');
    $section->addText('CREA: ${EngenheiroCREA}');
    
    // Salvar
    $objWriter = IOFactory::createWriter($phpWord, 'Word2007');
    $objWriter->save(TEMPLATE_PATH . 'modelo_curto.docx');
    
    echo "✅ Modelo CURTO criado: templates/modelo_curto.docx\n";
}

// ============================================
// MODELO LONGO (Proposta Detalhada - 5-8 páginas)
// ============================================
function criarModeloLongo() {
    $phpWord = new PhpWord();
    
    $phpWord->setDefaultFontName('Calibri');
    $phpWord->setDefaultFontSize(11);
    
    $section = $phpWord->addSection([
        'marginLeft' => 1134,
        'marginRight' => 1134,
        'marginTop' => 1418,
        'marginBottom' => 1418,
    ]);
    
    // CAPA
    $section->addTextBreak(3);
    $section->addText('${Empresa}', ['bold' => true, 'size' => 24, 'color' => '2C3E50', 'alignment' => 'center']);
    $section->addTextBreak();
    $section->addText('PROPOSTA TÉCNICA COMERCIAL', ['bold' => true, 'size' => 18, 'alignment' => 'center']);
    $section->addTextBreak();
    $section->addText('${NumeroProposta}', ['size' => 14, 'alignment' => 'center']);
    $section->addTextBreak(2);
    $section->addText('Cliente: ${ClienteNome}', ['size' => 14, 'alignment' => 'center']);
    $section->addText('${DataExtenso}', ['size' => 12, 'alignment' => 'center']);
    $section->addPageBreak();
    
    // SUMÁRIO EXECUTIVO
    $section->addText('SUMÁRIO EXECUTIVO', ['bold' => true, 'size' => 16, 'color' => '2C3E50']);
    $section->addText(
        'A ${Empresa} apresenta solução completa em levantamento topográfico de alta precisão ' .
        'utilizando tecnologia de Aerofotogrametria com Drones. Nossa metodologia garante ' .
        'precisão centimétrica, redução de prazos e entrega de produtos digitais compatíveis ' .
        'com os principais softwares de CAD e SIG do mercado.'
    );
    $section->addTextBreak();
    
    // 1. APRESENTAÇÃO DA EMPRESA
    $section->addText('1. APRESENTAÇÃO DA EMPRESA', ['bold' => true, 'size' => 14, 'color' => '2C3E50']);
    $section->addText('${Empresa} - CNPJ: ${EmpresaCNPJ}');
    $section->addText('Telefone: ${EmpresaTelefone} | WhatsApp: ${EmpresaWhatsApp}');
    $section->addText('${EmpresaEndereco}');
    $section->addTextBreak();
    
    // 2. DADOS DO CLIENTE E OBRA
    $section->addText('2. DADOS DO CLIENTE E OBRA', ['bold' => true, 'size' => 14, 'color' => '2C3E50']);
    $table = $section->addTable(['borderSize' => 6, 'borderColor' => '999999']);
    $table->addRow();
    $table->addCell(3000, ['bgColor' => 'F8F9FA'])->addText('Cliente:', ['bold' => true]);
    $table->addCell(6000)->addText('${ClienteNome}');
    $table->addRow();
    $table->addCell(3000, ['bgColor' => 'F8F9FA'])->addText('E-mail:', ['bold' => true]);
    $table->addCell(6000)->addText('${ClienteEmail}');
    $table->addRow();
    $table->addCell(3000, ['bgColor' => 'F8F9FA'])->addText('Telefone:', ['bold' => true]);
    $table->addCell(6000)->addText('${ClienteTelefone}');
    $table->addRow();
    $table->addCell(3000, ['bgColor' => 'F8F9FA'])->addText('Endereço da Obra:', ['bold' => true]);
    $table->addCell(6000)->addText('${EnderecoObra}, ${BairroObra} - ${CidadeObra}');
    $table->addRow();
    $table->addCell(3000, ['bgColor' => 'F8F9FA'])->addText('Área Estimada:', ['bold' => true]);
    $table->addCell(6000)->addText('${AreaEstimada} m²');
    $section->addTextBreak();
    
    // 3. OBJETIVO E ESCOPO DETALHADO
    $section->addText('3. OBJETIVO E ESCOPO DETALHADO', ['bold' => true, 'size' => 14, 'color' => '2C3E50']);
    $section->addText('Serviço Contratado:', ['bold' => true]);
    
    // SERVIÇO COM DESTAQUE
    $servico = $section->addText('${EscopoServico}', ['bold' => true, 'size' => 13, 'color' => '2C3E50']);
    $section->addTextBreak();
    
    $section->addText('Descrição Detalhada:', ['bold' => true]);
    $section->addText('${DescricaoServico}');
    $section->addTextBreak();
    
    // 4. METODOLOGIA DETALHADA
    $section->addText('4. METODOLOGIA DETALHADA', ['bold' => true, 'size' => 14, 'color' => '2C3E50']);
    
    $section->addText('ETAPA 1: PLANEJAMENTO E CONFIGURAÇÃO DE VOO (Escritório)', ['bold' => true, 'size' => 12]);
    $section->addText(
        '• Estudo preliminar da área via imagens de satélite;' .
        '\n• Definição da altitude de voo para garantir a resolução desejada (GSD);' .
        '\n• Planejamento da malha de fotos com sobreposição longitudinal e lateral;' .
        '\n• Cálculo de tempo de voo e quantidade de baterias necessárias.'
    );
    $section->addTextBreak();
    
    $section->addText('ETAPA 2: APOIO TERRESTRE - PONTOS DE CONTROLE (Campo)', ['bold' => true, 'size' => 12]);
    $section->addText(
        '• Distribuição estratégica de alvos de controle no terreno;' .
        '\n• Coleta de coordenadas geodésicas com GPS de alta precisão (RTK/PPK);' .
        '\n• Determinação de marcos temporários para amarração do levantamento.'
    );
    $section->addTextBreak();
    
    $section->addText('ETAPA 3: EXECUÇÃO DO VOO E CAPTURA DE DADOS (Campo)', ['bold' => true, 'size' => 12]);
    $section->addText(
        '• Checklist de segurança conforme normas DECEA;' .
        '\n• Voo autônomo programado via software de missão;' .
        '\n• Captura de imagens em ângulo nadir (90°) e oblíquo;' .
        '\n• Monitoramento em tempo real da qualidade das imagens.'
    );
    $section->addTextBreak();
    
    $section->addText('ETAPA 4: PROCESSAMENTO FOTOGRAMÉTRICO (Escritório)', ['bold' => true, 'size' => 12]);
    $section->addText(
        '• Triangulação aérea e alinhamento das fotos;' .
        '\n• Geração de Nuvem de Pontos Densa (milhões de pontos 3D);' .
        '\n• Classificação automática de pontos do solo;' .
        '\n• Geração de Modelo Digital de Terreno (MDT) e Superfície (MDS);' .
        '\n• Ortorretificação e geração do Ortomosaico georreferenciado.'
    );
    $section->addTextBreak();
    
    $section->addText('ETAPA 5: VETORIZAÇÃO E DESENHO TÉCNICO (Escritório)', ['bold' => true, 'size' => 12]);
    $section->addText(
        '• Importação do MDT e Ortomosaico em software CAD;' .
        '\n• Vetorização de elementos cartográficos (edificações, cercas, postes, etc.);' .
        '\n• Geração de curvas de nível equidistantes conforme escala;' .
        '\n• Elaboração de planta topográfica com legenda, escala e norte.'
    );
    $section->addTextBreak();
    
    // 5. EQUIPAMENTOS E TECNOLOGIA
    $section->addText('5. EQUIPAMENTOS E TECNOLOGIA', ['bold' => true, 'size' => 14, 'color' => '2C3E50']);
    $section->addText('Equipamentos utilizados neste projeto:');
    $section->addTextBreak();
    
    // TABELA DE EQUIPAMENTOS COM MARCAS
    $table = $section->addTable(['borderSize' => 6, 'borderColor' => '999999']);
    $table->addRow();
    $table->addCell(3000, ['bgColor' => 'F8F9FA'])->addText('Equipamento', ['bold' => true]);
    $table->addCell(6000, ['bgColor' => 'F8F9FA'])->addText('Marca/Modelo', ['bold' => true]);
    
    $table->addRow();
    $table->addCell(3000)->addText('Drone');
    $table->addCell(6000)->addText('${DroneMarca}');
    
    $table->addRow();
    $table->addCell(3000)->addText('GPS Geodésico');
    $table->addCell(6000)->addText('${GPSMarca}');
    
    $table->addRow();
    $table->addCell(3000)->addText('Veículo de Apoio');
    $table->addCell(6000)->addText('${VeiculoMarca}');
    $section->addTextBreak();
    
    // 6. PRODUTOS ENTREGÁVEIS
    $section->addText('6. PRODUTOS ENTREGÁVEIS', ['bold' => true, 'size' => 14, 'color' => '2C3E50']);
    $section->addText('Ao final do projeto, serão entregues:');
    
    $produtos = [
        ['Ortomosaico Georreferenciado', 'Arquivo GeoTIFF e JPEG', 'Resolução GSD conforme especificação'],
        ['Modelo Digital de Terreno (MDT)', 'Arquivo LAS, XYZ ou DWG', 'Precisão altimétrica ±5cm'],
        ['Curvas de Nível', 'Arquivo DWG/DXF', 'Equidistância conforme escala'],
        ['Planta Topográfica', 'PDF e DWG', 'Formato A1 ou A0 com legenda completa'],
        ['Relatório Técnico', 'PDF', 'Comprovação da precisão alcançada'],
        ['ART', 'Documento digital', 'Anotação de Responsabilidade Técnica no CREA']
    ];
    
    $table = $section->addTable(['borderSize' => 6, 'borderColor' => '999999']);
    $table->addRow();
    $table->addCell(2500, ['bgColor' => 'F8F9FA'])->addText('Produto', ['bold' => true]);
    $table->addCell(2500, ['bgColor' => 'F8F9FA'])->addText('Formato', ['bold' => true]);
    $table->addCell(4000, ['bgColor' => 'F8F9FA'])->addText('Especificações', ['bold' => true]);
    
    foreach ($produtos as $prod) {
        $table->addRow();
        $table->addCell(2500)->addText($prod[0]);
        $table->addCell(2500)->addText($prod[1]);
        $table->addCell(4000)->addText($prod[2]);
    }
    $section->addTextBreak();
    
    // 7. PRAZO E CRONOGRAMA
    $section->addText('7. PRAZO E CRONOGRAMA', ['bold' => true, 'size' => 14, 'color' => '2C3E50']);
    $section->addText('Prazo total de execução: ${PrazoEntrega}');
    $section->addText('• Etapas 1-2 (Planejamento e campo): 40% do prazo');
    $section->addText('• Etapas 3-5 (Processamento e entrega): 60% do prazo');
    $section->addTextBreak();
    
    // 8. INVESTIMENTO E CONDIÇÕES COMERCIAIS
    $section->addText('8. INVESTIMENTO E CONDIÇÕES COMERCIAIS', ['bold' => true, 'size' => 14, 'color' => '2C3E50']);
    $section->addText('Valor Global do Serviço:', ['bold' => true, 'size' => 12]);
    $section->addText('${ValorTotal}', ['bold' => true, 'size' => 18, 'color' => '2C3E50']);
    $section->addText('(${ValorExtenso})', ['italic' => true]);
    $section->addTextBreak();
    
    $section->addText('Condições de Pagamento:', ['bold' => true]);
    $table = $section->addTable(['borderSize' => 6, 'borderColor' => '999999']);
    $table->addRow();
    $table->addCell(2000, ['bgColor' => 'F8F9FA'])->addText('Parcela', ['bold' => true]);
    $table->addCell(2000, ['bgColor' => 'F8F9FA'])->addText('Valor', ['bold' => true]);
    $table->addCell(2000, ['bgColor' => 'F8F9FA'])->addText('Percentual', ['bold' => true]);
    $table->addCell(3000, ['bgColor' => 'F8F9FA'])->addText('Condição', ['bold' => true]);
    
    $table->addRow();
    $table->addCell(2000)->addText('Sinal/Mobilização');
    $table->addCell(2000)->addText('${Sinal}');
    $table->addCell(2000)->addText('30%');
    $table->addCell(3000)->addText('No aceite da proposta');
    
    $table->addRow();
    $table->addCell(2000)->addText('Entrega Final');
    $table->addCell(2000)->addText('${ValorFinal}');
    $table->addCell(2000)->addText('70%');
    $table->addCell(3000)->addText('Na entrega dos produtos');
    $section->addTextBreak();
    
    // DADOS BANCÁRIOS DETALHADOS
    $section->addText('Dados para Pagamento:', ['bold' => true, 'size' => 12]);
    $table = $section->addTable(['borderSize' => 6, 'borderColor' => '999999', 'width' => 9000]);
    $table->addRow();
    $table->addCell(2500, ['bgColor' => 'F8F9FA'])->addText('Banco:', ['bold' => true]);
    $table->addCell(6500)->addText('${Banco}');
    
    $table->addRow();
    $table->addCell(2500, ['bgColor' => 'F8F9FA'])->addText('Agência/Conta:', ['bold' => true]);
    $table->addCell(6500)->addText('${AgenciaConta}');
    
    $table->addRow();
    $table->addCell(2500, ['bgColor' => 'F8F9FA'])->addText('Chave Pix:', ['bold' => true]);
    $table->addCell(6500)->addText('${Pix}');
    
    $table->addRow();
    $table->addCell(2500, ['bgColor' => 'F8F9FA'])->addText('Favorecido:', ['bold' => true]);
    $table->addCell(6500)->addText('${Favorecido}');
    $section->addTextBreak();
    
    // 9. CONDIÇÕES GERAIS
    $section->addText('9. CONDIÇÕES GERAIS', ['bold' => true, 'size' => 14, 'color' => '2C3E50']);
    $section->addText('• Validade da Proposta: ${Validade}');
    $section->addText('• Prazo de Início: Imediato após aceite e disponibilidade da área');
    $section->addText('• Acesso à Obra: O contratante garante livre acesso da equipe');
    $section->addText('• Força Maior: Prazos podem ser renegociados em caso de condições climáticas adversas');
    $section->addText('• Confidencialidade: Todos os dados do projeto são tratados como confidenciais');
    $section->addTextBreak();
    
    // ASSINATURAS
    $section->addText('10. ACEITE DA PROPOSTA', ['bold' => true, 'size' => 14, 'color' => '2C3E50']);
    $section->addText('Ao assinar esta proposta, o cliente declara ciência e concordância com todas as condições aqui estabelecidas.');
    $section->addTextBreak(2);
    
    $table = $section->addTable();
    $table->addRow();
    $table->addCell(4500)->addText('_________________________________', ['bold' => true]);
    $table->addCell(4500)->addText('_________________________________', ['bold' => true]);
    $table->addRow();
    $table->addCell(4500)->addText('${Empresa}', ['bold' => true]);
    $table->addCell(4500)->addText('${ClienteNome}', ['bold' => true]);
    $table->addRow();
    $table->addCell(4500)->addText('${Engenheiro} - CREA: ${EngenheiroCREA}');
    $table->addCell(4500)->addText('CPF/CNPJ: _________________________');
    $table->addRow();
    $table->addCell(4500)->addText('Data: ${DataHoje}');
    $table->addCell(4500)->addText('Data: ____/____/________');
    
    // Salvar
    $objWriter = IOFactory::createWriter($phpWord, 'Word2007');
    $objWriter->save(TEMPLATE_PATH . 'modelo_longo.docx');
    
    echo "✅ Modelo LONGO criado: templates/modelo_longo.docx\n";
}

// ============================================
// EXECUTAR
// ============================================
echo "<pre>";
echo "GERANDO MODELOS WORD...\n";
echo "========================\n\n";

try {
    criarModeloCurto();
    criarModeloLongo();
    
    echo "\n========================\n";
    echo "✅ MODELOS CRIADOS COM SUCESSO!\n";
    echo "📁 Local: " . TEMPLATE_PATH . "\n";
    echo "\n📋 VARIÁVEIS DISPONÍVEIS:\n";
    echo "• Empresa, ClienteNome, ClienteEmail, ClienteTelefone\n";
    echo "• NumeroProposta, DataHoje, DataExtenso\n";
    echo "• EscopoServico (com negrito automático)\n";
    echo "• Equipamentos, DroneMarca, GPSMarca, VeiculoMarca\n";
    echo "• DadosBancarios, Banco, AgenciaConta, Pix, Favorecido\n";
    echo "• ValorTotal, ValorExtenso, Sinal, ValorFinal\n";
    echo "• PrazoEntrega, Validade, Engenheiro, EngenheiroCREA\n";
    
} catch (Exception $e) {
    echo "❌ ERRO: " . $e->getMessage();
}
echo "</pre>";
?>
