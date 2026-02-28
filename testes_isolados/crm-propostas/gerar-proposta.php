<?php
/**
 * GERADOR DE PROPROSTAS - SGT Propostas v5.0 (FINAL CORRIGIDO)
 * 
 * Todas as correções consolidadas:
 * - Logo do criador
 * - Dados completos do cliente/obra
 * - Sem duplicações
 * - Cores personalizadas
 */

define('SGT_PROPOSTAS', true);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../../ConnectionManager.php';
require_once __DIR__ . '/../../PropostaRepository.php';
require_once __DIR__ . '/../../renderizador_modelo_docx.php';

if (!isset($conn)) {
    die("Erro: Conexão com o banco de dados não disponível.");
}

// ============================================
// FUNÇÕES AUXILIARES
// ============================================

if (!function_exists('dataPorExtenso')) {
    function dataPorExtenso($data = null) {
        $meses = ['Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'];
        $ts = $data ? strtotime($data) : time();
        return date('d', $ts) . ' de ' . $meses[date('n', $ts) - 1] . ' de ' . date('Y', $ts);
    }
}

if (!function_exists('formatarMoeda')) {
    function formatarMoeda($valor) {
        $v = floatval(str_replace(['R$', '.', ','], ['', '', '.'], $valor));
        return number_format($v, 2, ',', '.');
    }
}

// ============================================
// BUSCA DA PROPOSTA
// ============================================

$id_proposta = intval($_GET['id'] ?? 0);
if ($id_proposta <= 0) {
    die("ID da proposta inválido.");
}

$repo = new PropostaRepository($conn);
$dados_proposta = $repo->buscarPorId($id_proposta);

if (!$dados_proposta) {
    die("Proposta #{$id_proposta} não encontrada.");
}

// ============================================
// BUSCA COMPLETA DO CLIENTE
// ============================================

$dadosCliente = [];
if (!empty($dados_proposta['id_cliente'])) {
    $stmt = $conn->prepare("SELECT nome_cliente as nome, email, telefone, celular, whatsapp FROM Clientes WHERE id_cliente = ? LIMIT 1");
    $stmt->bind_param("i", $dados_proposta['id_cliente']);
    $stmt->execute();
    $dadosCliente = $stmt->get_result()->fetch_assoc() ?: [];
}

// ============================================
// BUSCA COMPLETA DA OBRA
// ============================================

$dadosObra = [];
if (!empty($dados_proposta['id_obra'])) {
    $stmt = $conn->prepare("SELECT endereco, bairro, cidade, estado, area, unidade_area, tipo_terreno, cobertura_vegetal, acesso_local, restricoes_aereas, finalidade FROM obras WHERE id = ? LIMIT 1");
    $stmt->bind_param("i", $dados_proposta['id_obra']);
    $stmt->execute();
    $dadosObra = $stmt->get_result()->fetch_assoc() ?: [];
}

// ============================================
// BUSCA DA EMPRESA DO CRIADOR
// ============================================

$idCriador = intval($dados_proposta['id_criador'] ?? 0);
$dadosEmpresa = [];

if ($idCriador > 0) {
    $stmt = $conn->prepare("SELECT * FROM DadosEmpresa WHERE id_criador = ? LIMIT 1");
    $stmt->bind_param("i", $idCriador);
    $stmt->execute();
    $dadosEmpresa = $stmt->get_result()->fetch_assoc() ?: [];
}

// Fallback para empresa do usuário logado
if (empty($dadosEmpresa) && !empty($_SESSION['usuario_id'])) {
    $stmt = $conn->prepare("SELECT * FROM DadosEmpresa WHERE id_criador = ? LIMIT 1");
    $stmt->bind_param("i", $_SESSION['usuario_id']);
    $stmt->execute();
    $dadosEmpresa = $stmt->get_result()->fetch_assoc() ?: [];
}

// Fallback final
if (empty($dadosEmpresa)) {
    $dadosEmpresa = [
        'Empresa' => 'ELM Serviços Topográficos Ltda.',
        'CNPJ' => '14.059.118/0001-08',
        'Cidade' => 'Belo Horizonte',
        'Estado' => 'MG',
        'Telefone' => '(31) 9922-2617',
        'Email' => 'contato@elmtopografia.com.br',
        'Banco' => 'Banco Inter',
        'Agencia' => '0001',
        'Conta' => '12456-45',
        'PIX' => '31 9 9922-2617'
    ];
}

