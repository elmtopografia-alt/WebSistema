<?php
/**
 * PropostaPHPWord - Gerador de Propostas em Word (.docx)
 * Sistema SGT - Propostas Profissionais de Topografia
 * 
 * @requires phpoffice/phpword
 */

require_once __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\SimpleType\Jc;

class PropostaPHPWord {
    private $phpWord;
    private $dados;
    private $estilos;
    
    public function __construct($dados) {
        $this->dados = $dados;
        $this->phpWord = new PhpWord();
        $this->configurarEstilos();
    }
    
    private function configurarEstilos() {
        // Cores do tema SGT
        $this->estilos = [
            'azul_marinho' => '1E3A5F',
            'dourado' => 'D4AF37',
            'cinza_claro' => 'F8F9FA',
            'verde' => '27AE60',
            'vermelho' => 'E74C3C'
        ];
        
        // Estilos de fonte padrão
        $this->phpWord->setDefaultFontName('Calibri');
        $this->phpWord->setDefaultFontSize(11);
        
        // Estilos de título
        $this->phpWord->addTitleStyle(1, [
            'name' => 'Montserrat',
            'size' => 36,
            'bold' => true,
            'color' => $this->estilos['azul_marinho']
        ]);
        
        $this->phpWord->addTitleStyle(2, [
            'name' => 'Montserrat',
            'size' => 16,
            'bold' => true,
            'color' => $this->estilos['azul_marinho']
        ]);
    }
    
    public function gerar($caminho_saida) {
        // Seção 1: Cabeçalho
        $sectionHeader = $this->phpWord->addSection([
            'marginTop' => 0,
            'marginBottom' => 0,
            'marginLeft' => 0,
            'marginRight' => 0
        ]);
        
        $this->adicionarCabecalho($sectionHeader);
        
        // Seção 2: Conteúdo
        $sectionContent = $this->phpWord->addSection([
            'marginTop' => 400,
            'marginBottom' => 400,
            'marginLeft' => 800,
            'marginRight' => 800
        ]);
        
        $this->adicionarDadosCliente($sectionContent);
        $this->adicionarApresentacao($sectionContent);
        $this->adicionarEscopo($sectionContent);
        $this->adicionarMetodologia($sectionContent);
        $this->adicionarInvestimento($sectionContent);
        $this->adicionarRodape($sectionContent);
        
        // Salvar arquivo
        $writer = IOFactory::createWriter($this->phpWord, 'Word2007');
        $writer->save($caminho_saida);
        
        return $caminho_saida;
    }
    
    private function adicionarCabecalho($section) {
        // Tabela com fundo azul
        $table = $section->addTable([
            'width' => 100 * 50,
            'borderSize' => 0,
            'cellMargin' => 0
        ]);
        
        $table->addRow(3500);
        
        $cell = $table->addCell(10000, [
            'bgColor' => $this->estilos['azul_marinho'],
            'valign' => 'center'
        ]);
        
        // Proposta Nº
        $cell->addText(
            'Proposta Nº: ' . ($this->dados['numero_proposta'] ?? '001/2024'),
            ['color' => 'FFFFFF', 'size' => 10, 'name' => 'Calibri'],
            ['spaceAfter' => 200, 'indent' => 400]
        );
        
        // Título principal
        $cell->addText(
            'Levantamento',
            ['color' => 'FFFFFF', 'size' => 32, 'bold' => true, 'name' => 'Calibri'],
            ['spaceAfter' => 0, 'indent' => 400]
        );
        
        $cell->addText(
            'Topográfico',
            ['color' => 'FFFFFF', 'size' => 32, 'bold' => true, 'name' => 'Calibri'],
            ['spaceAfter' => 100, 'indent' => 400]
        );
        
        // Subtítulo (finalidade)
        $finalidade = $this->dados['finalidade'] ?? 'Para fins de Usucapião';
        $cell->addText(
            $finalidade,
            ['color' => $this->estilos['dourado'], 'size' => 14, 'name' => 'Calibri'],
            ['spaceAfter' => 0, 'indent' => 400]
        );
        
        // Linha dourada decorativa
        $section->addText('', [], ['borderBottomSize' => 12, 'borderBottomColor' => $this->estilos['dourado'], 'spaceAfter' => 0]);
    }
    
