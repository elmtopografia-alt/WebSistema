<?php
// ARQUIVO: gerar_documento.php
// VERSÃO: PLENA E CORRIGIDA (Valor Extenso)

require_once 'vendor/autoload.php';
require_once 'db.php';

session_start();
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Limpa qualquer output anterior
if (ob_get_length()) ob_clean();

if (!isset($_GET['id'])) { die("ID da proposta não fornecido."); }
$id_proposta = intval($_GET['id']);

// --- CONFIGURAÇÃO ---
$id_usuario = $_SESSION['usuario_id'];
$is_demo = (isset($_SESSION['ambiente']) && $_SESSION['ambiente'] === 'demo');

// 1. SELEÇÃO DA PASTA DE MODELOS (ISOLAMENTO)
if ($is_demo) {
    $pastaBase = __DIR__ . '/modelos_demo/';
} else {
    $pastaBase = __DIR__ . '/modelos_prod/';
}

// Mapa de Modelos
$mapaModelos = [
    11 => 'ModeloPropostaUsucapiao.docx', 12 => 'ModeloPropostaPlanimetrico.docx', 13 => 'ModeloPropostaPlanialtimetrico.docx',
    14 => 'ModeloPropostaObraTerraplanagem.docx', 15 => 'ModeloPropostaObraIndustrial.docx', 16 => 'ModeloPropostaObraCivil.docx',
    17 => 'ModeloPropostaLocacaodeObra.docx', 18 => 'ModeloPropostaLocacaoTerraplenagem.docx', 19 => 'ModeloPropostaDrone.docx',
    20 => 'ModeloPropostaDesdobramento.docx', 21 => 'ModeloPropostaConferencia.docx'
];

// --- FUNÇÕES AUXILIARES ---
function numeroParaExtenso($valor = 0) {
    // CORREÇÃO: Arredonda antes de processar
    $valor = round($valor, 2); 
    
    if (class_exists('NumberFormatter')) {
        $f = new NumberFormatter("pt-BR", NumberFormatter::SPELLOUT);
        return $f->format($valor) . " reais";
    }
    return number_format($valor, 2, ',', '.') . " (valor extenso)";
}

function dataParaExtenso($data) {
    setlocale(LC_TIME, 'pt_BR', 'pt_BR.utf-8', 'portuguese');
    return strftime('%d de %B de %Y', strtotime($data));
}

