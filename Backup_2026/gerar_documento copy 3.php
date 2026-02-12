<?php
// Nome da página: gerar_documento.php
// VERSÃO: Lógica de Nomenclatura Simplificada e Assertiva

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once 'db.php';
require_once 'vendor/autoload.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// --- FUNÇÕES DE APOIO ---

function dataParaExtenso($data) {
    setlocale(LC_TIME, 'pt_BR.utf-8', 'pt_BR', 'portuguese');
    return strftime('%d de %B de %Y', strtotime($data));
}

function numeroParaExtenso($valor = 0) {
    $singular = ["centavo", "real", "mil", "milhão", "bilhão", "trilhão", "quatrilhão"];
    $plural = ["centavos", "reais", "mil", "milhões", "bilhões", "trilhões", "quatrilhões"];
    $c = ["", "cem", "duzentos", "trezentos", "quatrocentos", "quinhentos", "seiscentos", "setecentos", "oitocentos", "novecentos"];
    $d = ["", "dez", "vinte", "trinta", "quarenta", "cinquenta", "sessenta", "setenta", "oitenta", "noventa"];
    $d10 = ["dez", "onze", "doze", "treze", "quatorze", "quinze", "dezesseis", "dezessete", "dezoito", "dezenove"];
    $u = ["", "um", "dois", "três", "quatro", "cinco", "seis", "sete", "oito", "nove"];
    $z=0;
    $valor=number_format($valor, 2, ".", ".");
    $inteiro=explode(".", $valor);
    for($i=0;$i<count($inteiro);$i++)
        for($ii=strlen($inteiro[$i]);$ii<3;$ii++)
            $inteiro[$i] = "0".$inteiro[$i];
    $rt = "";
    $fim = count($inteiro) - ($inteiro[count($inteiro) - 1] > 0 ? 1 : 0);
    for ($i = 0; $i < count($inteiro); $i++) {
        $valor = $inteiro[$i];
        $rc = (($valor > 100) && ($valor < 200)) ? "cento" : $c[$valor[0]];
        $rd = ($valor[1] < 2) ? "" : $d[$valor[1]];
        $ru = ($valor > 0) ? (($valor[1] == 1) ? $d10[$valor[2]] : $u[$valor[2]]) : "";
        $r = $rc . (($rc && ($rd || $ru)) ? " e " : "") . $rd . (($rd && $ru) ? " e " : "") . $ru;
        $t = count($inteiro) - 1 - $i;
        $r .= ($r) ? " " . ($valor > 1 ? $plural[$t] : $singular[$t]) : "";
        if ($valor == "000") $z++; elseif ($z > 0) $z--;
        if (($t == 1) && ($z > 0) && ($inteiro[0] > 0)) $r .= (($z > 1) ? " de " : "") . $plural[$t];
        if ($r) $rt = $rt . ((($i > 0) && ($i <= $fim) && ($inteiro[0] > 0) && ($z < 1)) ? (($i < $fim) ? ", " : " e ") : " ") . $r;
    }
    if(!$rt) return "zero reais";
    return trim(ucwords($rt));
}

// Função simples para remover acentos de uma palavra
function removerAcentos($string) {
    return preg_replace(array("/(á|à|ã|â|ä)/","/(Á|À|Ã|Â|Ä)/","/(é|è|ê|ë)/","/(É|È|Ê|Ë)/","/(í|ì|î|ï)/","/(Í|Ì|Î|Ï)/","/(ó|ò|õ|ô|ö)/","/(Ó|Ò|Õ|Ô|Ö)/","/(ú|ù|û|ü)/","/(Ú|Ù|Û|Ü)/","/(ñ)/","/(Ñ)/"),explode(" ","a A e E i I o O u U n N"),$string);
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Erro: ID inválido.");
}
$id_proposta = intval($_GET['id']);

