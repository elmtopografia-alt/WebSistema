<?php
// ARQUIVO: gerar_documento.php
// VERSÃO: GERAÇÃO DINÂMICA DE TEMPLATE (PARA RE-DOWNLOAD)

require_once 'vendor/autoload.php';
require_once 'db.php';

session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

if (!isset($_GET['id'])) die("ID inválido");
$id_proposta = intval($_GET['id']);

// --- CONFIGURAÇÃO ---
$pastaModelos = __DIR__ . '/modelos/';

// Função de Sanitização (Repetida aqui para não depender de includes extras)
function sanitizarNome($string) {
    $string = iconv('UTF-8', 'ASCII//TRANSLIT', $string);
    $string = preg_replace('/[^a-zA-Z0-9\s]/', '', $string);
    $string = preg_replace('/[\s-]+/', ' ', $string);
    $string = str_replace(' ', '', ucwords(strtolower($string)));
    return $string;
}

function numeroParaExtenso($valor = 0) {
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
    $sql = "SELECT p.*, tl.nome as nome_servico 
            FROM Propostas p 
            JOIN Tipo_Servicos tl ON p.id_servico = tl.id_servico
            WHERE p.id_proposta = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $id_proposta);
    $stmt->execute();
    $proposta = $stmt->get_result()->fetch_assoc();

    if (!$proposta) die("Proposta não encontrada.");

    $is_demo = $proposta['is_demo'];
    $id_criador = $proposta['id_criador'];

    // 2. BUSCA DADOS DA EMPRESA (Do dono da proposta)
    $stmt_emp = $conn->prepare("SELECT * FROM DadosEmpresa WHERE id_criador = ?");
    $stmt_emp->bind_param('i', $id_criador);
    $stmt_emp->execute();
    $emp = $stmt_emp->get_result()->fetch_assoc();
    if (!$emp) $emp = []; // Garante que é um array vazio se não achar

    // 3. SELEÇÃO DO ARQUIVO WORD (Lógica Dinâmica)
    $nome_servico_limpo = sanitizarNome($proposta['nome_servico']); // <-- NOME LIMPO
    $sufixo = $is_demo ? '_DEMO.docx' : '.docx';
    $nomeArquivoModelo = 'ModeloProposta' . $nome_servico_limpo . $sufixo;
    $caminhoArquivo = $pastaModelos . $nomeArquivoModelo;
    $nomeArquivoPadrao = $is_demo ? 'ModeloPropostaPadrao_DEMO.docx' : 'ModeloPropostaPadrao.docx';

    if (!file_exists($caminhoArquivo)) { $caminhoArquivo = $pastaModelos . $nomeArquivoPadrao; }
    if (!file_exists($caminhoArquivo)) { die("Modelo Word não encontrado em: " . $caminhoArquivo); }

    // 4. LOGICA DOS EQUIPAMENTOS (Recalcula)
    $equip_veiculo = 'Não'; $equip_estacao = 'Não'; $equip_gps = 'Não'; $equip_drone = 'Não';
    $res_loc = $conn->query("SELECT pl.*, tl.nome as nome_tipo, m.nome_marca 
                             FROM Proposta_Locacao pl 
                             JOIN Tipo_Locacao tl ON pl.id_locacao = tl.id_locacao 
                             LEFT JOIN Marcas m ON pl.id_marca = m.id_marca 
                             WHERE pl.id_proposta = $id_proposta");
    
    while ($row = $res_loc->fetch_assoc()) {
        $nm = mb_strtolower($row['nome_tipo']); $texto = $row['nome_marca'] ?? "Sim";
        if (strpos($nm, 'veículo') !== false) $equip_veiculo = $texto;
        if (strpos($nm, 'estação') !== false) $equip_estacao = $texto;
        if (strpos($nm, 'gps') !== false) $equip_gps = $texto;
        if (strpos($nm, 'drone') !== false) $equip_drone = $texto;
    }

    // 5. PREENCHIMENTO DO PHPWORD (Chaves da Proposta)
    $template = new \PhpOffice\PhpWord\TemplateProcessor($caminhoArquivo);
    $v_final = $proposta['valor_final_proposta'];
    $v_extenso = numeroParaExtenso($v_final);

    // Mapeamento COMPLETO (Usando os dados do banco)
    $template->setValue('numero_proposta', $proposta['numero_proposta']);
    $template->setValue('Agencia', $emp['Agencia'] ?? 'N/A');
    $template->setValue('Banco', $emp['Banco'] ?? 'N/A');
    $template->setValue('CNPJ', $emp['CNPJ'] ?? 'N/A');
    $template->setValue('Conta', $emp['Conta'] ?? 'N/A');
    $template->setValue('Drone', $equip_drone);
    $template->setValue('Empresa', $emp['Empresa'] ?? 'N/A');
    $template->setValue('Estacao_Total', $equip_estacao);
    $template->setValue('GPS', $equip_gps);
    $template->setValue('PIX', $emp['PIX'] ?? 'N/A');
    $template->setValue('ValorProposta', number_format($v_final, 2, ',', '.'));
    $template->setValue('ValorExtenso', $v_extenso);
    $template->setValue('Veiculo', $equip_veiculo);
    $template->setValue('area_obra', $proposta['area_obra'] ?? 'N/A');
    $template->setValue('bairro_obra', $proposta['bairro_obra'] ?? 'N/A');
    $template->setValue('celular_salvo', $proposta['celular_salvo']);
    $template->setValue('cidade_obra', $proposta['cidade_obra'] ?? 'N/A');
    $template->setValue('email_salvo', $proposta['email_salvo']);
    $template->setValue('endereco_obra', $proposta['endereco_obra'] ?? 'N/A');
    $template->setValue('estado_obra', $proposta['estado_obra'] ?? 'N/A');
    $template->setValue('finalidade', $proposta['finalidade'] ?? 'N/A');
    $template->setValue('mobilizacao_percentual', number_format($proposta['mobilizacao_percentual'] ?? 0, 2, ',', '.'));
    $template->setValue('mobilizacao_valor', number_format($proposta['mobilizacao_valor'] ?? 0, 2, ',', '.'));
    $template->setValue('nome_cliente_salvo', $proposta['nome_cliente_salvo']);
    $template->setValue('restante_percentual', number_format($proposta['restante_percentual'] ?? 0, 2, ',', '.'));
    $template->setValue('restante_valor', number_format($proposta['restante_valor'] ?? 0, 2, ',', '.'));
    $template->setValue('telefone_salvo', $proposta['telefone_salvo']);
    $template->setValue('whatsapp_salvo', $proposta['whatsapp_salvo']);
    $template->setValue('whatsapp', $emp['Whatsapp'] ?? 'N/A');
    $template->setValue('DExrenso', dataParaExtenso($proposta['data_criacao']));

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