try {
    // 1. BUSCA DADOS DA PROPOSTA
    $sql = "SELECT p.*, c.nome_cliente, c.email, c.telefone, c.celular, c.whatsapp 
            FROM Propostas p 
            JOIN Clientes c ON p.id_cliente = c.id_cliente 
            WHERE p.id_proposta = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $id_proposta);
    $stmt->execute();
    $proposta = $stmt->get_result()->fetch_assoc();

    if (!$proposta) { die("Proposta não encontrada no banco de dados."); }

    // 2. BUSCA DADOS DA EMPRESA (Pelo ID do Criador da Proposta)
    $stmt = $conn->prepare("SELECT * FROM DadosEmpresa WHERE id_criador = ?");
    $stmt->bind_param('i', $proposta['id_criador']);
    $stmt->execute();
    $emp = $stmt->get_result()->fetch_assoc();

    // 3. SELEÇÃO DO ARQUIVO WORD
    $id_servico = $proposta['id_servico'];
    $nomeArquivo = $mapaModelos[$id_servico] ?? 'ModeloPropostaPadrao.docx';
    $caminhoArquivo = $pastaBase . $nomeArquivo;

    if (!file_exists($caminhoArquivo)) {
        $caminhoArquivo = $pastaBase . 'ModeloPropostaPadrao.docx';
    }

    if (!file_exists($caminhoArquivo)) {
        die("Erro de Arquivo: Não foi possível localizar o modelo Word em $caminhoArquivo.");
    }

    // 4. LOGICA DOS EQUIPAMENTOS (Recalcular baseado no que foi salvo)
    $equip_veiculo = 'Não'; $equip_estacao = 'Não'; $equip_gps = 'Não'; $equip_drone = 'Não';
    
    $res_loc = $conn->query("SELECT pl.*, tl.nome as nome_tipo, m.nome_marca 
                             FROM Proposta_Locacao pl 
                             JOIN Tipo_Locacao tl ON pl.id_locacao = tl.id_locacao 
                             LEFT JOIN Marcas m ON pl.id_marca = m.id_marca 
                             WHERE pl.id_proposta = $id_proposta");
    
    while ($row = $res_loc->fetch_assoc()) {
        $nm = mb_strtolower($row['nome_tipo']);
        $texto = $row['nome_marca'] ? $row['nome_marca'] : "Sim";
        
        if (strpos($nm, 'veículo') !== false) $equip_veiculo = $texto;
        if (strpos($nm, 'estação') !== false) $equip_estacao = $texto;
        if (strpos($nm, 'gps') !== false) $equip_gps = $texto;
        if (strpos($nm, 'drone') !== false) $equip_drone = $texto;
    }

    // 5. PREENCHIMENTO DO PHPWORD
    $template = new \PhpOffice\PhpWord\TemplateProcessor($caminhoArquivo);

    // LOGO DA EMPRESA
    $caminhoLogo = '';
    if (!empty($emp['logo_caminho'])) {
        $caminhoLogo = __DIR__ . '/' . $emp['logo_caminho'];
    }

    if (!empty($caminhoLogo) && is_file($caminhoLogo)) {
        try { 
            $template->setImageValue('logo_empresa', ['path' => $caminhoLogo, 'height' => 81, 'width' => 587, 'ratio' => true]); 
        } catch (Exception $eImg) { 
            $template->setValue('logo_empresa', ''); 
        }
    } else { 
        $template->setValue('logo_empresa', ''); 
    }

    // Mapeamento Geral
    $v_final = $proposta['valor_final_proposta'];
    $v_extenso = numeroParaExtenso($v_final);

    $template->setValue('numero_proposta', $proposta['numero_proposta']);
    $template->setValue('Cidade', $proposta['cidade_obra']);
    $template->setValue('DExrenso', dataParaExtenso($proposta['data_criacao']));

    // Cliente
    $template->setValue('nome_cliente_salvo', $proposta['nome_cliente_salvo']);
    $template->setValue('email_salvo', $proposta['email_salvo']);
    $template->setValue('telefone_salvo', $proposta['telefone_salvo']);
    $template->setValue('celular_salvo', $proposta['celular_salvo']);
    $template->setValue('whatsapp_salvo', $proposta['whatsapp_salvo']);

    // Empresa
    $template->setValue('Empresa', $emp['Empresa']);
    $template->setValue('CNPJ', $emp['CNPJ']);
    $template->setValue('empresa_proponente_nome', $emp['Empresa']);
    $template->setValue('empresa_proponente_cidade', $emp['Cidade']);
    $template->setValue('whatsapp', $emp['Whatsapp']);

    // Equipamentos
    $template->setValue('Veiculo', $equip_veiculo);
    $template->setValue('Estacao_Total', $equip_estacao);
    $template->setValue('GPS', $equip_gps);
    $template->setValue('Drone', $equip_drone);

    // Valores Financeiros
    $template->setValue('ValorProposta', number_format($v_final, 2, ',', '.'));
    $template->setValue('ValorExtenso', $v_extenso);
    
    $template->setValue('mobilizacao_percentual', number_format($proposta['mobilizacao_percentual'], 2, ',', '.'));
    $template->setValue('mobilizacao_valor', number_format($proposta['mobilizacao_valor'], 2, ',', '.'));
    $template->setValue('restante_percentual', number_format($proposta['restante_percentual'], 2, ',', '.'));
    $template->setValue('restante_valor', number_format($proposta['restante_valor'], 2, ',', '.'));

    // Dados Bancários
    $template->setValue('Banco', $emp['Banco']);
    $template->setValue('Agencia', $emp['Agencia']);
    $template->setValue('Conta', $emp['Conta']);
    $template->setValue('PIX', $emp['PIX']);

    // Obra
    $template->setValue('endereco_obra', $proposta['endereco_obra']);
    $template->setValue('bairro_obra', $proposta['bairro_obra']);
    $template->setValue('cidade_obra', $proposta['cidade_obra']);
    $template->setValue('estado_obra', $proposta['estado_obra']);
    $template->setValue('area_obra', $proposta['area_obra']);
    $template->setValue('tipo_levantamento', $proposta['tipo_levantamento']);
    $template->setValue('finalidade', $proposta['finalidade']);
    $template->setValue('prazo_execucao', $proposta['prazo_execucao']);

    // 6. DOWNLOAD
    $nomeArquivoDownload = 'Proposta_' . $proposta['numero_proposta'] . '.docx';

    header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
    header('Content-Disposition: attachment; filename="' . $nomeArquivoDownload . '"');
    header('Cache-Control: max-age=0');

    $template->saveAs('php://output');
    exit;

} catch (Exception $e) {
    die("Erro ao gerar documento: " . $e->getMessage());
}
?>