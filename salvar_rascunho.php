<?php
/**
 * SALVAR_RASCUNHO.PHP — Versão Corrigida e Funcional
 * Banco: demanda | Tabela: Propostas
 * Usa INSERT direto (fallback) — não depende de PropostaRepository
 */

declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/ConnectionManager.php';
require_once __DIR__ . '/session_validator.php';

// Configuração de resposta
header('Content-Type: application/json; charset=utf-8');

try {
    // ═══════════════════════════════════════════════════════════════
    // 1. VALIDAÇÕES BÁSICAS
    // ═══════════════════════════════════════════════════════════════
    
    // Verifica se é AJAX ou POST normal
    $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
              strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    
    // CSRF protection (se existir token)
    if (!empty($_SESSION['csrf_token']) && 
        (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token'])) {
        throw new Exception('Token de segurança inválido');
    }
    
    // Valida usuário logado
    $idCriador = $_SESSION['usuario_id'] ?? 0;
    if (!$idCriador) {
        throw new Exception('Usuário não autenticado');
    }
    
    // Valida cliente mínimo
    $idCliente = intval($_POST['id_cliente'] ?? 0);
    if (!$idCliente) {
        throw new Exception('Cliente não selecionado');
    }
    
    // ═══════════════════════════════════════════════════════════════
    // 2. PREPARA DADOS DO FORMULÁRIO
    // ═══════════════════════════════════════════════════════════════
    
    // Número da proposta: PROP-ANO-SEQUENCIAL
    $anoAtual = date('Y');
    $conn = ConnectionManager::get();
    
    // Busca último número do ano
    $resNumero = $conn->query("SELECT numero_proposta FROM Propostas 
                               WHERE numero_proposta LIKE 'PROP-{$anoAtual}-%' 
                               ORDER BY id_proposta DESC LIMIT 1");
    $ultimoNumero = $resNumero->fetch_assoc()['numero_proposta'] ?? '';
    
    if ($ultimoNumero && preg_match('/PROP-' . $anoAtual . '-(\d+)/', $ultimoNumero, $matches)) {
        $sequencial = intval($matches[1]) + 1;
    } else {
        $sequencial = 1;
    }
    $numeroProposta = 'PROP-' . $anoAtual . '-' . str_pad((string)$sequencial, 3, '0', STR_PAD_LEFT);
    
    // Dados do cliente (para salvar "congelado")
    $nomeCliente = htmlspecialchars($_POST['nome_cliente'] ?? $_POST['nome_cliente_salvo'] ?? 'Cliente não informado');
    $empresaCliente = htmlspecialchars($_POST['empresa_cliente'] ?? $_POST['empresa_cliente_salvo'] ?? '');
    $emailCliente = htmlspecialchars($_POST['email_cliente'] ?? $_POST['email_salvo'] ?? '');
    $telefoneCliente = htmlspecialchars($_POST['telefone_cliente'] ?? $_POST['telefone_salvo'] ?? '');
    $celularCliente = htmlspecialchars($_POST['celular_cliente'] ?? $_POST['celular_salvo'] ?? '');
    $whatsappCliente = htmlspecialchars($_POST['whatsapp_cliente'] ?? $_POST['whatsapp_salvo'] ?? $celularCliente);
    
    // Dados da obra
    $tipoLevantamento = htmlspecialchars($_POST['tipo_levantamento'] ?? 'Levantamento Topográfico');
    $finalidade = htmlspecialchars($_POST['finalidade'] ?? 'Mapeamento para projeto');
    $areaObra = htmlspecialchars($_POST['area_obra'] ?? $_POST['area'] ?? '0');
    $unidadeArea = htmlspecialchars($_POST['unidade_area'] ?? 'm²');
    $enderecoObra = htmlspecialchars($_POST['endereco'] ?? $_POST['endereco_obra'] ?? '');
    $bairroObra = htmlspecialchars($_POST['bairro'] ?? $_POST['bairro_obra'] ?? '');
    $cidadeObra = htmlspecialchars($_POST['cidade'] ?? $_POST['cidade_obra'] ?? '');
    $estadoObra = htmlspecialchars($_POST['estado'] ?? $_POST['estado_obra'] ?? 'SP');
    
    // Prazo
    $diasCampo = intval($_POST['dias_campo'] ?? 0);
    $diasEscritorio = intval($_POST['dias_escritorio'] ?? 0);
    $prazoExecucao = htmlspecialchars($_POST['prazo_execucao'] ?? ($diasCampo + $diasEscritorio) . ' dias úteis');
    
    // Técnico
    $tipoTerreno = htmlspecialchars($_POST['tipo_terreno'] ?? 'Plano');
    $coberturaVegetal = htmlspecialchars($_POST['cobertura_vegetal'] ?? 'Moderada');
    $acessoLocal = htmlspecialchars($_POST['acesso_local'] ?? 'Fácil');
    $restricoesAereas = htmlspecialchars($_POST['restricoes_aereas'] ?? 'Nenhuma');
    
    // Equipamentos
    $modeloDrone = htmlspecialchars($_POST['modelo_drone'] ?? 'Não se aplica');
    $modeloGps = htmlspecialchars($_POST['modelo_gps'] ?? 'Não se aplica');
    $modeloEstacao = htmlspecialchars($_POST['modelo_estacao_total'] ?? 'Não se aplica');
    $modeloVeiculo = htmlspecialchars($_POST['modelo_veiculo'] ?? 'Não se aplica');
    
    // Custos (dos campos hidden do calculos.js)
    $totalSalarios = floatval($_POST['hidden_total_custos_salarios'] ?? $_POST['total_custos_salarios'] ?? 0);
    $totalEstadia = floatval($_POST['hidden_total_custos_estadia'] ?? $_POST['total_custos_estadia'] ?? 0);
    $totalConsumos = floatval($_POST['hidden_total_custos_consumos'] ?? $_POST['total_custos_consumos'] ?? 0);
    $totalLocacao = floatval($_POST['hidden_total_custos_locacao'] ?? $_POST['total_custos_locacao'] ?? 0);
    $totalAdmin = floatval($_POST['hidden_total_custos_admin'] ?? $_POST['total_custos_admin'] ?? 0);
    
    // Valores finais
    $percentualLucro = floatval($_POST['percentual_lucro'] ?? 30);
    $valorLucro = floatval($_POST['hidden_valor_lucro'] ?? $_POST['valor_lucro'] ?? 0);
    $subtotalComLucro = floatval($_POST['hidden_subtotal_com_lucro'] ?? $_POST['subtotal_com_lucro'] ?? 0);
    $valorDesconto = floatval($_POST['valor_desconto'] ?? 0);
    $valorFinal = floatval($_POST['hidden_valor_final_proposta'] ?? $_POST['valor_final_proposta'] ?? 0);
    
    // Pagamento
    $mobilizacaoPercentual = floatval($_POST['mobilizacao_percentual'] ?? 30);
    $mobilizacaoValor = floatval($_POST['hidden_mobilizacao_valor'] ?? $_POST['mobilizacao_valor'] ?? 0);
    $restantePercentual = floatval($_POST['hidden_restante_percentual'] ?? (100 - $mobilizacaoPercentual));
    $restanteValor = floatval($_POST['hidden_restante_valor'] ?? $_POST['restante_valor'] ?? 0);
    
    // DOCX/Editor
    $modeloDocx = htmlspecialchars($_POST['modelo_docx'] ?? 'PropostaDrone');
    $cor = htmlspecialchars($_POST['cor'] ?? 'verde');
    
    // ID do serviço
    $idServico = intval($_POST['id_servico'] ?? $_POST['tipo_servico_id'] ?? 1);
    $tipoServicoId = intval($_POST['tipo_servico_id'] ?? $idServico);
    
    // ═══════════════════════════════════════════════════════════════
    // 3. MONTA ARRAY DE INSERÇÃO (apenas campos que existem na tabela)
    // ═══════════════════════════════════════════════════════════════
    
    $dados = [
        // Identificação
        'numero_proposta' => $numeroProposta,
        'id_criador' => $idCriador,
        'id_cliente' => $idCliente,
        'nome_cliente_salvo' => $nomeCliente,
        'empresa_cliente_salvo' => $empresaCliente,
        'email_salvo' => $emailCliente,
        'telefone_salvo' => $telefoneCliente,
        'celular_salvo' => $celularCliente,
        'whatsapp_salvo' => $whatsappCliente,
        
        // Serviço/Obra
        'id_servico' => $idServico,
        'tipo_servico_id' => $tipoServicoId,
        'tipo_levantamento' => $tipoLevantamento,
        'finalidade' => $finalidade,
        'area_obra' => $areaObra,
        'unidade_area' => $unidadeArea,
        'endereco_obra' => $enderecoObra,
        'bairro_obra' => $bairroObra,
        'cidade_obra' => $cidadeObra,
        'estado_obra' => $estadoObra,
        
        // Prazo
        'prazo_execucao' => $prazoExecucao,
        'dias_campo' => $diasCampo,
        'dias_escritorio' => $diasEscritorio,
        
        // Técnico
        'tipo_terreno' => $tipoTerreno,
        'cobertura_vegetal' => $coberturaVegetal,
        'acesso_local' => $acessoLocal,
        'restricoes_aereas' => $restricoesAereas,
        
        // Equipamentos
        'modelo_drone' => $modeloDrone,
        'modelo_gps' => $modeloGps,
        'modelo_estacao_total' => $modeloEstacao,
        'modelo_veiculo' => $modeloVeiculo,
        
        // Custos
        'total_custos_salarios' => $totalSalarios,
        'total_custos_estadia' => $totalEstadia,
        'total_custos_consumos' => $totalConsumos,
        'total_custos_locacao' => $totalLocacao,
        'total_custos_admin' => $totalAdmin,
        
        // Valores
        'percentual_lucro' => $percentualLucro,
        'valor_lucro' => $valorLucro,
        'subtotal_com_lucro' => $subtotalComLucro,
        'valor_desconto' => $valorDesconto,
        'valor_final_proposta' => $valorFinal,
        
        // Pagamento
        'mobilizacao_percentual' => $mobilizacaoPercentual,
        'mobilizacao_valor' => $mobilizacaoValor,
        'restante_percentual' => $restantePercentual,
        'restante_valor' => $restanteValor,
        
        // DOCX
        'modelo_docx' => $modeloDocx,
        'cor' => $cor,
        
        // Status
        'status' => 'Em Elaboração',
        'fase_crm' => 'ELABORACAO',
        'is_demo' => 0
    ];
    
    // ═══════════════════════════════════════════════════════════════
    // 4. EXECUTA INSERT DIRETO (não usa Repository)
    // ═══════════════════════════════════════════════════════════════
    
    $campos = [];
    $placeholders = [];
    $valores = [];
    $tipos = '';
    
    foreach ($dados as $campo => $valor) {
        $campos[] = "`{$campo}`";
        $placeholders[] = '?';
        $valores[] = $valor;
        
        // Define tipo para bind_param
        if (is_int($valor)) {
            $tipos .= 'i';
        } elseif (is_float($valor)) {
            $tipos .= 'd';
        } else {
            $tipos .= 's';
        }
    }
    
    $sql = "INSERT INTO Propostas (" . implode(', ', $campos) . ", data_criacao, data_atualizacao) 
            VALUES (" . implode(', ', $placeholders) . ", NOW(), NOW())";
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception('Erro ao preparar query: ' . $conn->error);
    }
    
    $stmt->bind_param($tipos, ...$valores);
    $executou = $stmt->execute();
    
    if (!$executou) {
        throw new Exception('Erro ao executar INSERT: ' . $stmt->error);
    }
    
    $novoId = $stmt->insert_id;
    
    if (!$novoId || $novoId <= 0) {
        throw new Exception('Falha ao obter ID da proposta inserida');
    }
    
    // ═══════════════════════════════════════════════════════════════
    // 5. RETORNO DE SUCESSO
    // ═══════════════════════════════════════════════════════════════
    
    $resposta = [
        'success' => true,
        'id_proposta' => $novoId,
        'is_new' => true,
        'numero_proposta' => $numeroProposta,
        'message' => 'Proposta salva com sucesso',
        'redirect_url' => "editor_dinamico.php?id={$novoId}&modelo_docx={$modeloDocx}&cor={$cor}"
    ];
    
    echo json_encode($resposta, JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    // Log do erro (para debug)
    error_log('salvar_rascunho.php ERRO: ' . $e->getMessage());
    
    // Resposta de erro
    $resposta = [
        'success' => false,
        'error' => $e->getMessage(),
        'id_proposta' => null,
        'message' => 'Falha ao salvar: ' . $e->getMessage()
    ];
    
    http_response_code(400);
    echo json_encode($resposta, JSON_UNESCAPED_UNICODE);
}
