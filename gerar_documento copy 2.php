<?php
// Nome da página: gerar_documento.php
// VERSÃO: Templates Atualizados (Lista Oficial com 'Terraplenagem', etc.)

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

function sanitizarNomeArquivo($string) {
    $string = trim($string);
    $string = preg_replace(array("/(á|à|ã|â|ä)/","/(Á|À|Ã|Â|Ä)/","/(é|è|ê|ë)/","/(É|È|Ê|Ë)/","/(í|ì|î|ï)/","/(Í|Ì|Î|Ï)/","/(ó|ò|õ|ô|ö)/","/(Ó|Ò|Õ|Ô|Ö)/","/(ú|ù|û|ü)/","/(Ú|Ù|Û|Ü)/","/(ñ)/","/(Ñ)/"),explode(" ","a A e E i I o O u U n N"),$string);
    return preg_replace('/[^A-Za-z0-9]/', '', $string);
}

function pegarPrimeiraPalavra($frase) {
    $frase = trim($frase);
    $partes = explode(' ', $frase);
    return $partes[0] ?? '';
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) { die("Erro: ID inválido."); }
$id_proposta = intval($_GET['id']);

try {
    // 1. Busca de Dados
    $stmt = $conn->prepare("SELECT * FROM Propostas WHERE id_proposta = ?");
    $stmt->bind_param('i', $id_proposta);
    $stmt->execute();
    $dados_proposta = $stmt->get_result()->fetch_assoc();
    if (!$dados_proposta) throw new Exception("Proposta não encontrada.");

    $cliente_info = $conn->query("SELECT * FROM Clientes WHERE id_cliente = " . intval($dados_proposta['id_cliente']))->fetch_assoc();
    $empresa_info = $conn->query("SELECT * FROM DadosEmpresa LIMIT 1")->fetch_assoc();
    $servico_info = $conn->query("SELECT nome FROM Tipo_Servicos WHERE id_servico = " . intval($dados_proposta['id_servico']))->fetch_assoc();

    // 2. Template - LISTA OFICIAL ATUALIZADA
    // ID do Banco => Nome EXATO do arquivo no servidor
    $mapaModelos = [
        '11' => 'ModeloPropostaUsucapiao.docx',
        '12' => 'ModeloPropostaPlanimetrico.docx',
        '13' => 'ModeloPropostaPlaniaItimetrico.docx',       // Conforme sua lista
        '14' => 'ModeloPropostaObraTerraplenagem.docx',      // Corrigido Terraplenagem
        '15' => 'ModeloPropostaObralndustrial.docx',         // Conforme sua lista
        '16' => 'ModeloPropostaObraCivil.docx',
        '17' => 'ModeloPropostaLocacaodeObra.docx',
        '18' => 'ModeloPropostaLocacaodeTerraplenagem.docx', // Corrigido Locacaode + Terraplenagem
        '19' => 'ModeloPropostaDrone.docx',
        '20' => 'ModeloPropostaDesdobramento..docx',         // Conforme sua lista (..docx)
        '21' => 'ModeloPropostaConferencia.docx',
    ];
    
    $nomeModelo = $mapaModelos[$dados_proposta['id_servico']] ?? 'ModeloPropostaPadrao.docx';
    $caminhoModelo = __DIR__ . '/modelos/' . $nomeModelo;

    if (!file_exists($caminhoModelo)) throw new Exception("Template não encontrado: $nomeModelo");

    $template = new \PhpOffice\PhpWord\TemplateProcessor($caminhoModelo);

    // 3. Preenchimento Padrão
    $template->setValue('numero_proposta', $dados_proposta['numero_proposta']);
    $template->setValue('DExrenso', dataParaExtenso($dados_proposta['data_criacao']));
    $template->setValue('nome_cliente_salvo', htmlspecialchars($cliente_info['nome_cliente']));
    $template->setValue('email_salvo', htmlspecialchars($cliente_info['email']));
    $template->setValue('telefone_salvo', htmlspecialchars($cliente_info['telefone']));
    $template->setValue('celular_salvo', htmlspecialchars($dados_proposta['celular_salvo'] ?? ''));
    $template->setValue('whatsapp_salvo', htmlspecialchars($dados_proposta['whatsapp_salvo'] ?? ''));
    $template->setValue('endereco_obra', htmlspecialchars($dados_proposta['endereco_obra']));
    $template->setValue('bairro_obra', htmlspecialchars($dados_proposta['bairro_obra']));
    $template->setValue('cidade_obra', htmlspecialchars($dados_proposta['cidade_obra']));
    $template->setValue('estado_obra', htmlspecialchars($dados_proposta['estado_obra']));
    $template->setValue('tipo_levantamento', htmlspecialchars($dados_proposta['tipo_levantamento']));
    $template->setValue('finalidade', htmlspecialchars($dados_proposta['finalidade']));
    $template->setValue('area_obra', htmlspecialchars($dados_proposta['area_obra']));
    $template->setValue('ValorProposta', 'R$ ' . number_format($dados_proposta['valor_final_proposta'], 2, ',', '.'));
    $template->setValue('ValorExtenso', numeroParaExtenso($dados_proposta['valor_final_proposta']));
    $template->setValue('mobilizacao_percentual', number_format($dados_proposta['mobilizacao_percentual'], 0, ',', '.'));
    $template->setValue('mobilizacao_valor', 'R$ ' . number_format($dados_proposta['mobilizacao_valor'], 2, ',', '.'));
    $template->setValue('restante_percentual', number_format($dados_proposta['restante_percentual'], 0, ',', '.'));
    $template->setValue('restante_valor', 'R$ ' . number_format($dados_proposta['restante_valor'], 2, ',', '.'));

    // 4. Preenchimento Automático da Empresa (Rodapé e outros)
    if ($empresa_info) {
        foreach ($empresa_info as $coluna => $valor) {
            $template->setValue($coluna, htmlspecialchars($valor ?? ''));
        }
    }

    // 5. Equipamentos (Marcas/Modelos)
    $equipamentos = [ 'Veiculo' => 'Não', 'Estacao_Total' => 'Não', 'GPS' => 'Não', 'Drone' => 'Não' ];
    
    $res_loc = $conn->query("SELECT tl.nome as tipo, m.nome_marca FROM Proposta_Locacao pl JOIN Tipo_Locacao tl ON pl.id_locacao = tl.id_locacao LEFT JOIN Marcas m ON pl.id_marca = m.id_marca WHERE pl.id_proposta = $id_proposta");
    
    while ($row = $res_loc->fetch_assoc()) {
        $t = $row['tipo']; $m = $row['nome_marca'] ?? ''; 
        $valor_exibir = !empty($m) ? $m : 'Sim';
        if (stripos($t, 'Veiculo') !== false) $equipamentos['Veiculo'] = $valor_exibir; 
        if (stripos($t, 'Estação Total') !== false) $equipamentos['Estacao_Total'] = $valor_exibir; 
        if (stripos($t, 'GPS') !== false) $equipamentos['GPS'] = $valor_exibir; 
        if (stripos($t, 'Drone') !== false) $equipamentos['Drone'] = $valor_exibir; 
    }
    foreach($equipamentos as $k=>$v) { $template->setValue($k, $v); }

    // 6. Nome do Arquivo
    $empresaNomeCompleto = $empresa_info['Empresa'] ?? 'PROP';
    $primEmpresa = pegarPrimeiraPalavra($empresaNomeCompleto);
    $primEmpresaLimpo = sanitizarNomeArquivo($primEmpresa);
    if(strlen($primEmpresaLimpo) < 2) $primEmpresaLimpo = 'PROP';

    $numeroCompleto = $dados_proposta['numero_proposta'];
    $partesNumero = explode('-', $numeroCompleto);
    if(count($partesNumero) >= 3) { $ano = $partesNumero[1]; $num = $partesNumero[2]; } 
    else { $ano = date('Y'); $num = $numeroCompleto; }

    $cliNomeCompleto = $cliente_info['nome_cliente'];
    $primCliente = pegarPrimeiraPalavra($cliNomeCompleto);
    $primClienteLimpo = sanitizarNomeArquivo($primCliente);

    $nomeServico = $servico_info['nome'] ?? 'Servico';
    $nomeServico = str_ireplace("Levantamento ", "", $nomeServico);
    $nomeServicoLimpo = sanitizarNomeArquivo($nomeServico);

    $nomeArquivoFinal = "Proposta_" . $primEmpresaLimpo . "-" . $ano . "-" . $num . "_" . $primClienteLimpo . "_" . $nomeServicoLimpo . ".docx";

    // 7. Download
    header('Content-Description: File Transfer');
    header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
    header('Content-Disposition: attachment; filename="' . $nomeArquivoFinal . '"');
    header('Content-Transfer-Encoding: binary');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: public');
    
    $template->saveAs('php://output');
    exit;

} catch (Exception $e) {
    die("Erro Fatal: " . $e->getMessage());
}
?>