<?php
// Nome do Arquivo: salvar_proposta.php
// Função: Salva proposta usando o NOME DO ARQUIVO definido no Banco de Dados.

ini_set('display_errors', 0); 
error_reporting(E_ALL);
ob_start();

session_start();

if (!file_exists(__DIR__ . '/vendor/autoload.php')) {
    ob_end_clean();
    die("ERRO CRÍTICO: Pasta /vendor/ não encontrada.");
}

require_once 'vendor/autoload.php'; 
require_once 'config.php';
require_once 'db.php';
require_once 'CalculadoraOrcamento.php';

if (!isset($_SESSION['usuario_id'])) {
    ob_end_clean();
    header("Location: login.php");
    exit;
}

$id_criador = $_SESSION['usuario_id'];
$is_demo = (isset($_SESSION['ambiente']) && $_SESSION['ambiente'] === 'demo');
$conn = $is_demo ? Database::getDemo() : Database::getProd();
$calc = new CalculadoraOrcamento();

// Pastas
$nomePastaModelo = $is_demo ? 'modelos_demo' : 'modelos_prod';
$pastaBase = __DIR__ . '/' . $nomePastaModelo . '/';
$pastaSaida = __DIR__ . '/propostas_emitidas/';

if (!is_dir($pastaBase)) mkdir($pastaBase, 0755, true);
if (!is_dir($pastaSaida)) mkdir($pastaSaida, 0755, true);

// Funções Auxiliares
function limparStr($string) {
    return preg_replace('/[^a-zA-Z0-9]/', '', $string);
}

function gerarNumero($conn, $nomeEmpresa) {
    $prefixo = strtoupper(limparStr(explode(' ', trim($nomeEmpresa))[0]));
    if (strlen($prefixo) < 2) $prefixo = 'PROP';
    $ano = date('Y');
    
    $stmt = $conn->prepare("SELECT numero_proposta FROM Propostas WHERE numero_proposta LIKE CONCAT(?, '-', ?, '-%') ORDER BY id_proposta DESC LIMIT 1");
    $stmt->bind_param('ss', $prefixo, $ano);
    $stmt->execute();
    $res = $stmt->get_result();
    $prox_seq = 1;
    if ($res && $row = $res->fetch_assoc()) {
        $partes = explode('-', $row['numero_proposta']);
        $ultimo = intval(end($partes));
        $prox_seq = $ultimo + 1;
    }

    do {
        $numero_final = $prefixo . '-' . $ano . '-' . str_pad($prox_seq, 3, '0', STR_PAD_LEFT);
        $check = $conn->query("SELECT id_proposta FROM Propostas WHERE numero_proposta = '$numero_final'");
        if ($check->num_rows > 0) { $prox_seq++; } else { break; }
    } while (true);

    return $numero_final;
}

function numExtenso($valor = 0) {
    $valor = round($valor, 2); 
    if (class_exists('NumberFormatter')) {
        $f = new NumberFormatter("pt-BR", NumberFormatter::SPELLOUT);
        return $f->format($valor) . " reais";
    }
    return number_format($valor, 2, ',', '.') . " (valor extenso)";
}

