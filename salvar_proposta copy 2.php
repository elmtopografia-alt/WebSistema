<?php
// ARQUIVO: salvar_proposta.php
// VERSÃO: SINTAXE FINAL (Valores em Array Simples)

ini_set('display_errors', 1);
error_reporting(E_ALL);

if (!file_exists(__DIR__ . '/vendor/autoload.php')) {
    die("<div style='color:red;padding:20px;'><h1>ERRO:</h1><p>Pasta <b>/vendor/</b> não encontrada. Faça o upload do composer/vendor.</p></div>");
}

require_once 'vendor/autoload.php'; 
require_once 'db.php';
require_once 'valida_demo.php'; 

session_start();

if (!isset($_SESSION['usuario_id'])) { die("Sessão expirada."); }

// --- CONFIGURAÇÃO ---
$id_criador = $_SESSION['usuario_id'];
$is_demo = (isset($_SESSION['ambiente']) && $_SESSION['ambiente'] === 'demo');

// 1. SELEÇÃO DA PASTA DE MODELOS (ISOLbAMENTO)
if ($is_demo) {
    $pastaBase = __DIR__ . '/modelos_demo/';
} else {
    $pastaBase = __DIR__ . '/modelos_prod/';
}

if (!is_dir($pastaBase)) { 
    mkdir($pastaBase, 0755, true); 
}

// Mapa de Modelos (Mantido)
$mapaModelos = [
    11 => 'ModeloPropostaUsucapiao.docx', 12 => 'ModeloPropostaPlanimetrico.docx', 13 => 'ModeloPropostaPlanialtimetrico.docx',
    14 => 'ModeloPropostaObraTerraplanagem.docx', 15 => 'ModeloPropostaObraIndustrial.docx', 16 => 'ModeloPropostaObraCivil.docx',
    17 => 'ModeloPropostaLocacaodeObra.docx', 18 => 'ModeloPropostaLocacaoTerraplenagem.docx', 19 => 'ModeloPropostaDrone.docx',
    20 => 'ModeloPropostaDesdobramento.docx', 21 => 'ModeloPropostaConferencia.docx'
];

// --- FUNÇÕES ---
function gerarNumeroProposta($conn) {
    $prefixo = 'ELM'; $ano = date('Y');
    $res = $conn->query("SELECT numero_proposta FROM Propostas WHERE numero_proposta LIKE '$prefixo-$ano-%' ORDER BY id_proposta DESC LIMIT 1");
    $num = 0;
    if ($res && $row = $res->fetch_assoc()) {
        $partes = explode('-', $row['numero_proposta']);
        $num = intval(end($partes));
    }
    return $prefixo . '-' . $ano . '-' . str_pad($num + 1, 3, '0', STR_PAD_LEFT);
}

