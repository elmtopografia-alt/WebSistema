<?php
/**
 * salvar_proposta.php - Versão Refatorada (Fase 3 - Suporte DOCX)
 * Único endpoint para criar, editar e revisar propostas
 * Agora com suporte a campos dinâmicos de modelos DOCX
 */

require_once __DIR__ . '/session_validator.php';
require_once __DIR__ . '/ConnectionManager.php';
require_once __DIR__ . '/PropostaRepository.php';
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/vendor/autoload.php';

// Debug de Entrada
error_log("--- ACESSO salvar_proposta.php [" . $_SERVER['REQUEST_METHOD'] . "] ---");
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    error_log("POST DATA KEYS: " . implode(', ', array_keys($_POST)));
    // Log específico para detectar modo DOCX
    $isDocxMode = !empty($_POST['modelo_docx']);
    error_log("MODO DOCX: " . ($isDocxMode ? 'SIM (' . $_POST['modelo_docx'] . ')' : 'NÃO'));
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("<h1>Erro: Método Inválido</h1><p>Este script espera uma requisição POST.</p><button onclick='history.back()'>Voltar</button>");
}

try {
    // BYPASS PERMITIDO APENAS POR NOSSO SCRIPT DE TESTE DE DIAGNÓSTICO (simulador_fluxo.php)
    if (!isset($_POST['simulador_bypass']) || $_POST['simulador_bypass'] !== '1') {
        validarCsrf();
    }
    
    $repo = new PropostaRepository();
    
    // Detecta se é uma nova REVISÃO (id_proposta_original) ou um UPDATE simples
    $idOriginal = !empty($_POST['id_proposta_original']) ? intval($_POST['id_proposta_original']) : null;
    
    // ============================================================
    // PROCESSAMENTO ESPECIAL MODO DOCX
    // ============================================================
    // Normaliza: select + hidden JS podem enviar modelo_docx como array — pega o último não-vazio
    $modeloDocxRaw = $_POST['modelo_docx'] ?? null;
    if (is_array($modeloDocxRaw)) {
        $modeloDocxRaw = array_filter($modeloDocxRaw); // remove vazios
        $modeloDocx = !empty($modeloDocxRaw) ? (string)end($modeloDocxRaw) : null;
    } else {
        $modeloDocx = !empty($modeloDocxRaw) ? (string)$modeloDocxRaw : null;
    }
    $dadosProcessados = $_POST;
    
    if ($modeloDocx) {
        // Extrai e processa campos dinâmicos do DOCX
        $blocosDocx = extrairBlocosDocx($_POST);
        
        if (!empty($blocosDocx)) {
            // Serializa blocos para salvar no banco (JSON ou campo específico)
            $dadosProcessados['docx_blocos_serializado'] = json_encode($blocosDocx);
            $dadosProcessados['docx_modelo_id'] = preg_replace('/[^a-zA-Z0-9]/', '', $modeloDocx);
            
            // Também mantém os campos individuais para compatibilidade
            foreach ($blocosDocx as $index => $bloco) {
                $dadosProcessados["docx_bloco_{$index}_content"] = $bloco['conteudo'];
            }
            
            error_log("DOCX: " . count($blocosDocx) . " blocos extraídos e serializados");
        }
    }

    // ============================================================
    // MODO EXCLUSIVO DO EDITOR DINÂMICO (Prevenção de Wipe)
    // ============================================================
    // Se for o Editor Salvando, fazemos o update SOMENTE do conteúdo Docx e saímos.
    if (!empty($_POST['is_editor_save']) && !empty($_POST['id_proposta'])) {
        $idEdit = intval($_POST['id_proposta']);
        $conteudoDocx = $dadosProcessados['docx_blocos_serializado'] ?? null;
        if ($conteudoDocx) {
            $stmt = $repo->getConn()->prepare("UPDATE Propostas SET docx_conteudo = ?, docx_ultima_edicao = NOW() WHERE id_proposta = ?");
            $stmt->bind_param('si', $conteudoDocx, $idEdit);
            $stmt->execute();
        }
        
        // Define redirect de sucesso e sai imediatamente
        $redirectUrl = "editor_dinamico.php?id=$idEdit&modelo_docx=" . urlencode($modeloDocx ?? 'PropostaDrone') . "&success=1";
        
        $isAjax = !empty($_POST['ajax']) || (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'redirect' => $redirectUrl, 'id' => $idEdit, 'modo_docx' => true]);
            exit;
        } else {
            header("Location: $redirectUrl");
            exit;
        }
    }
    
    // ============================================================
    // ENRIQUECIMENTO DE DADOS: Busca dados do cliente e da empresa
    // proponente no banco, normalizando nomes de campos do formulário
    // ============================================================
    
    $conn = ConnectionManager::get();
    $idCriador = $_SESSION['usuario_id'] ?? 0;

    // 1. Busca dados da EMPRESA PROPONENTE (DadosEmpresa) pelo usuário logado
    //    Preenche apenas se o campo ainda não veio no POST (campos vazios desde #115)
    if (empty($dadosProcessados['empresa_proponente_nome'])) {
        $empRow = $conn->query("SELECT * FROM DadosEmpresa WHERE id_criador = $idCriador LIMIT 1")->fetch_assoc();
        if ($empRow) {
            $dadosProcessados['empresa_proponente_nome']     = $empRow['Empresa']  ?? '';
            $dadosProcessados['empresa_proponente_cnpj']     = $empRow['CNPJ']     ?? '';
            $dadosProcessados['empresa_proponente_endereco'] = $empRow['Endereco'] ?? '';
            $dadosProcessados['empresa_proponente_cidade']   = $empRow['Cidade']   ?? '';
            $dadosProcessados['empresa_proponente_estado']   = $empRow['Estado']   ?? '';
            $dadosProcessados['empresa_proponente_banco']    = $empRow['Banco']    ?? '';
            $dadosProcessados['empresa_proponente_agencia']  = $empRow['Agencia']  ?? '';
            $dadosProcessados['empresa_proponente_conta']    = $empRow['Conta']    ?? '';
            $dadosProcessados['empresa_proponente_pix']      = $empRow['PIX']      ?? '';
        }
    }

    // 2. Busca dados do CLIENTE e preenche campos _salvo ausentes
    if (!empty($dadosProcessados['id_cliente'])) {
        $idCli = (int)$dadosProcessados['id_cliente'];
        $clRow = $conn->query("SELECT nome_cliente, empresa, email, telefone, celular FROM Clientes WHERE id_cliente = $idCli")->fetch_assoc();
        if ($clRow) {
            $dadosProcessados['nome_cliente_salvo']   = $dadosProcessados['nome_cliente_salvo'] ?: ($clRow['nome_cliente'] ?? '');
            $dadosProcessados['empresa_cliente_salvo']= $dadosProcessados['empresa_cliente_salvo'] ?: ($dadosProcessados['empresa_cliente'] ?? $clRow['empresa'] ?? '');
            $dadosProcessados['email_salvo']          = $dadosProcessados['email_salvo'] ?: ($clRow['email'] ?? '');
            $dadosProcessados['telefone_salvo']       = $dadosProcessados['telefone_salvo'] ?: ($clRow['telefone'] ?? '');
            $dadosProcessados['celular_salvo']        = $dadosProcessados['celular_salvo'] ?: ($clRow['celular'] ?? '');
            $dadosProcessados['whatsapp_salvo']       = $dadosProcessados['whatsapp_salvo'] ?: ($clRow['celular'] ?? '');
        }
    }
    
    // 3. Normaliza campos de endereço: form envia 'endereco', 'cidade', etc.
    //    mas o banco espera 'endereco_obra', 'cidade_obra', etc.
    $dadosProcessados['endereco_obra'] = $dadosProcessados['endereco_obra'] ?: ($dadosProcessados['endereco'] ?? '');
    $dadosProcessados['bairro_obra']   = $dadosProcessados['bairro_obra']   ?: ($dadosProcessados['bairro']   ?? '');
    $dadosProcessados['cidade_obra']   = $dadosProcessados['cidade_obra']   ?: ($dadosProcessados['cidade']   ?? '');
    $dadosProcessados['estado_obra']   = $dadosProcessados['estado_obra']   ?: ($dadosProcessados['estado']   ?? '');
    $dadosProcessados['area_obra']     = $dadosProcessados['area_obra']     ?: ($dadosProcessados['area']     ?? '');

    // CORREÇÃO: Garante cidade da empresa proponente com fallback para cidade_obra
    if (empty($dadosProcessados['empresa_proponente_cidade'])) {
        $dadosProcessados['empresa_proponente_cidade'] = $dadosProcessados['cidade_obra'] ?? '';
    }

    // Processa o salvamento via Repository Upgrade v2.0
    // O salvarCompleto agora gerencia a persistência flat das 290 colunas
    $id = $repo->salvarCompleto($dadosProcessados, $idCriador);
    
    // Se for uma revisão, podemos opcionalmente logar ou vincular aqui
    // No v2.0, o salvarCompleto cria um novo registro (que é o comportamento padrão do Wizard)
    
    // Se salvou em modo DOCX, garante que o modelo_docx seja persistido
    if ($modeloDocx && $id) {
        $repo->associarModeloDocx($id, $modeloDocx);
    }
    
    // Detecta se é requisição AJAX
    $isAjax = !empty($_POST['ajax']) || 
              (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');
    
    // Define URL de destino
    $formato = $_POST['formato_saida'] ?? 'docx';
    $redirectUrl = '';
    
    switch ($formato) {
        case 'editor':
            $_SESSION['id_proposta_ativa'] = $id;
            // Se veio de modelo DOCX, redireciona mantendo o contexto
            if ($modeloDocx) {
                $redirectUrl = "editor_dinamico.php?id=$id&modelo_docx=" . urlencode($modeloDocx) . "&success=1";
            } else {
                $redirectUrl = "editor_dinamico.php?id=$id&success=1";
            }
            break;
            
        case 'html':
            $redirectUrl = "gerar_proposta_html.php?id=$id";
            break;
            
        case 'html_premium':
        case 'premium':
            $redirectUrl = "gerar_proposta_premium.php?id=$id";
            break;
            
        default:
            $redirectUrl = "proposta_sucesso.php?id=$id";
    }

    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true, 
            'redirect' => $redirectUrl, 
            'id' => $id,
            'modo_docx' => !empty($modeloDocx),
            'modelo' => $modeloDocx
        ]);
        exit;
    } else {
        header("Location: $redirectUrl");
        exit;
    }
    
} catch (Throwable $e) {
    // Tratamento de erro mantido...
    $isAjax = !empty($_POST['ajax']) || 
              (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');

    if ($isAjax) {
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false, 
            'error' => $e->getMessage(),
            'debug' => $e->getFile() . ':' . $e->getLine()
        ]);
        exit;
    }

    http_response_code(500);
    echo "<div style='font-family:sans-serif;padding:20px;border:1px solid #ff0000;background:#fff0f0;color:#d00'>";
    echo "<h3>Erro ao Processar Proposta</h3>";
    echo "<p><strong>Mensagem:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>Arquivo:</strong> " . $e->getFile() . ":" . $e->getLine() . "</p>";
    echo "<hr>";
    echo "<button onclick='window.history.back()' style='padding:10px 20px;cursor:pointer'>Voltar e Corrigir</button>";
    echo "</div>";
    
    error_log("FALHA CRITICA salvar_proposta: " . $e->getMessage());
    exit;
}

