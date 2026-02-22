<?php
// Inicia sessão para acessar dados do usuário logado
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once 'vendor/autoload.php';
require_once 'db.php'; // Garante acesso à conexão $conn


// Imports removidos (V2)

// =====================================================
// FUNÇÕES AUXILIARES
// =====================================================

/**
 * Formata um valor numérico como moeda brasileira (R$ X.XXX,XX)
 * Aceita número puro, string numérica ou string já formatada
 */
function formatarMoeda($valor)
{
    if (empty($valor) || $valor === 'R$ 0,00' || $valor === '0' || $valor === 0) {
        return 'R$ 0,00';
    }

    // Se já está formatado como moeda brasileira, retorna
    if (is_string($valor) && strpos($valor, 'R$') === 0) {
        return $valor;
    }

    // Converte para número
    if (is_string($valor)) {
        // Remove "R$" e espaços
        $valor = str_replace(['R$', 'R$ ', ' '], '', $valor);
        $valor = trim($valor);

        // Detecta formato brasileiro: X.XXX,XX (tem vírgula como separador decimal)
        if (strpos($valor, ',') !== false) {
            // Remove pontos de milhar primeiro
            $valor = str_replace('.', '', $valor);
            // Converte vírgula decimal em ponto
            $valor = str_replace(',', '.', $valor);
        }
        // Se só tem ponto, já está no formato americano
    }

    $numero = floatval($valor);
    return 'R$ ' . number_format($numero, 2, ',', '.');
}


/**
 * Converte valor numérico para texto por extenso em português
 * Ex: 4450.00 => "quatro mil quatrocentos e cinquenta reais"
 */
function valorPorExtenso($valor)
{
    // Limpa o valor - IMPORTANTE: primeiro remove R$ e espaços, depois trata pontos/vírgulas
    if (is_string($valor)) {
        // Remove "R$" e espaços em branco
        $valor = str_replace(['R$', 'R$ ', ' '], '', $valor);
        $valor = trim($valor);

        // Detecta formato brasileiro: X.XXX,XX (tem vírgula como separador decimal)
        if (strpos($valor, ',') !== false) {
            // Remove pontos de milhar primeiro
            $valor = str_replace('.', '', $valor);
            // Converte vírgula decimal em ponto
            $valor = str_replace(',', '.', $valor);
        }
        // Se só tem ponto, já está no formato americano
    }

    $valor = floatval($valor);

    if ($valor == 0) {
        return 'zero reais';
    }


    $unidades = [
        '',
        'um',
        'dois',
        'três',
        'quatro',
        'cinco',
        'seis',
        'sete',
        'oito',
        'nove',
        'dez',
        'onze',
        'doze',
        'treze',
        'quatorze',
        'quinze',
        'dezesseis',
        'dezessete',
        'dezoito',
        'dezenove'
    ];
    $dezenas = ['', '', 'vinte', 'trinta', 'quarenta', 'cinquenta', 'sessenta', 'setenta', 'oitenta', 'noventa'];
    $centenas = ['', 'cento', 'duzentos', 'trezentos', 'quatrocentos', 'quinhentos', 'seiscentos', 'setecentos', 'oitocentos', 'novecentos'];

    $inteiro = intval($valor);
    $centavos = round(($valor - $inteiro) * 100);

    $extenso = '';

    // Função auxiliar para converter número < 1000
    $converterCentena = function ($n) use ($unidades, $dezenas, $centenas) {
        if ($n == 0) return '';
        if ($n == 100) return 'cem';

        $c = intval($n / 100);
        $d = intval(($n % 100) / 10);
        $u = $n % 10;

        $texto = '';
        if ($c > 0) $texto .= $centenas[$c];

        if ($n % 100 > 0 && $c > 0) $texto .= ' e ';

        if ($n % 100 < 20) {
            $texto .= $unidades[$n % 100];
        } else {
            $texto .= $dezenas[$d];
            if ($u > 0) $texto .= ' e ' . $unidades[$u];
        }
        return trim($texto);
    };

    // Milhões
    if ($inteiro >= 1000000) {
        $milhoes = intval($inteiro / 1000000);
        $extenso .= $converterCentena($milhoes) . ($milhoes == 1 ? ' milhão' : ' milhões');
        $inteiro %= 1000000;
        if ($inteiro > 0) $extenso .= ($inteiro < 100 ? ' e ' : ' ');
    }

    // Milhares
    if ($inteiro >= 1000) {
        $milhares = intval($inteiro / 1000);
        if ($milhares == 1) {
            $extenso .= 'mil';
        } else {
            $extenso .= $converterCentena($milhares) . ' mil';
        }
        $inteiro %= 1000;
        if ($inteiro > 0) $extenso .= ($inteiro < 100 ? ' e ' : ' ');
    }

    // Centenas/Dezenas/Unidades
    if ($inteiro > 0) {
        $extenso .= $converterCentena($inteiro);
    }

    // Adiciona "reais"
    $extenso = trim($extenso);
    if ($extenso == 'um') {
        $extenso .= ' real';
    } else {
        $extenso .= ' reais';
    }

    // Centavos
    if ($centavos > 0) {
        $extenso .= ' e ' . $converterCentena($centavos);
        $extenso .= ($centavos == 1 ? ' centavo' : ' centavos');
    }

    return $extenso;
}

/**
 * Retorna a data atual por extenso em português
 * Ex: "01 de fevereiro de 2026"
 */
function dataPorExtenso($data = null)
{
    $meses = [
        1 => 'janeiro',
        2 => 'fevereiro',
        3 => 'março',
        4 => 'abril',
        5 => 'maio',
        6 => 'junho',
        7 => 'julho',
        8 => 'agosto',
        9 => 'setembro',
        10 => 'outubro',
        11 => 'novembro',
        12 => 'dezembro'
    ];

    if ($data === null) {
        $data = time();
    } elseif (is_string($data)) {
        $data = strtotime($data);
    }

    $dia = date('d', $data);
    $mes = $meses[intval(date('m', $data))];
    $ano = date('Y', $data);

    return "{$dia} de {$mes} de {$ano}";
}

// Inicializa a conexão se não estiver setada (db.php geralmente seta)
if (!isset($conn) || !($conn instanceof mysqli)) {
    $conn = Database::getProd();
}

// --- DADOS PARA CALCULADORA (SGT_DATA) ---
// Recupera dados auxiliares para os selects da calculadora
$arrays_js = [];
$tabelas = [
    'Tipo_Servicos' => ['id' => 'id_servico', 'nome' => 'nome', 'extra' => 'descricao'],
    'Tipo_Funcoes' => ['id' => 'id_funcao', 'nome' => 'nome', 'valor' => 'salario_base_default'],
    'Tipo_Estadia' => ['id' => 'id_estadia', 'nome' => 'nome', 'valor' => 'valor_unitario_default'],
    'Tipo_Consumo' => ['id' => 'id_consumo', 'nome' => 'nome', 'litro' => 'valor_litro_default', 'kml' => 'consumo_kml_default'],
    'Tipo_Locacao' => ['id' => 'id_locacao', 'nome' => 'nome', 'valor' => 'valor_mensal_default'],
    'Tipo_Custo_Admin' => ['id' => 'id_custo_admin', 'nome' => 'nome', 'valor' => 'valor_default']
];

foreach ($tabelas as $tabela => $cols) {
    if ($result = $conn->query("SELECT * FROM {$tabela} ORDER BY nome ASC")) {
        $arrays_js[$tabela] = [];
        while ($row = $result->fetch_assoc()) {
            $item = ['id' => $row[$cols['id']], 'nome' => $row[$cols['nome']]];
            if (isset($cols['extra'])) $item['descricao'] = $row[$cols['extra']];
            if (isset($cols['valor'])) $item['valor'] = (float)$row[$cols['valor']];
            if (isset($cols['litro'])) $item['litro'] = (float)$row[$cols['litro']];
            if (isset($cols['kml'])) $item['kml'] = (float)$row[$cols['kml']];
            $arrays_js[$tabela][] = $item;
        }
    }
}

// Marcas
$marcas_por_tipo = [];
if ($result = $conn->query("SELECT id_marca, id_locacao, nome_marca FROM Marcas ORDER BY nome_marca ASC")) {
    while ($row = $result->fetch_assoc()) {
        $marcas_por_tipo[$row['id_locacao']][] = [
            'id' => $row['id_marca'],
            'nome' => $row['nome_marca']
        ];
    }
}

// Endereço Empresa (para cálculo de distância)
// OBS: Em Produção, pegar do usuário logado. Aqui hardcoded ou pegar do primeiro.
$empresa_endereco = '';
// $id_usuario = $_SESSION['usuario_id'] ?? 0; // Se tiver sessão
// ... query normal ...

