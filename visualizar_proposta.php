<?php
/**
 * visualizar_proposta.php - Preview web da proposta (simula como ficará o PDF)
 * Abre em nova aba do editor_dinamico.php
 */

session_start();
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/layouts/layout_executivo.php';
require_once __DIR__ . '/layouts/layout_tecnico.php';
require_once __DIR__ . '/layouts/layout_criativo.php';

// Parâmetros da URL
$propostaId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$layoutSelecionado = $_GET['layout'] ?? 'padrao';

// Buscar dados usando o repositório centralizado
require_once __DIR__ . '/PropostaRepository.php';
$repo = new PropostaRepository();
$proposta = $repo->buscarPorId($propostaId);

if (!$proposta) {
    $ambiente = $_SESSION['ambiente'] ?? 'producao (padrao)';
    $db_usado = (defined('ENVIRONMENT') ? ENVIRONMENT : 'desconhecido');
    die("Proposta não encontrada ou acesso negado (ID: $propostaId | Ambiente: $ambiente)");
}

// Normalização para manter compatibilidade com o layout existente
$proposta['cliente_nome']     = $proposta['nome_cliente'] ?? '';
$proposta['cliente_email']    = $proposta['email_cliente'] ?? '';
$proposta['cliente_telefone'] = $proposta['telefone_cliente'] ?? '';
$proposta['obra_endereco']    = $proposta['endereco_obra'] ?? '';
$blocos = $proposta['docx_blocos'] ?? [];
$tema = $proposta['tema'] ?? 'classico';

// Prepara JSON seguro para o Javascript
$blocosJsonJs = !empty($proposta['docx_conteudo']) ? $proposta['docx_conteudo'] : '[]';
$numPropostaJs = $proposta['numero_proposta'] ?? 'S/N';


$selPadrao = $layoutSelecionado === 'padrao' ? 'selected' : '';
$selExecutivo = $layoutSelecionado === 'executivo' ? 'selected' : '';
$selTecnico = $layoutSelecionado === 'tecnico' ? 'selected' : '';
$selCriativo = $layoutSelecionado === 'criativo' ? 'selected' : '';

