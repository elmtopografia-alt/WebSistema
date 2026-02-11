<?php
/**
 * Nome do Arquivo: salvar_proposta.php
 * Função: Processa o salvamento da proposta (INSERT/UPDATE), salva itens relacionados
 * e decide o fluxo de saída (Editor Dinâmico, HTML ou DOCX).
 */

// 1. CONFIGURAÇÕES E ERROS
ini_set('display_errors', '0'); 
error_reporting(E_ALL);
ob_start();

require_once 'session_validator.php';
require_once 'config.php';
require_once 'db.php';
require_once 'CalculadoraOrcamento.php';

if (!isset($_SESSION['usuario_id'])) {
    ob_end_clean();
    header("Location: login.php");
    exit;
}

if (!file_exists(__DIR__ . '/vendor/autoload.php')) {
    ob_end_clean();
    die("ERRO CRÍTICO: Pasta /vendor/ não encontrada.");
}
require_once __DIR__ . '/vendor/autoload.php';

// 2. INICIALIZAÇÃO DE VARIÁVEIS
$id_criador = $_SESSION['usuario_id'];
$is_demo = (isset($_SESSION['ambiente']) && $_SESSION['ambiente'] === 'demo');
$is_demo_int = $is_demo ? 1 : 0;
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
    // Escapa e limpa o prefixo
    $empresaLimpa = trim($nomeEmpresa);
    if (empty($empresaLimpa)) $empresaLimpa = 'SGT';
    
    $prefixo = strtoupper(limparStr(explode(' ', $empresaLimpa)[0]));
    if (strlen($prefixo) < 2) $prefixo = 'PROP';
    $ano = date('Y');
    
    // Busca o maior sequencial para este prefixo/ano
    $stmt = $conn->prepare("SELECT numero_proposta FROM Propostas WHERE numero_proposta LIKE CONCAT(?, '-', ?, '-%') ORDER BY CAST(SUBSTRING_INDEX(numero_proposta, '-', -1) AS UNSIGNED) DESC LIMIT 1");
    $stmt->bind_param('ss', $prefixo, $ano);
    $stmt->execute();
    $res = $stmt->get_result();
    $prox_seq = 1;

    if ($res && $row = $res->fetch_assoc()) {
        $partes = explode('-', $row['numero_proposta']);
        $ultimo = intval(end($partes));
        $prox_seq = $ultimo + 1;
    }

    // Loop de segurança para garantir unicidade real (evitar race conditions)
    $tentativas = 0;
    do {
        $numero_final = $prefixo . '-' . $ano . '-' . str_pad($prox_seq, 3, '0', STR_PAD_LEFT);
        $check = $conn->query("SELECT id_proposta FROM Propostas WHERE numero_proposta = '$numero_final'");
        if ($check && $check->num_rows > 0) { 
            $prox_seq++; 
            $tentativas++;
        } else { 
            break; 
        }
        // Limite de segurança para evitar loop infinito
        if ($tentativas > 100) break; 
    } while (true);

    // Garantia final: nunca retornar vazio
    if (empty($numero_final)) {
        $numero_final = $prefixo . '-' . $ano . '-' . uniqid();
    }

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