    private function adicionarDadosCliente($section) {
        // Título
        $section->addText('Dados do Cliente', ['size' => 14, 'bold' => true, 'color' => $this->estilos['azul_marinho']], ['spaceAfter' => 200]);
        
        // Box com borda dourada
        $table = $section->addTable([
            'width' => 100 * 50,
            'borderSize' => 0,
            'cellMargin' => 150
        ]);
        
        $table->addRow();
        $cell = $table->addCell(9000, [
            'bgColor' => $this->estilos['cinza_claro'],
            'borderLeftSize' => 24,
            'borderLeftColor' => $this->estilos['dourado']
        ]);
        
        $cell->addText('Nome: ' . ($this->dados['nome_cliente'] ?? $this->dados['nome_cliente_salvo'] ?? ''), ['size' => 11]);
        $cell->addText('E-mail: ' . ($this->dados['email'] ?? $this->dados['email_salvo'] ?? ''), ['size' => 11]);
        $cell->addText('Telefone: ' . ($this->dados['telefone'] ?? $this->dados['telefone_salvo'] ?? ''), ['size' => 11]);
        $cell->addText('WhatsApp: ' . ($this->dados['whatsapp'] ?? $this->dados['whatsapp_salvo'] ?? ''), ['size' => 11]);
        
        $section->addText('', [], ['spaceAfter' => 200]);
        
        // Local da Obra
        $section->addText('Local da Obra', ['size' => 14, 'bold' => true, 'color' => $this->estilos['azul_marinho']], ['spaceAfter' => 200]);
        
        $table2 = $section->addTable([
            'width' => 100 * 50,
            'borderSize' => 0,
            'cellMargin' => 150
        ]);
        
        $table2->addRow();
        $cell2 = $table2->addCell(9000, [
            'bgColor' => $this->estilos['cinza_claro'],
            'borderLeftSize' => 24,
            'borderLeftColor' => $this->estilos['dourado']
        ]);
        
        $cell2->addText('Endereço: ' . ($this->dados['endereco_obra'] ?? $this->dados['endereco'] ?? ''), ['size' => 11]);
        $cell2->addText('Bairro: ' . ($this->dados['bairro_obra'] ?? $this->dados['bairro'] ?? ''), ['size' => 11]);
        $cell2->addText('Cidade: ' . ($this->dados['cidade_obra'] ?? $this->dados['cidade'] ?? '') . ' - ' . ($this->dados['estado_obra'] ?? $this->dados['estado'] ?? ''), ['size' => 11]);
        $cell2->addText('Área: ' . ($this->dados['area_obra'] ?? $this->dados['area'] ?? ''), ['size' => 11]);
        
        $section->addText('', [], ['spaceAfter' => 300]);
    }
    
    private function adicionarApresentacao($section) {
        $section->addText('Apresentação', ['size' => 16, 'bold' => true, 'color' => $this->estilos['azul_marinho']], ['spaceAfter' => 200]);
        
        // Box de destaque amarelo
        $table = $section->addTable([
            'width' => 100 * 50,
            'borderSize' => 0,
            'cellMargin' => 200
        ]);
        
        $table->addRow();
        $cell = $table->addCell(9000, [
            'bgColor' => 'FFF3CD',
            'borderTopSize' => 12,
            'borderTopColor' => $this->estilos['dourado'],
            'borderBottomSize' => 12,
            'borderBottomColor' => $this->estilos['dourado']
        ]);
        
        $empresa = $this->dados['empresa_proponente_nome'] ?? $this->dados['empresa'] ?? 'Nossa Empresa';
        
        $cell->addText(
            'A ' . $empresa . ' é uma empresa prestadora de serviços técnicos especializada exclusivamente em Engenharia de Agrimensura e Topografia.',
            ['size' => 11, 'bold' => true, 'color' => '856404'],
            ['spaceAfter' => 100]
        );
        
        $cell->addText(
            'Nosso foco é a elaboração precisa das peças técnicas (plantas, memoriais e laudos) que servem como base física para o processo.',
            ['size' => 10, 'color' => '856404']
        );
        
        $section->addText('', [], ['spaceAfter' => 200]);
        
        // Disclaimer
        $section->addText(
            'Importante: Nossa atuação limita-se estritamente à engenharia. Não somos um escritório de advocacia e não realizamos a regularização jurídica do imóvel.',
            ['size' => 10, 'italic' => true, 'color' => '856404'],
            ['spaceAfter' => 300]
        );
    }
    