$fabHtml = <<<HTML
    <!-- Botões Flutuantes e Seletor -->
    <style>
        .fab-container { position: fixed; bottom: 30px; right: 30px; display: flex; flex-direction: column; gap: 12px; z-index: 10000; }
        .fab { width: 56px; height: 56px; border-radius: 50%; border: none; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 20px; box-shadow: 0 4px 12px rgba(0,0,0,0.3); transition: all 0.3s; color: white; background: #6b7280;}
        .fab:hover { transform: translateY(-4px) scale(1.1); }
        .fab-pdf { background: #dc2626; }
        .fab-docx { background: #2563eb; }
        .fab-print { background: #059669; }
        .fab-close { background: #4b5563; }
        .layout-selector {
            position: fixed; top: 20px; right: 20px; background: white; padding: 15px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); z-index: 10000; font-family: sans-serif;
            border: 1px solid #e5e7eb; width: 200px;
        }
        .layout-selector h4 { margin: 0 0 10px 0; font-size: 14px; color: #374151; font-weight: 600; text-transform: uppercase;}
        .layout-selector select { width: 100%; padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px; outline: none; background: #f9fafb; cursor: pointer; color: #111827; }
        .layout-selector select:hover { border-color: #9ca3af; }
        @media print { .no-print { display: none !important; } }
        /* Font Awesome is used in the buttons now */
    </style>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <div class="layout-selector no-print">
        <h4>Visual Premium</h4>
        <select onchange="mudarLayout(this.value)">
            <option value="padrao" {$selPadrao}>Padrão (Legacy)</option>
            <option value="executivo" {$selExecutivo}>Layout Executivo</option>
            <option value="tecnico" {$selTecnico}>Layout Técnico</option>
            <option value="criativo" {$selCriativo}>Layout Criativo</option>
        </select>
    </div>

    <div class="fab-container no-print">
        <button class="fab fab-close" onclick="window.close()" title="Fechar">
            <i class="fas fa-times"></i>
        </button>
        <button class="fab fab-print" onclick="window.print()" title="Imprimir/Salvar PDF">
            <i class="fas fa-print"></i>
        </button>
        <button class="fab fab-docx" onclick="baixarDOCX()" title="Baixar DOCX">
            <i class="fas fa-file-word"></i>
        </button>
        <button class="fab fab-pdf" onclick="baixarPDF()" title="Baixar PDF">
            <i class="fas fa-file-pdf"></i>
        </button>
    </div>
    
    <script>
        function mudarLayout(layout) {
            window.location.href = "visualizar_proposta.php?id={$propostaId}&layout=" + layout;
        }
        function baixarPDF() {
            const btn = document.querySelector('.fab-pdf');
            const originalIcon = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            
            const blocosJson = {$blocosJsonJs};
            const strBlocos = JSON.stringify(blocosJson);


            const dados = {
                proposta_id: {$propostaId},
                layout: '{$layoutSelecionado}'
            };
            
            fetch('gerar_pdf.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(dados)
            })
            .then(response => {
                if(!response.ok) throw new Error('Erro na geração do PDF');
                return response.blob();
            })
            .then(blob => {
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = 'Proposta_' + '{$numPropostaJs}' + '.pdf';
                document.body.appendChild(a);
                a.click();
                window.URL.revokeObjectURL(url);
                btn.innerHTML = originalIcon;
            })
            .catch(err => {
                alert('Erro ao gerar PDF: ' + err.message);
                btn.innerHTML = originalIcon;
            });
        }

        function baixarDOCX() {
            const btn = document.querySelector('.fab-docx');
            const originalIcon = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            
            const dados = {
                proposta_id: {$propostaId},
                layout: '{$layoutSelecionado}'
            };
            
            fetch('gerar_docx.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(dados)
            })
            .then(response => {
                if(!response.ok) throw new Error('Erro na geração do DOCX');
                return response.blob();
            })
            .then(blob => {
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = 'Proposta_' + '{$numPropostaJs}' + '.docx';
                document.body.appendChild(a);
                a.click();
                window.URL.revokeObjectURL(url);
                btn.innerHTML = originalIcon;
            })
            .catch(err => {
                alert('Erro ao gerar DOCX: ' + err.message);
                btn.innerHTML = originalIcon;
            });
        }
    </script>
HTML;

if ($layoutSelecionado === 'executivo') {
    $htmlOutput = renderLayoutExecutivo($proposta, $blocos, []);
    $htmlOutput = str_replace('</body>', $fabHtml . '</body>', $htmlOutput);
    echo $htmlOutput;
    exit;
} elseif ($layoutSelecionado === 'tecnico') {
    $htmlOutput = renderLayoutTecnico($proposta, $blocos, []);
    $htmlOutput = str_replace('</body>', $fabHtml . '</body>', $htmlOutput);
    echo $htmlOutput;
    exit;
} elseif ($layoutSelecionado === 'criativo') {
    $htmlOutput = renderLayoutCriativo($proposta, $blocos, []);
    $htmlOutput = str_replace('</body>', $fabHtml . '</body>', $htmlOutput);
    echo $htmlOutput;
    exit;
}

// Temas CSS
$temas = [
    'classico' => [
        'brand' => '#b45f06',
        'brandLight' => '#d4a574',
        'bg' => '#fdf8f3',
        'font' => "'Segoe UI', Georgia, serif"
    ],
    'moderno' => [
        'brand' => '#059669',
        'brandLight' => '#34d399',
        'bg' => '#ecfdf5',
        'font' => "'Inter', system-ui, sans-serif"
    ],
    'minimalista' => [
        'brand' => '#2d3748',
        'brandLight' => '#4a5568',
        'bg' => '#f7fafc',
        'font' => "'Helvetica Neue', Arial, sans-serif"
    ]
];

$t = $temas[$tema] ?? $temas['classico'];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Preview: <?php echo htmlspecialchars($proposta['numero_proposta']); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        
        body {
            font-family: <?php echo $t['font']; ?>;
            background: #525659;
            padding: 20px;
            line-height: 1.6;
            color: #333;
        }
        
        .page {
            background: white;
            width: 21cm;
            min-height: 29.7cm;
            padding: 2cm;
            margin: 0 auto;
            box-shadow: 0 4px 24px rgba(0,0,0,0.15);
            position: relative;
        }
        
        @media print {
            body { background: none; padding: 0; }
            .page { 
                width: 100%; 
                box-shadow: none; 
                margin: 0; 
                padding: 1.5cm;
            }
            .no-print { display: none !important; }
        }
        
        /* Botões flutuantes */
        .fab-container {
            position: fixed;
            bottom: 30px;
            right: 30px;
            display: flex;
            flex-direction: column;
            gap: 12px;
            z-index: 1000;
        }
        
        .fab {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
            transition: all 0.3s;
            color: white;
        }
        
        .fab:hover { transform: translateY(-4px) scale(1.1); }
        .fab-pdf { background: #dc2626; }
        .fab-docx { background: #2563eb; }
        .fab-print { background: <?php echo $t['brand']; ?>; }
        .fab-close { background: #6b7280; }
        
        .fab-tooltip {
            position: absolute;
            right: 70px;
            background: rgba(0,0,0,0.8);
            color: white;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 12px;
            white-space: nowrap;
            opacity: 0;
            transition: opacity 0.2s;
            pointer-events: none;
        }
        
        .fab:hover .fab-tooltip { opacity: 1; }
        
        /* Conteúdo da proposta */
        .proposta-header {
            border-bottom: 3px solid <?php echo $t['brand']; ?>;
            padding-bottom: 20px;
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }
        
        .proposta-titulo h1 {
            color: <?php echo $t['brand']; ?>;
            font-size: 28pt;
            margin-bottom: 5px;
        }
        
        .proposta-meta {
            text-align: right;
            color: #666;
            font-size: 10pt;
        }
        
        .info-box {
            background: <?php echo $t['bg']; ?>;
            border-left: 4px solid <?php echo $t['brand']; ?>;
            padding: 20px;
            margin: 20px 0;
            border-radius: 0 8px 8px 0;
        }
        
        .info-box h3 {
            color: <?php echo $t['brand']; ?>;
            font-size: 11pt;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 10px;
        }
        
        h2 {
            color: <?php echo $t['brand']; ?>;
            font-size: 16pt;
            margin-top: 30px;
            margin-bottom: 15px;
            border-bottom: 1px solid <?php echo $t['brandLight']; ?>;
            padding-bottom: 8px;
        }
        
        h3 {
            color: <?php echo $t['brand']; ?>;
            font-size: 13pt;
            margin-top: 20px;
            margin-bottom: 10px;
        }
        
        p { margin-bottom: 12px; text-align: justify; }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            font-size: 10pt;
        }
        
        th {
            background: <?php echo $t['bg']; ?>;
            color: <?php echo $t['brand']; ?>;
            border-bottom: 2px solid <?php echo $t['brand']; ?>;
            padding: 12px;
            text-align: left;
            font-weight: 600;
        }
        
        td {
            border-bottom: 1px solid #e5e7eb;
            padding: 10px 12px;
        }
        
        tr:nth-child(even) { background: #f9fafb; }
        
        .valor-destaque {
            background: linear-gradient(135deg, <?php echo $t['bg']; ?>, white);
            border: 2px solid <?php echo $t['brand']; ?>;
            padding: 30px;
            text-align: center;
            margin: 30px 0;
            border-radius: 12px;
        }
        
        .valor-destaque .label {
            font-size: 11pt;
            color: <?php echo $t['brand']; ?>;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 10px;
        }
        
        .valor-destaque .valor {
            font-size: 28pt;
            font-weight: 700;
            color: <?php echo $t['brand']; ?>;
        }
        
        .assinatura {
            margin-top: 60px;
            text-align: center;
            page-break-inside: avoid;
        }
        
        .linha-assinatura {
            width: 60%;
            margin: 0 auto 10px;
            border-bottom: 1px solid #333;
            padding-bottom: 40px;
        }
        
        /* Badges de status */
        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .badge-rascunho { background: #fef3c7; color: #d97706; }
        .badge-enviada { background: #dbeafe; color: #2563eb; }
        .badge-aceita { background: #d1fae5; color: #059669; }
        
        /* Animações */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .page { animation: fadeIn 0.5s ease-out; }
    </style>
</head>
<body>

    <?php echo $fabHtml; ?>

    <div class="page">
        <div class="proposta-header">
            <div class="proposta-titulo">
                <h1>Proposta</h1>
                <p><?php echo htmlspecialchars($proposta['nome_servico'] ?? 'Serviço de Engenharia'); ?></p>
            </div>
            <div class="proposta-meta">
                <p>Nº <strong><?php echo htmlspecialchars($proposta['numero_proposta']); ?></strong></p>
                <p><?php echo date('d/m/Y', strtotime($proposta['data_criacao'])); ?></p>
                <p><?php echo htmlspecialchars($proposta['empresa_proponente_cidade'] ?? 'Belo Horizonte/MG'); ?></p>
                <div style="margin-top: 10px;">
                    <span class="badge badge-<?php echo ($proposta['status'] ?? 'rascunho'); ?>">
                        <?php echo strtoupper($proposta['status'] ?? 'rascunho'); ?>
                    </span>
                </div>
            </div>
        </div>

        <div class="info-box">
            <h3>Dados do Cliente</h3>
            <p><strong>Cliente:</strong> <?php echo htmlspecialchars($proposta['nome_cliente_salvo'] ?? ''); ?></p>
            <p><strong>E-mail:</strong> <?php echo htmlspecialchars($proposta['email_salvo'] ?? ''); ?></p>
            <p><strong>Telefone:</strong> <?php echo htmlspecialchars($proposta['telefone_salvo'] ?? ''); ?></p>
        </div>

        <div class="info-box" style="margin-top: 20px;">
            <h3>Local da Obra</h3>
            <p><strong>Endereço:</strong> <?php echo htmlspecialchars($proposta['endereco_obra'] ?? 'Não informado'); ?></p>
            <p><strong>Bairro:</strong> <?php echo htmlspecialchars($proposta['bairro_obra'] ?? ''); ?></p>
            <p><strong>Cidade/UF:</strong> <?php echo htmlspecialchars($proposta['cidade_obra'] ?? ''); ?> - <?php echo htmlspecialchars($proposta['estado_obra'] ?? ''); ?></p>
            <p><strong>Área:</strong> <?php echo htmlspecialchars($proposta['area_obra'] ?? '0'); ?> <?php echo htmlspecialchars($proposta['unidade_area'] ?? 'm²'); ?></p>
        </div>

        <div class="conteudo-blocos" style="margin-top: 30px;">
            <?php foreach ($blocos as $bloco): ?>
                <div class="bloco">
                    <?php if (!empty($bloco['titulo'])): ?>
                        <h2><?php echo htmlspecialchars($bloco['titulo']); ?></h2>
                    <?php endif; ?>

                    <?php 
                    $tipo = $bloco['tipo'] ?? 'texto';
                    $conteudo = $bloco['conteudo'] ?? '';

                    if ($tipo === 'texto'): ?>
                        <div class="texto-bloco">
                            <?php echo substituirVariaveisLocal($conteudo, $proposta); ?>
                        </div>
                    <?php elseif ($tipo === 'tabela'): ?>
                        <?php echo renderizarTabela($bloco); ?>
                    <?php elseif ($tipo === 'lista'): ?>
                        <?php echo renderizarLista($bloco); ?>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if (!empty($proposta['valor_final_proposta'])): ?>
        <div class="valor-destaque">
            <div class="label">Investimento Total</div>
            <div class="valor">R$ <?php echo number_format($proposta['valor_final_proposta'], 2, ',', '.'); ?></div>
            <div class="invest-extenso">
                (<?php echo $proposta['Valor_proposta_extenso'] ?? ''; ?>)
            </div>
        </div>
        <?php endif; ?>

        <div class="assinatura">
            <div class="linha-assinatura"></div>
            <p><strong><?php echo htmlspecialchars($proposta['nome_empresa'] ?? 'ELM Topografia'); ?></strong></p>
            <p>CNPJ: <?php echo htmlspecialchars($proposta['empresa_proponente_cnpj'] ?? ''); ?></p>
        </div>
    </div>

</body>
</html>

<?php
// Funções auxiliares
function substituirVariaveisLocal($texto, $proposta) {
    if (empty($texto)) return '';
    
    // Mapeamento básico para o resolvedor local
    $vars = [
        'Drone'         => $proposta['modelo_drone'] ?? 'Não aplicável',
        'Veiculo'       => $proposta['modelo_veiculo'] ?? 'Não incluso',
        'Estacao_Total' => $proposta['modelo_estacao_total'] ?? 'Não inclusa',
        'GPS'           => $proposta['modelo_gps'] ?? 'Não incluso',
        'nome_cliente'  => $proposta['nome_cliente_salvo'] ?? $proposta['nome_cliente'] ?? '',
        'ValorProposta' => number_format($proposta['valor_final_proposta'] ?? 0, 2, ',', '.'),
        'ValorExtenso'  => $proposta['Valor_proposta_extenso'] ?? ''
    ];

    foreach ($vars as $chave => $valor) {
        $texto = str_ireplace('${' . $chave . '}', $valor, $texto);
        $texto = str_ireplace('{{' . $chave . '}}', $valor, $texto);
        $texto = str_ireplace('[' . $chave . ']', $valor, $texto);
    }
    
    return $texto;
}

function renderizarTabela($dados) {
    if (is_string($dados)) $dados = json_decode($dados, true);
    if (empty($dados['linhas'])) return '';
    
    $html = '<table>';
    
    if (!empty($dados['cabecalhos'])) {
        $html .= '<tr>';
        foreach ($dados['cabecalhos'] as $cab) {
            $html .= '<th>' . htmlspecialchars($cab) . '</th>';
        }
        $html .= '</tr>';
    }
    
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

function renderizarLista($dados) {
    if (is_string($dados)) $dados = json_decode($dados, true);
    if (empty($dados['itens'])) return '';
    
    $tag = ($dados['tipo'] ?? 'ul') === 'ol' ? 'ol' : 'ul';
    $html = "<$tag style='margin-left: 20px; margin-bottom: 15px;'>";
    foreach ($dados['itens'] as $item) {
        $html .= '<li>' . htmlspecialchars($item) . '</li>';
    }
    $html .= "</$tag>";
    return $html;
}
?>