// 3. PROCESSAMENTO DO POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // CSRF VACINA
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
        ob_end_clean();
        die("ERRO DE SEGURANÇA: Token inválido (CSRF). Tente recarregar a página.");
    }

    $conn->begin_transaction();

    try {
        if (!isset($_POST['form_complete'])) {
            throw new Exception("Erro de transmissão: O formulário não foi recebido completamente. Tente novamente.");
        }

        // Dados Básicos
        $id_cliente = intval($_POST['id_cliente'] ?? 0);
        $id_servico = intval($_POST['id_servico'] ?? 0);
        $tipo_servico_id = !empty($_POST['tipo_servico_id']) ? intval($_POST['tipo_servico_id']) : null;
        
        $cliente_info = $conn->query("SELECT * FROM Clientes WHERE id_cliente = $id_cliente")->fetch_assoc();
        $emp = $conn->query("SELECT * FROM DadosEmpresa WHERE id_criador = $id_criador")->fetch_assoc();
        $serv_info = $conn->query("SELECT nome, arquivo_modelo FROM Tipo_Servicos WHERE id_servico = $id_servico")->fetch_assoc();

        if (!$cliente_info || !$emp) throw new Exception("Dados de cliente ou empresa não encontrados.");

        // Cálculos e Itens (Simplificado para o processamento)
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
        if (!empty($_POST['locacao_id'])) {
            foreach ($_POST['locacao_id'] as $k => $id) {
                if (!$id) continue;
                $qtd=floatval($_POST['locacao_qtd'][$k]); $val=floatval($_POST['locacao_valor'][$k]); $dias=floatval($_POST['locacao_dias'][$k]);
                $id_marca = !empty($_POST['locacao_id_marca'][$k]) ? intval($_POST['locacao_id_marca'][$k]) : null;
                $total_locacao += $calc->calcularLocacao($qtd, $val, $dias);
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
        $status = $_POST['status'] ?? 'Em elaboração';

        // Escopo e Campos Técnicos
        $p_contato = $_POST['contato_obra'] ?? '';
        $p_fin = $_POST['finalidade'] ?? '';
        $p_tipo = $_POST['tipo_levantamento'] ?? '';
        $p_area = $_POST['area_obra'] ?? $_POST['area'] ?? '';
        $p_unidade = $_POST['unidade_area'] ?? 'm²';
        $p_end = $_POST['endereco_obra'] ?? $_POST['endereco'] ?? '';
        $p_bairro = $_POST['bairro_obra'] ?? $_POST['bairro'] ?? '';
        $p_cid = $_POST['cidade_obra'] ?? $_POST['cidade'] ?? '';
        $p_uf = $_POST['estado_obra'] ?? $_POST['estado'] ?? '';
        $p_prazo = $_POST['prazo_execucao'] ?? '';
        $p_dc = intval($_POST['dias_campo'] ?? 0);
        $p_de = intval($_POST['dias_escritorio'] ?? 0);

        // Identificação (Update ou Insert)
        $id_prop_existente = isset($_POST['id_proposta_criada']) ? intval($_POST['id_proposta_criada']) : 0;
        if (!$id_prop_existente && isset($_POST['id_proposta'])) $id_prop_existente = intval($_POST['id_proposta']);

        if ($id_prop_existente > 0) {
            // Verifica dono para UPDATE
            $checkOwner = $conn->query("SELECT id_proposta, numero_proposta FROM Propostas WHERE id_proposta = $id_prop_existente AND id_criador = $id_criador");
            if ($checkOwner && $rowOwner = $checkOwner->fetch_assoc()) {
                $num_proposta = $rowOwner['numero_proposta'];
                $id_prop = $id_prop_existente;
            } else {
                $id_prop_existente = 0; // Força Insert se não for dono
            }
        }

        if ($id_prop_existente > 0) {
            // ========== UPDATE ==========
            // CORREÇÃO: Array organizado com contagem exata (43 valores)
            $sql = "UPDATE Propostas SET 
                id_cliente=?, nome_cliente_salvo=?, empresa_cliente_salvo=?, email_salvo=?, telefone_salvo=?, celular_salvo=?, whatsapp_salvo=?,
                id_servico=?, tipo_servico_id=?, contato_obra=?, finalidade=?, tipo_levantamento=?, area_obra=?, unidade_area=?, endereco_obra=?, bairro_obra=?, cidade_obra=?, estado_obra=?,
                prazo_execucao=?, dias_campo=?, dias_escritorio=?, status=?,
                total_custos_salarios=?, total_custos_estadia=?, total_custos_consumos=?, total_custos_locacao=?, total_custos_admin=?,
                percentual_lucro=?, valor_lucro=?, subtotal_com_lucro=?, valor_desconto=?, valor_final_proposta=?, Valor_proposta_extenso=?,
                mobilizacao_percentual=?, mobilizacao_valor=?, restante_percentual=?, restante_valor=?,
                tipo_terreno=?, cobertura_vegetal=?, acesso_local=?, restricoes_aereas=?, coordenadas_gps=?, data_atualizacao=NOW()
                WHERE id_proposta=?";
            
            $stmt = $conn->prepare($sql);
            
            $params = [
                $id_cliente,                            // 1
                $cliente_info['nome_cliente'],          // 2
                $_POST['empresa_cliente'] ?? '',        // 3
                $cliente_info['email'],                 // 4
                $cliente_info['telefone'],              // 5
                $cliente_info['celular'],               // 6
                $cliente_info['whatsapp'],              // 7
                $id_servico,                            // 8
                $tipo_servico_id,                       // 9
                $p_contato,                             // 10
                $p_fin,                                 // 11
                $p_tipo,                                // 12
                $p_area,                                // 13
                $p_unidade,                             // 14
                $p_end,                                 // 15
                $p_bairro,                              // 16
                $p_cid,                                 // 17
                $p_uf,                                  // 18
                $p_prazo,                               // 19
                $p_dc,                                  // 20
                $p_de,                                  // 21
                $status,                                // 22
                $total_salarios,                        // 23
                $total_estadia,                         // 24
                $total_consumos,                        // 25
                $total_locacao,                         // 26
                $total_admin,                           // 27
                $perc_lucro,                            // 28
                $valor_lucro,                           // 29
                $subtotal,                              // 30
                $desc,                                  // 31
                $final,                                 // 32
                $extenso,                               // 33
                $mob_perc,                              // 34
                $mob_val,                               // 35
                $rest_perc,                             // 36
                $rest_val,                              // 37
                $_POST['tipo_terreno'] ?? null,         // 38
                $_POST['cobertura_vegetal'] ?? null,    // 39
                $_POST['acesso_local'] ?? null,         // 40
                $_POST['restricoes_aereas'] ?? null,    // 41
                $_POST['coordenadas_gps'] ?? null,      // 42
                $id_prop                                // 43 (WHERE)
            ];
            
            if (!$stmt->execute($params)) throw new Exception("Erro no Update: " . $stmt->error);

            // Limpa itens antigos para reinserir
            $conn->query("DELETE FROM Proposta_Salarios WHERE id_proposta = $id_prop");
            $conn->query("DELETE FROM Proposta_Estadia WHERE id_proposta = $id_prop");
            $conn->query("DELETE FROM Proposta_Consumos WHERE id_proposta = $id_prop");
            $conn->query("DELETE FROM Proposta_Locacao WHERE id_proposta = $id_prop");
            $conn->query("DELETE FROM Proposta_Custos_Administrativos WHERE id_proposta = $id_prop");

        } else {
            // ========== INSERT ==========
            // CORREÇÃO: Contagem exata de colunas vs valores
            $sql = "INSERT INTO Propostas (
                numero_proposta, id_cliente, id_criador, is_demo,
                nome_cliente_salvo, empresa_cliente_salvo, email_salvo, telefone_salvo, celular_salvo, whatsapp_salvo,
                empresa_proponente_nome, empresa_proponente_cnpj, empresa_proponente_endereco, empresa_proponente_cidade, empresa_proponente_estado,
                empresa_proponente_banco, empresa_proponente_agencia, empresa_proponente_conta, empresa_proponente_pix,
                id_servico, tipo_servico_id, contato_obra, finalidade, tipo_levantamento, area_obra, unidade_area, 
                endereco_obra, bairro_obra, cidade_obra, estado_obra,
                prazo_execucao, dias_campo, dias_escritorio, status,
                total_custos_salarios, total_custos_estadia, total_custos_consumos, total_custos_locacao, total_custos_admin,
                percentual_lucro, valor_lucro, subtotal_com_lucro, valor_desconto, valor_final_proposta, Valor_proposta_extenso,
                mobilizacao_percentual, mobilizacao_valor, restante_percentual, restante_valor,
                tipo_terreno, cobertura_vegetal, acesso_local, restricoes_aereas, coordenadas_gps
            ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";

            $stmt = $conn->prepare($sql);
            
            $paramsArr = [
                $num_proposta,                          // 1
                $id_cliente,                            // 2
                $id_criador,                            // 3
                $is_demo_int,                           // 4
                $cliente_info['nome_cliente'],          // 5
                $_POST['empresa_cliente'] ?? '',        // 6
                $cliente_info['email'],                 // 7
                $cliente_info['telefone'],              // 8
                $cliente_info['celular'],               // 9
                $cliente_info['whatsapp'],              // 10
                $emp['Empresa'],                        // 11
                $emp['CNPJ'],                           // 12
                $emp['Endereco'],                       // 13
                $emp['Cidade'],                         // 14
                $emp['Estado'],                         // 15
                $emp['Banco'],                          // 16
                $emp['Agencia'],                        // 17
                $emp['Conta'],                          // 18
                $emp['PIX'],                            // 19
                $id_servico,                            // 20
                $tipo_servico_id,                       // 21
                $p_contato,                             // 22
                $p_fin,                                 // 23
                $p_tipo,                                // 24
                $p_area,                                // 25
                $p_unidade,                             // 26
                $p_end,                                 // 27
                $p_bairro,                              // 28
                $p_cid,                                 // 29
                $p_uf,                                  // 30
                $p_prazo,                               // 31
                $p_dc,                                  // 32
                $p_de,                                  // 33
                $status,                                // 34
                $total_salarios,                        // 35
                $total_estadia,                         // 36
                $total_consumos,                        // 37
                $total_locacao,                         // 38
                $total_admin,                           // 39
                $perc_lucro,                            // 40
                $valor_lucro,                           // 41
                $subtotal,                              // 42
                $desc,                                  // 43
                $final,                                 // 44
                $extenso,                               // 45
                $mob_perc,                              // 46
                $mob_val,                               // 47
                $rest_perc,                             // 48
                $rest_val,                              // 49
                $_POST['tipo_terreno'] ?? null,         // 50
                $_POST['cobertura_vegetal'] ?? null,    // 51
                $_POST['acesso_local'] ?? null,         // 52
                $_POST['restricoes_aereas'] ?? null,    // 53
                $_POST['coordenadas_gps'] ?? null       // 54
            ];

            if (!$stmt->execute($paramsArr)) throw new Exception("Erro no Insert: " . $stmt->error);

            $id_prop = $stmt->insert_id;
            // Fallback ID
            if (!$id_prop || $id_prop <= 0) {
                $rId = $conn->query("SELECT LAST_INSERT_ID() as last_id");
                $id_prop = ($rId && $rowId = $rId->fetch_assoc()) ? (int)$rowId['last_id'] : 0;
            }
        }

        if (!$id_prop || $id_prop <= 0) throw new Exception("Não foi possível gerar um ID válido para a proposta.");

        // Salva Itens (Salários, Estadia, etc)
        $s1 = $conn->prepare("INSERT INTO Proposta_Salarios (id_proposta, id_funcao, funcao, quantidade, salario_base, fator_encargos, dias) VALUES (?,?,?,?,?,?,?)");
        foreach($itens_salario as $i) { 
            $f=1+($i['enc']/100); 
            $s1->execute([$id_prop, $i['id'], $i['nome'], $i['qtd'], $i['base'], $f, $i['dias']]); 
        }
        $s2 = $conn->prepare("INSERT INTO Proposta_Estadia (id_proposta, id_estadia, tipo, quantidade, valor_unitario, dias) VALUES (?,?,?,?,?,?)");
        foreach($itens_estadia as $i) { 
            $s2->execute([$id_prop, $i['id'], $i['nome'], $i['qtd'], $i['val'], $i['dias']]); 
        }
        $s3 = $conn->prepare("INSERT INTO Proposta_Consumos (id_proposta, id_consumo, tipo, quantidade, consumo_kml, valor_litro, km_total) VALUES (?,?,?,?,?,?,?)");
        foreach($itens_consumo as $i) { 
            $s3->execute([$id_prop, $i['id'], $i['nome'], $i['qtd'], $i['kml'], $i['lit'], $i['kmt']]); 
        }
        $s4 = $conn->prepare("INSERT INTO Proposta_Locacao (id_proposta, id_locacao, id_marca, quantidade, valor_mensal, dias) VALUES (?,?,?,?,?,?)");
        foreach($itens_locacao as $i) { 
            $s4->execute([$id_prop, $i['id'], $i['id_marca'], $i['qtd'], $i['val'], $i['dias']]); 
        }
        $s5 = $conn->prepare("INSERT INTO Proposta_Custos_Administrativos (id_proposta, id_custo_admin, tipo, quantidade, valor) VALUES (?,?,?,?,?)");
        foreach($itens_admin as $i) { 
            $s5->execute([$id_prop, $i['id'], $i['nome'], $i['qtd'], $i['val']]); 
        }

        $conn->commit();

        // ============================================================
        // VALIDAÇÃO DE DADOS MÍNIMOS ANTES DO REDIRECIONAMENTO
        // ============================================================

        function validarDadosMinimos($id_cliente, $id_servico, $itens) {
            $erros = [];
            
            if (empty($id_cliente) || $id_cliente == 0) {
                $erros[] = "Cliente não selecionado";
            }
            if (empty($id_servico) || $id_servico == 0) {
                $erros[] = "Tipo de serviço não selecionado";
            }
            
            $temItens = !empty($itens['salarios']) || 
                        !empty($itens['estadia']) || 
                        !empty($itens['consumos']) || 
                        !empty($itens['locacao']);
            
            if (!$temItens) {
                $erros[] = "Nenhum item de custo adicionado (mão de obra, estadia, equipamentos, etc.)";
            }
            
            return $erros;
        }

        $itensParaValidar = [
            'salarios' => $itens_salario,
            'estadia' => $itens_estadia,
            'consumos' => $itens_consumo,
            'locacao' => $itens_locacao
        ];

        // 4. FLUXO DE SAÍDA
        $formatoSaida = $_POST['formato_saida'] ?? 'docx';

        if ($formatoSaida === 'editor') {
            $errosValidacao = validarDadosMinimos($id_cliente, $id_servico, $itensParaValidar);
            
            if (!empty($errosValidacao)) {
                $conn->rollback();
                $errosHtml = implode('</li><li>', $errosValidacao);
                $htmlErro = "
                <!DOCTYPE html>
                <html lang='pt-BR'>
                <head>
                    <meta charset='UTF-8'>
                    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                    <title>Dados Incompletos - SGT Propostas</title>
                    <style>
                        * { margin: 0; padding: 0; box-sizing: border-box; }
                        body {
                            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
                            background: linear-gradient(135deg, #0a0f1a 0%, #1a1f2e 100%);
                            min-height: 100vh;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            padding: 20px;
                        }
                        .container {
                            background: rgba(17, 24, 39, 0.95);
                            border: 1px solid rgba(249, 115, 22, 0.3);
                            border-radius: 20px;
                            padding: 40px;
                            max-width: 600px;
                            width: 100%;
                            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
                        }
                        .icon {
                            width: 80px;
                            height: 80px;
                            background: rgba(249, 115, 22, 0.15);
                            border-radius: 50%;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            margin: 0 auto 24px;
                            border: 2px solid rgba(249, 115, 22, 0.4);
                        }
                        .icon svg {
                            width: 40px;
                            height: 40px;
                            color: #f97316;
                        }
                        h1 {
                            color: #f8fafc;
                            font-size: 24px;
                            text-align: center;
                            margin-bottom: 16px;
                            font-weight: 700;
                        }
                        p.subtitle {
                            color: #94a3b8;
                            text-align: center;
                            margin-bottom: 24px;
                            font-size: 15px;
                            line-height: 1.6;
                        }
                        .erros-box {
                            background: rgba(239, 68, 68, 0.1);
                            border: 1px solid rgba(239, 68, 68, 0.3);
                            border-radius: 12px;
                            padding: 20px;
                            margin-bottom: 24px;
                        }
                        .erros-box h3 {
                            color: #fca5a5;
                            font-size: 13px;
                            text-transform: uppercase;
                            letter-spacing: 0.05em;
                            margin-bottom: 12px;
                            display: flex;
                            align-items: center;
                            gap: 8px;
                        }
                        .erros-box ul {
                            list-style: none;
                            padding-left: 0;
                        }
                        .erros-box li {
                            color: #fecaca;
                            padding: 8px 0;
                            padding-left: 28px;
                            position: relative;
                            font-size: 14px;
                            border-bottom: 1px solid rgba(239, 68, 68, 0.1);
                        }
                        .erros-box li:last-child {
                            border-bottom: none;
                        }
                        .erros-box li::before {
                            content: '✕';
                            position: absolute;
                            left: 0;
                            color: #ef4444;
                            font-weight: bold;
                            width: 20px;
                            height: 20px;
                            background: rgba(239, 68, 68, 0.2);
                            border-radius: 50%;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            font-size: 10px;
                        }
                        .botoes {
                            display: flex;
                            gap: 12px;
                            flex-wrap: wrap;
                        }
                        .btn {
                            flex: 1;
                            min-width: 140px;
                            padding: 14px 24px;
                            border-radius: 12px;
                            font-size: 14px;
                            font-weight: 600;
                            text-decoration: none;
                            text-align: center;
                            transition: all 0.2s;
                            cursor: pointer;
                            border: none;
                        }
                        .btn-primary {
                            background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
                            color: white;
                            box-shadow: 0 4px 14px rgba(249, 115, 22, 0.4);
                        }
                        .btn-primary:hover {
                            transform: translateY(-2px);
                            box-shadow: 0 6px 20px rgba(249, 115, 22, 0.5);
                        }
                        .btn-secondary {
                            background: rgba(255, 255, 255, 0.05);
                            color: #e2e8f0;
                            border: 1px solid rgba(255, 255, 255, 0.1);
                        }
                        .btn-secondary:hover {
                            background: rgba(255, 255, 255, 0.1);
                        }
                        .info-box {
                            background: rgba(59, 130, 246, 0.1);
                            border: 1px solid rgba(59, 130, 246, 0.3);
                            border-radius: 10px;
                            padding: 16px;
                            margin-top: 20px;
                        }
                        .info-box p {
                            color: #93c5fd;
                            font-size: 13px;
                            line-height: 1.6;
                        }
                        .info-box strong {
                            color: #60a5fa;
                        }
                    </style>
                </head>
                <body>
                    <div class='container'>
                        <div class='icon'>
                            <svg fill='none' stroke='currentColor' viewBox='0 0 24 24'>
                                <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' 
                                      d='M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z'/>
                            </svg>
                        </div>
                        <h1>Proposta Incompleta</h1>
                        <p class='subtitle'>
                            Não é possível abrir o Editor Avançado porque faltam informações essenciais. 
                            Complete os dados abaixo para continuar.
                        </p>
                        <div class='erros-box'>
                            <h3>
                                <svg width='16' height='16' fill='currentColor' viewBox='0 0 16 16'>
                                    <path d='M8 0a8 8 0 1 1 0 16A8 8 0 0 1 8 0zM4.5 7.5a.5.5 0 0 0 0 1h5.793l-2.147 2.146a.5.5 0 0 0 .708.708l3-3a.5.5 0 0 0 0-.708l-3-3a.5.5 0 1 0-.708.708L10.293 7.5H4.5z'/>
                                </svg>
                                Campos obrigatórios pendentes:
                            </h3>
                            <ul>
                                <li>{$errosHtml}</li>
                            </ul>
                        </div>
                        <div class='botoes'>
                            <a href='javascript:history.back()' class='btn btn-primary'>
                                ← Voltar e Completar
                            </a>
                            <a href='painel.php' class='btn btn-secondary'>
                                Ir para Painel
                            </a>
                        </div>
                        <div class='info-box'>
                            <p>
                                <strong>Dica:</strong> Preencha pelo menos o <strong>Cliente</strong>, 
                                <strong>Tipo de Serviço</strong> e adicione um <strong>item de custo</strong> 
                                (mão de obra, estadia ou equipamento) antes de acessar o editor.
                            </p>
                        </div>
                    </div>
                </body>
                </html>";
                
                ob_end_clean();
                echo $htmlErro;
                exit;
            }

            $_SESSION['id_proposta_ativa'] = $id_prop;
            while (ob_get_level()) { ob_end_clean(); }
            header("Location: editor_dinamico.php?id=" . $id_prop);
            exit;
        } elseif ($formatoSaida === 'html') {
            // Repassa dados para o gerador HTML
            $_POST['id_proposta_criada'] = $id_prop;
            $_POST['numero_proposta'] = $num_proposta;
            require_once 'gerar_proposta_html.php';
            exit;
        } else {
            // DOCX
            $_POST['id_proposta_criada'] = $id_prop;
            require_once 'gerar_documento.php';
            exit;
        }

    } catch (Exception $e) {
        if (isset($conn)) $conn->rollback();
        while (ob_get_level()) { ob_end_clean(); }
        error_log("ERRO salvar_proposta.php: " . $e->getMessage());
        die("<div style='background:#fee2e2; border:2px solid #ef4444; padding:20px; border-radius:10px; font-family:sans-serif;'>
                <h2 style='color:#b91c1c; margin-top:0;'>Erro ao Salvar</h2>
                <p>Detalhes: <b>" . htmlspecialchars($e->getMessage()) . "</b></p>
                <p><a href='javascript:history.back()' style='color:#b91c1c; font-weight:bold;'>← Voltar para Corrigir</a></p>
             </div>");
    }
}
?>