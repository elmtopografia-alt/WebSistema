<?php

namespace ProposalArchitect\Templates;

require_once __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Shared\Converter;
use PhpOffice\PhpWord\SimpleType\Jc;

class GeradorTemplateDrone
{
    public static function gerar($caminhoSaida)
    {
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

        // SEÇÃO
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

        // TÍTULO
        $section->addTitle('PROPOSTA DE SERVIÇOS', 1);
        $section->addText('Topografia e Mapeamento Aéreo com Drone', [
            'size' => 12,
            'italic' => true,
            'color' => $CORES['cinza_medio']
        ], 'Centralizado');
        $section->addTextBreak(1);
        $section->addText('${Cidade}, ${DExrenso}', ['size' => 11, 'color' => $CORES['cinza_escuro']], 'Corpo');
        $section->addTextBreak(1);

        // DADOS DO CLIENTE
        $section->addTitle('📌 Dados do Cliente', 2);
        $tblCliente = $section->addTable($tableStyle);
        $tblCliente->addRow();
        $tblCliente->addCell(Converter::cmToTwip(16), ['bgColor' => $CORES['azul_escuro'], 'gridSpan' => 2])
            ->addText('INFORMAÇÕES DO CONTRATANTE', ['bold' => true, 'color' => $CORES['branco'], 'size' => 10], ['alignment' => Jc::CENTER]);
        
        $camposCliente = [
            ['Nome/Razão Social', '${nome_cliente_salvo}'],
            ['E-mail', '${email_salvo}'],
            ['Telefone/Celular', '${telefone_salvo} / ${celular_salvo}'],
            ['WhatsApp', '${whatsapp_salvo}']
        ];
        
        $zebra = false;
        foreach ($camposCliente as $row) {
            $bg = $zebra ? $CORES['cinza_claro'] : $CORES['branco'];
            $tblCliente->addRow();
            $tblCliente->addCell(Converter::cmToTwip(4), ['bgColor' => $bg])->addText($row[0], 'Label');
            $tblCliente->addCell(Converter::cmToTwip(12), ['bgColor' => $bg])->addText($row[1], 'TextoNormal');
            $zebra = !$zebra;
        }
        $section->addTextBreak(1);

        // LOCAL DA OBRA
        $section->addTitle('📍 Local da Obra', 2);
        $tblObra = $section->addTable($tableStyle);
        $tblObra->addRow();
        $tblObra->addCell(Converter::cmToTwip(16), ['bgColor' => $CORES['azul_escuro'], 'gridSpan' => 2])
            ->addText('DADOS DO IMÓVEL', ['bold' => true, 'color' => $CORES['branco'], 'size' => 10], ['alignment' => Jc::CENTER]);
        
        $camposObra = [
            ['Endereço', '${endereco_obra}'],
            ['Bairro', '${bairro_obra}'],
            ['Cidade/Estado', '${cidade_obra} - ${estado_obra}'],
            ['Área Estimada', '${area_obra}']
        ];
        
        $zebra = false;
        foreach ($camposObra as $row) {
            $bg = $zebra ? $CORES['cinza_claro'] : $CORES['branco'];
            $tblObra->addRow();
            $tblObra->addCell(Converter::cmToTwip(4), ['bgColor' => $bg])->addText($row[0], 'Label');
            $tblObra->addCell(Converter::cmToTwip(12), ['bgColor' => $bg])->addText($row[1], 'TextoNormal');
            $zebra = !$zebra;
        }
        $section->addTextBreak(1);

        // CONTEÚDO DINÂMICO (Substituindo texto hardcoded por variáveis do editor)
        // 1. APRESENTAÇÃO
        $section->addTitle('1. Apresentação', 2);
        $section->addText('${apresentacao}', 'TextoNormal', 'Corpo');
        $section->addTextBreak(1);

        // 2. METODOLOGIA
        $section->addTitle('2. Metodologia', 2);
        $section->addText('${metodologia}', 'TextoNormal', 'Corpo');
        $section->addTextBreak(1);

        // 3. PRODUTOS
        $section->addTitle('3. Documentação e Produtos', 2);
        $section->addText('${documentacao}', 'TextoNormal', 'Corpo');
        $section->addTextBreak(1);

        // 4. PRAZOS (Tabela Dinâmica ou Fixa)
        $section->addTitle('4. Prazos Estimados', 2);
        // Aqui mantemos a estrutura de tabela placeholder, mas o conteúdo pode vir da var se o processador permitir HTML (Processor não permite HTML direto em valor simples).
        // SOLUÇÃO: Vamos manter a tabela fixa de prazos mas com valores variáveis onde possível, ou instruir o usuário que a tabela é fixa no template.
        // Como o usuário gostou do modelo do Drone, vamos manter a tabela rica do Drone.
        
        $section->addText('O cumprimento dos prazos depende de condições climáticas favoráveis.', ['size' => 10, 'italic' => true, 'color' => $CORES['cinza_medio']], 'Corpo');
        
        $tblPrazos = $section->addTable($tableStyle);
        $tblPrazos->addRow();
        $tblPrazos->addCell(Converter::cmToTwip(4), ['bgColor' => $CORES['azul_escuro']])->addText('ETAPA', ['bold' => true, 'color' => $CORES['branco'], 'size' => 10]);
        $tblPrazos->addCell(Converter::cmToTwip(8), ['bgColor' => $CORES['azul_escuro']])->addText('DESCRIÇÃO', ['bold' => true, 'color' => $CORES['branco'], 'size' => 10]);
        $tblPrazos->addCell(Converter::cmToTwip(4), ['bgColor' => $CORES['azul_escuro']])->addText('PRAZO', ['bold' => true, 'color' => $CORES['branco'], 'size' => 10]);

        // Linhas de Prazo (Fixas do Template Drone ou Variáveis?)
        // Vamos usar variáveis para flexibilidade, mas com defaults do contexto Drone
        $tblPrazos->addRow();
        $tblPrazos->addCell(Converter::cmToTwip(4))->addText('1. Mobilização', 'Label');
        $tblPrazos->addCell(Converter::cmToTwip(8))->addText('Planejamento e ida a campo', 'TextoNormal');
        $tblPrazos->addCell(Converter::cmToTwip(4))->addText('2 dias', 'TextoNormal'); // Hardcoded por enquanto, difícil mapear array dinâmico no TemplateProcessor sem cloneRow

        $tblPrazos->addRow();
        $tblPrazos->addCell(Converter::cmToTwip(4), ['bgColor' => $CORES['cinza_claro']])->addText('2. Execução', 'Label');
        $tblPrazos->addCell(Converter::cmToTwip(8), ['bgColor' => $CORES['cinza_claro']])->addText('Levantamento de Campo', 'TextoNormal');
        $tblPrazos->addCell(Converter::cmToTwip(4), ['bgColor' => $CORES['cinza_claro']])->addText('${dias_campo} dias', 'TextoNormal');

        $tblPrazos->addRow();
        $tblPrazos->addCell(Converter::cmToTwip(4))->addText('3. Escritório', 'Label');
        $tblPrazos->addCell(Converter::cmToTwip(8))->addText('Processamento e Desenho', 'TextoNormal');
        $tblPrazos->addCell(Converter::cmToTwip(4))->addText('${dias_escritorio} dias', 'TextoNormal');

        $tblPrazos->addRow();
        $tblPrazos->addCell(Converter::cmToTwip(12), ['bgColor' => $CORES['azul_claro'], 'gridSpan' => 2])->addText('TOTAL ESTIMADO', ['bold' => true, 'color' => $CORES['azul_escuro']]);
        $tblPrazos->addCell(Converter::cmToTwip(4), ['bgColor' => $CORES['azul_claro']])->addText('${prazo_execucao}', ['bold' => true, 'color' => $CORES['azul_escuro']]);
        
        $section->addTextBreak(1);

        // 5. INVESTIMENTO
        $section->addTitle('5. Investimento', 2);
        $valorTable = $section->addTable(['borderSize' => 10, 'borderColor' => $CORES['azul_escuro'], 'cellMargin' => Converter::pointToTwip(8)]);
        $valorTable->addRow(Converter::pointToTwip(35));
        $valorCell = $valorTable->addCell(Converter::cmToTwip(16), ['bgColor' => $CORES['azul_claro'], 'valign' => 'center']);
        $valorCell->addText('VALOR TOTAL DA PROPOSTA', ['size' => 10, 'bold' => true, 'color' => $CORES['azul_escuro']], ['alignment' => Jc::CENTER]);
        $valorCell->addText('R$ ${ValorProposta}', ['name' => $FONTES['titulo'], 'size' => 18, 'bold' => true, 'color' => $CORES['azul_escuro']], ['alignment' => Jc::CENTER]);
        $valorCell->addText('(${ValorExtenso})', ['size' => 10, 'italic' => true, 'color' => $CORES['cinza_medio']], ['alignment' => Jc::CENTER]);
        $section->addTextBreak(1);

        // 6. PAGAMENTO
        $section->addTitle('6. Condições de Pagamento', 2);
        $tblPagto = $section->addTable($tableStyle);
        $tblPagto->addRow();
        $tblPagto->addCell(Converter::cmToTwip(6), ['bgColor' => $CORES['azul_escuro']])->addText('ETAPA', ['bold' => true, 'color' => $CORES['branco'], 'size' => 10]);
        $tblPagto->addCell(Converter::cmToTwip(4), ['bgColor' => $CORES['azul_escuro']])->addText('%', ['bold' => true, 'color' => $CORES['branco'], 'size' => 10], ['alignment' => Jc::CENTER]);
        $tblPagto->addCell(Converter::cmToTwip(6), ['bgColor' => $CORES['azul_escuro']])->addText('VALOR', ['bold' => true, 'color' => $CORES['branco'], 'size' => 10], ['alignment' => Jc::RIGHT]);
        
        $tblPagto->addRow();
        $tblPagto->addCell(Converter::cmToTwip(6))->addText('Mobilização', 'TextoNormal');
        $tblPagto->addCell(Converter::cmToTwip(4))->addText('${mobilizacao_percentual}%', ['bold' => true], ['alignment' => Jc::CENTER]);
        $tblPagto->addCell(Converter::cmToTwip(6))->addText('R$ ${mobilizacao_valor}', ['bold' => true], ['alignment' => Jc::RIGHT]);

        $tblPagto->addRow();
        $tblPagto->addCell(Converter::cmToTwip(6), ['bgColor' => $CORES['cinza_claro']])->addText('Entrega Final', 'TextoNormal');
        $tblPagto->addCell(Converter::cmToTwip(4), ['bgColor' => $CORES['cinza_claro']])->addText('${restante_percentual}%', ['bold' => true], ['alignment' => Jc::CENTER]);
        $tblPagto->addCell(Converter::cmToTwip(6), ['bgColor' => $CORES['cinza_claro']])->addText('R$ ${restante_valor}', ['bold' => true], ['alignment' => Jc::RIGHT]);
        $section->addTextBreak(1);

        // DADOS BANCÁRIOS
        $tblBanco = $section->addTable($tableStyle);
        $bancoRows = [
            ['Banco', '${Banco}'],
            ['Agência / Conta', '${Agencia} / ${Conta}'],
            ['Titular', '${Empresa}'],
            ['CNPJ', '${CNPJ}'],
            ['PIX', '${PIX}']
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

        // 7. EQUIPAMENTOS
        $section->addTitle('7. Equipamentos e Tecnologia', 2);
        $tblEquip = $section->addTable($tableStyle);
        $tblEquip->addRow();
        $tblEquip->addCell(Converter::cmToTwip(5), ['bgColor' => $CORES['azul_escuro']])->addText('ITEM', ['bold' => true, 'color' => $CORES['branco'], 'size' => 10]);
        $tblEquip->addCell(Converter::cmToTwip(11), ['bgColor' => $CORES['azul_escuro']])->addText('DETALHES', ['bold' => true, 'color' => $CORES['branco'], 'size' => 10]);

        $equipRows = [
            ['Aeronave', '${Drone}'],
            ['GNSS RTK', '${GPS}'],
            ['Estação Total', '${Estacao_Total}'],
            ['Veículo', '${Veiculo}']
        ];
        $zebra = false;
        foreach ($equipRows as $row) {
            $bg = $zebra ? $CORES['cinza_claro'] : $CORES['branco'];
            $tblEquip->addRow();
            $tblEquip->addCell(Converter::cmToTwip(5), ['bgColor' => $bg])->addText($row[0], ['bold' => true, 'size' => 10]);
            $tblEquip->addCell(Converter::cmToTwip(11), ['bgColor' => $bg])->addText($row[1], 'TextoNormal');
            $zebra = !$zebra;
        }
        $section->addTextBreak(2);

        // ASSINATURA
        $section->addTitle('8. Considerações Finais', 2);
        // Usar variável de considerações ou texto fixo? Melhor variável
        $section->addText('${consideracoes_content}', 'TextoNormal', 'Corpo'); 
        $section->addTextBreak(2);

        $section->addText('Atenciosamente,', 'TextoNormal', 'Corpo');
        $section->addTextBreak(2);
        $section->addText('_______________________________________', 'TextoNormal', 'Centralizado');
        $section->addText('${Empresa}', ['bold' => true, 'color' => $CORES['azul_escuro']], 'Centralizado');
        $section->addText('Engenharia e Projetos', 'Rodape', 'Centralizado');

        // Salvando
        $objWriter = IOFactory::createWriter($phpWord, 'Word2007');
        $objWriter->save($caminhoSaida);
        
        return file_exists($caminhoSaida);
    }
}