    private function adicionarEscopo($section) {
        $section->addText('Escopo do Serviço', ['size' => 16, 'bold' => true, 'color' => $this->estilos['azul_marinho']], ['spaceAfter' => 200]);
        
        $tipoLevantamento = $this->dados['tipo_levantamento'] ?? 'Levantamento Topográfico Planimétrico Cadastral';
        
        $section->addText(
            'O serviço contratado refere-se única e exclusivamente ao ' . $tipoLevantamento . ', incluindo a emissão de ART junto ao CREA.',
            ['size' => 11],
            ['spaceAfter' => 200]
        );
        
        // Duas colunas: Inclui vs Não Inclui
        $table = $section->addTable([
            'width' => 100 * 50,
            'borderSize' => 0,
            'cellMargin' => 100
        ]);
        
        $table->addRow();
        
        // INCLUÍDO (verde)
        $cellInclui = $table->addCell(4500, [
            'bgColor' => 'D4EDDA',
            'borderSize' => 0
        ]);
        $cellInclui->addText('O QUE ESTÁ INCLUÍDO', ['bold' => true, 'color' => '155724', 'size' => 12], ['spaceAfter' => 100]);
        
        $incluidos = [
            'Medição em campo com equipamentos de precisão',
            'Confecção de plantas e memoriais descritivos',
            'Transcrição dos dados dos confrontantes',
            'Emissão de ART de serviço topográfico',
            'Arquivos digitais (PDF e DWG)',
            'Relatório fotográfico técnico'
        ];
        
        foreach ($incluidos as $item) {
            $cellInclui->addText('• ' . $item, ['size' => 10, 'color' => '155724'], ['spaceAfter' => 60]);
        }
        
        // Espaço entre colunas
        $table->addCell(200);
        
        // NÃO INCLUÍDO (vermelho)
        $cellNaoInclui = $table->addCell(4500, [
            'bgColor' => 'F8D7DA',
            'borderSize' => 0
        ]);
        $cellNaoInclui->addText('O QUE NÃO ESTÁ INCLUÍDO', ['bold' => true, 'color' => '721C24', 'size' => 12], ['spaceAfter' => 100]);
        
        $naoIncluidos = [
            'Protocolização em cartórios ou prefeituras',
            'Assessoria jurídica ou advocatícia',
            'Coleta de assinaturas de confrontantes',
            'Solicitação de documentos a vizinhos',
            'Garantia de titulação da propriedade'
        ];
        
        foreach ($naoIncluidos as $item) {
            $cellNaoInclui->addText('• ' . $item, ['size' => 10, 'color' => '721C24'], ['spaceAfter' => 60]);
        }
        
        $section->addText('', [], ['spaceAfter' => 300]);
    }
    