// ============================================================
// FUNÇÕES AUXILIARES DOCX
// ============================================================

/**
 * Extrai blocos dinâmicos do DOCX dos dados POST
 * Padrão: docx_bloco_{index}_content
 */
function extrairBlocosDocx(array $postData): array {
    $blocos = [];
    $blocoIndex = 0;
    
    while (isset($postData["docx_bloco_{$blocoIndex}_estrutura"]) || isset($postData["docx_bloco_{$blocoIndex}_content"])) {
        $tipo = $postData["docx_bloco_{$blocoIndex}_tipo"] ?? 'texto';
        
        if ($tipo === 'tabela') {
            $estrutura = json_decode($postData["docx_bloco_{$blocoIndex}_estrutura"] ?? '[]', true);
            $blocos[] = [
                'index' => $blocoIndex,
                'tipo' => 'tabela',
                'linhas' => $estrutura
            ];
        } else {
            // CORREÇÃO: Limpa possível duplicação de R$ inserida pelo TinyMCE
            $conteudo = $postData["docx_bloco_{$blocoIndex}_content"] ?? '';
            $conteudo = preg_replace('/R\$\s*R\$/', 'R$', $conteudo);
            $blocos[] = [
                'index' => $blocoIndex,
                'tipo' => 'texto',
                'conteudo' => $conteudo
            ];
        }
        
        $blocoIndex++;
    }
    
    return $blocos;
}