// Monta objeto para o JS
$SGT_DATA = [
    'opcoesFuncao' => isset($arrays_js['Tipo_Funcoes']) ? $arrays_js['Tipo_Funcoes'] : [],
    'opcoesEstadia' => isset($arrays_js['Tipo_Estadia']) ? $arrays_js['Tipo_Estadia'] : [],
    'opcoesConsumo' => isset($arrays_js['Tipo_Consumo']) ? $arrays_js['Tipo_Consumo'] : [],
    'opcoesLocacao' => isset($arrays_js['Tipo_Locacao']) ? $arrays_js['Tipo_Locacao'] : [],
    'opcoesAdmin'   => isset($arrays_js['Tipo_Custo_Admin']) ? $arrays_js['Tipo_Custo_Admin'] : [],
    'marcasPorTipo' => $marcas_por_tipo,
    'enderecoEmpresa' => $empresa_endereco
];
// -----------------------------------------


// =====================================================
// EDITOR DINÂMICO V2 - ARQUITETURA EXPLÍCITA
// =====================================================

// Inicializa a conexão se não estiver setada
if (!isset($conn) || !($conn instanceof mysqli)) {
    $conn = Database::getProd();
}

// DETECÇÃO DE CONTEXTO (Serviço)
$idServicoContexto = 0;
if (!empty($_POST['id_servico'])) {
    $idServicoContexto = intval($_POST['id_servico']);
} elseif (!empty($_GET['id_proposta'])) {
    $idProp = intval($_GET['id_proposta']);
    $res = $conn->query("SELECT id_servico FROM Propostas WHERE id_proposta = $idProp");
    if ($res && $row = $res->fetch_assoc()) {
        $idServicoContexto = intval($row['id_servico']);
    }
}

// CARREGAMENTO DOS BLOCOS (V2)
$globalBlocks = [];
if ($idServicoContexto > 0) {
    // Busca estrutura base do serviço
    $stmtBlock = $conn->prepare("SELECT * FROM service_type_blocks WHERE service_type_id = ? AND is_active = 1 ORDER BY display_order ASC");
    $stmtBlock->bind_param("i", $idServicoContexto);
    $stmtBlock->execute();
    $resultBlock = $stmtBlock->get_result();
    while ($row = $resultBlock->fetch_assoc()) {
        // Normaliza campos para compatibilidade visual
        $row['id'] = $row['block_slug']; // Slug é o ID único do bloco
        $row['title'] = $row['block_title'];
        $row['required'] = (bool)$row['is_required'];
        $globalBlocks[] = $row;
    }
    $stmtBlock->close();
}

// Metadata provisório
$metadata = ['name' => 'Editor de Proposta'];
if (isset($_POST['tipo_servico'])) $metadata['name'] = $_POST['tipo_servico'];



?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editor Dinâmico | <?= $metadata['name'] ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.inputmask/5.0.6/jquery.inputmask.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script> <!-- Tailwind via CDN para prototipagem rápida -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.8.3/tinymce.min.js" referrerpolicy="origin"></script>

    <script>
        // Dados do Banco de Dados injetados para a Calculadora
        window.SGT_DATA = <?= json_encode($SGT_DATA); ?>;
    </script>
    <script src="calculos.js"></script> <!-- O Cérebro da Calculadora (Sistema Existente) -->
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#2563eb',
                        secondary: '#64748b',
                        tech: '#0ea5e9',
                        financial: '#10b981',
                        presentation: '#8b5cf6',
                        legal: '#94a3b8'
                    }
                }
            }
        }


    </script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8fafc;
        }

        .block-card {
            transition: all 0.2s;
        }

        .block-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }

        /* Cores de borda por categoria */
        .border-l-presentation {
            border-left-color: #8b5cf6;
        }

        .border-l-technical {
            border-left-color: #0ea5e9;
        }

        .border-l-financial {
            border-left-color: #10b981;
        }

        .border-l-legal {
            border-left-color: #94a3b8;
        }

        .border-l-layout {
            border-left-color: #334155;
        }

        /* Fixes para Calculadora */
        .cost-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            background: #f8fafc;
            padding: 0.75rem;
            border-radius: 0.5rem;
            border: 1px solid #e2e8f0;
            margin-bottom: 0.5rem;
        }

        .cost-icon {
            width: 2rem;
            height: 2rem;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #eff6ff;
            color: #2563eb;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .cost-details {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr 1fr;
            /* Layout Tabela */
            gap: 1rem;
            /* Espaçamento vital */
            flex: 1;
            align-items: end;
            /* Alinha inputs por baixo */
        }

        /* Melhoria de Visibilidade dos Campos (Solicitação Usuário) */
        .cost-details label {
            display: block;
            font-size: 0.75rem;
            font-weight: 600;
            color: #475569;
            /* Slate 600 - Mais escuro/legível */
            margin-bottom: 0.25rem;
            text-transform: uppercase;
            letter-spacing: 0.025em;
        }

        .cost-details input,
        .cost-details select {
            width: 100%;
            border: 1px solid #cbd5e1;
            /* Borda mais visível */
            border-radius: 0.375rem;
            padding: 0.5rem;
            font-size: 0.95rem;
            /* Fonte maior */
            color: #0f172a;
            /* Preto quase puro */
            background-color: #fff;
        }

        .cost-details input:focus,
        .cost-details select:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.1);
            outline: none;
        }

        /* Ajuste Mobile */
        @media (max-width: 768px) {
            .cost-details {
                grid-template-columns: 1fr;
                gap: 0.75rem;
            }

            .cost-item {
                flex-direction: column;
                align-items: stretch;
            }

            .cost-icon {
                display: none;
                /* Esconde ícone no mobile para limpar a tela */
            }
        }
    </style>
</head>

