<?php
/**
 * Controller de Geração de Proposta
 * Integração com sistema legado SGT Propostas
 */

// DEBUG TEMPORÁRIO
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once 'config.php';
require_once 'db.php';
require_once 'equipamentos_mapper.php';

// Inicializa Conexão
$conn = Database::getProd();

// Verificação de autenticação (adaptar ao seu sistema)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// if (!isset($_SESSION['usuario_id'])) { header('Location: login.php'); exit; }

// Recebe ID da proposta (via GET ou POST)
$id_proposta = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Tenta pegar o último ID se não informado
if ($id_proposta === 0) {
    // Busca a última proposta criada
    $sqlLast = "SELECT id_proposta FROM Propostas ORDER BY id_proposta DESC LIMIT 1";
    $resultLast = $conn->query($sqlLast);
    if ($resultLast && $rowLast = $resultLast->fetch_assoc()) {
        $id_proposta = $rowLast['id_proposta'];
    } else {
        die("<h1>ID da proposta não informado e nenhuma proposta encontrada no banco.</h1>");
    }
}

// BUSCA DADOS DO BANCO (Resiliente: Usa p.* para evitar erro de coluna inexistente)
$sql = "SELECT 
            p.*,
            c.nome_cliente as cliente_nome,
            c.email as cliente_email,
            c.telefone as cliente_telefone,
            c.whatsapp as cliente_whatsapp,
            p.area_obra as area_estimada,
            p.valor_final_proposta as valor_total,
            p.data_criacao
        FROM Propostas p
        LEFT JOIN Clientes c ON p.id_cliente = c.id_cliente
        WHERE p.id_proposta = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_proposta);
$stmt->execute();
$result = $stmt->get_result();

if (!$result || $result->num_rows === 0) {
    die("Proposta não encontrada ou erro na consulta: " . $conn->error);
}

$dados = $result->fetch_assoc();
$stmt->close(); // FECHA O STATEMENT PARA LIBERAR A CONEXÃO

// PROCESSA EQUIPAMENTOS COM A TABELA DE RELACIONAMENTO
$equipamentos = EquipamentosMapper::processar($dados);

// BUSCA CONTEÚDO PERSONALIZADO (Textos do Editor)
$conteudo = [
    'apresentacao' => '',
    'finalidade' => '',
    'escopo' => '',
    'metodologia' => '',
    'cronograma' => '',
    'investimento' => '',
    'condicoes_pagamento' => '',
    'dados_bancarios' => '',
    'consideracoes_finais' => '',
    'consideracoes' => '' // Adicionado para fallback
];

$sqlConteudo = "SELECT block_id, conteudo_texto FROM Proposta_Conteudo_Personalizado WHERE id_proposta = ?";
try {
    if ($stmtConteudo = $conn->prepare($sqlConteudo)) {
        $stmtConteudo->bind_param("i", $id_proposta);
        $stmtConteudo->execute();
        $resConteudo = $stmtConteudo->get_result();
        while ($row = $resConteudo->fetch_assoc()) {
            if (array_key_exists($row['block_id'], $conteudo)) {
                $conteudo[$row['block_id']] = $row['conteudo_texto'];
            }
        }
        $stmtConteudo->close(); 
    }
} catch (Throwable $e) {
    // Silencia erro se a tabela não existir
}

// BUSCA CRONOGRAMA DINÂMICO
$cronograma_itens = [];
$sqlCronograma = "SELECT * FROM Proposta_Cronograma WHERE id_proposta = ? ORDER BY ordem ASC";

try {
    if ($stmtCronograma = $conn->prepare($sqlCronograma)) {
        $stmtCronograma->bind_param("i", $id_proposta);
        $stmtCronograma->execute();
        $resCronograma = $stmtCronograma->get_result();
        while ($row = $resCronograma->fetch_assoc()) {
            $cronograma_itens[] = $row;
        }
        $stmtCronograma->close();
    }
} catch (Throwable $e) {
    // Silencia erro se a tabela não existir
}


// VALIDAÇÃO: Alerta se faltar equipamentos críticos
$validacao = EquipamentosMapper::validarCompletude($dados);
if (!$validacao['completo']) {
    // Log para debugging (remover em produção ou mostrar alerta sutil)
    error_log("Proposta $id_proposta com equipamentos incompletos: " . implode(', ', $validacao['faltantes']));
}

// GERA TABELA HTML DOS EQUIPAMENTOS
$tabela_equipamentos_html = EquipamentosMapper::gerarTabelaHTML($equipamentos);

// PREPARA VARIÁVEIS PARA O TEMPLATE
$numero_proposta = $dados['numero_proposta'] ?? 'GEOMETRPOLE-2026-' . str_pad($id_proposta, 3, '0', STR_PAD_LEFT);
$data_formatada = date('d \d\e F \d\e Y', strtotime($dados['data_criacao'] ?? date('Y-m-d')));

$cliente = [
    'nome' => $dados['cliente_nome'] ?? 'Não informado',
    'email' => $dados['cliente_email'] ?? 'Não informado',
    'telefone' => $dados['cliente_telefone'] ?? 'Não informado',
    'whatsapp' => $dados['cliente_whatsapp'] ?? 'Não informado'
];

$obra = [
    'endereco' => ($dados['endereco_obra'] ?? 'Não informado') . ', ' . ($dados['numero_obra'] ?? 'S/N'),
    'bairro' => $dados['bairro_obra'] ?? 'Não informado',
    'cidade_estado' => ($dados['cidade_obra'] ?? 'Belo Horizonte') . ' - ' . ($dados['estado_obra'] ?? 'MG'),
    'area' => $dados['area_estimada'] ?? '25'
];

$valor_total = floatval($dados['valor_total'] ?? 3500);
$valor_extenso = valorPorExtenso($valor_total); // Função abaixo

// FUNÇÃO AUXILIAR: Valor por extenso (simplificada - usar biblioteca real em produção)
function valorPorExtenso($valor) {
    if (class_exists('NumberFormatter')) {
        $formatter = new NumberFormatter('pt_BR', NumberFormatter::SPELLOUT);
        return $formatter->format($valor) . ' reais';
    }
    return number_format($valor, 2, ',', '.') . ' reais'; // Fallback
}

// INCLUI TEMPLATE (que usará todas as variáveis acima)
include 'proposta_template_v2.php';
?>
