<?php
/**
 * GERADOR DE PROPOSTAS - SGT Propostas
 * 
 * Versão com Integração Real ao Banco de Dados (Corrigida 17/02)
 */

define('SGT_PROPOSTAS', true);

// 1. Carrega configurações do sistema isolado
require_once 'config.php';

// 2. Carrega conexão e configurações do sistema real (CRM)
require_once '../../db.php';

if (!isset($conn)) {
    die("Erro: Conexão com o banco de dados não disponível.");
}

// ============================================
// BUSCA DE DADOS REAIS
// ============================================

$id_proposta = intval($_GET['id'] ?? 0);
$tema_forcado = $_GET['tema'] ?? null;

if ($id_proposta <= 0) {
    die("ID da proposta inválido ou não fornecido.");
}

// 1. Busca Proposta Principal (Campos mapeados conforme SHOW COLUMNS)
$res = $conn->query("SELECT * FROM Propostas WHERE id_proposta = $id_proposta LIMIT 1");
if (!$res || $res->num_rows === 0) {
    die("Proposta ID $id_proposta não encontrada.");
}
$dados_proposta = $res->fetch_assoc();

// 2. Busca Dados da Empresa (Configurações do CRM)
$res_empresa = $conn->query("SELECT * FROM DadosEmpresa LIMIT 1");
$dados_empresa = $res_empresa->fetch_assoc() ?: [];

// 3. Busca Conteúdo Personalizado (Blocos do Editor)
$blocos_dados = [];
$res_blocos = $conn->query("SELECT block_id, conteudo_texto FROM Proposta_Conteudo_Personalizado WHERE id_proposta = $id_proposta");
while ($bloco_personalizado = $res_blocos->fetch_assoc()) {
    $blocos_dados[$bloco_personalizado['block_id']] = $bloco_personalizado['conteudo_texto'];
}

// 4. Busca Estrutura de Blocos (Manteve lógica de fallback)
$service_id = intval($dados_proposta['id_servico'] ?? 0);
$sq_blocks = "SELECT sb.block_slug, sb.block_title, sb.display_order 
              FROM service_type_blocks sb 
              WHERE sb.service_type_id = $service_id AND sb.is_active = 1
              UNION
              SELECT pt.slug as block_slug, pt.name as block_title, pt.order as display_order
              FROM proposal_block_templates pt
              WHERE pt.is_active = 1 AND pt.slug NOT IN (SELECT block_slug FROM service_type_blocks WHERE service_type_id = $service_id)
              ORDER BY display_order ASC";

$res_estrutura = $conn->query($sq_blocks);
$blocos_finais = [];
while ($est = $res_estrutura->fetch_assoc()) {
    $slug = $est['block_slug'];
    if (isset($blocos_dados[$slug])) {
        $blocos_finais[] = [
            'titulo' => $est['block_title'],
            'conteudo' => $blocos_dados[$slug]
        ];
    }
}

// ============================================
// MAPEAMENTO E FORMATAÇÃO (DE -> PARA REAL)
// ============================================

$data_base = $dados_proposta['data_criacao'] ?? date('Y-m-d');
$meses = [
    '01'=>'Janeiro', '02'=>'Fevereiro', '03'=>'Março', '04'=>'Abril', 
    '05'=>'Maio', '06'=>'Junho', '07'=>'Julho', '08'=>'Agosto', 
    '09'=>'Setembro', '10'=>'Outubro', '11'=>'Novembro', '12'=>'Dezembro'
];
$data_formatada = date('d', strtotime($data_base)) . ' de ' . $meses[date('m', strtotime($data_base))] . ' de ' . date('Y', strtotime($data_base));

// Variáveis para substituição (Campos reais do banco SGT)
$vars = [
    'numero_proposta' => $dados_proposta['numero_proposta'],
    'cliente_nome'    => $dados_proposta['nome_cliente_salvo'] ?? '',
    'obra_endereco'   => $dados_proposta['endereco_obra'] ?? '',
    'obra_bairro'     => $dados_proposta['bairro_obra'] ?? '',
    'obra_cidade'     => $dados_proposta['cidade_obra'] ?? '',
    'obra_uf'         => $dados_proposta['estado_obra'] ?? '',
    'obra_area'       => ($dados_proposta['area_obra'] ?? '') . ' ' . ($dados_proposta['unidade_area'] ?? 'm²'),
    'valor_total'     => number_format((float)($dados_proposta['valor_final_proposta'] ?? 0), 2, ',', '.'),
    'valor_extenso'   => $dados_proposta['Valor_proposta_extenso'] ?? '',
    'validade'        => $dados_proposta['prazo_execucao'] ?? '15 dias',
    'cidade_emissao'  => $dados_proposta['cidade_obra'] ?? 'Belo Horizonte',
];

