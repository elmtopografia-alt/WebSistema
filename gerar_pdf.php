<?php
/**
 * gerar_pdf.php - Gera PDF da proposta a partir do editor dinâmico
 * Requer: mPDF (composer require mpdf/mpdf)
 */

session_start();
require_once __DIR__ . '/config.php'; // Adjusted to typical SGT config path given the root location
require_once __DIR__ . '/db.php';     // Adjusted to typical SGT db path

require_once __DIR__ . '/layouts/layout_executivo.php';
require_once __DIR__ . '/layouts/layout_tecnico.php';
require_once __DIR__ . '/layouts/layout_criativo.php';

// Adjusted autoload path assuming composer is run in the root of the project
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
} else {
    // Tratamento de erro elegante se o mPDF não estiver instalado
    http_response_code(500);
    die(json_encode(['error' => 'A biblioteca mPDF não está instalada. Execute "composer require mpdf/mpdf" na raiz do projeto.']));
}

use Mpdf\Mpdf;

header('Content-Type: application/pdf');

try {
    // Receber dados do POST
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['proposta_id'])) {
        throw new Exception('Dados inválidos');
    }

    $propostaId = intval($input['proposta_id']);
    $blocos = $input['blocos'] ?? [];
    $tema = $input['tema'] ?? 'classico';
    
    // Buscar dados da proposta (ajustado para usar $conn padrão do SGT)
    global $conn;
    $stmt = $conn->prepare("
        SELECT p.*, c.nome as cliente_nome, c.email as cliente_email, 
               c.telefone as cliente_telefone, o.endereco as obra_endereco
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

    // Configurar mPDF
    $layoutSelecionado = $input['layout'] ?? 'padrao';

    $mpdfConfig = [
        'mode' => 'utf-8',
        'format' => 'A4',
        'margin_left' => 0,
        'margin_right' => 0,
        'margin_top' => 0,
        'margin_bottom' => 0,
        'margin_header' => 0,
        'margin_footer' => 0,
        'tempDir' => __DIR__ . '/temp'
    ];

    // Se for layout padrão, mantemos margens para não quebrar o legado
    if ($layoutSelecionado === 'padrao') {
        $mpdfConfig['margin_left'] = 15;
        $mpdfConfig['margin_right'] = 15;
        $mpdfConfig['margin_top'] = 15;
        $mpdfConfig['margin_bottom'] = 15;
    }

    $mpdf = new Mpdf($mpdfConfig);

    // CSS e HTML baseados no layout
    if ($layoutSelecionado === 'executivo') {
        $html = renderLayoutExecutivo($proposta, $blocos, []);
        $mpdf->WriteHTML($html);
    } elseif ($layoutSelecionado === 'tecnico') {
        $html = renderLayoutTecnico($proposta, $blocos, []);
        $mpdf->WriteHTML($html);
    } elseif ($layoutSelecionado === 'criativo') {
        $html = renderLayoutCriativo($proposta, $blocos, []);
        $mpdf->WriteHTML($html);
    } else {
        // Fallback para o modo legado
        $css = obterCSSDoTema($tema);
        $html = montarHTMLProposta($proposta, $blocos, $css);
        
        // Configurar cabeçalho e rodapé apenas no modo legado
        $mpdf->SetHTMLHeader("
            <div style='border-bottom: 2px solid #b45f06; padding-bottom: 10px; margin-bottom: 20px;'>
                <table width='100%'>
                    <tr>
                        <td width='60%'>
                            <img src='assets/img/logo.png' style='max-height: 50px;'>
                        </td>
                        <td width='40%' style='text-align: right; font-size: 10pt; color: #666;'>
                            <strong>{$proposta['numero_proposta']}</strong><br>
                            " . date('d/m/Y') . "
                        </td>
                    </tr>
                </table>
            </div>
        ");
        
        $mpdf->SetHTMLFooter("
            <div style='border-top: 1px solid #ddd; padding-top: 10px; font-size: 9pt; color: #666; text-align: center;'>
                <strong>ELM Serviços Topográficos Ltda.</strong> | 
                Belo Horizonte - MG | 
                (31) 3625-4769 | 
                contato@geometropole.com.br<br>
                Página {PAGENO} de {nbpg}
            </div>
        ");

        $mpdf->WriteHTML($css, \Mpdf\HTMLParserMode::HEADER_CSS);
        $mpdf->WriteHTML($html, \Mpdf\HTMLParserMode::HTML_BODY);
    }
    
    // Nome do arquivo
    $filename = "Proposta_{$proposta['numero_proposta']}.pdf";
    
    // Output
    $mpdf->Output($filename, 'D'); // 'D' = download, 'I' = inline
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}

/**
 * Retorna CSS baseado no tema selecionado
 */
function obterCSSDoTema($tema) {
    $temas = [
        'classico' => '
            body { font-family: "Segoe UI", Arial, sans-serif; font-size: 11pt; line-height: 1.6; color: #333; }
            h1 { color: #b45f06; font-size: 24pt; border-bottom: 3px solid #b45f06; padding-bottom: 10px; }
            h2 { color: #92400e; font-size: 16pt; margin-top: 24px; border-bottom: 1px solid #d4a574; padding-bottom: 5px; }
            h3 { color: #b45f06; font-size: 13pt; margin-top: 16px; }
            .info-box { background: #fdf8f3; border-left: 4px solid #b45f06; padding: 15px; margin: 15px 0; }
            table { width: 100%; border-collapse: collapse; margin: 15px 0; }
            th { background: #f8fafc; border-bottom: 2px solid #b45f06; padding: 10px; text-align: left; font-weight: 600; }
            td { border-bottom: 1px solid #e5e7eb; padding: 10px; }
            .valor-destaque { font-size: 20pt; color: #b45f06; font-weight: bold; text-align: center; padding: 20px; border: 2px solid #b45f06; margin: 20px 0; }
            .page-break { page-break-after: always; }
        ',
        'moderno' => '
            body { font-family: "Inter", Arial, sans-serif; font-size: 11pt; line-height: 1.6; color: #1f2937; }
            h1 { color: #059669; font-size: 24pt; font-weight: 700; }
            h2 { color: #047857; font-size: 16pt; margin-top: 24px; border-left: 4px solid #059669; padding-left: 10px; }
            .info-box { background: #ecfdf5; border-radius: 8px; padding: 15px; margin: 15px 0; }
            .valor-destaque { background: linear-gradient(135deg, #059669, #047857); color: white; padding: 20px; border-radius: 12px; text-align: center; font-size: 20pt; font-weight: bold; }
        ',
        'minimalista' => '
            body { font-family: "Helvetica", Arial, sans-serif; font-size: 11pt; line-height: 1.8; color: #2d3748; }
            h1 { font-size: 22pt; font-weight: 300; letter-spacing: -0.5px; border-bottom: 1px solid #cbd5e0; padding-bottom: 15px; }
            h2 { font-size: 14pt; font-weight: 600; margin-top: 30px; color: #4a5568; text-transform: uppercase; letter-spacing: 1px; }
            .valor-destaque { font-size: 24pt; font-weight: 300; text-align: center; margin: 30px 0; padding: 20px; border: 1px solid #e2e8f0; }
        '
    ];
    
    return $temas[$tema] ?? $temas['classico'];
}

/**
 * Monta o HTML completo da proposta
 */
function montarHTMLProposta($proposta, $blocos, $css) {
    $html = '<div class="proposta-content">';
    
    // Cabeçalho com dados do cliente
    $html .= '
        <div class="info-box">
            <strong>Cliente:</strong> ' . htmlspecialchars($proposta['cliente_nome']) . '<br>
            <strong>Proposta:</strong> ' . htmlspecialchars($proposta['numero_proposta']) . '<br>
            <strong>Data:</strong> ' . date('d/m/Y', strtotime($proposta['data_criacao'])) . '
        </div>
    ';
    
    // Processar blocos dinâmicos
    foreach ($blocos as $index => $bloco) {
        $tipo = $bloco['tipo'] ?? 'texto';
        $conteudo = $bloco['conteudo'] ?? '';
        $titulo = $bloco['titulo'] ?? '';
        
        switch ($tipo) {
            case 'titulo':
                $html .= "<h1>" . htmlspecialchars($titulo) . "</h1>";
                $html .= "<div>" . $conteudo . "</div>"; // HTML permitido
                break;
                
            case 'secao':
                $html .= "<h2>" . htmlspecialchars($titulo) . "</h2>";
                $html .= "<div>" . $conteudo . "</div>";
                break;
                
            case 'subsecao':
                $html .= "<h3>" . htmlspecialchars($titulo) . "</h3>";
                $html .= "<div>" . $conteudo . "</div>";
                break;
                
            case 'tabela':
                $html .= renderizarTabelaPDF($conteudo);
                break;
                
            case 'lista':
                $html .= renderizarListaPDF($conteudo);
                break;
                
            case 'valor':
                $html .= '<div class="valor-destaque">' . $conteudo . '</div>';
                break;
                
            case 'quebra_pagina':
                $html .= '<div class="page-break"></div>';
                break;
                
            default:
                $html .= "<p>" . $conteudo . "</p>";
        }
    }
    
    // Assinatura
    $html .= '
        <div style="margin-top: 60px; text-align: center;">
            <div style="border-top: 1px solid #333; width: 300px; margin: 0 auto; padding-top: 10px;">
                <strong>ELM Serviços Topográficos Ltda.</strong><br>
                CNPJ: 14.059.118/0001-08
            </div>
        </div>
    ';
    
    $html .= '</div>';
    
    return $html;
}

/**
 * Renderiza tabela para PDF
 */
function renderizarTabelaPDF($dados) {
    if (is_string($dados)) {
        $dados = json_decode($dados, true);
    }
    
    if (empty($dados['linhas'])) return '';
    
    $html = '<table>';
    
    // Cabeçalho
    if (!empty($dados['cabecalhos'])) {
        $html .= '<tr>';
        foreach ($dados['cabecalhos'] as $cab) {
            $html .= '<th>' . htmlspecialchars($cab) . '</th>';
        }
        $html .= '</tr>';
    }
    
    // Linhas
    foreach ($dados['linhas'] as $linha) {
        $html .= '<tr>';
        foreach ($linha as $celula) {
            $html .= '<td>' . htmlspecialchars($celula) . '</td>';
        }
        $html .= '</tr>';
    }
    
    $html .= '</table>';
    return $html;
}

/**
 * Renderiza lista para PDF
 */
function renderizarListaPDF($dados) {
    if (is_string($dados)) {
        $dados = json_decode($dados, true);
    }
    
    if (empty($dados['itens'])) return '';
    
    $tipo = $dados['tipo'] ?? 'ul'; // ul ou ol
    
    $html = "<$tipo style='margin: 15px 0; padding-left: 20px;'>";
    foreach ($dados['itens'] as $item) {
        $html .= '<li style="margin-bottom: 8px;">' . htmlspecialchars($item) . '</li>';
    }
    $html .= "</$tipo>";
    
    return $html;
}