    private function adicionarMetodologia($section) {
        $section->addText('Metodologia de Trabalho', ['size' => 16, 'bold' => true, 'color' => $this->estilos['azul_marinho']], ['spaceAfter' => 200]);
        
        $etapas = [
            ['01', 'Reconhecimento', 'de Área'],
            ['02', 'Medição', 'de Campo'],
            ['03', 'Processamento', 'de Dados'],
            ['04', 'Elaboração', 'de Documentos'],
            ['05', 'Entrega', 'Final']
        ];
        
        // Tabela horizontal para etapas
        $table = $section->addTable([
            'width' => 100 * 50,
            'borderSize' => 0,
            'cellMargin' => 50
        ]);
        
        $table->addRow();
        
        foreach ($etapas as $etapa) {
            $cell = $table->addCell(1800, ['valign' => 'center']);
            $cell->addText($etapa[0], ['size' => 20, 'bold' => true, 'color' => $this->estilos['azul_marinho']], ['alignment' => Jc::CENTER]);
            $cell->addText($etapa[1], ['size' => 9, 'bold' => true, 'color' => $this->estilos['azul_marinho']], ['alignment' => Jc::CENTER]);
            $cell->addText($etapa[2], ['size' => 9, 'color' => '666666'], ['alignment' => Jc::CENTER]);
        }
        
        $section->addText('', [], ['spaceAfter' => 200]);
        
        // Prazos
        $diasCampo = $this->dados['dias_campo'] ?? 0;
        $diasEscritorio = $this->dados['dias_escritorio'] ?? 0;
        $prazoExecucao = $this->dados['prazo_execucao'] ?? ($diasCampo + $diasEscritorio) . ' dias';
        
        $tableEq = $section->addTable([
            'width' => 100 * 50,
            'borderSize' => 0,
            'cellMargin' => 150
        ]);
        
        $tableEq->addRow();
        $cellEq = $tableEq->addCell(9000, [
            'bgColor' => $this->estilos['cinza_claro'],
            'borderLeftSize' => 24,
            'borderLeftColor' => $this->estilos['dourado']
        ]);
        
        $cellEq->addText('Prazos e Cronograma', ['bold' => true, 'size' => 11], ['spaceAfter' => 100]);
        $cellEq->addText(
            'Dias de Campo: ' . $diasCampo . ' | Dias de Escritório: ' . $diasEscritorio . ' | Prazo Total: ' . $prazoExecucao,
            ['size' => 10]
        );
        
        $section->addText('', [], ['spaceAfter' => 300]);
    }
    