try {
    // 1. Busca de Dados
    $sql = "SELECT * FROM Propostas WHERE id_proposta = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $id_proposta);
    $stmt->execute();
    $dados_proposta = $stmt->get_result()->fetch_assoc();

    if (!$dados_proposta) throw new Exception("Proposta não encontrada.");

    $cliente_info = $conn->query("SELECT * FROM Clientes WHERE id_cliente = " . intval($dados_proposta['id_cliente']))->fetch_assoc();
    $empresa_info = $conn->query("SELECT * FROM DadosEmpresa LIMIT 1")->fetch_assoc();
    $servico_info = $conn->query("SELECT nome FROM Tipo_Servicos WHERE id_servico = " . intval($dados_proposta['id_servico']))->fetch_assoc();

    // 2. Template
    $mapaModelos = [
        '11' => 'ModeloPropostaUsucapiao.docx',
        '12' => 'ModeloPropostaPlanimetrico.docx',
        '13' => 'ModeloPropostaPlanialtimetrico.docx',
        '14' => 'ModeloPropostaObraTerraplanagem.docx',
        '15' => 'ModeloPropostaObraIndustrial.docx',
        '16' => 'ModeloPropostaObraCivil.docx',
        '17' => 'ModeloPropostaLocacaodeObra.docx',
        '18' => 'ModeloPropostaLocacaoTerraplenagem.docx',
        '19' => 'ModeloPropostaDrone.docx',
        '20' => 'ModeloPropostaDesdobramento.docx',
        '21' => 'ModeloPropostaConferencia.docx',
    ];
    $nomeModelo = $mapaModelos[$dados_proposta['id_servico']] ?? 'ModeloPropostaPadrao.docx';
    $caminhoModelo = __DIR__ . '/modelos/' . $nomeModelo;

    if (!file_exists($caminhoModelo)) throw new Exception("Template não encontrado: $nomeModelo");

    $templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor($caminhoModelo);

    // 3. Preenchimento
    $templateProcessor->setValue('numero_proposta', htmlspecialchars($dados_proposta['numero_proposta']));
    $templateProcessor->setValue('DExrenso', dataParaExtenso($dados_proposta['data_criacao']));
    
    // Cliente
    $templateProcessor->setValue('nome_cliente_salvo', htmlspecialchars($cliente_info['nome_cliente']));
    $templateProcessor->setValue('email_salvo', htmlspecialchars($cliente_info['email']));
    $templateProcessor->setValue('telefone_salvo', htmlspecialchars($cliente_info['telefone']));
    $templateProcessor->setValue('celular_salvo', htmlspecialchars($dados_proposta['celular_salvo'] ?? ''));
    $templateProcessor->setValue('whatsapp_salvo', htmlspecialchars($dados_proposta['whatsapp_salvo'] ?? ''));
    
    // Obra
    $templateProcessor->setValue('endereco_obra', htmlspecialchars($dados_proposta['endereco_obra']));
    $templateProcessor->setValue('bairro_obra', htmlspecialchars($dados_proposta['bairro_obra']));
    $templateProcessor->setValue('cidade_obra', htmlspecialchars($dados_proposta['cidade_obra']));
    $templateProcessor->setValue('estado_obra', htmlspecialchars($dados_proposta['estado_obra']));
    $templateProcessor->setValue('tipo_levantamento', htmlspecialchars($dados_proposta['tipo_levantamento']));
    $templateProcessor->setValue('finalidade', htmlspecialchars($dados_proposta['finalidade']));
    $templateProcessor->setValue('area_obra', htmlspecialchars($dados_proposta['area_obra']));
    
    // Financeiro
    $templateProcessor->setValue('ValorProposta', 'R$ ' . number_format($dados_proposta['valor_final_proposta'], 2, ',', '.'));
    $templateProcessor->setValue('ValorExtenso', numeroParaExtenso($dados_proposta['valor_final_proposta']));
    $templateProcessor->setValue('mobilizacao_percentual', number_format($dados_proposta['mobilizacao_percentual'], 0, ',', '.'));
    $templateProcessor->setValue('mobilizacao_valor', 'R$ ' . number_format($dados_proposta['mobilizacao_valor'], 2, ',', '.'));
    $templateProcessor->setValue('restante_percentual', number_format($dados_proposta['restante_percentual'], 0, ',', '.'));
    $templateProcessor->setValue('restante_valor', 'R$ ' . number_format($dados_proposta['restante_valor'], 2, ',', '.'));

    // Empresa
    if ($empresa_info) {
        foreach ($empresa_info as $key => $value) {
            $templateProcessor->setValue($key, htmlspecialchars($value ?? ''));
        }
    }

    // Equipamentos (Marcas)
    $equipamentos = [ 'Veiculo' => 'Não', 'Estacao_Total' => 'Não', 'GPS' => 'Não', 'Drone' => 'Não' ];
    $modelos = [ 'Veiculo' => '', 'Estacao_Total' => '', 'GPS' => '', 'Drone' => '' ];
    $res_loc = $conn->query("SELECT tl.nome as tipo, m.nome_marca FROM Proposta_Locacao pl JOIN Tipo_Locacao tl ON pl.id_locacao = tl.id_locacao LEFT JOIN Marcas m ON pl.id_marca = m.id_marca WHERE pl.id_proposta = $id_proposta");
    while ($row = $res_loc->fetch_assoc()) {
        $t = $row['tipo']; $m = $row['nome_marca'] ?? '';
        if (stripos($t, 'Veiculo') !== false) { $equipamentos['Veiculo'] = 'Sim'; $modelos['Veiculo'] = $m; }
        if (stripos($t, 'Estação Total') !== false) { $equipamentos['Estacao_Total'] = 'Sim'; $modelos['Estacao_Total'] = $m; }
        if (stripos($t, 'GPS') !== false) { $equipamentos['GPS'] = 'Sim'; $modelos['GPS'] = $m; }
        if (stripos($t, 'Drone') !== false) { $equipamentos['Drone'] = 'Sim'; $modelos['Drone'] = $m; }
    }
    foreach($equipamentos as $k=>$v) $templateProcessor->setValue($k, $v);
    foreach($modelos as $k=>$v) $templateProcessor->setValue("modelo_$k", $v);


    // -----------------------------------------------------------
    // 4. LÓGICA DE NOME DE ARQUIVO SIMPLIFICADA E CORRIGIDA
    // -----------------------------------------------------------
    
    // Passo A: O Número (Já contém ELM-ANO-NUM)
    $parteNumero = $dados_proposta['numero_proposta'];

    // Passo B: O Primeiro Nome do Cliente
    $nomeCompleto = trim($cliente_info['nome_cliente']); 
    // Explode ANTES de limpar qualquer coisa para garantir a separação
    $partesNome = explode(" ", $nomeCompleto);
    $primeiroNome = $partesNome[0];
    // Remove acentos e caracteres estranhos apenas da primeira palavra
    $primeiroNomeLimpo = removerAcentos($primeiroNome);
    $primeiroNomeLimpo = preg_replace('/[^A-Za-z0-9]/', '', $primeiroNomeLimpo);

    // Passo C: O Serviço (Ex: "Desdobramento")
    // Removemos o prefixo "Levantamento " se existir, para ficar curto
    $nomeServico = $servico_info['nome'];
    $nomeServico = str_ireplace("Levantamento ", "", $nomeServico); // Remove "Levantamento "
    $nomeServicoLimpo = removerAcentos($nomeServico);
    $nomeServicoLimpo = preg_replace('/[^A-Za-z0-9]/', '', $nomeServicoLimpo); // Remove espaços do serviço (Ex: ObraCivil)

    // Montagem Final
    // Ex: Proposta_ELM-2025-028_Edivaldo_Desdobramento.docx
    $nomeArquivoFinal = "Proposta_" . $parteNumero . "_" . $primeiroNomeLimpo . "_" . $nomeServicoLimpo . ".docx";


    // 5. Download
    header('Content-Description: File Transfer');
    header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
    header('Content-Disposition: attachment; filename="' . $nomeArquivoFinal . '"');
    header('Content-Transfer-Encoding: binary');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: public');
    
    $templateProcessor->saveAs('php://output');
    exit;

} catch (Exception $e) {
    die("Erro: " . $e->getMessage());
}
?>