// Substituição nos textos
foreach ($blocos_finais as &$bloco) {
    foreach ($vars as $key => $val) {
        $bloco['conteudo'] = str_replace('${' . $key . '}', (string)$val, $bloco['conteudo']);
    }
}

$proposta = [
    'id'                => $id_proposta,
    'numero'            => $dados_proposta['numero_proposta'],
    'tipo_servico'      => $dados_proposta['tipo_levantamento'] ?? 'Serviços Técnicos',
    'data'              => $data_base,
    'data_extenso'      => $data_formatada,
    'validade_dias'     => 15, // Fallback fixo
    'cidade'            => $dados_proposta['cidade_obra'] ?? 'BH',
    
    // Cliente
    'cliente_nome'      => $dados_proposta['nome_cliente_salvo'] ?? '',
    'cliente_email'     => $dados_proposta['email_salvo'] ?? '',
    'cliente_telefone'  => $dados_proposta['telefone_salvo'] ?? '',
    'cliente_whatsapp'  => $dados_proposta['whatsapp_salvo'] ?? '',
    
    // Obra
    'obra_endereco'     => $dados_proposta['endereco_obra'] ?? '',
    'obra_bairro'       => $dados_proposta['bairro_obra'] ?? '',
    'obra_cidade'       => $dados_proposta['cidade_obra'] ?? '',
    'obra_uf'           => $dados_proposta['estado_obra'] ?? '',
    'obra_area'         => ($dados_proposta['area_obra'] ?? '') . ' ' . ($dados_proposta['unidade_area'] ?? 'm²'),
    
    // Valores
    'valor'             => (float)($dados_proposta['valor_final_proposta'] ?? 0),
    'valor_extenso'     => $dados_proposta['Valor_proposta_extenso'] ?? '',
    'observacao_valor'  => 'Investimento calculado com base na complexidade técnica.',
    
    // Parcelas
    'parcelas'          => [
        [
            'descricao'  => 'Mobilização (Sinal)',
            'percentual' => (float)($dados_proposta['mobilizacao_percentual'] ?? 40),
            'valor'      => (float)($dados_proposta['mobilizacao_valor'] ?? 0),
            'condicao'   => 'No aceite da proposta',
        ],
        [
            'descricao'  => 'Entrega Final',
            'percentual' => (float)($dados_proposta['restante_percentual'] ?? 60),
            'valor'      => (float)($dados_proposta['restante_valor'] ?? 0),
            'condicao'   => 'Na entrega dos arquivos',
        ],
    ],
    
    'blocos'            => $blocos_finais,
    
    // Equipamentos (Campos reais)
    'equipamentos'      => [
        ['nome' => 'Veículo', 'descricao' => trim(($dados_proposta['marca_veiculo'] ?? '') . ' ' . ($dados_proposta['modelo_veiculo'] ?? ''))],
        ['nome' => 'Estação Total', 'descricao' => trim(($dados_proposta['marca_estacao_total'] ?? '') . ' ' . ($dados_proposta['modelo_estacao_total'] ?? ''))],
        ['nome' => 'GPS/GNSS', 'descricao' => trim(($dados_proposta['marca_gps'] ?? '') . ' ' . ($dados_proposta['modelo_gps'] ?? ''))],
        ['nome' => 'Vant/Drone', 'descricao' => trim(($dados_proposta['marca_drone'] ?? '') . ' ' . ($dados_proposta['modelo_drone'] ?? ''))],
    ],
];

// Equipamentos Vazios (Limpeza)
$proposta['equipamentos'] = array_filter($proposta['equipamentos'], function($e) {
    return !empty($e['descricao']);
});

// ============================================
// LÓGICA DE TEMA
// ============================================

if ($tema_forcado && isset($TEMAS[$tema_forcado])) {
    $tema_nome = $tema_forcado;
} else {
    $tema_nome = detectarTema($proposta['tipo_servico']);
}

$tema_info = carregarTema($tema_nome);
$config = $CONFIG;

if (!empty($dados_empresa)) {
    $config['empresa']['nome']     = $dados_empresa['Empresa'];
    $config['empresa']['cnpj']     = $dados_empresa['CNPJ'] ?? '';
    // Corrigido mapeamento de cidade/estado empresa se disponível
    $config['empresa']['endereco'] = ($dados_empresa['Cidade'] ?? '') . ' - ' . ($dados_empresa['Estado'] ?? '');
}

include 'templates/base.php';
