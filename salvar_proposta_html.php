<?php
// salvar_proposta_html.php
// Wrapper para salvar a proposta HTML em arquivo físico com versionamento

// ini_set('display_errors', 1);
// error_reporting(E_ALL);

session_start();
require_once 'db.php';

// Verifica sessão
if (!isset($_SESSION['usuario_id'])) {
    die("Acesso negado. Faça login.");
}

// 1. Lógica de Revisão (Copiada/Adaptada de salvar_edicao_proposta.php)
function gerarNumeroRevisao($conn, $id_original, $id_criador) {
    if (!$id_original) return 'NOVO-' . time();
    
    // Busca número original
    $stmt = $conn->prepare("SELECT numero_proposta FROM Propostas WHERE id_proposta = ?");
    $stmt->bind_param('i', $id_original);
    $stmt->execute();
    $res = $stmt->get_result();
    
    if ($res->num_rows === 0) return 'NOVO-' . time();
    $row = $res->fetch_assoc();
    $numero_completo = $row['numero_proposta'];
    
    // Identifica Raiz (PROP-001-Rv02 -> PROP-001)
    $numero_raiz = preg_replace('/-Rv\d+$/', '', $numero_completo);
    
    // Busca maior revisão desta raiz
    $busca = $numero_raiz . "-Rv%";
    $stmtCheck = $conn->prepare("SELECT numero_proposta FROM Propostas WHERE numero_proposta LIKE ? ORDER BY id_proposta DESC LIMIT 1");
    $stmtCheck->bind_param('s', $busca);
    $stmtCheck->execute();
    $resCheck = $stmtCheck->get_result();
    
    $prox_seq = 1;
    if ($rowCheck = $resCheck->fetch_assoc()) {
        if (preg_match('/-Rv(\d+)$/', $rowCheck['numero_proposta'], $matches)) {
            $prox_seq = intval($matches[1]) + 1;
        }
    }
    
    return $numero_raiz . '-Rv' . str_pad($prox_seq, 2, '0', STR_PAD_LEFT);
}


