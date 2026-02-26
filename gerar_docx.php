<?php
/**
 * gerar_docx.php - Gera DOCX preenchido com dados da proposta
 * Requer: PHPWord (composer require phpoffice/phpword)
 */

session_start();
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
} else {
    http_response_code(500);
    die(json_encode(['error' => 'A biblioteca PHPWord não está instalada. Execute "composer require phpoffice/phpword".']));
}

use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Shared\Html;

header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['proposta_id'])) {
        throw new Exception('Dados inválidos');
    }

    $propostaId = intval($input['proposta_id']);
    $blocos = $input['blocos'] ?? [];
    $modeloDocxId = $input['modelo_docx_id'] ?? null;
    
    // Buscar dados da proposta
    global $conn;
    $stmt = $conn->prepare("
        SELECT p.*, c.nome as cliente_nome, c.email as cliente_email, 
               c.telefone as cliente_telefone, c.celular as cliente_celular,
               o.endereco as obra_endereco, o.bairro as obra_bairro, 
               o.cidade as obra_cidade, o.estado as obra_estado,
               o.area as obra_area
        FROM propostas p
        LEFT JOIN clientes c ON p.cliente_id = c.id
        LEFT JOIN obras o ON p.obra_id = o.id
        WHERE p.id = ?
    ");
    $stmt->execute([$propostaId]);
    $proposta = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$proposta) {
        throw new Exception('Proposta não encontrada');
    }

    // Criar novo documento Word
    $phpWord = new PhpWord();
    
    // Configurar estilos padrão
    $phpWord->setDefaultFontName('Segoe UI');
    $phpWord->setDefaultFontSize(11);
    
    // Estilos personalizados
    $phpWord->addTitleStyle(1, ['bold' => true, 'size' => 24, 'color' => 'b45f06', 'spaceAfter' => 200]);
    $phpWord->addTitleStyle(2, ['bold' => true, 'size' => 16, 'color' => '92400e', 'spaceAfter' => 150]);
    $phpWord->addTitleStyle(3, ['bold' => true, 'size' => 13, 'color' => 'b45f06', 'spaceAfter' => 100]);
    
    // Seção principal
    $layoutSelecionado = $input['layout'] ?? 'padrao';
    
    if ($layoutSelecionado === 'executivo') {
        $section = $phpWord->addSection([
            'marginTop' => 0,
            'marginBottom' => 0,
            'marginLeft' => 0,
            'marginRight' => 0,
        ]);
        applyLayoutExecutivoDOCX($phpWord, $section, $proposta, $blocos);
    } elseif ($layoutSelecionado === 'tecnico') {
        $section = $phpWord->addSection([
            'marginTop' => 0,
            'marginBottom' => 0,
            'marginLeft' => 0,
            'marginRight' => 0,
        ]);
        applyLayoutTecnicoDOCX($phpWord, $section, $proposta, $blocos);
    } else {
        // Layout Padrão (Legado)
        $section = $phpWord->addSection([
            'marginLeft' => 1440,
            'marginRight' => 1440,
            'marginTop' => 1440,
            'marginBottom' => 1440,
        ]);
        renderLayoutPadraoDOCX($phpWord, $section, $proposta, $blocos);
    }

    // Salvar arquivo temporário
    $filename = "Proposta_{$proposta['numero_proposta']}.docx";
    // Ensure temp dir exists
    if (!is_dir(__DIR__ . '/temp')) {
        mkdir(__DIR__ . '/temp', 0777, true);
    }
    $tempFile = tempnam(__DIR__ . '/temp', 'docx_');
    
    $writer = IOFactory::createWriter($phpWord, 'Word2007');
    $writer->save($tempFile);
    
    // Enviar para download
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Length: ' . filesize($tempFile));
    header('Cache-Control: no-cache, must-revalidate');
    
    readfile($tempFile);
    unlink($tempFile);
    exit;
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}

/**
 * Funções de Renderização Premium DOCX
 */