<body class="h-screen flex flex-col">

    <!-- Header -->
    <header class="bg-white border-b border-slate-200 px-6 py-4 flex justify-between items-center sticky top-0 z-50">
        <div class="flex items-center gap-3">
            <div class="bg-primary/10 text-primary p-2 rounded-lg">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                </svg>
            </div>
            <div>
                <h1 class="font-bold text-slate-800 text-lg"><?= $metadata['name'] ?></h1>
                <p class="text-xs text-slate-500">Editor Dinâmico v1.0 • ProposalArchitect™ Engine</p>
            </div>
        </div>
        <div class="flex gap-3">
            <button type="button" onclick="salvarRascunho()" class="px-4 py-2 text-sm font-medium text-slate-600 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path>
                </svg>
                Salvar Rascunho
            </button>
            <!-- Hidden field for Save Format -->
            <input type="hidden" name="formato_saida" id="inputFormatoSaida" value="docx">

            <button type="button" onclick="submitForm('html')" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 shadow-lg shadow-blue-600/25 transition-all flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                </svg>
                Salvar e Visualizar Web
            </button> <!-- Fim -->

            <button type="button" onclick="submitForm('docx')" class="px-5 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 shadow-lg shadow-green-600/25 transition-all flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Gerar Word
            </button>
        </div>
    </header>

    <div class="flex flex-1 overflow-hidden">

        <!-- Sidebar Navigation (Gerada Dinamicamente) -->
        <aside class="w-64 bg-white border-r border-slate-200 overflow-y-auto hidden md:block">
            <div class="p-4">
                <h3 class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-4">Índice da Proposta</h3>
                <nav class="space-y-1">
                    <?php
                    // Renderização da Navegação (V2 - Flat List)
                    function renderNav($blocks)
                    {
                        if (empty($blocks)) {
                            echo "<div class='p-4 text-xs text-slate-400'>Nenhum bloco definido.</div>";
                            return;
                        }

                        foreach ($blocks as $node) {
                            // Ícones por categoria
                            $iconMap = [
                                'technical' => 'text-tech',
                                'financial' => 'text-financial',
                                'presentation' => 'text-presentation',
                                'legal' => 'text-legal',
                                'layout' => 'text-slate-400'
                            ];
                            $iconColor = isset($iconMap[$node['category']]) ? $iconMap[$node['category']] : 'text-slate-400';
                            $activeClass = 'text-slate-600'; // Default styling

                            echo "<a href='#block-{$node['id']}' class='flex items-center gap-2 px-3 py-2 rounded-md hover:bg-slate-50 {$activeClass}'>";
                            echo "<div class='w-2 h-2 rounded-full {$node['category']} bg-current opacity-50 {$iconColor}'></div>";
                            echo "<span class='text-sm'>{$node['title']}</span>";
                            echo "</a>";
                        }
                    }
                    renderNav($globalBlocks);
                    ?>
                </nav>
            </div>

            <!-- Barra de Progresso Fake -->
            <div class="p-4 border-t border-slate-200 mt-auto">
                <div class="flex justify-between text-xs mb-1">
                    <span class="font-medium text-slate-600">Completude</span>
                    <span class="text-primary font-bold">0%</span>
                </div>
                <div class="w-full bg-slate-100 rounded-full h-2">
                    <div class="bg-primary h-2 rounded-full w-0 transition-all duration-500" style="width: 0%"></div>
                </div>
            </div>
        </aside>

        <!-- Main Content (Formulário Gerado) -->
        <main class="flex-1 overflow-y-auto p-8 scroll-smooth" id="main-scroll">
            <div class="max-w-3xl mx-auto space-y-8">

                <?php
                // Captura dados vindos do Wizard (POST)
                $incomingData = $_POST;

                // --- LÓGICA DE EDIÇÃO (CARREGAR DO BANCO SE VIER VIA GET) ---
                if (empty($incomingData) && isset($_GET['id_proposta'])) {
                    $idPropEdit = intval($_GET['id_proposta']);
                    
                    if ($conn && $idPropEdit > 0) {
                        // 1. Busca Dados Principais da Proposta
                        $sqlEdit = "SELECT p.*, c.nome_cliente, c.email, c.telefone, c.celular, c.whatsapp, c.empresa as empresa_cliente,
                                    s.nome as nome_servico, s.descricao as descricao_servico
                                    FROM Propostas p 
                                    LEFT JOIN Clientes c ON p.id_cliente = c.id_cliente 
                                    LEFT JOIN Tipo_Servicos s ON p.id_servico = s.id_servico
                                    WHERE p.id_proposta = ?";
                        $stmtEdit = $conn->prepare($sqlEdit);
                        $stmtEdit->bind_param("i", $idPropEdit);
                        $stmtEdit->execute();
                        $resEdit = $stmtEdit->get_result();
                        
                        if ($rowEdit = $resEdit->fetch_assoc()) {
                            // Mapeia campos do Banco para formato do Editor ($incomingData)
                            $incomingData['id_proposta'] = $rowEdit['id_proposta'];
                            $incomingData['id_proposta_original'] = $rowEdit['id_proposta']; // Para salvar como UPDATE
                            $incomingData['numero_proposta'] = $rowEdit['numero_proposta'];
                            
                            // Cliente
                            $incomingData['id_cliente'] = $rowEdit['id_cliente'];
                            $incomingData['nome_cliente'] = $rowEdit['nome_cliente_salvo']; // Usa o salvo na proposta (snapshot)
                            $incomingData['empresa_cliente'] = $rowEdit['empresa_cliente_salvo'] ?? ($rowEdit['empresa_cliente'] ?? ''); // Snapshot ou atual
                            $incomingData['email'] = $rowEdit['email_salvo'];
                            $incomingData['telefone'] = $rowEdit['telefone_salvo'];
                            $incomingData['celular'] = $rowEdit['celular_salvo'];
                            $incomingData['empresa_cliente'] = $rowEdit['empresa_cliente']; // Do join atual

                            // Serviço e Local
                            $incomingData['id_servico'] = $rowEdit['id_servico'];
                            $incomingData['tipo_servico'] = $rowEdit['nome_servico']; 
                            $incomingData['finalidade'] = $rowEdit['finalidade'];
                            $incomingData['tipo_levantamento'] = $rowEdit['tipo_levantamento'];
                            $incomingData['area'] = $rowEdit['area_obra']; // Editor espera 'area', banco tem 'area_obra'
                            // $incomingData['unidade_area'] = 'm²'; // Default
                            $incomingData['endereco'] = $rowEdit['endereco_obra'];
                            $incomingData['bairro'] = $rowEdit['bairro_obra'];
                            $incomingData['cidade'] = $rowEdit['cidade_obra'];
                            $incomingData['estado'] = $rowEdit['estado_obra'];

                            // Cronograma
                            $incomingData['prazo_execucao'] = $rowEdit['prazo_execucao'];
                            $incomingData['dias_campo'] = $rowEdit['dias_campo'];
                            $incomingData['dias_escritorio'] = $rowEdit['dias_escritorio'];

                            // Financeiro
                            // Nota: O banco guarda formatado em float, o editor espera float ou string.
                            // Para garantir compatibilidade com `formatarMoeda`, passamos raw.
                            $incomingData['valor_final_proposta'] = $rowEdit['valor_final_proposta'];
                            $incomingData['valor_lucro'] = $rowEdit['valor_lucro'];
                            $incomingData['mobilizacao_percentual'] = $rowEdit['mobilizacao_percentual'];
                            $incomingData['mobilizacao_valor'] = $rowEdit['mobilizacao_valor'];
                            $incomingData['restante_percentual'] = $rowEdit['restante_percentual'];
                            $incomingData['restante_valor'] = $rowEdit['restante_valor'];

                            // Drone / Extras
                            $incomingData['tipo_terreno'] = $rowEdit['tipo_terreno'] ?? '';
                            $incomingData['cobertura_vegetal'] = $rowEdit['cobertura_vegetal'] ?? '';
                            $incomingData['acesso_local'] = $rowEdit['acesso_local'] ?? '';
                            $incomingData['restricoes_aereas'] = $rowEdit['restricoes_aereas'] ?? '';
                            $incomingData['coordenadas_gps'] = $rowEdit['coordenadas_gps'] ?? '';

                        }
                        $stmtEdit->close();

                        // 3. Recupera Conteúdo Personalizado (Textos Editados)
                        $stmtContent = $conn->prepare("SELECT block_id, conteudo_texto FROM Proposta_Conteudo_Personalizado WHERE id_proposta = ?");
                        $stmtContent->bind_param("i", $idPropEdit);
                        $stmtContent->execute();
                        $resContent = $stmtContent->get_result();
                        while ($rowContent = $resContent->fetch_assoc()) {
                             $incomingData[$rowContent['block_id']] = $rowContent['conteudo_texto'];
                             
                             // Mapeamentos específicos de legacy
                             if ($rowContent['block_id'] === 'apresentacao') $incomingData['apresentacao_content'] = $rowContent['conteudo_texto'];
                             if ($rowContent['block_id'] === 'escopo') $incomingData['escopo_content'] = $rowContent['conteudo_texto'];
                        }
                        $stmtContent->close();
                    }
                }

                // ==================================================================================
                // [FIX CRÍTICO] RECUPERAÇÃO FORÇADA DE CUSTOS (Se não vierem no POST ou na carga inicial)
                // Se estamos editando uma proposta (tem ID) e não temos os arrays de custo, buscamos do DB.
                // Isso garante que ao salvar vindo do Editor, não perderemos os custos.
                // ==================================================================================
                $idPropForCosts = $incomingData['id_proposta_original'] ?? ($incomingData['id_proposta'] ?? 0);
                
                // Verifica se falta algum dos principais arrays de custo
                $hasCosts = !empty($incomingData['salario_id_funcao']) || !empty($incomingData['estadia_id']) || !empty($incomingData['consumo_id']);
                
                if ($idPropForCosts > 0 && !$hasCosts && $conn) {
                    
                    // Recupera Salários
                    $resSal = $conn->query("SELECT * FROM Proposta_Salarios WHERE id_proposta = $idPropForCosts");
                    if ($resSal) while($row = $resSal->fetch_assoc()){
                        $incomingData['salario_id_funcao'][] = $row['id_funcao'];
                        $incomingData['salario_nome'][] = $row['funcao'];
                        $incomingData['salario_qtd'][] = $row['quantidade'];
                        $incomingData['salario_valor'][] = $row['salario_base'];
                        $incomingData['encargos'][] = ($row['fator_encargos'] - 1) * 100;
                        $incomingData['salario_dias'][] = $row['dias'];
                    }

                    // Recupera Estadia
                    $resEst = $conn->query("SELECT * FROM Proposta_Estadia WHERE id_proposta = $idPropForCosts");
                    if ($resEst) while($row = $resEst->fetch_assoc()){
                        $incomingData['estadia_id'][] = $row['id_estadia'];
                        $incomingData['estadia_nome'][] = $row['tipo'];
                        $incomingData['estadia_qtd'][] = $row['quantidade'];
                        $incomingData['estadia_valor'][] = $row['valor_unitario'];
                        $incomingData['estadia_dias'][] = $row['dias'];
                    }

                    // Recupera Consumos
                    $resCons = $conn->query("SELECT * FROM Proposta_Consumos WHERE id_proposta = $idPropForCosts");
                    if ($resCons) while($row = $resCons->fetch_assoc()){
                        $incomingData['consumo_id'][] = $row['id_consumo'];
                        $incomingData['consumo_nome'][] = $row['tipo'];
                        $incomingData['consumo_qtd'][] = $row['quantidade'];
                        $incomingData['consumo_kml'][] = $row['consumo_kml'];
                        $incomingData['consumo_litro'][] = $row['valor_litro'];
                        $incomingData['consumo_km_total'][] = $row['km_total'];
                    }

                    // Recupera Locação
                    $resLoc = $conn->query("SELECT * FROM Proposta_Locacao WHERE id_proposta = $idPropForCosts");
                    if ($resLoc) while($row = $resLoc->fetch_assoc()){
                        $incomingData['locacao_id'][] = $row['id_locacao'];
                        // Tenta recuperar nome
                        $nomeLoc = '';
                        if($row['id_locacao']) {
                            $qN = $conn->query("SELECT nome FROM Tipo_Locacao WHERE id_locacao = ".$row['id_locacao']);
                            if($qN && $rN=$qN->fetch_assoc()) $nomeLoc = $rN['nome'];
                        }
                        $incomingData['locacao_nome'][] = $nomeLoc;
                        $incomingData['locacao_id_marca'][] = $row['id_marca'];
                        $incomingData['locacao_qtd'][] = $row['quantidade'];
                        $incomingData['locacao_valor'][] = $row['valor_mensal'];
                        $incomingData['locacao_dias'][] = $row['dias'];
                    }

                    // Recupera Admin
                    $resAdm = $conn->query("SELECT * FROM Proposta_Custos_Administrativos WHERE id_proposta = $idPropForCosts");
                    if ($resAdm) while($row = $resAdm->fetch_assoc()){
                        $incomingData['admin_id'][] = $row['id_custo_admin'];
                        $incomingData['admin_nome'][] = $row['tipo'];
                        $incomingData['admin_qtd'][] = $row['quantidade'];
                        $incomingData['admin_valor'][] = $row['valor'];
                    }
                }
                // -----------------------------------------------------------

                // --- CARREGAR RASCUNHO SALVO (Se houver - Sobrescreve DB se for rascunho mais recente?) ---
                // Mantemos a lógica original de Rascunho, mas ajustada para funcionar com o ID carregado acima
                $idPropDraft = $incomingData['id_proposta_original'] ?? ($incomingData['id_proposta'] ?? null);
                if (!empty($idPropDraft) && $conn) {
                    // (Lógica existente de rascunho mantida abaixo...)
                    $stmtDraft = $conn->prepare("SELECT block_id, conteudo_texto FROM Proposta_Conteudo_Personalizado WHERE id_proposta = ?");
                    if ($stmtDraft) {
                        $stmtDraft->bind_param("i", $idPropDraft);
                        $stmtDraft->execute();
                        $resDraft = $stmtDraft->get_result();
                        while ($rowDraft = $resDraft->fetch_assoc()) {
                            // Só sobrescreve se tiver conteúdo
                            if (!empty($rowDraft['conteudo_texto'])) {
                                $incomingData[$rowDraft['block_id']] = $rowDraft['conteudo_texto'];
                            }
                        }
                        $stmtDraft->close();
                    }
                }
                // -------------------------------------------

                // Se veio id_cliente, busca TODOS os dados do cliente no banco
                if (!empty($incomingData['id_cliente']) && $conn) {
                    $id_cli = intval($incomingData['id_cliente']);
                    $stmt_cli = $conn->prepare("SELECT nome_cliente, empresa, email, telefone, celular FROM Clientes WHERE id_cliente = ?");
                    $stmt_cli->bind_param("i", $id_cli);
                    $stmt_cli->execute();
                    $res_cli = $stmt_cli->get_result();
                    if ($row_cli = $res_cli->fetch_assoc()) {
                        $incomingData['nome_cliente'] = $row_cli['nome_cliente'];
                        $incomingData['empresa_cliente'] = $row_cli['empresa'] ?? '';
                        $incomingData['email'] = $row_cli['email'] ?? '';
                        $incomingData['telefone'] = $row_cli['telefone'] ?? '';
                        $incomingData['celular'] = $row_cli['celular'] ?? '';
                    }
                    $stmt_cli->close();
                }

                // Se veio id_servico mas não tipo_servico, busca o nome do serviço
                if (!empty($incomingData['id_servico']) && empty($incomingData['tipo_servico']) && $conn) {
                    $id_srv = intval($incomingData['id_servico']);
                    $stmt_srv = $conn->prepare("SELECT nome FROM Tipo_Servicos WHERE id_servico = ?");
                    $stmt_srv->bind_param("i", $id_srv);
                    $stmt_srv->execute();
                    $res_srv = $stmt_srv->get_result();
                    if ($row_srv = $res_srv->fetch_assoc()) {
                        $incomingData['tipo_servico'] = $row_srv['nome'];
                    }
                    $stmt_srv->close();
                }

                // Extrai equipamentos dos arrays de locação (locacao_nome[] e locacao_id_marca[])
                // O wizard envia arrays com os nomes dos equipamentos e IDs das marcas
                $equipamentosUtilizados = [];

                // Busca nomes das marcas pelos IDs
                $marcasNomes = [];
                if (!empty($incomingData['locacao_id_marca']) && is_array($incomingData['locacao_id_marca']) && $conn) {
                    foreach ($incomingData['locacao_id_marca'] as $idx => $idMarca) {
                        if (!empty($idMarca)) {
                            $stmtMarca = $conn->prepare("SELECT nome_marca FROM Marcas WHERE id_marca = ?");
                            $stmtMarca->bind_param("i", $idMarca);
                            $stmtMarca->execute();
                            $resMarca = $stmtMarca->get_result();
                            if ($rowMarca = $resMarca->fetch_assoc()) {
                                $marcasNomes[$idx] = $rowMarca['nome_marca'];
                            }
                            $stmtMarca->close();
                        }
                    }
                }

                // Processa equipamentos com suas marcas
                if (!empty($incomingData['locacao_nome']) && is_array($incomingData['locacao_nome'])) {
                    foreach ($incomingData['locacao_nome'] as $idx => $equipNome) {
                        if (!empty($equipNome)) {
                            // Combina nome do equipamento com a marca
                            $nomeCompleto = $equipNome;
                            if (!empty($marcasNomes[$idx])) {
                                $nomeCompleto .= ' ' . $marcasNomes[$idx];
                            }

                            $nome = strtolower($equipNome);
                            // Categoriza por tipo de equipamento
                            // MELHORIA: Junta múltiplos equipamentos se houver mais de um do mesmo tipo
                            if (strpos($nome, 'estação') !== false || strpos($nome, 'estacao') !== false || strpos($nome, 'total') !== false) {
                                $equipamentosUtilizados['estacao_total'][] = $nomeCompleto;
                            } elseif (strpos($nome, 'gps') !== false || strpos($nome, 'gnss') !== false || strpos($nome, 'rtk') !== false) {
                                $equipamentosUtilizados['gps'][] = $nomeCompleto;
                            } elseif (strpos($nome, 'drone') !== false || strpos($nome, 'vant') !== false || strpos($nome, 'dji') !== false) {
                                $equipamentosUtilizados['drone'][] = $nomeCompleto;
                            } elseif (strpos($nome, 'veículo') !== false || strpos($nome, 'veiculo') !== false || strpos($nome, 'carro') !== false) {
                                $equipamentosUtilizados['veiculo'][] = $nomeCompleto;
                            }
                        }
                    }
                }

                // Mescla equipamentos extraídos com incomingData (converte array em string separada por vírgula)
                if (!empty($equipamentosUtilizados['estacao_total'])) {
                    $incomingData['estacao_total'] = implode(', ', $equipamentosUtilizados['estacao_total']);
                }
                if (!empty($equipamentosUtilizados['gps'])) {
                    $incomingData['gps'] = implode(', ', $equipamentosUtilizados['gps']);
                }
                if (!empty($equipamentosUtilizados['drone'])) {
                    $incomingData['drone'] = implode(', ', $equipamentosUtilizados['drone']);
                }
                if (!empty($equipamentosUtilizados['veiculo'])) {
                    $incomingData['veiculo'] = implode(', ', $equipamentosUtilizados['veiculo']);
                }


                // [V2] Lógica "Mágica" de Contexto (Drone/Padrão) REMOVIDA.
                // O conteúdo agora vem explicitamente da tabela `service_type_blocks`.
                


                // =====================================================
                // MAPEAMENTO DE VARIÁVEIS DO WIZARD PARA SUBSTITUIÇÃO
                // =====================================================
                function getVariableMap($incomingData, $conn = null)
                {
                    // Busca dados da empresa do usuário logado
                    global $equipamentosUtilizados;
                    $empresa = [];
                    if ($conn) {
                        // Tenta pegar o id do usuário da sessão
                        $id_usuario = isset($_SESSION['usuario_id']) ? intval($_SESSION['usuario_id']) : 0;

                        if ($id_usuario > 0) {
                            // Busca empresa do usuário logado
                            $stmt_emp = $conn->prepare("SELECT * FROM DadosEmpresa WHERE id_criador = ? LIMIT 1");
                            $stmt_emp->bind_param('i', $id_usuario);
                            $stmt_emp->execute();
                            $res = $stmt_emp->get_result();
                            if ($row = $res->fetch_assoc()) {
                                $empresa = $row;
                            }
                            $stmt_emp->close();
                        }

                        // Fallback: se não encontrou, busca a primeira empresa
                        if (empty($empresa)) {
                            $res = $conn->query("SELECT * FROM DadosEmpresa LIMIT 1");
                            if ($res && $row = $res->fetch_assoc()) {
                                $empresa = $row;
                            }
                        }
                    }

                    // Determina o modo do logo: 'full' = logo completa, 'icon' = logo simples + título
                    // NOTA: Quando header_logo_mode='full', mostra só a logo grande
                    //       Quando header_logo_mode='icon', mostra logo pequena + título "PROPOSTA TÉCNICA COMERCIAL"
                    $modoLogo = $empresa['header_logo_mode'] ?? 'full';
                    // Traduz para o padrão usado no cabeçalho
                    $tipoLogo = ($modoLogo === 'full') ? 'completa' : 'simples';

                    return [
                        // Dados do Cliente
                        'nome_cliente' => $incomingData['nome_cliente'] ?? '',
                        'empresa_cliente' => $incomingData['empresa_cliente'] ?? '',
                        'nome_cliente_salvo' => $incomingData['nome_cliente'] ?? '',
                        'email_salvo' => $incomingData['email'] ?? '',
                        'telefone_salvo' => $incomingData['telefone'] ?? '',
                        'celular_salvo' => $incomingData['celular'] ?? '',
                        'whatsapp_salvo' => $incomingData['celular_cliente'] ?? ($incomingData['celular'] ?? ''),
                        'numero_proposta' => $incomingData['numero_proposta'] ?? '000/0000', // Captura o número vindo do hidden

                        // Local da Obra
                        // Local da Obra (Prioriza Wizard 'endereco', mas aceita Editor 'endereco_obra' para compatibilidade com Rascunhos)
                        'endereco_obra' => $incomingData['endereco'] ?? ($incomingData['endereco_obra'] ?? ''),
                        'bairro_obra' => $incomingData['bairro'] ?? ($incomingData['bairro_obra'] ?? ''),
                        'cidade_obra' => $incomingData['cidade'] ?? ($incomingData['cidade_obra'] ?? ''),
                        'estado_obra' => $incomingData['estado'] ?? ($incomingData['estado_obra'] ?? ''),

                        // Dados Técnicos
                        'finalidade' => trim($incomingData['finalidade'] ?? 'Levantamento Topográfico'),
                        'area_obra' => (!empty($incomingData['area'])
                            ? number_format(floatval($incomingData['area']), 2, ',', '.') . ' ' . ($incomingData['unidade_area'] ?? 'm²')
                            : 'A definir'),
                        'tipo_levantamento' => trim($incomingData['tipo_levantamento'] ?? 'Planialtimétrico Cadastral'),

                        // Equipamentos (Lógica Dinâmica: Pega do Post/Edit ou do Array Global calculado)
                        // Se $incomingData tiver os valores (ex: salvou rascunho), usa eles.
                        // Se não, usa o $equipamentosUtilizados calculado no início do arquivo (ex: vindo do Wizard).

                        'Veiculo' => !empty($incomingData['veiculo']) ? $incomingData['veiculo'] : ($equipamentosUtilizados['veiculo'][0] ?? 'Veículo próprio'),
                        'Estacao_Total' => !empty($incomingData['estacao_total']) ? $incomingData['estacao_total'] : ($equipamentosUtilizados['estacao_total'][0] ?? ''),
                        'GPS' => !empty($incomingData['gps']) ? $incomingData['gps'] : ($equipamentosUtilizados['gps'][0] ?? ''),
                        'Drone' => !empty($incomingData['drone']) ? $incomingData['drone'] : ($equipamentosUtilizados['drone'][0] ?? ''),

                        // Cronograma
                        'dias_campo' => $incomingData['dias_campo'] ?? '1',
                        'dias_escritorio' => $incomingData['dias_escritorio'] ?? '4',
                        'prazo_execucao' => $incomingData['prazo_execucao'] ?? '5 dias úteis',

                        // NOVAS VARIÁVEIS DO SISTEMA (Compatibilidade com novos templates)
                        'ClienteNome' => $incomingData['nome_cliente'] ?? '',
                        'ClienteEmail' => $incomingData['email'] ?? '',
                        'ClienteTelefone' => $incomingData['telefone'] ?? ($incomingData['celular'] ?? ''),
                        'ClienteEndereco' => $incomingData['endereco'] ?? ($incomingData['endereco_obra'] ?? ''),
                        'ClienteBairro' => $incomingData['bairro'] ?? ($incomingData['bairro_obra'] ?? ''),
                        'ClienteCidadeUF' => ($incomingData['cidade'] ?? '') . '/' . ($incomingData['estado'] ?? ''),
                        
                        // VARIÁVEIS ESPECÍFICAS DRONE (Com Fallback para persistência)
                        'TipoTerreno' => $incomingData['tipo_terreno'] ?? ($incomingData['TipoTerreno'] ?? 'Plano'),
                        'CoberturaVegetal' => $incomingData['cobertura_vegetal'] ?? ($incomingData['CoberturaVegetal'] ?? 'Baixa'),
                        'AcessoLocal' => $incomingData['acesso_local'] ?? ($incomingData['AcessoLocal'] ?? 'Fácil'),
                        'RestricoesAereas' => $incomingData['restricoes_aereas'] ?? ($incomingData['RestricoesAereas'] ?? 'Nenhuma'),
                        'CoordenadasGPS' => $incomingData['coordenadas_gps'] ?? ($incomingData['CoordenadasGPS'] ?? ''),

                        // Financeiro (formatados como moeda brasileira)
                        'ValorProposta' => formatarMoeda($incomingData['valor_final_proposta'] ?? 0),
                        'ValorExtenso' => valorPorExtenso($incomingData['valor_final_proposta'] ?? 0),
                        'mobilizacao_percentual' => $incomingData['mobilizacao_percentual'] ?? '30',
                        'mobilizacao_valor' => formatarMoeda($incomingData['mobilizacao_valor'] ?? 0),
                        'restante_percentual' => $incomingData['restante_percentual'] ?? '70',
                        'restante_valor' => formatarMoeda($incomingData['restante_valor'] ?? 0),

                        // Dados da Empresa
                        'Empresa' => $empresa['Empresa'] ?? 'ELM Topografia',
                        'CNPJ' => $empresa['CNPJ'] ?? '',
                        'Banco' => $empresa['Banco'] ?? '',
                        'Agencia' => $empresa['Agencia'] ?? '',
                        'Conta' => $empresa['Conta'] ?? '',
                        'PIX' => $empresa['PIX'] ?? ($empresa['CNPJ'] ?? ''),
                        'whatsapp' => $empresa['Whatsapp'] ?? '',

                        // Layout (Cabeçalho e Rodapé)
                        'logo_empresa' => $empresa['logo_caminho'] ?? 'assets/logo_sgt.png',
                        'logo_icon' => $empresa['logo_icon_caminho'] ?? '', // Logo ícone para modo compacto
                        'tipo_logo' => $tipoLogo, // 'completa' ou 'simples'
                        'header_logo_mode' => $modoLogo, // 'full' ou 'icon'
                        'numero_proposta' => $incomingData['numero_proposta'] ?? (date('Y') . '/' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT)),
                        'Cidade' => $empresa['Cidade'] ?? ($incomingData['cidade'] ?? 'Belo Horizonte'),
                        'DataExtenso' => dataPorExtenso(),
                    ];
                }


                // Função para substituir variáveis ${var} no texto
                function substituirVariaveis($texto, $variaveis)
                {
                    if (empty($texto)) return '';

                    foreach ($variaveis as $chave => $valor) {
                        $texto = str_replace('${' . $chave . '}', $valor, $texto);
                    }
                    return $texto;
                }

                // Função recursiva para renderizar os cards de input

                // Função Principal de Renderização (V2)
                function renderPropostaBlocks($blocks)
                {
                    global $conn, $incomingData;

                    if (empty($blocks)) {
                        echo "<div class='text-center p-8 text-slate-400'>Nenhum bloco de conteúdo definido para este serviço.</div>";
                        return;
                    }

                    // Prepara variáveis para substituição
                    $variaveis = getVariableMap($incomingData, $conn);

                    foreach ($blocks as $node) {
                        $slug = $node['id']; // block_slug
                        $borderClass = 'border-l-' . ($node['category'] ?? 'technical');
                        
                        // 1. RESOLUÇÃO DE CONTEÚDO
                        // Prioridade: 1. Input Usuário (POST/Edit) > 2. Conteúdo Padrão do Bloco
                        
                        $defaultContent = $node['default_content'] ?? '';
                        // Substitui variáveis apenas no default (templates)
                        // Se o usuário já editou, presume-se que o texto está final ou que ele gerencia as vars
                        $defaultContent = substituirVariaveis($defaultContent, $variaveis);

                        // Tenta encontrar valor já preenchido
                        $userValue = '';
                        if (!empty($incomingData[$slug . '_content'])) {
                            $userValue = $incomingData[$slug . '_content'];
                        } elseif (isset($incomingData[$slug]) && !is_array($incomingData[$slug])) {
                             // Fallback para caso o slug seja usado direto como nome do campo
                            $userValue = $incomingData[$slug];
                        }

                        $finalContent = $userValue !== '' ? $userValue : $defaultContent;

                        // Pula renderização de wrapper se for cabeçalho (renderiza full width/diferente)
                        if ($slug !== 'cabecalho') {
                            echo "<div id='block-{$slug}' class='bg-white rounded-xl shadow-sm border-l-4 {$borderClass} block-card p-6 scroll-mt-24'>";
                            
                            // Header do Card
                            echo "<div class='flex justify-between items-start mb-4'>";
                            echo "<div class='flex items-center gap-2'><h3 class='text-lg font-semibold text-slate-800'>{$node['title']}</h3><span class='uppercase text-[10px] bg-slate-100 text-slate-500 px-2 py-0.5 rounded'>{$node['category']}</span></div>";
                            if (!empty($node['required'])) echo "<span class='text-xs text-red-600 bg-red-50 px-2 rounded'>Obrigatório</span>";
                            echo "</div>";

                            echo "<div class='grid grid-cols-1 md:grid-cols-2 gap-4'>";
                        }

                        // === SWITCH DE RENDERIZAÇÃO POR TIPO (SLUG) ===
                        switch ($slug) {
                            case 'cabecalho':
                                // Lógica de Cabeçalho (Mantida)
                                $tipoLogo = $variaveis['tipo_logo'] ?? 'simples';
                                echo '<div id="block-cabecalho" class="mb-6 scroll-mt-24">';
                                if ($tipoLogo === 'completa') {
                                    echo '<div class="rounded-t-lg overflow-hidden"><img src="' . htmlspecialchars($variaveis['logo_empresa']) . '" class="w-full object-contain" onerror="this.src=\'assets/logo_sgt.png\'"></div>';
                                } else {
                                    echo '<div class="bg-gradient-to-r from-slate-800 to-slate-700 p-5 rounded-t-lg text-white flex items-center gap-5">';
                                    echo '<div class="w-16 h-16 rounded-lg p-1 flex items-center justify-center flex-shrink-0"><img src="' . htmlspecialchars($variaveis['logo_empresa']) . '" class="max-h-14 max-w-14 object-contain" onerror="this.src=\'assets/logo_sgt.png\'"></div>';
                                    echo '<div class="flex-1"><div class="text-2xl md:text-3xl font-normal tracking-wider uppercase">PROPOSTA TÉCNICA COMERCIAL</div></div>';
                                    echo '</div>';
                                }
                                echo '<div class="flex justify-between items-center bg-slate-100 border border-slate-200 rounded-b-lg px-5 py-3">';
                                echo '<div class="text-sm text-slate-600"><span class="font-medium">📅</span> ' . htmlspecialchars($variaveis['DataExtenso']) . '</div>';
                                echo '<div class="text-sm text-slate-600"><span class="font-medium">Nº</span> <strong class="text-slate-800">' . htmlspecialchars($variaveis['numero_proposta']) . '</strong></div>';
                                echo '</div></div>';
                                echo '<input type="hidden" name="numero_proposta" value="' . htmlspecialchars($variaveis['numero_proposta']) . '">';
                                break;

                            case 'rodape':
                                echo '<div class="col-span-2 bg-slate-100 p-4 rounded-lg border border-slate-200 text-center text-sm text-slate-600">';
                                echo htmlspecialchars($variaveis['Empresa']) . ' • CNPJ: ' . htmlspecialchars($variaveis['CNPJ']) . ' • WhatsApp: ' . htmlspecialchars($variaveis['whatsapp']);
                                echo '</div>';
                                break;

                            case 'equipamentos':
                            case 'equipamentos_padrao':
                            case 'equipamentos_drone':
                                // Renderiza inputs para edição dos equipamentos
                                renderInput('veiculo', 'Veículo', 'text', 'Ex: Fiat Strada', false, $variaveis['Veiculo']);
                                renderInput('estacao_total', 'Estação Total', 'text', 'Ex: Topcon ES-105', false, $variaveis['Estacao_Total']);
                                renderInput('gps', 'GPS/GNSS', 'text', 'Ex: Trimble R8s', false, $variaveis['GPS']);
                                renderInput('drone', 'Drone (opcional)', 'text', 'Ex: DJI Phantom 4 RTK', false, $variaveis['Drone']);
                                // Se houver texto complementar no bloco
                                if (!empty($finalContent)) {
                                    renderInput($slug . '_content', 'Observações Equipamentos', 'textarea', '', true, $finalContent);
                                }
                                break;
                            
                            case 'cronograma':
                            case 'cronograma_padrao':
                            case 'cronograma_drone':
                                echo '<div class="col-span-2 bg-gradient-to-r from-blue-50 to-cyan-50 p-4 rounded-lg border border-blue-200">';
                                echo '<label class="block text-sm font-bold text-blue-800 mb-3">📅 Cronograma de Execução</label>';
                                echo '<div class="grid grid-cols-3 gap-4">';
                                echo '<div class="text-center"><div class="text-3xl font-bold text-blue-600">' . htmlspecialchars($variaveis['dias_campo']) . '</div><div class="text-xs text-blue-500">Dia(s) de Campo</div></div>';
                                echo '<div class="text-center"><div class="text-3xl font-bold text-blue-600">' . htmlspecialchars($variaveis['dias_escritorio']) . '</div><div class="text-xs text-blue-500">Dia(s) de Escritório</div></div>';
                                echo '<div class="text-center"><div class="text-xl font-bold text-green-600 bg-green-100 px-3 py-2 rounded-lg">' . htmlspecialchars($variaveis['prazo_execucao']) . '</div><div class="text-xs text-green-500 mt-1">Prazo Total</div></div>';
                                echo '</div></div>';
                                renderInput('dias_campo', 'Dias de Campo', 'number', '1', false, $variaveis['dias_campo']);
                                renderInput('dias_escritorio', 'Dias de Escritório', 'number', '4', false, $variaveis['dias_escritorio']);
                                if (!empty($finalContent)) {
                                     // O conteúdo HTML (tabela) vai num textarea para edição ou preservado
                                    renderInput($slug . '_content', 'Detalhamento do Cronograma', 'textarea', '', true, $finalContent);
                                }
                                break;

                            case 'investimento':
                                $total = $variaveis['ValorProposta'];
                                $lucro = formatarMoeda($incomingData['valor_lucro'] ?? 0);
                                echo '<div class="col-span-2 bg-gradient-to-br from-green-50 to-emerald-50 p-4 rounded-lg border border-green-200">';
                                echo '<label class="block text-sm font-bold text-green-800 mb-2">💰 Resumo Financeiro</label>';
                                echo '<div class="grid grid-cols-2 gap-4">';
                                echo '<div><label class="text-xs text-green-600">Valor Total da Proposta</label><div class="text-2xl font-bold text-green-700">' . htmlspecialchars($total) . '</div><input type="hidden" name="valor_proposta" value="' . htmlspecialchars($total) . '"></div>';
                                echo '<div><label class="text-xs text-green-600">Lucro Estimado</label><div class="text-lg font-medium text-blue-600">' . htmlspecialchars($lucro) . '</div></div>';
                                echo '</div></div>';
                                if (!empty($finalContent)) renderInput($slug . '_content', 'Texto sobre Investimento', 'textarea', '', true, $finalContent);
                                break;

                            case 'condicoes_pagamento':
                                $mobPerc = $variaveis['mobilizacao_percentual'];
                                $mobVal = $variaveis['mobilizacao_valor'];
                                $restPerc = $variaveis['restante_percentual'];
                                $restVal = $variaveis['restante_valor'];
                                echo '<div class="col-span-2 bg-blue-50 p-4 rounded-lg border border-blue-200">';
                                echo '<div class="grid grid-cols-2 md:grid-cols-4 gap-3">';
                                echo '<div><label class="text-xs text-blue-600 block mb-1">Entrada %</label><input type="text" name="mobilizacao_percentual" value="' . htmlspecialchars($mobPerc) . '" class="w-full px-2 py-1 border border-blue-300 rounded text-center font-bold"></div>';
                                echo '<div><label class="text-xs text-blue-600 block mb-1">Valor Entrada</label><input type="text" name="mobilizacao_valor" value="' . htmlspecialchars($mobVal) . '" class="w-full px-2 py-1 border border-blue-300 rounded text-center" readonly></div>';
                                echo '<div><label class="text-xs text-blue-600 block mb-1">Restante %</label><input type="text" value="' . htmlspecialchars($restPerc) . '" class="w-full px-2 py-1 border border-slate-200 rounded text-center bg-slate-100" readonly></div>';
                                echo '<div><label class="text-xs text-blue-600 block mb-1">Valor Restante</label><input type="text" name="restante_valor" value="' . htmlspecialchars($restVal) . '" class="w-full px-2 py-1 border border-slate-200 rounded text-center bg-slate-100" readonly></div>';
                                echo '</div></div>';
                                if (!empty($finalContent)) renderInput($slug . '_content', 'Texto Condições', 'textarea', '', true, $finalContent);
                                break;
                            
                            case 'dados_bancarios':
                                renderInput('banco', 'Banco', 'text', '', false, $variaveis['Banco']);
                                renderInput('agencia', 'Agência', 'text', '', false, $variaveis['Agencia']);
                                renderInput('conta', 'Conta Corrente', 'text', '', false, $variaveis['Conta']);
                                renderInput('pix', 'Chave PIX', 'text', '', false, $variaveis['PIX']);
                                break;

                            case 'dados_cliente':
                                // Renderiza o conteúdo HTML do banco (com variáveis substituídas) como preview
                                if (!empty($finalContent)) {
                                    echo "<div class='md:col-span-2 mb-4 p-4 bg-slate-50 rounded border border-slate-200'>";
                                    echo "<label class='block text-sm font-medium text-slate-700 mb-2'>Resumo do Cliente (Pré-visualização do Banco)</label>";
                                    echo "<div class='text-sm text-slate-600 prose prose-sm max-w-none'>" . $finalContent . "</div>";
                                    echo "</div>";
                                }
                                
                                // Inputs para edição (sempre visíveis para permitir alteração)
                                renderInput('nome_cliente', 'Nome do Cliente', 'text', '', false, $variaveis['nome_cliente']);
                                renderInput('empresa_cliente', 'Empresa do Cliente', 'text', '', false, $variaveis['empresa_cliente'] ?? '');
                                renderInput('email', 'E-mail', 'email', '', false, $variaveis['email_salvo']);
                                renderInput('telefone', 'Telefone', 'tel', '', false, $variaveis['telefone_salvo']);
                                renderInput('celular', 'Celular', 'tel', '', false, $variaveis['celular_salvo']);
                                
                                // CAMPOS DE ENDEREÇO (movidos do case 'local_obra' que não existe mais para este serviço)
                                echo "<div class='md:col-span-2 mt-4 border-t pt-4'><h5 class='text-sm font-bold text-gray-500'>Local da Obra</h5></div>";
                                renderInput('endereco', 'Endereço', 'text', '', false, $variaveis['endereco_obra']);
                                renderInput('bairro', 'Bairro', 'text', '', false, $variaveis['bairro_obra']);
                                renderInput('cidade', 'Cidade', 'text', '', false, $variaveis['cidade_obra']);
                                renderInput('estado', 'Estado', 'text', '', false, $variaveis['estado_obra']);
                                
                                // CAMPOS EXTRAS DRONE (só para serviço 19 ou se as variáveis existirem)
                                if ($idServicoContexto == 19 || isset($variaveis['TipoTerreno'])) {
                                    echo "<div class='md:col-span-2 mt-4 border-t pt-4'><h5 class='text-sm font-bold text-gray-500'>Especificidades da Área (Drone)</h5></div>";
                                    
                                    // Tipo Terreno
                                    echo "<div><label class='block text-sm font-medium text-slate-700 mb-1'>Tipo de Terreno</label>";
                                    echo "<select name='tipo_terreno' class='w-full px-3 py-2 border border-slate-300 rounded-lg'>";
                                    $opts = ['Plano', 'Acidentado', 'Misto', 'Montanhoso'];
                                    foreach($opts as $o) {
                                        $sel = (isset($variaveis['TipoTerreno']) && $variaveis['TipoTerreno'] == $o) ? 'selected' : '';
                                        echo "<option value='$o' $sel>$o</option>";
                                    }
                                    echo "</select></div>";

                                    // Cobertura Vegetal
                                    echo "<div><label class='block text-sm font-medium text-slate-700 mb-1'>Cobertura Vegetal</label>";
                                    echo "<select name='cobertura_vegetal' class='w-full px-3 py-2 border border-slate-300 rounded-lg'>";
                                    $opts = ['Baixa', 'Média', 'Alta', 'Densa'];
                                    foreach($opts as $o) {
                                        $sel = (isset($variaveis['CoberturaVegetal']) && $variaveis['CoberturaVegetal'] == $o) ? 'selected' : '';
                                        echo "<option value='$o' $sel>$o</option>";
                                    }
                                    echo "</select></div>";

                                    // Acesso
                                    echo "<div><label class='block text-sm font-medium text-slate-700 mb-1'>Acesso ao Local</label>";
                                    echo "<select name='acesso_local' class='w-full px-3 py-2 border border-slate-300 rounded-lg'>";
                                    $opts = ['Fácil', 'Moderado', 'Difícil', 'Restrito'];
                                    foreach($opts as $o) {
                                        $sel = (isset($variaveis['AcessoLocal']) && $variaveis['AcessoLocal'] == $o) ? 'selected' : '';
                                        echo "<option value='$o' $sel>$o</option>";
                                    }
                                    echo "</select></div>";

                                    renderInput('restricoes_aereas', 'Restrições Aéreas', 'text', 'Ex: Aeroporto próximo', false, $variaveis['RestricoesAereas'] ?? '');
                                    renderInput('coordenadas_gps', 'Coordenadas GPS', 'text', 'Lat, Long', false, $variaveis['CoordenadasGPS'] ?? '');
                                }
                                break;
                                // Case 'local_obra' removido propositalmente pois foi unificado acima

                            default:
                                // Bloco Genérico (Apresentação, Metodologia, etc.)
                                // Usa o slug + '_content' como nome do campo
                                renderInput($slug . '_content', $node['title'], 'textarea', 'Conteúdo do bloco...', true, $finalContent);
                        }

                        if ($slug !== 'cabecalho') {
                            echo "</div></div>"; // Fecha Grid e Card
                        }
                    }
                }

                function renderInput($name, $label, $type = 'text', $placeholder = '', $fullWidth = false, $value = '')
                {
                    // Lógica simplificada de renderInput (reaproveitada)
                     global $incomingData;
                    if ($value === '' && isset($incomingData[$name])) $value = $incomingData[$name];
                    $colSpan = $fullWidth || $type === 'textarea' ? 'md:col-span-2' : '';
                    echo "<div class='{$colSpan}'>";
                    if($label) echo "<label class='block text-sm font-medium text-slate-700 mb-1'>{$label}</label>";
                    $safeVal = htmlspecialchars($value);
                    if ($type === 'textarea') {
                        echo "<textarea name='{$name}' rows='8' class='w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all font-mono text-sm leading-relaxed' placeholder='{$placeholder}'>{$safeVal}</textarea>";
                    } else {
                        echo "<input type='{$type}' name='{$name}' value='{$safeVal}' class='w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all' placeholder='{$placeholder}'>";
                    }
                    echo "</div>";
                }
                
                // Início do Formulário
                echo '<form id="formProposta" method="POST" action="salvar_proposta.php" target="_blank">';
                
                // Persistência de dados ocultos essenciais (IDs, etc)
                foreach ($incomingData as $key => $val) {
                    if (is_array($val)) {
                        foreach ($val as $k => $v) {
                            if (!is_array($v)) {
                                echo "<input type='hidden' name='{$key}[{$k}]' value='" . htmlspecialchars($v) . "'>";
                            }
                        }
                    } elseif (strlen($val) < 2000) { 
                         echo "<input type='hidden' name='{$key}' value='" . htmlspecialchars($val) . "'>";
                    }
                }
                
                // Renderiza Loop Principal
                renderPropostaBlocks($globalBlocks);
                
                ?>


                <!-- Botão Salvar Final -->
                <!-- Botões de Ação -->
                <div class="pt-8 border-t border-slate-200 grid grid-cols-1 md:grid-cols-2 gap-4">
                    
                    <div class="flex flex-col sm:flex-row gap-3 w-full sm:w-auto">
                    <!-- Botão Word (Secundário/Opcional) -->
                    <button type="button" id="btn-docx" onclick="submitForm('docx')" 
                        class="px-6 py-3 rounded-xl font-bold transition-all duration-300 flex items-center justify-center gap-2 border-2 border-slate-200 text-slate-600 hover:border-slate-300 hover:bg-slate-50">
                        <img src="assets/icon_word.svg" class="w-6 h-6"> <span>Baixar Editável (Word)</span>
                    </button>

                    <!-- Botão Web/PDF (Primário) -->
                    <button type="button" id="btn-html" onclick="submitForm('html')" 
                        class="px-8 py-3 rounded-xl font-bold text-white shadow-lg shadow-blue-500/30 transition-all duration-300 transform hover:-translate-y-1 flex items-center justify-center gap-2 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 flex-1">
                        <i class="bi bi-globe"></i> <span>Salvar e Visualizar Web (HTML)</span>
                        <div id="loading-icon-html" class="hidden animate-spin rounded-full h-5 w-5 border-b-2 border-white"></div>
                    </button>
                </div>
                </div>    <p class="text-center text-sm text-slate-500 mt-4">
                        O motor irá validar todas as regras de estrutura antes de gerar o arquivo.
                    </p>
                </div>

                <?php echo "</form>"; // Fim do Formulário 
                ?>

            </div>
        </main>
    </div>

    <script>
        // Função de Preview (Visualização)
        function visualizarPDF() {
            const form = document.querySelector('form');

            // Cria um form temporário para postar em nova aba
            const tempForm = document.createElement('form');
            tempForm.method = 'POST';
            tempForm.action = 'preview_proposta.php'; // Script que gera o HTML/PDF temporário
            tempForm.target = '_blank';
            tempForm.style.display = 'none';

            // Copia os dados
            const formData = new FormData(form);
            for (let [key, val] of formData.entries()) {
                const input = document.createElement('input');
                input.name = key;
                input.value = val;
                tempForm.appendChild(input);
            }

            document.body.appendChild(tempForm);
            tempForm.submit();
            document.body.removeChild(tempForm);
        }

        async function salvarRascunho() {
            const btn = document.querySelector('button[onclick="salvarRascunho()"]');
            const originalText = btn ? btn.innerText : 'Salvar Rascunho';
            
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Salvando...';
            }

            try {
                // Sincroniza TinyMCE
                if (typeof tinymce !== 'undefined') {
                    tinymce.triggerSave();
                }

                const form = document.getElementById('formProposta');
                const formData = new FormData(form);

                const response = await fetch('salvar_rascunho.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    // Update ID if new
                    if (result.is_new && result.id_proposta) {
                         let hiddenId = document.querySelector('input[name="id_proposta_original"]');
                         if (!hiddenId) {
                             hiddenId = document.createElement('input');
                             hiddenId.type = 'hidden';
                             hiddenId.name = 'id_proposta_original';
                             form.appendChild(hiddenId);
                         }
                         hiddenId.value = result.id_proposta;
                    }
                    
                    alert('✅ Rascunho salvo com sucesso!');
                } else {
                    throw new Error(result.message || 'Erro desconhecido');
                }

            } catch (error) {
                console.error('Erro ao salvar rascunho:', error);
                alert('❌ Erro ao salvar rascunho: ' + error.message);
            } finally {
                if (btn) {
                    btn.disabled = false;
                    btn.innerText = originalText;
                }
            }
        }

        async function submitForm(formato = 'docx') {
            console.log('--- INICIANDO SUBMISSÃO ---');
            console.log('Formato solicitado:', formato);

            // Se formato não for passado, assume docx
            const btnHtml = document.querySelector('button[onclick="submitForm(\'html\')"]');
            const btnDocx = document.querySelector('button[onclick="submitForm(\'docx\')"]');
            const iconHtml = document.getElementById('loading-icon-html');
            const form = document.getElementById('formProposta');

            if (!form) {
                console.error('ERRO CRÍTICO: Formulário #formProposta não encontrado!');
                alert('Erro interno: Formulário não encontrado.');
                return;
            }

            // UI Feedback
            if (formato === 'html') {
                if(btnHtml) {
                    btnHtml.disabled = true;
                    btnHtml.classList.add('opacity-75');
                }
                if(iconHtml) iconHtml.classList.remove('hidden');
            } else {
                if(btnDocx) {
                    btnDocx.disabled = true;
                    btnDocx.innerText = 'Gerando DOCX...';
                }
            }

            try {
                // Sincroniza TinyMCE antes de enviar
                if (typeof tinymce !== 'undefined') {
                    console.log('Sincronizando TinyMCE...');
                    tinymce.triggerSave();
                }

                // Cria/Atualiza input hidden para formato
                let inputFormato = form.querySelector('input[name="formato_saida"]');
                if (!inputFormato) {
                    inputFormato = document.createElement('input');
                    inputFormato.type = 'hidden';
                    inputFormato.name = 'formato_saida';
                    form.appendChild(inputFormato);
                }
                inputFormato.value = formato;
                console.log('Input formato definido:', inputFormato.value);

                // Submissão
                const originalAction = form.action;
                form.action = 'salvar_proposta.php';
                form.target = '_blank'; // Abre em nova aba
                
                console.log('Enviando formulário para:', form.action);
                form.submit();
                console.log('Formulário enviado!');
                
                // Restaura UI após um tempo (pois a página não recarrega se for target _blank)
                setTimeout(() => {
                    console.log('Restaurando UI...');
                    if (btnHtml) {
                        btnHtml.disabled = false;
                        btnHtml.classList.remove('opacity-75');
                    }
                    if (iconHtml) iconHtml.classList.add('hidden');
                    
                    if (btnDocx) {
                        btnDocx.disabled = false;
                        btnDocx.innerHTML = '<i class="bi bi-file-word"></i> <span>Salvar DOCX</span>';
                    }
                    form.action = originalAction;
                }, 3000);

            } catch (error) {
                console.error('Erro ao processar:', error);
                alert('Erro ao processar solicitação: ' + error.message);
                
                // Reset UI em caso de erro imediato
                 if (btnHtml) {
                    btnHtml.disabled = false;
                    btnHtml.classList.remove('opacity-75');
                }
                if (iconHtml) iconHtml.classList.add('hidden');
                 if (btnDocx) {
                    btnDocx.disabled = false;
                    btnDocx.innerHTML = '<i class="bi bi-file-word"></i> <span>Salvar DOCX</span>';
                }
            }
        }




        // ===== MÁSCARAS DE TELEFONE/CELULAR =====
        function aplicarMascaraTelefone(input) {
            let valor = input.value.replace(/\D/g, '');
            if (valor.length > 11) valor = valor.substring(0, 11);

            if (valor.length <= 10) {
                // Telefone fixo: (31) 3333-3333
                valor = valor.replace(/^(\d{2})(\d)/, '($1) $2');
                valor = valor.replace(/(\d{4})(\d)/, '$1-$2');
            } else {
                // Celular: (31) 99999-9999
                valor = valor.replace(/^(\d{2})(\d)/, '($1) $2');
                valor = valor.replace(/(\d{5})(\d)/, '$1-$2');
            }
            input.value = valor;
        }

        // Aplica máscaras nos campos de telefone/celular ao carregar a página
        document.addEventListener('DOMContentLoaded', function() {
            const camposTelefone = document.querySelectorAll('input[name="telefone_cliente"], input[name="celular_cliente"]');
            camposTelefone.forEach(function(campo) {
                campo.addEventListener('input', function() {
                    aplicarMascaraTelefone(this);
                });
                // Aplica máscara no valor inicial
                if (campo.value) aplicarMascaraTelefone(campo);
            });

            // --- FIX: Restaurar Marcas Salvas ---
            // Recupera marcas salvas do PHP
            const savedBrands = <?php echo json_encode($incomingData['locacao_id_marca'] ?? []); ?>;
            
            // Aguarda um pouco para garantir que elementos dinâmicos (se houver) estejam prontos
            setTimeout(() => {
                const equipmentSelects = document.querySelectorAll('select[name="locacao_id[]"]');
                equipmentSelects.forEach((select, index) => {
                    // 1. Dispara change para popular as opções de marca (calculos.js escuta isso)
                    if (select.value) {
                        select.dispatchEvent(new Event('change'));
                        
                        // 2. Seleciona a marca salva correspondente (se houver)
                        if (savedBrands[index]) {
                            const row = select.closest('.cost-item');
                            if (row) {
                                const brandSelect = row.querySelector('div.div-marca select');
                                if (brandSelect) {
                                    brandSelect.value = savedBrands[index];
                                }
                            }
                        }
                    }
                });
            }, 500); // 500ms delay para garantir que calculos.js já rodou inicializações
        });
    </script>
    <script>
        // Inicialização do TinyMCE para todos os textareas
        document.addEventListener("DOMContentLoaded", function() {
            if (typeof tinymce !== 'undefined') {
                tinymce.init({
                    selector: 'textarea', 
                    // language: 'pt_BR', // Desativado para evitar erro 404 se não houver arquivo de tradução do CDN
                    height: 300,
                    menubar: false,
                    plugins: 'autolink lists link charmap preview anchor searchreplace visualblocks code fullscreen insertdatetime media table help wordcount',
                    toolbar: 'undo redo | blocks | bold italic underline | alignleft aligncenter | bullist numlist outdent indent | removeformat | help',
                    content_style: 'body { font-family:Inter,sans-serif; font-size:14px; color: #334155; }',
                    setup: function(editor) {
                        editor.on('change keyup blur', function(e) {
                            editor.save(); 
                        });
                    }
                });
            } else {
                console.error("TinyMCE não carregado.");
            }
        });
    </script>
</body>

</html>