// 2. Prepara Dados para Geração
try {
    // Captura ID original se existir
    $id_proposta_original = isset($_POST['id_proposta_original']) && !empty($_POST['id_proposta_original']) 
                          ? intval($_POST['id_proposta_original']) 
                          : null;

    // Se tiver ID original, gera novo número de revisão
    // Se não tiver (proposta nova), o número virá do campo 'numero_proposta' (no caso de primeira edição vinda do wizard) ou geramos um novo
    // No fluxo atual: Wizard cria Draft -> Salva.
    // Aqui assumimos que estamos "Finalizando" uma edição.
    
    $novo_numero = $_POST['numero_proposta'] ?? 'S/N';
    
    if ($id_proposta_original) {
        $novo_numero = gerarNumeroRevisao($conn, $id_proposta_original, $_SESSION['usuario_id']);
        $_POST['numero_proposta'] = $novo_numero; // Injeta no POST para o gerador usar
    }

    // 3. Captura o HTML Gerado
    ob_start();
    include 'gerar_proposta_html.php';
    $htmlContent = ob_get_clean();

    // ---------------------------------------------------------
    // CORREÇÃO DE CAMINHOS RELATIVOS (Bug do Logo Quebrado)
    // O arquivo é salvo em /propostas_html/, então 'assets/' vira '../assets/'
    // Abrange também 'uploads/' e 'images/' caso seja logo customizada
    // ---------------------------------------------------------
    $htmlContent = str_replace('src="assets/', 'src="../assets/', $htmlContent);
    $htmlContent = str_replace('src="uploads/', 'src="../uploads/', $htmlContent);
    $htmlContent = str_replace('src="images/', 'src="../images/', $htmlContent);
    
    // Versão com aspas simples (caso ocorra)
    $htmlContent = str_replace("src='assets/", "src='../assets/", $htmlContent);
    $htmlContent = str_replace("src='uploads/", "src='../uploads/", $htmlContent);
    $htmlContent = str_replace("src='images/", "src='../images/", $htmlContent);
    
    // Também ajusta links (CSS/Favicon)
    $htmlContent = str_replace('href="assets/', 'href="../assets/', $htmlContent);
    // ---------------------------------------------------------

    // 4. Salva o Arquivo Físico
    $filename = 'proposta_' . preg_replace('/[^a-zA-Z0-9_-]/', '', $novo_numero) . '.html';
    $dirSaida = __DIR__ . '/propostas_html';
    
    // Garante que o diretório existe
    if (!is_dir($dirSaida)) {
        if (!mkdir($dirSaida, 0755, true)) {
             throw new Exception("Falha crítica: Não foi possível criar a pasta $dirSaida em " . __DIR__);
        }
    }

    $path = $dirSaida . '/' . $filename;
    
    if (file_put_contents($path, $htmlContent) === false) {
        $error = error_get_last();
        throw new Exception("Erro ao gravar arquivo no servidor. Caminho tentado: $path. Detalhes: " . ($error['message'] ?? 'Sem detalhes'));
    }
    
    // 5. Salva/Atualiza no Banco de Dados (CRIA REVISÃO REAL)
    if ($id_proposta_original) {
        // A. Duplicar Proposta Pai
        $sqlClone = "INSERT INTO Propostas (
            numero_proposta, id_cliente, nome_cliente_salvo, email_salvo, telefone_salvo, celular_salvo, whatsapp_salvo,
            empresa_proponente_nome, empresa_proponente_cnpj, empresa_proponente_endereco, empresa_proponente_cidade, 
            empresa_proponente_estado, empresa_proponente_banco, empresa_proponente_agencia, empresa_proponente_conta, 
            empresa_proponente_pix, id_servico, contato_obra, finalidade, tipo_levantamento, area_obra, endereco_obra, 
            bairro_obra, cidade_obra, estado_obra, prazo_execucao, dias_campo, dias_escritorio, total_custos_salarios,
            total_custos_estadia, total_custos_consumos, total_custos_locacao, total_custos_admin, percentual_lucro, 
            valor_lucro, subtotal_com_lucro, valor_desconto, valor_final_proposta, Valor_proposta_extenso, 
            mobilizacao_percentual, mobilizacao_valor, restante_percentual, restante_valor, id_criador, is_demo, status
        ) SELECT 
            ?, id_cliente, nome_cliente_salvo, email_salvo, telefone_salvo, celular_salvo, whatsapp_salvo,
            empresa_proponente_nome, empresa_proponente_cnpj, empresa_proponente_endereco, empresa_proponente_cidade, 
            empresa_proponente_estado, empresa_proponente_banco, empresa_proponente_agencia, empresa_proponente_conta, 
            empresa_proponente_pix, id_servico, contato_obra, finalidade, tipo_levantamento, area_obra, endereco_obra, 
            bairro_obra, cidade_obra, estado_obra, prazo_execucao, dias_campo, dias_escritorio, total_custos_salarios,
            total_custos_estadia, total_custos_consumos, total_custos_locacao, total_custos_admin, percentual_lucro, 
            valor_lucro, subtotal_com_lucro, valor_desconto, valor_final_proposta, Valor_proposta_extenso, 
            mobilizacao_percentual, mobilizacao_valor, restante_percentual, restante_valor, id_criador, is_demo, 'Emitida'
          FROM Propostas WHERE id_proposta = ?";
          
        $stmtClone = $conn->prepare($sqlClone);
        $stmtClone->bind_param('si', $novo_numero, $id_proposta_original);
        if (!$stmtClone->execute()) {
             throw new Exception("Erro ao criar registro da revisão no banco: " . $stmtClone->error);
        }
        $novo_id_rel = $conn->insert_id;
        $stmtClone->close();

        // B. Duplicar Itens de Custo (Essencial para edição funcionar no painel)
        
        // 1. Salários
        $conn->query("INSERT INTO Proposta_Salarios (id_proposta, id_funcao, funcao, quantidade, salario_base, fator_encargos, dias) 
                      SELECT $novo_id_rel, id_funcao, funcao, quantidade, salario_base, fator_encargos, dias FROM Proposta_Salarios WHERE id_proposta = $id_proposta_original");
        
        // 2. Estadia
        $conn->query("INSERT INTO Proposta_Estadia (id_proposta, id_estadia, tipo, quantidade, valor_unitario, dias) 
                      SELECT $novo_id_rel, id_estadia, tipo, quantidade, valor_unitario, dias FROM Proposta_Estadia WHERE id_proposta = $id_proposta_original");

        // 3. Consumos
        $conn->query("INSERT INTO Proposta_Consumos (id_proposta, id_consumo, tipo, quantidade, consumo_kml, valor_litro, km_total) 
                      SELECT $novo_id_rel, id_consumo, tipo, quantidade, consumo_kml, valor_litro, km_total FROM Proposta_Consumos WHERE id_proposta = $id_proposta_original");

        // 4. Locação
        $conn->query("INSERT INTO Proposta_Locacao (id_proposta, id_locacao, id_marca, quantidade, valor_mensal, dias) 
                      SELECT $novo_id_rel, id_locacao, id_marca, quantidade, valor_mensal, dias FROM Proposta_Locacao WHERE id_proposta = $id_proposta_original");

        // 5. Custos Administrativos
        $conn->query("INSERT INTO Proposta_Custos_Administrativos (id_proposta, id_custo_admin, tipo, quantidade, valor) 
                      SELECT $novo_id_rel, id_custo_admin, tipo, quantidade, valor FROM Proposta_Custos_Administrativos WHERE id_proposta = $id_proposta_original");
        
        // C. Duplicar Conteúdo Personalizado (Rascunho)
        $sqlDraft = "INSERT INTO Proposta_Conteudo_Personalizado (id_proposta, block_id, conteudo_texto)
                     SELECT ?, block_id, conteudo_texto FROM Proposta_Conteudo_Personalizado WHERE id_proposta = ?";
        $stmtDraft = $conn->prepare($sqlDraft);
        $stmtDraft->bind_param('ii', $novo_id_rel, $id_proposta_original);
        $stmtDraft->execute();
        $stmtDraft->close();
    }

    // Redireciona para visualizar o arquivo salvo criado
    header("Location: propostas_html/" . $filename);
    exit;

} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}
?>