function dataExtenso($data) {
    setlocale(LC_TIME, 'pt_BR', 'pt_BR.utf-8', 'portuguese');
    $meses = [1=>'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'];
    $timestamp = strtotime($data);
    return date('d', $timestamp) . " de " . $meses[(int)date('m', $timestamp)] . " de " . date('Y', $timestamp);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $conn->begin_transaction();

    try {
        // Verificação de Integridade do POST
        if (!isset($_POST['form_complete'])) {
            throw new Exception("Erro de transmissão: O formulário não foi recebido completamente. Tente novamente.");
        }
        if ($is_demo) {
            $hoje = date('Y-m-d');
            $stmtL = $conn->prepare("SELECT COUNT(*) as qtd FROM Propostas WHERE id_criador = ? AND DATE(data_criacao) = ?");
            $stmtL->bind_param('is', $id_criador, $hoje);
            $stmtL->execute();
            if ($stmtL->get_result()->fetch_assoc()['qtd'] >= 10) throw new Exception("Limite diário DEMO atingido.");
        }

        $id_cliente = intval($_POST['id_cliente'] ?? 0);
        $id_servico = intval($_POST['id_servico'] ?? 0);
        
        $cliente_info = $conn->query("SELECT * FROM Clientes WHERE id_cliente = $id_cliente")->fetch_assoc();
        $emp = $conn->query("SELECT * FROM DadosEmpresa WHERE id_criador = $id_criador")->fetch_assoc();
        
        // --- AQUI ESTÁ A MUDANÇA: PEGA O NOME DO ARQUIVO DO BANCO ---
        $serv_info = $conn->query("SELECT nome, arquivo_modelo FROM Tipo_Servicos WHERE id_servico = $id_servico")->fetch_assoc();
        
        // Se tiver arquivo configurado no banco, usa ele. Se não, usa o Padrão.
        $arquivoModeloNome = !empty($serv_info['arquivo_modelo']) ? $serv_info['arquivo_modelo'] : 'ModeloPropostaPadrao.docx';

        if (!$cliente_info || !$emp) throw new Exception("Dados incompletos.");

        // Cálculos
        $total_salarios = 0; $itens_salario = [];
        if (!empty($_POST['salario_id_funcao'])) {
            foreach ($_POST['salario_id_funcao'] as $k => $id) {
                if (!$id) continue;
                $qtd=floatval($_POST['salario_qtd'][$k]); $base=floatval($_POST['salario_valor'][$k]); $enc=floatval($_POST['encargos'][$k]); $dias=floatval($_POST['salario_dias'][$k]);
                $total_salarios += $calc->calcularSalarios($qtd, $base, $enc, $dias);
                $itens_salario[] = ['id'=>$id, 'nome'=>$_POST['salario_nome'][$k], 'qtd'=>$qtd, 'base'=>$base, 'enc'=>$enc, 'dias'=>$dias];
            }
        }
        $total_estadia = 0; $itens_estadia = [];
        if (!empty($_POST['estadia_id'])) {
            foreach ($_POST['estadia_id'] as $k => $id) {
                if (!$id) continue;
                $qtd=floatval($_POST['estadia_qtd'][$k]); $val=floatval($_POST['estadia_valor'][$k]); $dias=floatval($_POST['estadia_dias'][$k]);
                $total_estadia += $calc->calcularEstadia($qtd, $val, $dias);
                $itens_estadia[] = ['id'=>$id, 'nome'=>$_POST['estadia_nome'][$k], 'qtd'=>$qtd, 'val'=>$val, 'dias'=>$dias];
            }
        }
        $total_consumos = 0; $itens_consumo = [];
        if (!empty($_POST['consumo_id'])) {
            foreach ($_POST['consumo_id'] as $k => $id) {
                if (!$id) continue;
                $qtd=floatval($_POST['consumo_qtd'][$k]); $kml=floatval($_POST['consumo_kml'][$k]); $lit=floatval($_POST['consumo_litro'][$k]); $kmt=floatval($_POST['consumo_km_total'][$k]);
                $total_consumos += $calc->calcularConsumos($qtd, $kml, $lit, $kmt);
                $itens_consumo[] = ['id'=>$id, 'nome'=>$_POST['consumo_nome'][$k], 'qtd'=>$qtd, 'kml'=>$kml, 'lit'=>$lit, 'kmt'=>$kmt];
            }
        }
        $total_locacao = 0; $itens_locacao = [];
        $equip_veiculo='Não'; $equip_estacao='Não'; $equip_gps='Não'; $equip_drone='Não';
        if (!empty($_POST['locacao_id'])) {
            foreach ($_POST['locacao_id'] as $k => $id) {
                if (!$id) continue;
                $qtd=floatval($_POST['locacao_qtd'][$k]); $val=floatval($_POST['locacao_valor'][$k]); $dias=floatval($_POST['locacao_dias'][$k]);
                $id_marca = !empty($_POST['locacao_id_marca'][$k]) ? $_POST['locacao_id_marca'][$k] : null;
                $total_locacao += $calc->calcularLocacao($qtd, $val, $dias);
                $nome_cat = $_POST['locacao_nome'][$k] ?? ''; 
                $nome_marca_texto = "Sim";
                if ($id_marca) {
                    $qm = $conn->query("SELECT nome_marca FROM Marcas WHERE id_marca = ".intval($id_marca));
                    if($qm && $rm = $qm->fetch_assoc()) $nome_marca_texto = $rm['nome_marca'];
                }
                $nm_lower = mb_strtolower($nome_cat);
                if (strpos($nm_lower, 'veículo')!==false || strpos($nm_lower, 'veiculo')!==false) $equip_veiculo = $nome_marca_texto;
                if (strpos($nm_lower, 'estação')!==false || strpos($nm_lower, 'estacao')!==false) $equip_estacao = $nome_marca_texto;
                if (strpos($nm_lower, 'gps')!==false) $equip_gps = $nome_marca_texto;
                if (strpos($nm_lower, 'drone')!==false) $equip_drone = $nome_marca_texto;
                $itens_locacao[] = ['id'=>$id, 'id_marca'=>$id_marca, 'qtd'=>$qtd, 'val'=>$val, 'dias'=>$dias];
            }
        }
        $total_admin = 0; $itens_admin = [];
        if (!empty($_POST['admin_id'])) {
            foreach ($_POST['admin_id'] as $k => $id) {
                if (!$id) continue;
                $qtd=floatval($_POST['admin_qtd'][$k]); $val=floatval($_POST['admin_valor'][$k]);
                $total_admin += $calc->calcularAdmin($qtd, $val);
                $itens_admin[] = ['id'=>$id, 'nome'=>$_POST['admin_nome'][$k], 'qtd'=>$qtd, 'val'=>$val];
            }
        }

        $custoOperacional = $total_salarios + $total_estadia + $total_consumos + $total_locacao + $total_admin;
        $perc_lucro = floatval($_POST['percentual_lucro'] ?? 0);
        $desc = floatval($_POST['valor_desconto'] ?? 0);
        $fechamento = $calc->fecharProposta($custoOperacional, $perc_lucro, $desc);
        
        $final = $fechamento['valor_final'];
        $valor_lucro = $fechamento['valor_lucro'];
        $subtotal = $fechamento['subtotal'];
        $mob_perc = floatval($_POST['mobilizacao_percentual'] ?? 30);
        $mob_val = $final * ($mob_perc / 100);
        $rest_perc = 100 - $mob_perc;
        $rest_val = $final - $mob_val;
        $extenso = numExtenso($final);
        
        $num_proposta = gerarNumero($conn, $emp['Empresa']);
        $status = 'Em elaboração';
        $is_demo_int = $is_demo ? 1 : 0;

        $sql = "INSERT INTO Propostas (
            numero_proposta, id_cliente, id_criador, is_demo,
            nome_cliente_salvo, email_salvo, telefone_salvo, celular_salvo, whatsapp_salvo,
            empresa_proponente_nome, empresa_proponente_cnpj, empresa_proponente_endereco, empresa_proponente_cidade, empresa_proponente_estado,
            empresa_proponente_banco, empresa_proponente_agencia, empresa_proponente_conta, empresa_proponente_pix,
            id_servico, contato_obra, finalidade, tipo_levantamento, area_obra, endereco_obra, bairro_obra, cidade_obra, estado_obra,
            prazo_execucao, dias_campo, dias_escritorio, status,
            total_custos_salarios, total_custos_estadia, total_custos_consumos, total_custos_locacao, total_custos_admin,
            percentual_lucro, valor_lucro, subtotal_com_lucro, valor_desconto, valor_final_proposta, Valor_proposta_extenso,
            mobilizacao_percentual, mobilizacao_valor, restante_percentual, restante_valor
        ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";

        $stmt = $conn->prepare($sql);
        $p_contato=$_POST['contato_obra']; $p_fin=$_POST['finalidade']; $p_tipo=$_POST['tipo_levantamento'];
        $p_area=$_POST['area']; $p_end=$_POST['endereco']; $p_bairro=$_POST['bairro']; 
        $p_cid=$_POST['cidade']; $p_uf=$_POST['estado']; $p_prazo=$_POST['prazo_execucao'];
        $p_dc=intval($_POST['dias_campo']); $p_de=intval($_POST['dias_escritorio']);

        $types = "siiissssssssssssssisssssssssiisddddddddddsdddd";
        $stmt->bind_param($types, $num_proposta, $id_cliente, $id_criador, $is_demo_int, $cliente_info['nome_cliente'], $cliente_info['email'], $cliente_info['telefone'], $cliente_info['celular'], $cliente_info['whatsapp'], $emp['Empresa'], $emp['CNPJ'], $emp['Endereco'], $emp['Cidade'], $emp['Estado'], $emp['Banco'], $emp['Agencia'], $emp['Conta'], $emp['PIX'], $id_servico, $p_contato, $p_fin, $p_tipo, $p_area, $p_end, $p_bairro, $p_cid, $p_uf, $p_prazo, $p_dc, $p_de, $status, $total_salarios, $total_estadia, $total_consumos, $total_locacao, $total_admin, $perc_lucro, $valor_lucro, $subtotal, $desc, $final, $extenso, $mob_perc, $mob_val, $rest_perc, $rest_val);
        $stmt->execute();
        $id_prop = $conn->insert_id;

        // Itens
        $s1 = $conn->prepare("INSERT INTO Proposta_Salarios (id_proposta, id_funcao, funcao, quantidade, salario_base, fator_encargos, dias) VALUES (?,?,?,?,?,?,?)");
        foreach($itens_salario as $i) { $f=1+($i['enc']/100); $s1->bind_param('iisiddi', $id_prop, $i['id'], $i['nome'], $i['qtd'], $i['base'], $f, $i['dias']); $s1->execute(); }
        $s2 = $conn->prepare("INSERT INTO Proposta_Estadia (id_proposta, id_estadia, tipo, quantidade, valor_unitario, dias) VALUES (?,?,?,?,?,?)");
        foreach($itens_estadia as $i) { $s2->bind_param('iisidi', $id_prop, $i['id'], $i['nome'], $i['qtd'], $i['val'], $i['dias']); $s2->execute(); }
        $s3 = $conn->prepare("INSERT INTO Proposta_Consumos (id_proposta, id_consumo, tipo, quantidade, consumo_kml, valor_litro, km_total) VALUES (?,?,?,?,?,?,?)");
        foreach($itens_consumo as $i) { $s3->bind_param('iisiddd', $id_prop, $i['id'], $i['nome'], $i['qtd'], $i['kml'], $i['lit'], $i['kmt']); $s3->execute(); }
        $s4 = $conn->prepare("INSERT INTO Proposta_Locacao (id_proposta, id_locacao, id_marca, quantidade, valor_mensal, dias) VALUES (?,?,?,?,?,?)");
        foreach($itens_locacao as $i) { $s4->bind_param('iiisdi', $id_prop, $i['id'], $i['id_marca'], $i['qtd'], $i['val'], $i['dias']); $s4->execute(); }
        $s5 = $conn->prepare("INSERT INTO Proposta_Custos_Administrativos (id_proposta, id_custo_admin, tipo, quantidade, valor) VALUES (?,?,?,?,?)");
        foreach($itens_admin as $i) { $s5->bind_param('iisid', $id_prop, $i['id'], $i['nome'], $i['qtd'], $i['val']); $s5->execute(); }

        $conn->commit();

        // =========================================================
        // VALIDAÇÃO E GERAÇÃO
        // =========================================================
        if (!file_exists($pastaBase . $arquivoModeloNome)) {
            // Tenta o Padrão
            $arquivoModeloNome = 'ModeloPropostaPadrao.docx';
        }
        
        if (!file_exists($pastaBase . $arquivoModeloNome)) {
             throw new Exception("Modelo não encontrado: " . $arquivoModeloNome);
        }

        $template = new \PhpOffice\PhpWord\TemplateProcessor($pastaBase . $arquivoModeloNome);
        
        // Logo
        // Logo
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

        // Variáveis
        $template->setValue('numero_proposta', $num_proposta);
        $template->setValue('Cidade', $emp['Cidade']);
        $template->setValue('DExrenso', dataExtenso(date('Y-m-d')));
        $template->setValue('nome_cliente_salvo', $cliente_info['nome_cliente']);
        $template->setValue('Empresa', htmlspecialchars($emp['Empresa']));
        $template->setValue('ValorProposta', number_format($final, 2, ',', '.'));
        $template->setValue('ValorExtenso', $extenso);
        $template->setValue('CNPJ', $emp['CNPJ']); $template->setValue('empresa_proponente_nome', $emp['Empresa']);
        $template->setValue('empresa_proponente_cidade', $emp['Cidade']); $template->setValue('whatsapp', $emp['Whatsapp']);
        $template->setValue('Veiculo', $equip_veiculo); $template->setValue('Estacao_Total', $equip_estacao);
        $template->setValue('GPS', $equip_gps); $template->setValue('Drone', $equip_drone);
        $template->setValue('mobilizacao_percentual', number_format($mob_perc, 2, ',','.')); 
        $template->setValue('mobilizacao_valor', number_format($mob_val, 2, ',','.'));
        $template->setValue('restante_percentual', number_format($rest_perc, 2, ',','.')); 
        $template->setValue('restante_valor', number_format($rest_val, 2, ',','.'));
        $template->setValue('Banco', $emp['Banco']); $template->setValue('Agencia', $emp['Agencia']);
        $template->setValue('Conta', $emp['Conta']); $template->setValue('PIX', $emp['PIX']);
        $template->setValue('endereco_obra', $p_end); $template->setValue('bairro_obra', $p_bairro);
        $template->setValue('cidade_obra', $p_cid); $template->setValue('estado_obra', $p_uf);
        $template->setValue('area_obra', $p_area); $template->setValue('tipo_levantamento', $p_tipo);
        $template->setValue('finalidade', $p_fin); $template->setValue('prazo_execucao', $p_prazo);
        $template->setValue('email_salvo', $cliente_info['email']); $template->setValue('telefone_salvo', $cliente_info['telefone']);
        $template->setValue('celular_salvo', $cliente_info['celular']); $template->setValue('whatsapp_salvo', $cliente_info['whatsapp']);

        $nomeEmpresaLimpo = limparStr(explode(' ', trim($emp['Empresa']))[0]);
        $nomeClienteLimpo = limparStr(explode(' ', trim($cliente_info['nome_cliente']))[0]);
        $nomeServicoLimpo = limparStr($serv_info['nome']);
        
        $nomeArquivoDownload = $nomeEmpresaLimpo . '-' . $nomeClienteLimpo . '-' . $nomeServicoLimpo . '-' . $num_proposta . '.docx';

        $template->saveAs($pastaSaida . $nomeArquivoDownload);
        
        ob_end_clean();
        header("Location: proposta_sucesso.php?arquivo=" . urlencode($nomeArquivoDownload) . "&id=" . $id_prop);
        exit;

    } catch (Exception $e) {
        $conn->rollback();
        ob_end_clean();
        die("<div style='font-family:sans-serif; text-align:center; margin-top:50px;'><h1 style='color:red'>Erro ao Salvar</h1><p>" . $e->getMessage() . "</p><a href='criar_proposta.php'>Voltar</a></div>");
    }
}
?>