// ============================================
// RESOLVER LOGO DA EMPRESA
// ============================================

$logoPath = '';
$logoCandidatos = [
    $dadosEmpresa['logo_caminho'] ?? null,
    $dadosEmpresa['logo_url'] ?? null,
    $dadosEmpresa['logo_empresa'] ?? null,
];

foreach ($logoCandidatos as $logo) {
    if (empty($logo)) continue;
    
    // URL externa
    if (strpos($logo, 'http') === 0) {
        $logoPath = $logo;
        break;
    }
    
    // Path local
    $basename = basename($logo);
    $paths = [
        __DIR__ . '/../../uploads/' . $basename => '../../uploads/' . $basename,
        __DIR__ . '/../../assets/' . $basename => '../../assets/' . $basename,
    ];
    
    foreach ($paths as $fisico => $web) {
        if (file_exists($fisico)) {
            $logoPath = $web;
            break 2;
        }
    }
}

// Logo padrão
if (empty($logoPath)) {
    if (file_exists(__DIR__ . '/../../assets/logo_sgt.png')) {
        $logoPath = '../../assets/logo_sgt.png';
    }
}

// ============================================
// CONFIGURAÇÃO DA EMPRESA
// ============================================

$config['empresa'] = [
    'nome' => $dadosEmpresa['Empresa'],
    'cnpj' => $dadosEmpresa['CNPJ'],
    'cidade' => $dadosEmpresa['Cidade'] ?? 'Belo Horizonte',
    'estado' => $dadosEmpresa['Estado'] ?? 'MG',
    'endereco' => ($dadosEmpresa['Cidade'] ?? 'Belo Horizonte') . ' - ' . ($dadosEmpresa['Estado'] ?? 'MG'),
    'telefone' => $dadosEmpresa['Telefone'] ?? '(31) 9922-2617',
    'email' => $dadosEmpresa['Email'] ?? 'contato@elmtopografia.com.br',
    'logo' => $logoPath,
];

$config['banco'] = [
    'nome' => $dadosEmpresa['Banco'] ?? 'Banco Inter',
    'agencia' => $dadosEmpresa['Agencia'] ?? '0001',
    'conta' => $dadosEmpresa['Conta'] ?? '12456-45',
    'pix' => $dadosEmpresa['PIX'] ?? $dadosEmpresa['CNPJ'] ?? '14.059.118/0001-08',
];

// ============================================
// MONTAR DADOS DA PROPOSTA
// ============================================

// Valores
$valorTotal = floatval($dados_proposta['valor_proposta'] ?? 0);
$mobPercent = floatval($dados_proposta['mobilizacao_percentual'] ?? 30);
$mobValor = $valorTotal * ($mobPercent / 100);
$restValor = $valorTotal - $mobValor;
$restPercent = 100 - $mobPercent;

// Data
$dataProposta = !empty($dados_proposta['data_proposta']) ? $dados_proposta['data_proposta'] : date('Y-m-d');

// Área (evitar duplicação de unidade)
$areaValor = $dadosObra['area'] ?? $dados_proposta['area_obra'] ?? '0';
$areaUnidade = $dadosObra['unidade_area'] ?? $dados_proposta['unidade_area'] ?? 'm²';

// Limpeza agressiva de duplicações de unidades comuns
$areaLimpo = trim(preg_replace('/\b(m²|ha|km|km²|m2)\s+\1\b/i', '$1', $areaValor));
$areaLimpo = trim(preg_replace('/\s*(m²|ha|km|km²|m2)\s*$/i', '', $areaLimpo));