function numeroParaExtenso($valor = 0) {
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

// --- PROCESSAMENTO DO POST ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $conn->begin_transaction();

    try {
        // 🔒 LIMITE DIÁRIO (DEMO)
        if ($is_demo) {
            $hoje = date('Y-m-d');
            $sqlLimit = "SELECT COUNT(*) as qtd FROM Propostas WHERE id_criador = ? AND DATE(data_criacao) = ?";
            $stmtLimit = $conn->prepare($sqlLimit);
            $stmtLimit->bind_param('is', $id_criador, $hoje);
            $stmtLimit->execute();
            $resLimit = $stmtLimit->get_result()->fetch_assoc();
            
            if ($resLimit['qtd'] >= 10) {
                throw new Exception("🔒 LIMITE DIÁRIO ATINGIDO: No modo demonstração, você pode gerar até 10 propostas por dia.");
            }
        }
        
        $id_cliente = intval($_POST['id_cliente']);
        $id_servico = intval($_POST['id_servico']);
        
        if (!$id_cliente || !$id_servico) { throw new Exception("Cliente ou Serviço não selecionados."); }

        // Buscas de dados
        $cliente_info = $conn->query("SELECT * FROM Clientes WHERE id_cliente = $id_cliente")->fetch_assoc();
        
        $stmt_emp = $conn->prepare("SELECT * FROM DadosEmpresa WHERE id_criador = ?");
        $stmt_emp->bind_param('i', $id_criador);
        $stmt_emp->execute();
        $emp = $stmt_emp->get_result()->fetch_assoc();
        
        if (!$cliente_info) { throw new Exception("❌ Erro: Cliente selecionado não foi encontrado no banco."); }
        if (!$emp) { throw new Exception("❌ Erro: Dados da sua empresa não estão cadastrados. Acesse o menu 'Minha Empresa'."); }

        $num_prop = gerarNumeroProposta($conn);
        $v_final = floatval($_POST['valor_final_proposta'] ?? 0);
        $v_extenso = numeroParaExtenso($v_final);
        $status = 'Em elaboração';

        // Tratamento de nulos/vazios
        $celular = $cliente_info['celular'] ?? ''; $whatsapp = $cliente_info['whatsapp'] ?? '';

        // --- VALORES DO INSERT (Sintaxe Simplificada) ---
        $params_values = [
            $num_prop, $id_cliente, $id_criador, ($is_demo?1:0),
            $cliente_info['nome_cliente'], $cliente_info['email'], $cliente_info['telefone'], $celular, $whatsapp,
            $emp['Empresa'], $emp['CNPJ'], $emp['Endereco'], $emp['Cidade'], $emp['Estado'],
            $emp['Banco'], $emp['Agencia'], $emp['Conta'], $emp['PIX'],
            $id_servico, $_POST['contato_obra'], $_POST['finalidade'], $_POST['tipo_levantamento'],
            $_POST['area'], $_POST['endereco'], $_POST['bairro'], $_POST['cidade'], $_POST['estado'],
            $_POST['prazo_execucao'], $_POST['dias_campo'], $_POST['dias_escritorio'],
            $status,
            $_POST['total_custos_salarios'], $_POST['total_custos_estadia'], $_POST['total_custos_consumos'], 
            $_POST['total_custos_locacao'], $_POST['total_custos_admin'],
            $_POST['percentual_lucro'], $_POST['valor_lucro'], $_POST['subtotal_com_lucro'], 
            $_POST['valor_desconto'], $v_final, $v_extenso,
            $_POST['mobilizacao_percentual'], $_POST['mobilizacao_valor'], $_POST['restante_percentual'], $_POST['restante_valor']
        ];

        // Definição Manual dos Tipos (Corresponde à ordem do array acima)
        $tipos = 'siissssssssssssssssissssssssssdddddddddddddd';

        // Montagem do SQL
        $sql = "INSERT INTO Propostas (
            numero_proposta, id_cliente, id_criador, is_demo,
            nome_cliente_salvo, email_salvo, telefone_salvo, celular_salvo, whatsapp_salvo,
            empresa_proponente_nome, empresa_proponente_cnpj, empresa_proponente_endereco, empresa_proponente_cidade, empresa_proponente_estado,
            empresa_proponente_banco, empresa_proponente_agencia, empresa_proponente_conta, empresa_proponente_pix,
            id_servico, contato_obra, finalidade, tipo_levantamento, 
            area_obra, endereco_obra, bairro_obra, cidade_obra, estado_obra,
            prazo_execucao, dias_campo, dias_escritorio, status,
            total_custos_salarios, total_custos_estadia, total_custos_consumos, total_custos_locacao, total_custos_admin,
            percentual_lucro, valor_lucro, subtotal_com_lucro, valor_desconto, valor_final_proposta, Valor_proposta_extenso,
            mobilizacao_percentual, mobilizacao_valor, restante_percentual, restante_valor
        ) VALUES (" . str_repeat('?,', count($params_values) - 1) . "?)";

        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($tipos, ...$params_values);
        $stmt->execute();
        $id_proposta = $conn->insert_id;

        // --- INSERTS ITENS SECUNDÁRIOS ---
        $equip_veiculo = 'Não'; $equip_estacao = 'Não'; $equip_gps = 'Não'; $equip_drone = 'Não';
        
        if (!empty($_POST['locacao_id'])) {
            $stmt_i = $conn->prepare("INSERT INTO Proposta_Locacao (id_proposta, id_locacao, id_marca, quantidade, valor_mensal, dias) VALUES (?,?,?,?,?,?)");
            foreach ($_POST['locacao_id'] as $k => $v) {
                if (!$v) continue;
                $marca_id = !empty($_POST['locacao_id_marca'][$k]) ? $_POST['locacao_id_marca'][$k] : null;
                $stmt_i->bind_param('iiisdi', $id_proposta, $v, $marca_id, $_POST['locacao_qtd'][$k], $_POST['locacao_valor'][$k], $_POST['locacao_dias'][$k]);
                $stmt_i->execute();
                
                $q_tipo = $conn->query("SELECT nome FROM Tipo_Locacao WHERE id_locacao = " . intval($v))->fetch_assoc();
                if($q_tipo) {
                    $nm = mb_strtolower($q_tipo['nome']);
                    $val_eq = "Sim";
                    if($marca_id) {
                        $qm = $conn->query("SELECT nome_marca FROM Marcas WHERE id_marca = ".intval($marca_id))->fetch_assoc();
                        if($qm) $val_eq = $qm['nome_marca'];
                    }
                    if(strpos($nm, 'veículo')!==false) $equip_veiculo = $val_eq;
                    if(strpos($nm, 'estação')!==false) $equip_estacao = $val_eq;
                    if(strpos($nm, 'gps')!==false) $equip_gps = $val_eq;
                    if(strpos($nm, 'drone')!==false) $equip_drone = $val_eq;
                }
            }
        }
        
        if(!empty($_POST['salario_id_funcao'])) {
            $stmt_i = $conn->prepare("INSERT INTO Proposta_Salarios (id_proposta, id_funcao, funcao, quantidade, salario_base, dias) VALUES (?,?,?,?,?,?)");
            foreach($_POST['salario_id_funcao'] as $k=>$v) { if(!$v) continue; $stmt_i->bind_param('iisidi', $id_proposta, $v, $_POST['salario_nome'][$k], $_POST['salario_qtd'][$k], $_POST['salario_valor'][$k], $_POST['salario_dias'][$k]); $stmt_i->execute(); }
        }
        
        if(!empty($_POST['estadia_id'])) {
            $stmt_i = $conn->prepare("INSERT INTO Proposta_Estadia (id_proposta, id_estadia, tipo, quantidade, valor_unitario, dias) VALUES (?,?,?,?,?,?)");
            foreach($_POST['estadia_id'] as $k=>$v) { if(!$v) continue; $stmt_i->bind_param('iisidi', $id_proposta, $v, $_POST['estadia_nome'][$k], $_POST['estadia_qtd'][$k], $_POST['estadia_valor'][$k], $_POST['estadia_dias'][$k]); $stmt_i->execute(); }
        }
        
        if(!empty($_POST['consumo_id'])) {
            $stmt_i = $conn->prepare("INSERT INTO Proposta_Consumos (id_proposta, id_consumo, tipo, quantidade, consumo_kml, valor_litro, km_total) VALUES (?,?,?,?,?,?,?)");
            foreach($_POST['consumo_id'] as $k=>$v) { if(!$v) continue; $stmt_i->bind_param('iisiddd', $id_proposta, $v, $_POST['consumo_nome'][$k], $_POST['consumo_qtd'][$k], $_POST['consumo_kml'][$k], $_POST['consumo_litro'][$k], $_POST['consumo_km_total'][$k]); $stmt_i->execute(); }
        }
        
        if(!empty($_POST['admin_id'])) {
            $stmt_i = $conn->prepare("INSERT INTO Proposta_Custos_Administrativos (id_proposta, id_custo_admin, tipo, quantidade, valor) VALUES (?,?,?,?,?)");
            foreach($_POST['admin_id'] as $k=>$v) { if(!$v) continue; $stmt_i->bind_param('iisid', $id_proposta, $v, $_POST['admin_nome'][$k], $_POST['admin_qtd'][$k], $_POST['admin_valor'][$k]); $stmt_i->execute(); }
        }

        $conn->commit();

        // --- GERA WORD ---
        $nomeArquivoModelo = $mapaModelos[$id_servico] ?? 'ModeloPropostaPadrao.docx';
        $caminhoCompleto = $pastaBase . $nomeArquivoModelo;
        
        if (!file_exists($caminhoCompleto)) { $caminhoCompleto = $pastaBase . 'ModeloPropostaPadrao.docx'; }
        if (!file_exists($caminhoCompleto)) { throw new Exception("Modelo Word não encontrado em: $caminhoCompleto"); }

        $template = new \PhpOffice\PhpWord\TemplateProcessor($caminhoCompleto);
        
        // Mapeamento
        $template->setValue('numero_proposta', $num_prop);
        $template->setValue('Cidade', $_POST['cidade']);
        $template->setValue('DExrenso', dataParaExtenso(date('Y-m-d')));
        $template->setValue('nome_cliente_salvo', $cliente_info['nome_cliente']);
        $template->setValue('email_salvo', $cliente_info['email']);
        $template->setValue('telefone_salvo', $cliente_info['telefone']);
        $template->setValue('celular_salvo', $celular);
        $template->setValue('whatsapp_salvo', $whatsapp);
        $template->setValue('Empresa', $emp['Empresa']);
        $template->setValue('CNPJ', $emp['CNPJ']);
        $template->setValue('empresa_proponente_nome', $emp['Empresa']);
        $template->setValue('empresa_proponente_cidade', $emp['Cidade']);
        $template->setValue('whatsapp', $emp['Whatsapp']);
        $template->setValue('Veiculo', $equip_veiculo);
        $template->setValue('Estacao_Total', $equip_estacao);
        $template->setValue('GPS', $equip_gps);
        $template->setValue('Drone', $equip_drone);
        $template->setValue('ValorProposta', number_format($v_final, 2, ',', '.'));
        $template->setValue('ValorExtenso', $v_extenso);
        $template->setValue('mobilizacao_percentual', number_format($_POST['mobilizacao_percentual'], 2, ',', '.'));
        $template->setValue('mobilizacao_valor', number_format($_POST['mobilizacao_valor'], 2, ',', '.'));
        $template->setValue('restante_percentual', number_format($_POST['restante_percentual'], 2, ',', '.'));
        $template->setValue('restante_valor', number_format($_POST['restante_valor'], 2, ',', '.'));
        $template->setValue('Banco', $emp['Banco']);
        $template->setValue('Agencia', $emp['Agencia']);
        $template->setValue('Conta', $emp['Conta']);
        $template->setValue('PIX', $emp['PIX']);
        $template->setValue('endereco_obra', $_POST['endereco']);
        $template->setValue('bairro_obra', $_POST['bairro']);
        $template->setValue('cidade_obra', $_POST['cidade']);
        $template->setValue('estado_obra', $_POST['estado']);
        $template->setValue('area_obra', $_POST['area']);
        $template->setValue('tipo_levantamento', $_POST['tipo_levantamento']);
        $template->setValue('finalidade', $_POST['finalidade']);
        $template->setValue('prazo_execucao', $_POST['prazo_execucao']);
        
        // Download
        ob_clean(); 
        $nomeArquivoDownload = 'Proposta_' . $num_prop . '.docx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        header('Content-Disposition: attachment; filename="' . $nomeArquivoDownload . '"');
        header('Cache-Control: max-age=0');
        $template->saveAs('php://output');
        exit;

    } catch (Exception $e) {
        $conn->rollback();
        echo "<div style='font-family:sans-serif; background:#fff0f0; border:1px solid red; padding:20px; color:#c00;'>";
        echo "<h3>🛑 ERRO:</h3>";
        echo "<p>" . $e->getMessage() . "</p>";
        echo "<p>Linha: " . $e->getLine() . "</p>";
        echo "<button onclick='history.back()'>Voltar</button>";
        echo "</div>";
        exit;
    }
}
?>