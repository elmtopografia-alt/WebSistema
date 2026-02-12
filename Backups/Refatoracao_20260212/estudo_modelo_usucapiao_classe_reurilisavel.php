<?php
require_once 'vendor/autoload.php';

use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Style\Font;
use PhpOffice\PhpWord\Style\Paragraph;
use PhpOffice\PhpWord\Style\Table;
use PhpOffice\PhpWord\Style\Cell;

class PropostaTopografia {
    private $phpWord;
    private $section;
    private $currentSection;

    public function __construct() {
        $this->phpWord = new PhpWord();
        $this->setupStyles();
        
        // Inicializa seção principal (Layout 'continuous' para permitir fluidez ou padrão)
        $this->currentSection = $this->phpWord->addSection([
            'marginTop' => 0,
            'marginBottom' => 0,
            'marginLeft' => 0, // Margens serão controladas internamente se necessário
            'marginRight' => 0,
        ]);
    }

    private function setupStyles() {
        $this->phpWord->setDefaultFontName('Montserrat');
        $this->phpWord->setDefaultFontSize(11);
        $this->phpWord->setDefaultParagraphStyle([
            'lineHeight' => 1.15,
            'spaceAfter' => 120,
        ]);
        // Definir estilos globais aqui se necessário
    }

    // --- MÉTODOS DE ESTRUTURA ---

    public function addHeader($numero_proposta, $titulo_principal, $sub_titulo, $cidade_data) {
        // Tabela para cabeçalho full-width
        $table = $this->currentSection->addTable([
            'width' => 100 * 50,
            'borderSize' => 0,
            'cellMargin' => 0,
        ]);
        
        $table->addRow(2500); // Altura fixa ajustável

        // Esquerda - Azul Escuro
        $cellLeft = $table->addCell(8000, ['bgColor' => '1e3a5f', 'valign' => 'center']);
        $cellLeft->addText('Proposta Nº: ' . $numero_proposta, ['color' => 'FFFFFF', 'size' => 10]);
        $cellLeft->addText($titulo_principal, ['color' => 'FFFFFF', 'size' => 28, 'bold' => true], ['spaceAfter' => 0]);
        if($sub_titulo) {
            $cellLeft->addText($sub_titulo, ['color' => 'd4af37', 'size' => 14]); // Dourado
        }

        // Direita - Azul Escuro (Data)
        $cellRight = $table->addCell(3000, ['bgColor' => '1e3a5f', 'valign' => 'top']);
        $cellRight->addText($cidade_data, ['color' => 'FFFFFF', 'size' => 10], ['align' => 'right']);

        // Linha dourada abaixo
        $this->currentSection->addText('', [], ['borderBottomSize' => 12, 'borderBottomColor' => 'd4af37', 'spaceAfter' => 0]);
        
        // Adiciona margem para começar o conteúdo
        $this->addSpacer(300);
        
        // Cria uma nova seção com margens normais para o conteúdo, se preferir separar
        // Por enquanto, seguimos na mesma seção, apenas ajustando indentação via tabelas ou pars
    }

    public function addSectionTitle($titulo) {
        // Wrapper para margem
        $table = $this->currentSection->addTable(['width' => 100 * 50, 'unit' => 'pct', 'alignment' => 'center']);
        $table->addRow();
        $cell = $table->addCell(100); // Dummy para centralizar se precisar, ou usar section com margem
        // Simplesmente adicionando texto direto por enquanto, assumindo margem visual
        $this->currentSection->addText(
            $titulo,
            ['size' => 14, 'bold' => true, 'color' => '1e3a5f', 'name' => 'Montserrat'],
            ['spaceAfter' => 200, 'indentLeft' => 800, 'indentRight' => 800] // Margens manuais
        );
    }

    public function addInfoBox($dadosArray) {
        // Tabela centralizada com margens simuladas
        $table = $this->currentSection->addTable([
            'width' => 90 * 50, // 90% da largura
            'alignment' => 'center',
            'borderSize' => 0,
            'cellMargin' => 150
        ]);

        $table->addRow();
        $cell = $table->addCell(10000, [
            'bgColor' => 'f8f9fa',
            'borderLeftSize' => 24,
            'borderLeftColor' => 'd4af37'
        ]);

        foreach ($dadosArray as $label => $valor) {
            $cell->addText("$label: $valor", ['size' => 11, 'name' => 'Open Sans'], ['spaceAfter' => 60]);
        }
        
        $this->addSpacer(200);
    }