$proposta = [
    'numero' => $dados_proposta['numero_proposta'] ?? '0000',
    'tipo_servico' => $dados_proposta['nome_servico'] ?? 'Levantamento Topográfico com Drone',
    'data_extenso' => dataPorExtenso($dataProposta),
    'cidade_empresa' => $config['empresa']['cidade'],
    
    // CLIENTE
    'cliente_nome' => $dadosCliente['nome'] ?? $dados_proposta['nome_cliente'] ?? '',
    'cliente_email' => $dadosCliente['email'] ?? $dados_proposta['email_cliente'] ?? '',
    'cliente_telefone' => $dadosCliente['telefone'] ?? $dados_proposta['telefone_cliente'] ?? '',
    'cliente_celular' => $dadosCliente['celular'] ?? $dados_proposta['celular_cliente'] ?? '',
    'cliente_whatsapp' => $dadosCliente['whatsapp'] ?? $dados_proposta['whatsapp_cliente'] ?? '',
    
    // OBRA
    'obra_endereco' => $dadosObra['endereco'] ?? $dados_proposta['endereco_obra'] ?? '',
    'obra_bairro' => $dadosObra['bairro'] ?? $dados_proposta['bairro_obra'] ?? '',
    'obra_cidade' => $dadosObra['cidade'] ?? $dados_proposta['cidade_obra'] ?? '',
    'obra_uf' => $dadosObra['estado'] ?? $dados_proposta['estado_obra'] ?? '',
    'obra_area' => $areaLimpo . ' ' . $areaUnidade,
    
    // ESCOPO
    'finalidade' => $dadosObra['finalidade'] ?? $dados_proposta['finalidade'] ?? '',
    'tipo_terreno' => $dadosObra['tipo_terreno'] ?? $dados_proposta['tipo_terreno'] ?? 'Não informado',
    'cobertura_vegetal' => $dadosObra['cobertura_vegetal'] ?? $dados_proposta['cobertura_vegetal'] ?? 'Não informado',
    'acesso_local' => $dadosObra['acesso_local'] ?? $dados_proposta['acesso_local'] ?? 'Não informado',
    'restricoes_aereas' => $dadosObra['restricoes_aereas'] ?? $dados_proposta['restricoes_aereas'] ?? 'Não informado',
    
    // VALORES
    'valor' => $valorTotal,
    'valor_extenso' => $dados_proposta['valor_extenso'] ?? '',
    'mobilizacao_percentual' => $mobPercent,
    'mobilizacao_valor' => $mobValor,
    'restante_percentual' => $restPercent,
    'restante_valor' => $restValor,
    'validade_dias' => $dados_proposta['validade_dias'] ?? 15,
    
    // CONTROLE
    'is_docx' => !empty($dados_proposta['modelo_docx']),
    'cor_personalizada' => $dados_proposta['cor_personalizada'] ?? null,
];

// ============================================
// FLUXO DOCX
// ============================================

