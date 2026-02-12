<?php

/**
 * =========================================================
 * GERADOR DE DOCUMENTO DINÂMICO (COM TEMPLATES)
 * =========================================================
 * Recebe dados do editor_dinamico.php via POST, identifica
 * o modelo correto no banco de dados e gera o DOCX.
 * 
 * @author Sistema SGT Proposta
 * @version 2.0 (Template Engine)
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Configurações e Dependências
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once 'vendor/autoload.php';
require_once 'config.php';
require_once 'db.php';

use PhpOffice\PhpWord\TemplateProcessor;

// =====================================================
// FUNÇÕES AUXILIARES
// =====================================================

function formatarMoeda($valor)
{
    if (empty($valor)) return 'R$ 0,00';
    if (is_string($valor)) {
        if (strpos($valor, 'R$') === 0) return $valor;
        $valor = str_replace(['R$', ' '], '', $valor);
        if (strpos($valor, ',') !== false) {
            $valor = str_replace('.', '', $valor);
            $valor = str_replace(',', '.', $valor);
        }
    }
    return 'R$ ' . number_format(floatval($valor), 2, ',', '.');
}

function valorPorExtenso($valor)
{
    // Implementação simplificada ou usar biblioteca externa se disponível
    // Aqui vamos formatar apenas o valor numérico se a função completa não estiver disponível
    if (function_exists('valorExtensoDoc')) {
        return valorExtensoDoc($valor);
    }
    return 'valor em reais'; // Fallback simples
}

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
    $timestamp = $data ? strtotime($data) : time();
    $dia = date('d', $timestamp);
    $mes = $meses[intval(date('m', $timestamp))];
    $ano = date('Y', $timestamp);
    return "$dia de $mes de $ano";
}

// =====================================================
// PROCESSAMENTO PRINCIPAL
// =====================================================

try {
    // 1. Validar Requisição
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' && !isset($_GET['debug'])) {
        throw new Exception("Método inválido. Use POST.");
    }

    $dados = $_POST;
    if (empty($dados) && isset($_GET['debug'])) {
        // Dados Mock para Debug
        $dados = [
            'nome_cliente' => 'Cliente Debug',
            'finalidade' => 'Drone',
            'valor_final_proposta' => '1500.00'
        ];
    }

    // 2. Identificar Template no Banco
    $conn = Database::getProd();
    $pastaModelos = __DIR__ . '/modelos_prod/';
    $arquivoModelo = 'ModeloProfissionalV2.docx'; // Default

    // Tenta identificar pelo serviço/finalidade enviado
    $termoBusca = '';
    if (!empty($dados['finalidade'])) $termoBusca = $dados['finalidade'];
    elseif (!empty($dados['tipo_levantamento'])) $termoBusca = $dados['tipo_levantamento'];

    if (!empty($termoBusca)) {
        // Busca aproximada no banco
        $termoBuscaEscaped = $conn->real_escape_string($termoBusca);
        // Tenta achar um serviço que contenha o termo ou que o termo contenha o nome do serviço
        $sql = "SELECT arquivo_modelo FROM Tipo_Servicos 
                WHERE (nome LIKE '%$termoBuscaEscaped%' OR '$termoBuscaEscaped' LIKE CONCAT('%', nome, '%'))
                AND arquivo_modelo IS NOT NULL 
                ORDER BY LENGTH(nome) DESC LIMIT 1";

        $res = $conn->query($sql);
        if ($res && $row = $res->fetch_assoc()) {
            if (!empty($row['arquivo_modelo']) && file_exists($pastaModelos . $row['arquivo_modelo'])) {
                $arquivoModelo = $row['arquivo_modelo'];
            }
        }
    }

    $caminhoModelo = $pastaModelos . $arquivoModelo;

    if (!file_exists($caminhoModelo)) {
        // Fallback final
        $caminhoModelo = $pastaModelos . 'ModeloProfissionalV2.docx';
        if (!file_exists($caminhoModelo)) {
            throw new Exception("Arquivo de modelo não encontrado: $arquivoModelo");
        }
    }

    // 3. Processar Template
    $template = new TemplateProcessor($caminhoModelo);

    // Dados da Empresa (Criador ou Padrão)
    $id_usuario = isset($_SESSION['usuario_id']) ? intval($_SESSION['usuario_id']) : 0;
    $empresa = [];
    if ($id_usuario > 0) {
        $res = $conn->query("SELECT * FROM DadosEmpresa WHERE id_criador = $id_usuario LIMIT 1");
        $empresa = $res->fetch_assoc() ?: [];
    }
    if (empty($empresa)) {
        $res = $conn->query("SELECT * FROM DadosEmpresa LIMIT 1");
        $empresa = $res->fetch_assoc() ?: [];
    }

    // Mapeamento de Variáveis (Flattening)
    // Mescla dados do POST + Dados da Empresa + Dados Calculados

    // Variáveis Padrão
    $vars = [
        // Cliente
        'nome_cliente' => $dados['nome_cliente'] ?? 'CLIENTE',
        'email_cliente' => $dados['email_cliente'] ?? ($dados['email'] ?? ''),
        'telefone_cliente' => $dados['telefone_cliente'] ?? ($dados['telefone'] ?? ''),
        'documento_cliente' => $dados['documento_cliente'] ?? '',

        // Obra/Serviço
        'finalidade' => $dados['finalidade'] ?? 'Serviço de Topografia',
        'tipo_levantamento' => $dados['tipo_levantamento'] ?? '',
        'endereco_obra' => $dados['endereco_obra'] ?? ($dados['endereco'] ?? ''),
        'bairro_obra' => $dados['bairro_obra'] ?? ($dados['bairro'] ?? ''),
        'cidade_obra' => $dados['cidade_obra'] ?? ($dados['cidade'] ?? ''),
        'estado_obra' => $dados['estado_obra'] ?? ($dados['estado'] ?? ''),
        'area_obra' => $dados['area_obra'] ?? ($dados['area'] ?? ''),

        // Financeiro
        'valor_proposta' => formatarMoeda($dados['valor_final_proposta'] ?? 0),
        'valor_extenso' => valorPorExtenso($dados['valor_final_proposta'] ?? 0),
        'mobilizacao_percentual' => $dados['mobilizacao_percentual'] ?? '30',
        'mobilizacao_valor' => formatarMoeda($dados['mobilizacao_valor'] ?? 0),
        'restante_percentual' => $dados['restante_percentual'] ?? '70',
        'restante_valor' => formatarMoeda($dados['restante_valor'] ?? 0),

        // Cronograma
        'dias_campo' => $dados['dias_campo'] ?? '0',
        'dias_escritorio' => $dados['dias_escritorio'] ?? '0',
        'prazo_total' => $dados['prazo_execucao'] ?? '',

        // Empresa
        'nome_empresa' => $empresa['Empresa'] ?? 'SGT Topografia',
        'cnpj_empresa' => $empresa['CNPJ'] ?? '',
        'email_empresa' => $empresa['Email'] ?? '',
        'telefone_empresa' => $empresa['Telefone'] ?? '',
        'whatsapp_empresa' => $empresa['Whatsapp'] ?? '',
        'cidade_empresa' => $empresa['Cidade'] ?? 'Belo Horizonte',

        // Sistema
        'numero_proposta' => $dados['numero_proposta'] ?? date('Y') . '/001',
        'data_extenso' => dataPorExtenso(),
        'ano_atual' => date('Y')
    ];

    // Equipamentos (Lógica Condicional Simples)
    $vars['veiculo'] = !empty($dados['veiculo']) ? $dados['veiculo'] : '.';
    $vars['estacao_total'] = !empty($dados['estacao_total']) ? $dados['estacao_total'] : '.';
    $vars['gps'] = !empty($dados['gps']) ? $dados['gps'] : '.';
    $vars['drone'] = !empty($dados['drone']) ? $dados['drone'] : '.';

    // Substituição no Template
    foreach ($vars as $chave => $valor) {
        $template->setValue($chave, $valor);
        // Também tenta versão UPPERCASE para compatibilidade
        $template->setValue(strtoupper($chave), $valor);
    }

    // Blocos de Texto Longo (Preserva quebras de linha)
    $textAreas = [
        'apresentacao_content',
        'escopo_content',
        'metodologia_content',
        'cronograma_obs',
        'investimento_texto',
        'condicoes_texto',
        'consideracoes_content'
    ];

    foreach ($textAreas as $area) {
        $texto = $dados[$area] ?? '';
        // Converte quebras de linha em <w:br/>
        $texto = str_replace(["\r\n", "\r", "\n"], "<w:br/>", htmlspecialchars($texto));
        $template->setValue($area, $texto);
    }

    // 4. Salvar Arquivo
    $dirDestino = __DIR__ . '/propostas_emitidas/';
    if (!is_dir($dirDestino)) mkdir($dirDestino, 0755, true);

    $nomeClienteSanitized = preg_replace('/[^A-Za-z0-9]/', '', iconv('UTF-8', 'ASCII//TRANSLIT', $vars['nome_cliente']));
    $nomeArquivo = 'Proposta_' . $nomeClienteSanitized . '_' . date('Ymd_His') . '.docx';
    $caminhoFinal = $dirDestino . $nomeArquivo;

    $template->saveAs($caminhoFinal);

    // 5. Retorno
    $urlDownload = 'propostas_emitidas/' . $nomeArquivo;

    if (isset($dados['ajax']) || isset($_GET['ajax']) || isset($_POST['ajax'])) {
        echo json_encode([
            'success' => true,
            'message' => 'Proposta gerada com sucesso!',
            'arquivo' => $nomeArquivo,
            'url' => $urlDownload,
            'modelo_usado' => $arquivoModelo
        ]);
    } else {
        // Redirecionamento clássico
        header("Location: final.php?arquivo=" . urlencode($nomeArquivo) . "&cliente=" . urlencode($vars['nome_cliente']));
        exit;
    }
} catch (Exception $e) {
    if (isset($_POST['ajax']) || isset($_GET['ajax'])) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    } else {
        die("<h1>Erro ao gerar proposta</h1><p>" . $e->getMessage() . "</p>");
    }
}