function applyLayoutExecutivoDOCX($phpWord, $section, $proposta, $blocos) {
    $corPrimaria = '1e3a5f';
    $corSecundaria = 'c9a227';
    
    // Cabeçalho Premium
    $headerTable = $section->addTable(['width' => 100 * 50, 'unit' => 'pct']);
    $headerTable->addRow(2000);
    $cellLeft = $headerTable->addCell(7000, ['bgColor' => $corPrimaria, 'valign' => 'center']);
    $cellLeft->addText('PROPOSTA TÉCNICA', ['color' => 'FFFFFF', 'size' => 9, 'bold' => true]);
    $cellLeft->addText('ELM SERVIÇOS', ['color' => 'FFFFFF', 'size' => 24, 'bold' => true]);
    $cellLeft->addText('Topografia & Mapeamento', ['color' => $corSecundaria, 'size' => 10]);
    
    $cellRight = $headerTable->addCell(3000, ['bgColor' => $corPrimaria, 'valign' => 'center']);
    $cellRight->addText($proposta['numero_proposta'], ['color' => 'FFFFFF', 'size' => 14, 'bold' => true], ['align' => 'right']);
    $cellRight->addText(date('d/m/Y'), ['color' => 'FFFFFF', 'size' => 9], ['align' => 'right']);

    $section->addText('', [], ['borderBottomSize' => 12, 'borderBottomColor' => $corSecundaria, 'spaceAfter' => 400]);

    // Conteúdo com Margem Simulada
    foreach ($blocos as $bloco) {
        $tipo = $bloco['tipo'] ?? 'texto';
        if ($tipo === 'secao') {
            $section->addText(mb_strtoupper($bloco['titulo']), ['bold' => true, 'size' => 14, 'color' => $corPrimaria], ['spaceBefore' => 200, 'spaceAfter' => 100]);
            if (!empty($bloco['conteudo'])) {
                Html::addHtml($section, processarVariaveis($bloco['conteudo'], $proposta));
            }
        } elseif ($tipo === 'valor') {
            $vTable = $section->addTable(['width' => 90 * 50, 'alignment' => 'center']);
            $vTable->addRow();
            $vCell = $vTable->addCell(10000, ['bgColor' => $corPrimaria, 'valign' => 'center']);
            $vCell->addText('INVESTIMENTO TOTAL', ['color' => 'FFFFFF', 'size' => 10, 'bold' => true], ['align' => 'center']);
            $vCell->addText($bloco['conteudo'], ['color' => $corSecundaria, 'size' => 28, 'bold' => true], ['align' => 'center']);
        } else {
            Html::addHtml($section, processarVariaveis($bloco['conteudo'] ?? '', $proposta));
        }
        $section->addTextBreak(1);
    }
}

function applyLayoutTecnicoDOCX($phpWord, $section, $proposta, $blocos) {
    $corPrimaria = '374151';
    $corSecundaria = 'ea580c';
    
    // Cabeçalho Grid
    $headerTable = $section->addTable(['width' => 100 * 50, 'unit' => 'pct', 'borderSize' => 6, 'borderColor' => $corPrimaria]);
    $headerTable->addRow();
    $cell = $headerTable->addCell(5000, ['bgColor' => $corPrimaria]);
    $cell->addText('RELATÓRIO TÉCNICO', ['color' => 'FFFFFF', 'bold' => true]);
    
    $cell = $headerTable->addCell(5000);
    $cell->addText($proposta['numero_proposta'], ['bold' => true], ['align' => 'right']);
    
    $section->addTextBreak(2);

    foreach ($blocos as $bloco) {
        $tipo = $bloco['tipo'] ?? 'texto';
        if ($tipo === 'secao') {
            $section->addText('SEC // ' . mb_strtoupper($bloco['titulo']), ['bold' => true, 'size' => 12, 'color' => $corSecundaria]);
            Html::addHtml($section, processarVariaveis($bloco['conteudo'] ?? '', $proposta));
        } else {
            Html::addHtml($section, processarVariaveis($bloco['conteudo'] ?? '', $proposta));
        }
        $section->addTextBreak(1);
    }
}