if ($proposta['is_docx'] && !empty($dados_proposta['modelo_docx'])) {
    // Determina a cor: URL > banco > padrão
    $corAtiva = $_GET['cor'] ?? $dados_proposta['cor'] ?? 'verde';
    $coresValidas = ['verde', 'azul', 'laranja', 'cinza', 'marrom'];
    if (!in_array($corAtiva, $coresValidas)) $corAtiva = 'verde';
    $corCapital = ucfirst($corAtiva);

    $modeloBase = $dados_proposta['modelo_docx']; // ex: 'PropostaDrone'

    // Mapeamento: tenta nome com cor primeiro, depois fallbacks
    $candidatos = [
        __DIR__ . "/../../modelos_gerados/Modelo{$modeloBase}{$corCapital}.php",  // ModeloPropostaDroneVerde.php
        __DIR__ . "/../../modelos_gerados/Modelo{$modeloBase}.php",               // ModeloPropostaDrone.php
        __DIR__ . "/../../modelos_gerados/{$modeloBase}{$corCapital}.php",         // PropostaDroneVerde.php
        __DIR__ . "/../../modelos_gerados/{$modeloBase}.php",                      // PropostaDrone.php
        __DIR__ . "/../../modelos_gerados/bk/Modelo{$modeloBase}.php",            // bk/ModeloPropostaDrone.php
    ];

    $modeloFile = null;
    foreach ($candidatos as $c) {
        if (file_exists($c)) { $modeloFile = $c; break; }
    }

    if ($modeloFile) {
        require_once $modeloFile;
        
        // Tenta encontrar a classe correta com e sem cor
        $classesCandidatas = [
            "SGT\\Propostas\\Modelo{$modeloBase}{$corCapital}",  // SGT\Propostas\ModeloPropostaDroneVerde
            "SGT\\Propostas\\Modelo{$modeloBase}",               // SGT\Propostas\ModeloPropostaDrone
            "Modelo{$modeloBase}{$corCapital}",                  // ModeloPropostaDroneVerde (sem namespace)
            "Modelo{$modeloBase}",                               // ModeloPropostaDrone (sem namespace)
        ];
        $classeFull = null;
        foreach ($classesCandidatas as $c) {
            if (class_exists($c)) { $classeFull = $c; break; }
        }
        
        if ($classeFull) {
            $modelo = new $classeFull();
            $dadosRender = [
                // EMPRESA (mapeamento expandido para compatibilidade com editor)
                'Empresa' => $config['empresa']['nome'],
                'empresa_nome' => $config['empresa']['nome'],
                'empresa_proponente_nome' => $config['empresa']['nome'],
                'CNPJ' => $config['empresa']['cnpj'],
                'empresa_cnpj' => $config['empresa']['cnpj'],
                'empresa_proponente_cnpj' => $config['empresa']['cnpj'],
                'Cidade' => $config['empresa']['cidade'],
                'cidade' => $config['empresa']['cidade'],
                'empresa_cidade' => $config['empresa']['cidade'],
                'empresa_proponente_cidade' => $config['empresa']['cidade'],
                'Estado' => $config['empresa']['estado'],
                'empresa_estado' => $config['empresa']['estado'],
                'Endereco' => $config['empresa']['endereco'],
                'empresa_endereco' => $config['empresa']['endereco'],
                'Telefone' => $config['empresa']['telefone'],
                'empresa_telefone' => $config['empresa']['telefone'],
                'whatsapp' => $config['empresa']['telefone'],
                'whatsapp_empresa' => $config['empresa']['telefone'],
                'Email' => $config['empresa']['email'],
                'empresa_email' => $config['empresa']['email'],
                'Logo' => $config['empresa']['logo'],
                'logo_empresa' => $config['empresa']['logo'],
                'empresa_logo' => $config['empresa']['logo'],
                
                // BANCO (chaves do editor)
                'Banco' => $config['banco']['nome'],
                'empresa_proponente_banco' => $config['banco']['nome'],
                'Agencia' => $config['banco']['agencia'],
                'empresa_proponente_agencia' => $config['banco']['agencia'],
                'Conta' => $config['banco']['conta'],
                'empresa_proponente_conta' => $config['banco']['conta'],
                'PIX' => mb_strtoupper($config['banco']['pix']),
                'empresa_proponente_pix' => $config['banco']['pix'],
                
                // CLIENTE (todas as variações de chave)
                'nome_cliente' => $proposta['cliente_nome'],
                'nome_cliente_salvo' => $proposta['cliente_nome'],
                'email_cliente' => $proposta['cliente_email'],
                'email_salvo' => $proposta['cliente_email'],
                'telefone_cliente' => $proposta['cliente_telefone'],
                'telefone_salvo' => $proposta['cliente_telefone'],
                'celular_cliente' => $proposta['cliente_celular'],
                'celular_salvo' => $proposta['cliente_celular'],
                'whatsapp_cliente' => $proposta['cliente_whatsapp'],
                'whatsapp_salvo' => $proposta['cliente_whatsapp'],
                
                // OBRA
                'endereco_obra' => $proposta['obra_endereco'],
                'bairro_obra' => $proposta['obra_bairro'],
                'cidade_obra' => $proposta['obra_cidade'],
                'estado_obra' => $proposta['obra_uf'],
                'cidade_limpo' => $proposta['obra_cidade'],
                'area_obra' => $areaLimpo,
                'unidade_area' => $areaUnidade,
                'AreaEstimada' => $proposta['obra_area'],
                
                // ESCOPO
                'finalidade' => $proposta['finalidade'],
                'TipoTerreno' => $proposta['tipo_terreno'],
                'CoberturaVegetal' => $proposta['cobertura_vegetal'],
                'AcessoLocal' => $proposta['acesso_local'],
                'RestricoesAereas' => $proposta['restricoes_aereas'],
                
                // PROPOSTA
                'numero_proposta' => $proposta['numero'],
                'DataExtenso' => $proposta['data_extenso'],
                'data_criacao' => $dataProposta,
                
                // VALORES
                'valor_final_proposta' => $valorTotal,
                'ValorProposta' => formatarMoeda($valorTotal),
                'ValorExtenso' => $proposta['valor_extenso'],
                'mobilizacao_percentual' => $mobPercent,
                'mobilizacao_valor' => formatarMoeda($mobValor),
                'restante_percentual' => $restPercent,
                'restante_valor' => formatarMoeda($restValor),
                
                // EQUIPAMENTOS
                'Drone' => $dados_proposta['equipamento_drone'] ?? 'Não aplicável',
                'GPS' => $dados_proposta['equipamento_gps'] ?? 'Par de Receptores GNSS RTK',
                'Estacao_Total' => $dados_proposta['equipamento_estacao'] ?? 'Não inclusa',
                'Veiculo' => $dados_proposta['equipamento_veiculo'] ?? 'Não incluso',
                
                // COR PERSONALIZADA
                'cor_personalizada' => $proposta['cor_personalizada'],
            ];
            
            // Blocos dinâmicos (v5.1 Prioridade absoluta para o que vem do Editor Dinâmico)
            $blocosInjetados = [];
            
            // 1. Tenta carregar do array docx_blocos (já decodificado pelo PropostaRepository)
            if (!empty($dados_proposta['docx_blocos']) && is_array($dados_proposta['docx_blocos'])) {
                $blocosInjetados = $dados_proposta['docx_blocos'];
            }
            // 2. Fallback para docx_conteudo (JSON bruto)
            elseif (!empty($dados_proposta['docx_conteudo'])) {
                $decoded = json_decode($dados_proposta['docx_conteudo'], true);
                if (is_array($decoded)) {
                    $blocosInjetados = $decoded;
                }
            }
            
            if (!empty($blocosInjetados)) {
                $dadosRender['blocos_custom'] = $blocosInjetados;
            }

            // Fallback para campos individuais docx_bloco_X
            for ($i = 0; $i < 40; $i++) {
                $campo = "docx_bloco_{$i}_content";
                if (!empty($dados_proposta[$campo])) {
                    $dadosRender["docx_bloco_{$i}"] = $dados_proposta[$campo];
                }
            }
            
            // Renderizar SEM passar pelo resolvedor (dados já estão completos)
            if (method_exists($modelo, 'renderDireto')) {
                $proposta['html_docx'] = $modelo->renderDireto($dadosRender);
            } else {
                // Fallback se ainda não implementado
                require_once __DIR__ . '/../../ResolvedorChavesSistema.php';
                $resolvedor = new ResolvedorChavesSistema($conn);
                $proposta['html_docx'] = $modelo->render($dadosRender, $resolvedor, $idCriador);
            }
        }
    }
}

