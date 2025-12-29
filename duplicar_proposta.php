<?php
// Nome do Arquivo: duplicar_proposta.php
// Função: Clona uma proposta e seus itens, gerando novo número sequencial.

session_start();
require_once 'config.php';
require_once 'db.php';

if (!isset($_SESSION['usuario_id']) || !isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$id_usuario = $_SESSION['usuario_id'];
$is_demo = ($_SESSION['ambiente'] === 'demo');
$conn = $is_demo ? Database::getDemo() : Database::getProd();
$id_origem = intval($_GET['id']);

// 1. Busca Proposta Original
$sql = "SELECT * FROM Propostas WHERE id_proposta = ? AND id_criador = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('ii', $id_origem, $id_usuario);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows === 0) die("Erro ao buscar proposta original.");
$origem = $res->fetch_assoc();

// 2. Gera Novo Número (Função local)
function gerarNovoNumero($conn, $nomeEmpresa) {
    $primeiroNome = explode(' ', trim($nomeEmpresa))[0];
    $s = trim($primeiroNome);
    if (function_exists('iconv')) $s = iconv('UTF-8', 'ASCII//TRANSLIT', $s);
    $prefixo = strtoupper(preg_replace('/[^a-zA-Z0-9]/', '', $s));
    if (strlen($prefixo) < 2) $prefixo = 'PROP';
    $ano = date('Y');
    
    $stmt = $conn->prepare("SELECT numero_proposta FROM Propostas WHERE numero_proposta LIKE CONCAT(?, '-', ?, '-%') ORDER BY id_proposta DESC LIMIT 1");
    $stmt->bind_param('ss', $prefixo, $ano);
    $stmt->execute();
    $res = $stmt->get_result();
    $num = 0;
    if ($res && $row = $res->fetch_assoc()) {
        $partes = explode('-', $row['numero_proposta']);
        $num = intval(end($partes));
    }
    return $prefixo . '-' . $ano . '-' . str_pad($num + 1, 3, '0', STR_PAD_LEFT);
}

$novo_numero = gerarNovoNumero($conn, $origem['empresa_proponente_nome']);

// 3. Insere Nova Proposta (Cópia)
// Removemos id_proposta (auto_inc) e atualizamos numero, data e status
$conn->begin_transaction();
try {
    $sqlInsert = "INSERT INTO Propostas (
        numero_proposta, id_cliente, nome_cliente_salvo, email_salvo, telefone_salvo, celular_salvo, whatsapp_salvo,
        empresa_proponente_nome, empresa_proponente_cnpj, empresa_proponente_endereco, empresa_proponente_cidade, empresa_proponente_estado,
        empresa_proponente_banco, empresa_proponente_agencia, empresa_proponente_conta, empresa_proponente_pix,
        id_servico, contato_obra, finalidade, tipo_levantamento, area_obra, endereco_obra, bairro_obra, cidade_obra, estado_obra,
        prazo_execucao, dias_campo, dias_escritorio, status,
        total_custos_salarios, total_custos_estadia, total_custos_consumos, total_custos_locacao, total_custos_admin,
        percentual_lucro, valor_lucro, subtotal_com_lucro, valor_desconto, valor_final_proposta, Valor_proposta_extenso,
        mobilizacao_percentual, mobilizacao_valor, restante_percentual, restante_valor,
        id_criador, is_demo, data_criacao
    ) SELECT 
        ?, id_cliente, nome_cliente_salvo, email_salvo, telefone_salvo, celular_salvo, whatsapp_salvo,
        empresa_proponente_nome, empresa_proponente_cnpj, empresa_proponente_endereco, empresa_proponente_cidade, empresa_proponente_estado,
        empresa_proponente_banco, empresa_proponente_agencia, empresa_proponente_conta, empresa_proponente_pix,
        id_servico, contato_obra, finalidade, tipo_levantamento, area_obra, endereco_obra, bairro_obra, cidade_obra, estado_obra,
        prazo_execucao, dias_campo, dias_escritorio, 'Em elaboração',
        total_custos_salarios, total_custos_estadia, total_custos_consumos, total_custos_locacao, total_custos_admin,
        percentual_lucro, valor_lucro, subtotal_com_lucro, valor_desconto, valor_final_proposta, Valor_proposta_extenso,
        mobilizacao_percentual, mobilizacao_valor, restante_percentual, restante_valor,
        id_criador, is_demo, NOW()
    FROM Propostas WHERE id_proposta = ?";
    
    $stmtIns = $conn->prepare($sqlInsert);
    $stmtIns->bind_param('si', $novo_numero, $id_origem);
    $stmtIns->execute();
    $id_novo = $conn->insert_id;

    // 4. Copia Itens Relacionados
    // Salários
    $conn->query("INSERT INTO Proposta_Salarios (id_proposta, id_funcao, funcao, quantidade, salario_base, fator_encargos, dias) 
                  SELECT $id_novo, id_funcao, funcao, quantidade, salario_base, fator_encargos, dias FROM Proposta_Salarios WHERE id_proposta = $id_origem");
    
    // Estadia
    $conn->query("INSERT INTO Proposta_Estadia (id_proposta, id_estadia, tipo, quantidade, valor_unitario, dias) 
                  SELECT $id_novo, id_estadia, tipo, quantidade, valor_unitario, dias FROM Proposta_Estadia WHERE id_proposta = $id_origem");

    // Consumos
    $conn->query("INSERT INTO Proposta_Consumos (id_proposta, id_consumo, tipo, quantidade, consumo_kml, valor_litro, km_total) 
                  SELECT $id_novo, id_consumo, tipo, quantidade, consumo_kml, valor_litro, km_total FROM Proposta_Consumos WHERE id_proposta = $id_origem");

    // Locação
    $conn->query("INSERT INTO Proposta_Locacao (id_proposta, id_locacao, id_marca, quantidade, valor_mensal, dias) 
                  SELECT $id_novo, id_locacao, id_marca, quantidade, valor_mensal, dias FROM Proposta_Locacao WHERE id_proposta = $id_origem");

    // Admin
    $conn->query("INSERT INTO Proposta_Custos_Administrativos (id_proposta, id_custo_admin, tipo, quantidade, valor) 
                  SELECT $id_novo, id_custo_admin, tipo, quantidade, valor FROM Proposta_Custos_Administrativos WHERE id_proposta = $id_origem");

    $conn->commit();
    header("Location: index.php?msg=duplicado");

} catch (Exception $e) {
    $conn->rollback();
    die("Erro ao duplicar: " . $e->getMessage());
}