function renderLayoutPadraoDOCX($phpWord, $section, $proposta, $blocos) {
    // Implementação original movida para função organizada
    $header = $section->addHeader();
    $header->addText($proposta['numero_proposta'], ['bold' => true, 'color' => 'b45f06'], ['alignment' => 'right']);
    
    foreach ($blocos as $bloco) {
        $tipo = $bloco['tipo'] ?? 'texto';
        $conteudo = $bloco['conteudo'] ?? '';
        $titulo = $bloco['titulo'] ?? '';
        
        switch ($tipo) {
            case 'secao':
                $section->addTitle($titulo, 2);
                Html::addHtml($section, processarVariaveis($conteudo, $proposta));
                break;
            case 'valor_destaque':
            case 'valor':
                $section->addText('INVESTIMENTO: ' . $conteudo, ['bold' => true, 'size' => 16]);
                break;
            default:
                Html::addHtml($section, processarVariaveis($conteudo, $proposta));
        }
        $section->addTextBreak(1);
    }
}

/**
 * Processa variáveis {{campo}} no texto
 */
function processarVariaveis($texto, $dados) {
    $variaveis = [
        '{{nome_cliente}}' => $dados['cliente_nome'] ?? '',
        '{{email_cliente}}' => $dados['cliente_email'] ?? '',
        '{{telefone_cliente}}' => $dados['cliente_telefone'] ?? '',
        '{{celular_cliente}}' => $dados['cliente_celular'] ?? '',
        '{{endereco_obra}}' => $dados['obra_endereco'] ?? '',
        '{{bairro_obra}}' => $dados['obra_bairro'] ?? '',
        '{{cidade_obra}}' => $dados['obra_cidade'] ?? '',
        '{{estado_obra}}' => $dados['obra_estado'] ?? '',
        '{{area_obra}}' => $dados['obra_area'] ? $dados['obra_area'] . ' ha' : '',
        '{{numero_proposta}}' => $dados['numero_proposta'] ?? '',
        '{{data_atual}}' => date('d/m/Y'),
        '{{data_atual_extenso}}' => strftime('%d de %B de %Y'),
        '{{valor_total}}' => isset($dados['valor_total']) ? 'R$ ' . number_format($dados['valor_total'], 2, ',', '.') : '',
    ];
    
    return str_replace(array_keys($variaveis), array_values($variaveis), $texto);
}

/**
 * Adiciona tabela ao DOCX
 */
function adicionarTabelaDOCX($section, $dados) {
    if (is_string($dados)) {
        $dados = json_decode($dados, true);
    }
    
    if (empty($dados['linhas'])) return;
    
    $linhas = count($dados['linhas']) + (empty($dados['cabecalhos']) ? 0 : 1);
    $colunas = count($dados['cabecalhos'] ?? $dados['linhas'][0]);
    
    $table = $section->addTable([
        'borderSize' => 6,
        'borderColor' => 'e5e7eb',
        'cellMargin' => 80,
        'alignment' => 'center'
    ]);
    
    // Cabeçalho
    if (!empty($dados['cabecalhos'])) {
        $table->addRow();
        foreach ($dados['cabecalhos'] as $cab) {
            $cell = $table->addCell(2000, ['bgColor' => 'f8fafc', 'borderBottomSize' => 8, 'borderBottomColor' => 'b45f06']);
            $cell->addText($cab, ['bold' => true, 'color' => 'b45f06', 'size' => 10]);
        }
    }
    
    // Dados
    foreach ($dados['linhas'] as $i => $linha) {
        $table->addRow();
        foreach ($linha as $celula) {
            $cell = $table->addCell(2000);
            $cell->addText($celula, ['size' => 10]);
        }
    }
}

/**
 * Adiciona lista ao DOCX
 */
function adicionarListaDOCX($section, $dados) {
    if (is_string($dados)) {
        $dados = json_decode($dados, true);
    }
    
    if (empty($dados['itens'])) return;
    
    foreach ($dados['itens'] as $item) {
        $section->addListItem($item, 0, null, $dados['tipo'] ?? 'bullet');
    }
}
