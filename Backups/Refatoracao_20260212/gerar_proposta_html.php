<?php
// gerar_proposta_html.php
// Gerador de Propostas HTML Server-Side
// Baseado na arquitetura "ProposalBlockRenderer"

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        echo '<h1>Fatal Error (Caught)</h1><pre>';
        print_r($error);
        echo '</pre>';
        exit;
    }
});

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'db.php';
require_once 'config.php'; // Para constantes como BASE_URL se necessário

// =====================================================
// CARREGAR DADOS DO BANCO (SE ACESSAR VIA GET)
// =====================================================

$variaveis = array();

// Se receber ID via GET, carrega do banco de dados
if (isset($_GET['id']) && intval($_GET['id']) > 0) {
    $id_proposta = intval($_GET['id']);
    $ambiente = isset($_SESSION['ambiente']) ? $_SESSION['ambiente'] : 'producao';
    $conn = ($ambiente === 'demo') ? Database::getDemo() : Database::getProd();
    
    // Buscar proposta
    $sql = "SELECT p.*, c.nome_cliente, c.email, c.telefone, c.celular, c.whatsapp,
            s.nome as nome_servico, s.descricao as descricao_servico
            FROM Propostas p 
            LEFT JOIN Clientes c ON p.id_cliente = c.id_cliente 
            LEFT JOIN Tipo_Servicos s ON p.id_servico = s.id_servico
            WHERE p.id_proposta = $id_proposta";
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
        $proposta = $result->fetch_assoc();
        
        // Buscar Itens de Equipamento (Locação) para a Proposta
        $itens_locacao = [];
        $sqlLoc = "SELECT l.nome FROM Proposta_Locacao pl 
                   JOIN Tipo_Locacao l ON pl.id_locacao = l.id_locacao 
                   WHERE pl.id_proposta = $id_proposta";
        $resLoc = $conn->query($sqlLoc);
        if ($resLoc) {
            while ($row = $resLoc->fetch_assoc()) {
                $itens_locacao[] = $row['nome'];
            }
        }

        // --- DETECÇÃO DRONE (SERVER-SIDE GENERATOR) ---
        $isDrone = false;
        foreach ($itens_locacao as $item) {
            if (stripos($item, 'Drone') !== false || stripos($item, 'VANT') !== false || stripos($item, 'DJI') !== false) {
                $isDrone = true; break;
            }
        }
        if (stripos($proposta['nome_servico'] ?? '', 'Drone') !== false || stripos($proposta['nome_servico'] ?? '', 'Aerofotogrametria') !== false) {
            $isDrone = true;
        }

        // TEXTOS PADRÃO (CONTEXT AWARE)
        if ($isDrone) {
            $defApresentacao = '
                <p>A <strong>${Empresa}</strong> apresenta esta proposta técnica visando a execução de levantamento topográfico planialtimétrico através de <strong>Aerofotogrametria com Drones (VANTs)</strong>.</p>
                <p>Diferente de simples filmagens aéreas, este serviço trata-se de <strong>Engenharia de Precisão</strong>. O objetivo é gerar uma representação digital fiel do terreno, com coordenadas exatas (Latitude, Longitude e Altitude), servindo de base legal e técnica para projetos de arquitetura, loteamentos, regularização fundiária e cálculos de volume.</p>';
            
            $defMetodologia = '
                <h4>1. Planejamento e Configuração de Voo (Escritório)</h4>
                <p>Antes de ir a campo, realizamos o estudo da área via satélite. Definimos a altura de voo para garantir a resolução desejada (GSD) e a área de abrangência. O drone segue uma "grade" programada via GPS, garantindo cobertura total do terreno.</p>
                <h4>2. Apoio Terrestre - Pontos de Controle (Campo)</h4>
                <p>Esta é a etapa que diferencia uma foto comum de um mapa topográfico. Nossa equipe distribui e pinta alvos no chão. As coordenadas exatas são coletadas com GPS Geodésico de Alta Precisão (RTK). Esses pontos servem como "âncoras" garantindo precisão centimétrica.</p>
                <h4>3. Execução do Voo e Captura de Dados (Campo)</h4>
                <p>Checklist de segurança: verificação de baterias, hélices, interferência magnética e autorizações DECEA. O drone percorre rota autônoma capturando centenas de fotos em ângulos verticais (nadir) e oblíquos.</p>
                <h4>4. Processamento Fotogramétrico (Escritório)</h4>
                <p>Utilizamos Workstations e softwares específicos: (1) Alinhamento das fotos, (2) Criação da Nuvem de Pontos Densa com milhões de pontos 3D, (3) Georreferenciamento com os Pontos de Controle para precisão milimétrica.</p>
                <h4>5. Vetorização e Desenho Técnico (Escritório - CAD)</h4>
                <p>Desenhista técnico utiliza o modelo 3D para "desenhar" o mapa final em CAD. Vetorização de guias, cercas, edificações, postes, árvores e geração das Curvas de Nível.</p>';

            $defDocumentacao = '
                <p>Serão entregues os seguintes produtos técnicos:</p>
                <ul>
                    <li><strong>Ortomosaico Georreferenciado (TIF/JPG):</strong> "Foto" gigante da área em escala real;</li>
                    <li><strong>MDT (Modelo Digital de Terreno):</strong> Representação 3D do solo para terraplenagem;</li>
                    <li><strong>Curvas de Nível (DWG/DXF):</strong> Arquivo CAD com topografia do terreno;</li>
                    <li><strong>Planta Topográfica Planialtimétrica (PDF):</strong> Mapa finalizado com legendas;</li>
                    <li><strong>Relatório de Processamento:</strong> Comprovação da precisão alcançada;</li>
                    <li><strong>ART (Anotação de Responsabilidade Técnica):</strong> Registro no CREA.</li>
                </ul>';
        } else {
            // DEFAULTS ORIGINAIS
            $defApresentacao = '
                <p>A <strong>${Empresa}</strong> é uma empresa especializada em soluções de Engenharia e Topografia, atuando com equipamentos de alta tecnologia e equipe técnica qualificada.</p>
                <p>Nosso objetivo é fornecer dados precisos e confiáveis para garantir a segurança e a qualidade do seu projeto, seguindo rigorosamente as normas técnicas da ABNT (NBR 13.133).</p>
                <p>Apresentamos a seguir nossa proposta comercial para prestação de serviços de topografia, conforme solicitado.</p>';
            
            $defMetodologia = '
                <h4>Levantamento de Campo</h4>
                <p>Utilizaremos equipamentos de última geração (GPS RTK e/ou Estação Total) para a coleta de dados, garantindo precisão milimétrica nas coordenadas.</p>
                <h4>Processamento de Dados</h4>
                <p>Os dados coletados serão processados em softwares específicos (cálculos topográficos e ajustamento), gerando a nuvem de pontos e o desenho técnico fiel à realidade do terreno.</p>
                <h4>Desenho Técnico</h4>
                <p>Elaboração de plantas topográficas contendo curvas de nível, perímetro, edificações, árvores e demais interferências relevantes.</p>';

            $defDocumentacao = '
                <p>Serão entregues os seguintes documentos técnicos:</p>
                <ul>
                    <li>Planta Topográfica (Formato PDF e DWG);</li>
                    <li>Memorial Descritivo do Perímetro;</li>
                    <li>ART (Anotação de Responsabilidade Técnica) registrada no CREA;</li>
                    <li>Relatório Fotográfico (se aplicável).</li>
                </ul>';
        }

        // Mapear campos para variáveis esperadas pelo renderer
        $variaveis = array(
            'id_proposta' => $proposta['id_proposta'],
            'numero_proposta' => $proposta['numero_proposta'], // Corrigido para numero_proposta (string)
            'nome_cliente_salvo' => $proposta['nome_cliente_salvo'], // Corrigido para nome salvo na proposta
            'email_salvo' => $proposta['email_salvo'],
            'telefone_salvo' => $proposta['telefone_salvo'],
            'celular_salvo' => $proposta['celular_salvo'],
            'whatsapp_salvo' => $proposta['whatsapp_salvo'],
            
            // FINANCEIRO - CORREÇÃO DE MAPEAMENTO
            'ValorProposta' => 'R$ ' . number_format(floatval($proposta['valor_final_proposta'] ?? 0), 2, ',', '.'),
            'ValorExtenso' => $proposta['Valor_proposta_extenso'] ?? '',
            'mobilizacao_valor' => 'R$ ' . number_format(floatval($proposta['mobilizacao_valor'] ?? 0), 2, ',', '.'),
            'mobilizacao_percentual' => number_format(floatval($proposta['mobilizacao_percentual'] ?? 30), 0), // Sem decimais se for inteiro
            'restante_valor' => 'R$ ' . number_format(floatval($proposta['restante_valor'] ?? 0), 2, ',', '.'),
            'restante_percentual' => number_format(floatval($proposta['restante_percentual'] ?? 70), 0),
            
            'prazo_execucao' => $proposta['prazo_execucao'] ?? '',
            'dias_campo' => $proposta['dias_campo'] ?? '',
            'dias_escritorio' => $proposta['dias_escritorio'] ?? '',
            'data_criacao' => $proposta['data_criacao'],
            'DataExtenso' => date('d \d\e F \d\e Y', strtotime($proposta['data_criacao'])),
            'Cidade' => $proposta['cidade_obra'] ?? '',
            
            'endereco_obra' => $proposta['endereco_obra'] ?? '',
            'bairro_obra' => $proposta['bairro_obra'] ?? '',
            'cidade_obra' => $proposta['cidade_obra'] ?? '',
            'estado_obra' => $proposta['estado_obra'] ?? '',
            'area_obra' => $proposta['area_obra'] ?? '',
            'nome_servico' => $proposta['nome_servico'] ?? '',
            'descricao_servico' => $proposta['descricao_servico'] ?? '',
            
            // CONTEÚDO TÉCNICO
            'finalidade' => !empty($proposta['finalidade']) ? $proposta['finalidade'] : 'Prestação de serviços de topografia para fins de estudo, projeto ou regularização fundiária.',
            'escopo' => !empty($proposta['tipo_levantamento']) ? $proposta['tipo_levantamento'] : ($proposta['descricao_servico'] ?? 'Levantamento topográfico planialtimétrico cadastral.'),
            'is_drone' => $isDrone, // Flag para Renderer
            
            'apresentacao' => $proposta['apresentacao'] ?? $defApresentacao,
            'metodologia' => $proposta['metodologia'] ?? $defMetodologia,
            'documentacao' => $proposta['documentacao'] ?? $defDocumentacao,
            
            'consideracoes_content' => $proposta['consideracoes'] ?? '
                <p><strong>Validade da Proposta:</strong> 15 dias.</p>
                <p><strong>Prazo de Início:</strong> Imediato após o aceite e disponibilidade da equipe.</p>
                <p><strong>Acesso à Obra:</strong> O contratante deverá garantir o livre acesso da equipe ao local do serviço.</p>',
                
             // EQUIPAMENTOS DETECTADOS
             'lista_equipamentos' => $itens_locacao
        );
    }
} else {
    // Se receber via POST (fluxo antigo do editor ou via salvar_proposta.php)
    $variaveis = $_POST;
}