    private function adicionarInvestimento($section) {
        $section->addText('Investimento', ['size' => 16, 'bold' => true, 'color' => $this->estilos['azul_marinho']], ['spaceAfter' => 200, 'alignment' => Jc::CENTER]);
        
        // Caixa azul com valor
        $table = $section->addTable([
            'width' => 100 * 50,
            'borderSize' => 0,
            'cellMargin' => 400
        ]);
        
        $table->addRow();
        $cell = $table->addCell(9000, [
            'bgColor' => $this->estilos['azul_marinho'],
            'valign' => 'center'
        ]);
        
        $valor = $this->dados['valor_final_proposta'] ?? 0;
        $valorFormatado = 'R$ ' . number_format($valor, 2, ',', '.');
        $valorExtenso = $this->dados['Valor_proposta_extenso'] ?? $this->dados['valor_extenso'] ?? '';
        
        $cell->addText($valorFormatado, ['size' => 36, 'bold' => true, 'color' => 'FFFFFF', 'name' => 'Calibri'], ['alignment' => Jc::CENTER, 'spaceAfter' => 100]);
        $cell->addText($valorExtenso, ['size' => 12, 'italic' => true, 'color' => $this->estilos['dourado']], ['alignment' => Jc::CENTER]);
        
        $section->addText('', [], ['spaceAfter' => 200]);
        
        // Condições de pagamento
        $section->addText('Condições de Pagamento', ['size' => 14, 'bold' => true, 'color' => $this->estilos['azul_marinho']], ['spaceAfter' => 200, 'alignment' => Jc::CENTER]);
        
        $tablePag = $section->addTable([
            'width' => 100 * 50,
            'borderSize' => 0,
            'cellMargin' => 200
        ]);
        
        $tablePag->addRow();
        
        // Mobilização
        $cellMob = $tablePag->addCell(4500, [
            'bgColor' => 'E9ECEF',
            'borderSize' => 6,
            'borderColor' => 'DEE2E6'
        ]);
        $mobPct = $this->dados['mobilizacao_percentual'] ?? 50;
        $mobVal = $this->dados['mobilizacao_valor'] ?? ($valor * $mobPct / 100);
        $mobValFormatado = 'R$ ' . number_format($mobVal, 2, ',', '.');
        
        $cellMob->addText($mobPct . '%', ['size' => 28, 'bold' => true, 'color' => $this->estilos['azul_marinho']], ['alignment' => Jc::CENTER]);
        $cellMob->addText('MOBILIZAÇÃO', ['size' => 10, 'bold' => true, 'color' => '6C757D'], ['alignment' => Jc::CENTER, 'spaceAfter' => 60]);
        $cellMob->addText($mobValFormatado, ['size' => 14, 'bold' => true, 'color' => $this->estilos['verde']], ['alignment' => Jc::CENTER]);
        
        $tablePag->addCell(200);
        
        // Restante
        $cellRest = $tablePag->addCell(4500, [
            'bgColor' => 'E9ECEF',
            'borderSize' => 6,
            'borderColor' => 'DEE2E6'
        ]);
        $restPct = $this->dados['restante_percentual'] ?? (100 - $mobPct);
        $restVal = $this->dados['restante_valor'] ?? ($valor * $restPct / 100);
        $restValFormatado = 'R$ ' . number_format($restVal, 2, ',', '.');
        
        $cellRest->addText($restPct . '%', ['size' => 28, 'bold' => true, 'color' => $this->estilos['azul_marinho']], ['alignment' => Jc::CENTER]);
        $cellRest->addText('CONCLUSÃO', ['size' => 10, 'bold' => true, 'color' => '6C757D'], ['alignment' => Jc::CENTER, 'spaceAfter' => 60]);
        $cellRest->addText($restValFormatado, ['size' => 14, 'bold' => true, 'color' => $this->estilos['verde']], ['alignment' => Jc::CENTER]);
        
        $section->addText('', [], ['spaceAfter' => 300]);
        
        // Dados bancários
        $section->addText('Dados Bancários', ['size' => 12, 'bold' => true, 'color' => '6C757D'], ['spaceAfter' => 200, 'alignment' => Jc::CENTER]);
        
        $banco = $this->dados['empresa_proponente_banco'] ?? $this->dados['banco'] ?? '';
        $agencia = $this->dados['empresa_proponente_agencia'] ?? $this->dados['agencia'] ?? '';
        $conta = $this->dados['empresa_proponente_conta'] ?? $this->dados['conta'] ?? '';
        $cnpj = $this->dados['empresa_proponente_cnpj'] ?? $this->dados['cnpj'] ?? '';
        $pix = $this->dados['empresa_proponente_pix'] ?? $this->dados['pix'] ?? '';
        
        $tableBank = $section->addTable([
            'width' => 100 * 50,
            'borderSize' => 0,
            'cellMargin' => 100
        ]);
        
        $tableBank->addRow();
        $tableBank->addCell(3000)->addText('Banco: ' . $banco, ['size' => 10], ['alignment' => Jc::CENTER]);
        $tableBank->addCell(3000)->addText('Agência: ' . $agencia, ['size' => 10], ['alignment' => Jc::CENTER]);
        $tableBank->addCell(3000)->addText('Conta: ' . $conta, ['size' => 10], ['alignment' => Jc::CENTER]);
        
        $section->addText('CNPJ: ' . $cnpj . ' | PIX: ' . $pix, ['size' => 9, 'color' => '6C757D'], ['alignment' => Jc::CENTER]);
    }
    
    private function adicionarRodape($section) {
        $section->addText('', [], ['spaceAfter' => 400]);
        
        // Agradecimento
        $section->addText(
            'Agradecemos a oportunidade de apresentar nossa proposta de serviços técnicos.',
            ['size' => 11, 'italic' => true],
            ['alignment' => Jc::CENTER, 'spaceAfter' => 200]
        );
        
        // Rodapé azul
        $table = $section->addTable([
            'width' => 100 * 50,
            'borderSize' => 0,
            'cellMargin' => 300
        ]);
        
        $table->addRow(1500);
        $cell = $table->addCell(10000, [
            'bgColor' => $this->estilos['azul_marinho'],
            'valign' => 'center'
        ]);
        
        $empresa = $this->dados['empresa_proponente_nome'] ?? $this->dados['empresa'] ?? 'Empresa';
        $whatsapp = $this->dados['whatsapp_empresa'] ?? $this->dados['empresa_proponente_telefone'] ?? '';
        
        $cell->addText($empresa, ['size' => 18, 'bold' => true, 'color' => 'FFFFFF', 'name' => 'Calibri'], ['alignment' => Jc::CENTER, 'spaceAfter' => 60]);
        $cell->addText($whatsapp . ' | Engenharia e Topografia de Precisão', ['size' => 10, 'color' => $this->estilos['dourado']], ['alignment' => Jc::CENTER]);
    }
}