    public function addPresentation($texto_destaque, $texto_normal) {
        $this->addSectionTitle('APRESENTAÇÃO');
        
        $table = $this->currentSection->addTable([
            'width' => 90 * 50,
            'alignment' => 'center',
            'cellMargin' => 200
        ]);

        $table->addRow();
        $cell = $table->addCell(10000, [
            'bgColor' => 'fff3cd', // Amarelo
            'borderTopSize' => 12,
            'borderTopColor' => 'd4af37',
            'borderBottomSize' => 12,
            'borderBottomColor' => 'd4af37'
        ]);

        $cell->addText($texto_destaque, ['size' => 11, 'bold' => true, 'color' => '856404'], ['spaceAfter' => 100]);
        $cell->addText($texto_normal, ['size' => 10, 'color' => '856404']);
        
        $this->addSpacer(300);
    }

    public function addScope($incluidos, $nao_incluidos) {
        $this->addSectionTitle('ESCOPO DO SERVIÇO');

        $table = $this->currentSection->addTable([
            'width' => 90 * 50,
            'alignment' => 'center',
            'cellMargin' => 100
        ]);
        
        $table->addRow();
        
        // Incluídos
        $cellInc = $table->addCell(4800, ['bgColor' => 'd4edda']);
        $cellInc->addText('✓ O QUE ESTÁ INCLUÍDO', ['bold' => true, 'color' => '155724', 'size' => 11], ['spaceAfter' => 100]);
        foreach ($incluidos as $item) {
            $cellInc->addText("• $item", ['size' => 10, 'color' => '155724'], ['spaceAfter' => 60]);
        }

        // Espaço
        $table->addCell(400); 

        // Não Incluídos
        $cellExc = $table->addCell(4800, ['bgColor' => 'f8d7da']);
        $cellExc->addText('✗ O QUE NÃO ESTÁ INCLUÍDO', ['bold' => true, 'color' => '721c24', 'size' => 11], ['spaceAfter' => 100]);
        foreach ($nao_incluidos as $item) {
            $cellExc->addText("• $item", ['size' => 10, 'color' => '721c24'], ['spaceAfter' => 60]);
        }
        
        $this->addSpacer(300);
    }

    public function addValorDestaque($valor, $extenso) {
        $this->addSectionTitle('INVESTIMENTO');

        $table = $this->currentSection->addTable([
            'width' => 90 * 50,
            'alignment' => 'center',
            'cellMargin' => 200
        ]);

        $table->addRow();
        $cell = $table->addCell(10000, ['bgColor' => '1e3a5f', 'valign' => 'center']);
        
        $cell->addText($valor, ['size' => 36, 'bold' => true, 'color' => 'FFFFFF'], ['align' => 'center', 'spaceAfter' => 100]);
        $cell->addText($extenso, ['size' => 12, 'italic' => true, 'color' => 'd4af37'], ['align' => 'center']);

        $this->addSpacer(300);
    }
    
    // Método auxiliar para imagens (como a tabela que o usuário quer postar)
    public function addImage($path, $width = 500) {
        if (file_exists($path)) {
            $this->currentSection->addImage($path, [
                'width' => $width,
                'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER
            ]);
            $this->addSpacer(200);
        } else {
            $this->currentSection->addText("[Imagem não encontrada: $path]", ['color' => 'FF0000']);
        }
    }

    public function addFooter($empresa, $contato) {
        // Simplesmente uma tabela ao final
        $this->addSpacer(300);
        $table = $this->currentSection->addTable([
            'width' => 100 * 50,
            'borderSize' => 0,
            'cellMargin' => 300
        ]);
        $table->addRow(1000);
        $cell = $table->addCell(10000, ['bgColor' => '1e3a5f', 'valign' => 'center']);
        $cell->addText($empresa, ['size' => 16, 'bold' => true, 'color' => 'FFFFFF'], ['align' => 'center']);
        $cell->addText($contato, ['size' => 10, 'color' => 'd4af37'], ['align' => 'center']);
    }

    private function addSpacer($size = 200) {
        $this->currentSection->addText('', [], ['spaceAfter' => $size]);
    }

    public function generate($filename) {
        // Garantir extensão
        if (!preg_match('/\.docx$/', $filename)) {
            $filename .= '.docx';
        }
        
        try {
            $objWriter = IOFactory::createWriter($this->phpWord, 'Word2007');
            $objWriter->save($filename);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}
?>