// [FIX] GARANTIA DE VARIÁVEIS INJETADAS
// Se houver variáveis no POST que não estão em $variaveis (ou estão vazias), mescla.
// Isso é crucial para quando salvar_proposta.php injeta valores calculados (ValorProposta, ValorExtenso)
if (!empty($_POST)) {
    foreach ($_POST as $key => $val) {
        if (!isset($variaveis[$key]) || empty($variaveis[$key])) {
            $variaveis[$key] = $val;
        }
    }
}

// =====================================================
// HELPER FUNCTIONS (Importadas ou redefinidas)
// =====================================================

// Garantir que as funções de formatação estejam disponíveis
if (!function_exists('formatarMoeda')) {
    function formatarMoeda($valor) {
        if (empty($valor)) return 'R$ 0,00';
        if (is_string($valor) && strpos($valor, 'R$') === 0) return $valor;
        $val = floatval(str_replace(['R$', '.', ','], ['', '', '.'], $valor));
        return 'R$ ' . number_format($val, 2, ',', '.');
    }
}
if (!function_exists('valorPorExtenso')) {
    // Versão simplificada ou inclua o arquivo de funções se tiver
    function valorPorExtenso($valor) {
        // Implementação completa estaria em functions.php. 
        // Aqui um fallback simples se não estiver incluído.
        // O ideal é a variável já vir preenchida do passo anterior.
        return $valor; 
    }
}