// ============================================
// EQUIPAMENTOS PARA LEGACY
// ============================================

$proposta['equipamentos'] = [
    ['nome' => 'Aeronave', 'descricao' => ($dados_proposta['equipamento_drone'] ?? 'Não aplicável') . ' (Câmera de Alta Resolução)'],
    ['nome' => 'GPS - Geodésia', 'descricao' => ($dados_proposta['equipamento_gps'] ?? 'Par de Receptores GNSS RTK')],
    ['nome' => 'Estação Total', 'descricao' => ($dados_proposta['equipamento_estacao'] ?? 'Não inclusa')],
    ['nome' => 'Processamento', 'descricao' => 'Workstations com placas gráficas de alto desempenho'],
    ['nome' => 'Veiculo', 'descricao' => ($dados_proposta['equipamento_veiculo'] ?? 'Não incluso')],
];

// ============================================
// PARCELAS
// ============================================

$proposta['parcelas'] = [
    [
        'descricao' => 'Mobilização (Sinal)',
        'percentual' => $mobPercent,
        'valor' => $mobValor,
        'condicao' => 'No aceite da proposta'
    ],
    [
        'descricao' => 'Entrega Final',
        'percentual' => $restPercent,
        'valor' => $restValor,
        'condicao' => 'Na entrega dos arquivos digitais e físicos'
    ]
];

// ============================================
// TEMA
// ============================================

$tema_info = [
    'nome' => 'classico',
    'fontes' => ['Inter', 'Playfair Display'],
    'css_inline' => '',
    'header_template' => 'header-classico.php'
];

// ============================================
// INCLUIR TEMPLATE
// ============================================

include 'templates/base.php';