// =====================================================
// CSS PROFISSIONAL PARA A PROPOSTA
// =====================================================

function getProposalStyles() {
    return '
    <style>
        /* 
         * PREMIUM ENTERPRISE THEME (2026)
         * Palette: Professional Blue (#2563eb), Business Gray (scale), Success Green (#059669)
         * Typography: Inter (Google Fonts)
         */
        @import url("https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap");

        :root {
            --primary: #2563eb;       /* Azul Profissional */
            --primary-dark: #1e40af;
            --secondary: #1e293b;     /* Grafite Escuro */
            --text-main: #334155;     /* Grafite Médio */
            --text-light: #64748b;    /* Cinza Info */
            --success: #059669;       /* Verde Sucesso */
            --success-bg: #ecfdf5;
            --bg-page: #f8fafc;       /* Off-white */
            --bg-card: #ffffff;
            --border-color: #e2e8f0;
            
            /* Spacing System */
            --space-1: 0.25rem;   /* 4px */
            --space-2: 0.5rem;    /* 8px */
            --space-3: 0.75rem;   /* 12px */
            --space-4: 1rem;      /* 16px */
            --space-5: 1.25rem;   /* 20px */
            --space-6: 1.5rem;    /* 24px */
            --radius-sm: 0.375rem;
            --radius-md: 0.5rem;
            --radius-lg: 0.75rem;
        }

        body {
            margin: 0;
            padding: 0;
            background-color: var(--bg-page);
            font-family: "Inter", system-ui, -apple-system, sans-serif;
            color: var(--text-main);
            -webkit-font-smoothing: antialiased;
        }
        
        .proposal-document {
            font-size: 11pt;
            line-height: 1.6;
            max-width: 210mm;
            min-height: 297mm;
            margin: 40px auto;
            padding: 25mm 20mm;
            background: var(--bg-card);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05), 0 4px 6px -2px rgba(0, 0, 0, 0.025);
            position: relative;
        }
        
        /* Typography */
        h1, h2, h3, h4 { color: var(--secondary); letter-spacing: -0.025em; }
        
        /* Cabeçalho Premium */
        .header-block {
            border-bottom: 3px solid var(--primary);
            padding-bottom: var(--space-6);
            margin-bottom: var(--space-6);
            display: flex;
            flex-direction: column;
            gap: var(--space-4);
        }
        
        .logo-compact {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: var(--space-5);
            padding: var(--space-4) 0;
        }
        
        .logo-compact img {
            max-height: 85px; 
            width: auto;
            object-fit: contain;
        }
        
        .logo-compact h1 {
            font-size: 22pt;
            font-weight: 800;
            color: var(--secondary); 
            text-transform: uppercase;
            margin: 0;
            text-align: right;
            line-height: 1.1;
        }

        .proposal-meta {
            background: var(--bg-page);
            padding: var(--space-3) var(--space-4);
            border-radius: var(--radius-md);
            border: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: var(--space-2);
        }
        
        .proposal-number {
            font-size: 12pt;
            font-weight: 700;
            color: var(--primary);
        }
        
        .location-date {
            font-size: 10pt;
            color: var(--text-light);
            font-weight: 500;
        }

        /* Títulos de Seção */
        .section-title {
            font-size: 16pt;
            color: var(--secondary);
            border-bottom: 2px solid var(--border-color);
            padding-bottom: var(--space-2);
            margin-bottom: var(--space-5);
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: var(--space-2);
            page-break-after: avoid;
        }
        
        .section-title.numbered { color: var(--primary); }
        .section-title.numbered::before {
            /* content: counter(section); ...complexo sem setup, mantemos texto */
        }

        /* Icons for Section Titles */
        .section-icon {
            width: 24px;
            height: 24px;
            stroke: var(--primary);
            fill: none;
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round;
            vertical-align: text-bottom;
            margin-right: 8px;
        }
        
        /* Blocos de Conteúdo */
        .proposal-block {
            margin-bottom: 40px;
            orphans: 3;
            widows: 3;
        }
        
        .header-block, 
        .investment-block, 
        .bank-data, 
        .final-block,
        .info-list { 
            page-break-inside: avoid;
        }

        /* Listas de Informação (Cards) */
        .info-list {
            list-style: none;
            padding: 0;
            display: grid;
            gap: var(--space-2);
        }
        
        .info-list li {
            padding: var(--space-3);
            background: var(--bg-page);
            border-radius: var(--radius-sm);
            display: grid;
            grid-template-columns: 140px 1fr;
            align-items: baseline;
            font-size: 10.5pt;
        }
        
        .info-list li strong {
            color: var(--text-light);
            font-weight: 600;
            text-transform: uppercase;
            font-size: 9pt;
            letter-spacing: 0.05em;
        }

        /* Conteúdo Rico */
        .content-text {
            text-align: justify;
            color: var(--text-main);
        }
        .content-text h4 {
            color: var(--primary-dark);
            font-size: 12pt;
            margin-top: var(--space-5);
            margin-bottom: var(--space-2);
            font-weight: 700;
            border-left: 4px solid var(--primary);
            padding-left: var(--space-2);
        }
        .content-text p { margin-bottom: var(--space-3); }
        .content-text ul { margin: var(--space-3) 0; padding-left: var(--space-4); }
        .content-text li { margin-bottom: var(--space-1); }

        /* Tabela Prazos Premium */
        .proposal-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin: var(--space-5) 0;
            border: 1px solid var(--border-color);
            border-radius: var(--radius-md);
            overflow: hidden;
        }
        
        .proposal-table th {
            background: var(--bg-page);
            color: var(--text-main);
            font-weight: 700;
            text-align: left;
            padding: var(--space-3) var(--space-4);
            border-bottom: 1px solid var(--border-color);
            font-size: 10pt;
            text-transform: uppercase;
        }
        
        .proposal-table td {
            padding: var(--space-3) var(--space-4);
            border-bottom: 1px solid var(--border-color);
            color: var(--text-main);
        }
        
        .proposal-table tr:last-child td { border-bottom: none; }
        .proposal-table tr:hover td { background-color: #f1f5f9; }
        
        .total-row td {
            background: #eff6ff !important;
            color: var(--primary-dark);
            font-weight: 700;
            font-size: 11pt;
            border-top: 2px solid var(--primary);
        }

        /* Bloco de Investimento (Destaque) */
        .investment-block {
            background: linear-gradient(145deg, #ffffff 0%, var(--success-bg) 100%);
            border: 1px solid #d1fae5;
            border-radius: var(--radius-lg);
            padding: 30px;
            box-shadow: 0 10px 15px -3px rgba(5, 150, 105, 0.1);
            position: relative;
            overflow: hidden;
        }
        
        .investment-block::before {
            content: "";
            position: absolute;
            top: 0; left: 0; width: 4px; height: 100%;
            background: var(--success);
        }

        .investment-value {
            text-align: center;
            margin: var(--space-5) 0;
            padding: var(--space-5);
            background: rgba(255,255,255,0.8);
            backdrop-filter: blur(4px);
            border-radius: var(--radius-md);
            border: 1px solid #d1fae5;
        }
        
        .investment-value h3 {
            font-family: "Inter", sans-serif;
            font-size: 32pt;
            color: var(--success);
            margin: 0;
            font-weight: 800;
            letter-spacing: -1px;
        }
        
        .value-extenso {
            display: block;
            color: #047857;
            font-size: 10pt;
            margin-top: var(--space-2);
            font-weight: 600;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        /* Condições de Pagamento */
        .payment-list li {
            background: #fff;
            border: 1px solid var(--border-color);
            border-left: 4px solid var(--primary);
            padding: var(--space-4);
            margin-bottom: var(--space-3);
            border-radius: var(--radius-sm);
            box-shadow: 0 2px 4px rgba(0,0,0,0.02);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .payment-list .value { font-size: 13pt; color: var(--primary); font-weight: 700; }
        .payment-list .note { font-size: 9pt; color: var(--text-light); }

        /* Dados Bancários */
        .bank-data {
            background: var(--bg-page);
            border: 1px dashed var(--border-color);
            border-radius: var(--radius-md);
            padding: var(--space-5);
            margin-top: var(--space-5);
        }
        
        .bank-data h4 { font-size: 10pt; text-transform: uppercase; color: var(--text-light); margin-top:0; }
        .bank-list li { margin-bottom: var(--space-1); font-family: "Inter", monospace; color: var(--text-main); }

        /* Rodapé e Assinatura */
        .signature {
            margin-top: 60px;
            text-align: center;
            position: relative;
            padding-top: 30px;
        }
        .signature::before {
            content: "";
            display: block;
            width: 200px;
            height: 1px;
            background: var(--border-color);
            margin: 0 auto 20px auto;
        }
        
        .company-name { font-size: 15pt; font-weight: 700; color: var(--secondary); margin-bottom: 2px; }
        .responsible { color: var(--text-light); font-size: 10pt; font-weight: 500; text-transform: uppercase; letter-spacing: 1px;}
        .contact { color: var(--primary); font-weight: 600; margin-top: 5px; }

        .proposal-footer {
            margin-top: 60px;
            padding-top: 20px;
            border-top: 1px solid var(--border-color);
            text-align: center;
            font-size: 9pt;
            color: #94a3b8;
            font-weight: 400;
        }

        /* Print Settings */
        @media print {
            body { background: white; -webkit-print-color-adjust: exact; }
            .proposal-document {
                margin: 0; padding: 0; box-shadow: none; max-width: 100%; border: none;
            }
            .no-print { display: none !important; }
            .page-break { page-break-after: always; }
        }
    </style>
    ';
}

// =====================================================
// CLASS: ProposalBlockRenderer
// =====================================================

class ProposalBlockRenderer {
    private $variaveis;
    private $isDrone;
    
    public function __construct($variaveis, $isDrone) {
        $this->variaveis = $variaveis;
        $this->isDrone = $isDrone;
    }
    
    private function getVar($key, $default = '') {
        return isset($this->variaveis[$key]) ? $this->variaveis[$key] : $default;
    }
    
    public function renderCabecalho() {
        echo '<div class="proposal-block header-block">';
        
        // Simulação de logo (idealmente viria da variável)
        $logoUrl = $this->getVar('logo_empresa');
        
        echo '<div class="logo-compact">';
        if (!empty($logoUrl)) {
             echo '<img src="' . htmlspecialchars($logoUrl) . '" alt="Logo">';
        }
        echo '<h1>PROPOSTA TÉCNICA COMERCIAL</h1>';
        echo '</div>';
        
        echo '<div class="proposal-meta">';
        echo '<div class="meta-row"><span class="proposal-number">Proposta Nº: <strong>' . htmlspecialchars($this->getVar('numero_proposta')) . '</strong></span></div>';
        echo '<div class="meta-row location-date"><span>' . htmlspecialchars($this->getVar('Cidade')) . ', ' . htmlspecialchars($this->getVar('DataExtenso')) . '</span></div>';
        echo '</div>';
        echo '</div>';
    }
    
    public function renderDadosCliente() {
        echo '<div class="proposal-block">';
        echo '<h2 class="section-title"><svg viewBox="0 0 24 24" class="section-icon"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg> Dados do Cliente</h2>';
        echo '<ul class="info-list">';
        echo '<li><strong>Nome:</strong> ' . htmlspecialchars($this->getVar('nome_cliente_salvo')) . '</li>';
        echo '<li><strong>E-mail:</strong> ' . htmlspecialchars($this->getVar('email_salvo')) . '</li>';
        
        $fones = [];
        if($this->getVar('telefone_salvo')) $fones[] = $this->getVar('telefone_salvo');
        if($this->getVar('celular_salvo')) $fones[] = $this->getVar('celular_salvo');
        if($this->getVar('whatsapp_salvo')) $fones[] = $this->getVar('whatsapp_salvo');
        
        if (!empty($fones)) {
            echo '<li><strong>Contato:</strong> ' . htmlspecialchars(implode(' / ', array_unique($fones))) . '</li>';
        }
        echo '</ul>';
        echo '</div>';
    }
    
    public function renderLocalObra() {
        // Se as variáveis de obra estiverem vazias, pular ou mostrar msg? Mostrar o que tem.
        echo '<div class="proposal-block">';
        echo '<h2 class="section-title"><svg viewBox="0 0 24 24" class="section-icon"><path d="M2 20h20"/><path d="M5 20v-5c0-4 3.1-7 7-7s7 3.1 7 7v5"/><path d="M12 4v4"/></svg> Local da Obra</h2>';
        echo '<ul class="info-list">';
        echo '<li><strong>Endereço:</strong> ' . htmlspecialchars($this->getVar('endereco_obra')) . '</li>';
        echo '<li><strong>Bairro:</strong> ' . htmlspecialchars($this->getVar('bairro_obra')) . '</li>';
        echo '<li><strong>Cidade/UF:</strong> ' . htmlspecialchars($this->getVar('cidade_obra')) . ' - ' . htmlspecialchars($this->getVar('estado_obra')) . '</li>';
        echo '<li><strong>Área Estimada:</strong> ' . htmlspecialchars($this->getVar('area_obra')) . '</li>';
        echo '</ul>';
        echo '</div>';
    }
    
    public function renderSecaoTexto($numero, $titulo, $contentKey) {
        $rawContent = $this->getVar($contentKey);
        $finalContent = $this->substituirVariaveis($rawContent);
        
        // Verifica se tem conteúdo real (ignorando tags vazias)
        if (trim(strip_tags($finalContent)) === '' && strpos($finalContent, '<img') === false) {
             // Se vazio, não renderiza nada? Ou avisa?
             // Se for obrigatório, renderiza msg. Se opcional, pula.
        }
        
        echo '<div class="proposal-block content-block">';
        echo '<h2 class="section-title numbered">' . $numero . '. ' . htmlspecialchars($titulo) . '</h2>';
        echo '<div class="content-text">';
        echo $finalContent; 
        echo '</div>';
        echo '</div>';
    }
    
    public function renderTabelaPrazos() {
        echo '<div class="proposal-block">';
        echo '<h2 class="section-title numbered">4. Prazos Estimados</h2>';
        
        if ($this->getVar('prazos_content')) {
             $content = $this->substituirVariaveis($this->getVar('prazos_content'));
             echo '<div class="content-text">' . $content . '</div>';
        } else {
             // Fallback: Tabela Padrão (Hardcoded structure as requested)
             echo '<p class="section-intro">O cumprimento dos prazos depende de condições climáticas favoráveis.</p>';
             echo '<table class="proposal-table">';
             echo '<thead><tr><th>Etapa</th><th>Descrição</th><th>Prazo Estimado</th></tr></thead>';
             echo '<tbody>';
             // Dados vindos de variáveis ou fixos?
             $etapas = [
                 ['1. Mobilização', 'Planejamento e ida a campo', 'Até 02 dias'],
                 ['2. Execução', 'Levantamento de Campo', $this->getVar('dias_campo') . ' dias'],
                 ['3. Escritório', 'Processamento e Desenho', $this->getVar('dias_escritorio') . ' dias'],
             ];
             foreach($etapas as $e) {
                 echo "<tr><td><strong>{$e[0]}</strong></td><td>{$e[1]}</td><td>{$e[2]}</td></tr>";
             }
             // Total Row
             echo '<tr class="total-row"><td colspan="2">TOTAL ESTIMADO</td><td>' . $this->getVar('prazo_execucao') . '</td></tr>';
             echo '</tbody></table>';
        }
        echo '</div>';
    }
    
    public function renderInvestimento() {
        echo '<div class="proposal-block investment-block">';
        echo '<h2 class="section-title numbered">5. Investimento</h2>';
        echo '<p>O valor total para execução dos serviços descritos é de:</p>';
        
        echo '<div class="investment-value">';
        echo '<h3>' . htmlspecialchars($this->getVar('ValorProposta')) . '</h3>'; // Já deve vir formatado
        echo '<span class="value-extenso">(' . htmlspecialchars($this->getVar('ValorExtenso')) . ')</span>';
        echo '</div>';
        
        echo '<p class="investment-note">Este investimento reflete o custo-benefício de nossa tecnologia e expertise.</p>';
        echo '</div>';
    }
    
    public function renderCondicoesPagamento() {
        echo '<div class="proposal-block">';
        echo '<h2 class="section-title numbered">6. Condições de Pagamento</h2>';
        
        echo '<ul class="payment-list">';
        echo '<li>';
        echo '<strong>Mobilização (Sinal):</strong> <span class="value">' . htmlspecialchars($this->getVar('mobilizacao_valor')) . '</span>';
        echo ' <span class="note">(' . htmlspecialchars($this->getVar('mobilizacao_percentual')) . '% no aceite)</span>';
        echo '</li>';
        echo '<li>';
        echo '<strong>Entrega Final:</strong> <span class="value">' . htmlspecialchars($this->getVar('restante_valor')) . '</span>';
        echo ' <span class="note">(' . htmlspecialchars($this->getVar('restante_percentual')) . '% na entrega)</span>';
        echo '</li>';
        echo '</ul>';
        
        // Dados Bancários
        echo '<div class="bank-data">';
        echo '<h4>Dados Bancários</h4>';
        echo '<ul class="bank-list">';
        echo '<li><strong>Banco:</strong> ' . htmlspecialchars($this->getVar('Banco')) . '</li>';
        echo '<li><strong>Ag/Conta:</strong> ' . htmlspecialchars($this->getVar('Agencia')) . ' / ' . htmlspecialchars($this->getVar('Conta')) . '</li>';
        echo '<li><strong>Pix:</strong> ' . htmlspecialchars($this->getVar('PIX')) . '</li>';
        echo '<li><strong>Favorecido:</strong> ' . htmlspecialchars($this->getVar('Empresa')) . '</li>';
        echo '</ul>';
        echo '</div>';
        
        echo '</div>';
    }
    
    public function renderEquipamentos() {
        echo '<div class="proposal-block">';
        echo '<h2 class="section-title numbered">7. Equipamentos</h2>';
        
        if ($this->getVar('conteudo_equipamentos')) {
             echo '<div class="content-text">' . $this->substituirVariaveis($this->getVar('conteudo_equipamentos')) . '</div>';
        } else {
             $lista = $this->getVar('lista_equipamentos');
             if (!empty($lista) && is_array($lista)) {
                 echo '<ul class="equipment-list">';
                 foreach($lista as $item) {
                     echo '<li><strong>' . htmlspecialchars($item) . '</strong></li>';
                 }
                 echo '</ul>';
             } else {
                 // Fallback para variáveis antigas ou mensagem padrão
                 $temEquip = false;
                 echo '<ul class="equipment-list">';
                 if ($this->getVar('Veiculo')) { echo '<li><strong>Veículo:</strong> ' . htmlspecialchars($this->getVar('Veiculo')) . '</li>'; $temEquip=true; }
                 if ($this->getVar('Drone')) { echo '<li><strong>Aeronave:</strong> ' . htmlspecialchars($this->getVar('Drone')) . '</li>'; $temEquip=true; }
                 if ($this->getVar('GPS')) { echo '<li><strong>GPS:</strong> ' . htmlspecialchars($this->getVar('GPS')) . '</li>'; $temEquip=true; }
                 if ($this->getVar('Estacao_Total')) { echo '<li><strong>Estação Total:</strong> ' . htmlspecialchars($this->getVar('Estacao_Total')) . '</li>'; $temEquip=true; }
                 
                 if (!$temEquip) {
                     echo '<li>Utilizaremos equipamentos adequados para a execução dos serviços (GPS RTK, Estação Total, etc.).</li>';
                 }
                 echo '</ul>';
             }
        }
        echo '</div>';
    }
    
    public function renderConsideracoesRodape() {
        echo '<div class="proposal-block final-block">';
        echo '<h2 class="section-title numbered">8. Considerações Finais</h2>';
        
        $texto = $this->getVar('consideracoes_content');
        if (empty($texto)) $texto = "A proposta tem validade de 15 dias.";
        
        // ESTRATÉGIA ANTI-DUPLICIDADE: Regex para detectar e limpar assinatura manual
        $textoClean = preg_replace('/(?:<p[^>]*>|<div>|<br\s*\/?>)?\s*(?:<[^>]+>\s*)*Atenciosamente[,.:]?[\s\S]*$/ui', '', $texto);
        if ($textoClean !== null) $texto = $textoClean;
        
        echo '<div class="content-text">' . $this->substituirVariaveis($texto) . '</div>';
        
        // Assinatura Automática (Centralizada e Negrito)
        echo '<div class="signature">';
        echo '<p class="closing">Atenciosamente,</p>';
        echo '<div class="company-name">' . htmlspecialchars($this->getVar('Empresa')) . '</div>';
        echo '<div class="responsible">Engenheiro Responsável</div>';
        
        $contato = $this->getVar('whatsapp') ? $this->getVar('whatsapp') : $this->getVar('telefone_salvo');
        if ($contato) {
             echo '<div class="contact">' . htmlspecialchars($contato) . '</div>';
        }
        echo '</div>';
        echo '</div>';
        
        // Rodapé
        echo '<div class="proposal-footer">';
        echo htmlspecialchars($this->getVar('Empresa')) . ' • CNPJ: ' . htmlspecialchars($this->getVar('CNPJ')) . ' • WhatsApp: ' . htmlspecialchars($this->getVar('whatsapp'));
        echo '</div>';
    }
    
    private function substituirVariaveis($texto) {
        if (empty($texto)) return '';
        
        foreach ($this->variaveis as $key => $val) {
            if (is_array($val)) continue; // Skip arrays
            $placeholder = '${' . $key . '}';
            $texto = str_replace($placeholder, $val, $texto);
        }
        return $texto;
    }
}

// =====================================================
// MAIN EXECUTION
// =====================================================

// isDrone flag (pode vir de POST ou estar na proposta)
$isDrone = (isset($_POST['is_drone']) && $_POST['is_drone'] == '1') || (isset($variaveis['is_drone']) && $variaveis['is_drone'] == '1');

// Instancia Renderizador
$renderer = new ProposalBlockRenderer($variaveis, $isDrone);

// Recupera a estrutura de blocos do banco de dados
$blocks = [];
if (isset($conn)) {
    try {
        $result = $conn->query("SELECT * FROM proposal_block_templates WHERE is_active = 1 ORDER BY `order` ASC");
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $blocks[] = $row;
            }
        }
    } catch (Exception $e) {
        // Tabela não existe ou erro de SQL? Usa fallback silenciosamente.
        // error_log("Erro ao buscar templates de bloco: " . $e->getMessage());
    }
}

// Se não houver blocos no banco (fallback ou falha de query), cria estrutura padrão compatível
if (empty($blocks)) {
    // Fallback Mock para garantir funcionamento mesmo sem DB atualizado
    $blocks = [
        ['slug' => 'cabecalho', 'category' => 'layout', 'name' => 'Cabeçalho'],
        ['slug' => 'dados_cliente', 'category' => 'presentation', 'name' => 'Dados do Cliente'],
        ['slug' => 'local_obra', 'category' => 'presentation', 'name' => 'Local da Obra'],
        ['slug' => 'apresentacao', 'name' => '1. Apresentação', 'category' => 'presentation'],
        ['slug' => 'metodologia', 'name' => '2. Metodologia', 'category' => 'technical'],
        ['slug' => 'documentacao', 'name' => '3. Documentação', 'category' => 'technical'],
        ['slug' => 'tabela_prazos', 'name' => '4. Prazos', 'category' => 'technical'],
        ['slug' => 'investimento', 'name' => '5. Investimento', 'category' => 'financial'],
        ['slug' => 'condicoes_pagamento', 'name' => '6. Pagamento', 'category' => 'financial'],
        ['slug' => 'equipamentos', 'name' => '7. Equipamentos', 'category' => 'technical'],
        ['slug' => 'consideracoes', 'name' => '8. Considerações', 'category' => 'legal']
    ];
}

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Proposta <?php echo htmlspecialchars($variaveis['numero_proposta'] ?? 'Nova'); ?></title>
    <?php echo getProposalStyles(); ?>
</head>
<body>

    <div class="no-print" style="position:fixed; top:20px; right:20px; z-index:9999; display:flex; gap:10px;">
        <a href="<?php echo htmlspecialchars(BASE_URL); ?>/painel_crm.php" style="background:#64748b; color:white; text-decoration:none; padding:10px 20px; border-radius:5px; font-size:14px; font-weight:bold; box-shadow:0 2px 5px rgba(0,0,0,0.2); display:flex; align-items:center;">
            ⬅️ Voltar ao Painel
        </a>
        <button onclick="window.print()" style="background:#2563eb; color:white; border:none; padding:10px 20px; border-radius:5px; cursor:pointer; font-size:14px; font-weight:bold; box-shadow:0 2px 5px rgba(0,0,0,0.2);">
            🖨️ Imprimir / Salvar PDF
        </button>
    </div>

    <main class="proposal-document">
        
        <?php
        // Renderiza Blocos Dinamicamente
        foreach ($blocks as $block) {
            $slug = $block['slug'];
            $category = $block['category'];
            $name = $block['name'] ?? '';
            
            // Lógica de Despacho (Router)
            switch ($slug) {
                // --- BLOCOS DE LAYOUT E DADOS FIXOS ---
                case 'cabecalho':
                    $renderer->renderCabecalho();
                    break;
                    
                case 'dados_cliente':
                    $renderer->renderDadosCliente();
                    break;
                    
                case 'local_obra':
                    $renderer->renderLocalObra();
                    break;
                
                case 'rodape':
                    // Rodapé renderizado no final das considerações, mas se tiver bloco isolado, ignora
                    break;

                // --- BLOCOS ESPECÍFICOS FINANCEIROS/TÉCNICOS ---
                case 'equipamentos':
                    $renderer->renderEquipamentos();
                    break;
                    
                case 'prazos': 
                case 'tabela_prazos':
                    $renderer->renderTabelaPrazos();
                    break;
                    
                case 'investimento':
                    $renderer->renderInvestimento();
                    break;
                    
                case 'condicoes_pagamento':
                    $renderer->renderCondicoesPagamento();
                    break;
                    
                case 'dados_bancarios':
                    // Incluído em condicoes_pagamento
                    break;

                case 'consideracoes':
                    // Inclui rodapé e assinatura
                    $renderer->renderConsideracoesRodape(); 
                    break;

                // --- BLOCOS DE TEXTO GENÉRICO ---
                default:
                    // PADRÃO DE CHAVE: Tenta pelo SLUG primeiro ou slug + '_content'
                    $contentKey = $slug; 
                    if (!isset($variaveis[$contentKey]) && isset($variaveis[$slug . '_content'])) {
                        $contentKey = $slug . '_content';
                    }
                    
                    // Extrair número do título se houver (ex: "1. Apresentação")
                    $tituloExibicao = $name;
                    $numero = '';
                    if (preg_match('/^(\d+)\.\s*(.*)$/', $name, $matches)) {
                        $numero = $matches[1];
                        $tituloExibicao = $matches[2];
                    }
                    
                    $renderer->renderSecaoTexto($numero, $tituloExibicao, $contentKey);
                    break;
            }
        }
        ?>
        
    </main>

</body>
</html>
