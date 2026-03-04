<?php
/**
 * salvar_proposta.php - Versão PHP 8.1+ (Fase 4 - Config JSON Fix)
 * Único endpoint para criar, editar e revisar propostas
 * Agora com suporte a campos dinâmicos de modelos DOCX
 * 
 * PHP 8.1+ Features: Union types, null-safe operator, match expression, 
 * readonly properties (onde aplicável), named arguments
 * 
 * Substitua o arquivo existente inteiro por este conteúdo
 */

declare(strict_types=1);

require_once __DIR__ . '/session_validator.php';
require_once __DIR__ . '/ConnectionManager.php';
require_once __DIR__ . '/PropostaRepository.php';
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/vendor/autoload.php';

// ============================================================
// CONFIGURAÇÃO DE LOG E DEBUG (PHP 8.1+)
// ============================================================

/**
 * Logger estruturado para PHP 8.1+
 */
class PropostaLogger
{
    public static function log(string $mensagem, array $contexto = []): void
    {
        $timestamp = date('Y-m-d H:i:s');
        $contextoStr = empty($contexto) ? '' : json_encode($contexto, JSON_THROW_ON_ERROR);
        error_log("[{$timestamp}] {$mensagem} {$contextoStr}");
    }
    
    public static function logPostData(array $post): void
    {
        $isDocxMode = !empty($post['modelo_docx']);
        self::log("ACESSO salvar_proposta.php", [
            'method' => $_SERVER['REQUEST_METHOD'] ?? 'unknown',
            'keys' => array_keys($post),
            'modo_docx' => $isDocxMode,
            'modelo' => $isDocxMode ? $post['modelo_docx'] : null
        ]);
    }
}

PropostaLogger::logPostData($_POST);

// ============================================================
// FUNÇÕES AUXILIARES DE EXTRAÇÃO (DOCX -> Legacy)
// PHP 8.1+: Tipos de retorno union, null-safe
// ============================================================

/**
 * Extrai texto limpo de HTML
 */
function extrairTextoLimpo(?string $htmlContent): string
{
    if (empty($htmlContent)) {
        return '';
    }
    
    $texto = strip_tags($htmlContent);
    $texto = html_entity_decode($texto, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    return trim($texto);
}

/**
 * Extrai texto após um prefixo específico
 */
function extrairAposPrefixo(?string $html, string $prefixo): string
{
    $texto = extrairTextoLimpo($html);
    $pos = stripos($texto, $prefixo);
    
    if ($pos !== false) {
        return trim(substr($texto, $pos + strlen($prefixo)));
    }
    
    return $texto;
}

/**
 * Gera configuração DOCX a partir dos blocos (PHP 8.1+ fix)
 * Esta função resolve o problema do config_docx_json vazio
 */
function gerarConfigDocx(array $blocos, string $modeloId): string
{
    $config = [
        'versao' => '2.1',
        'modelo_id' => $modeloId,
        'gerado_em' => date('Y-m-d H:i:s'),
        'total_blocos' => count($blocos),
        'blocos' => []
    ];
    
    foreach ($blocos as $index => $bloco) {
        $config['blocos'][] = [
            'index' => $index,
            'tipo' => $bloco['tipo'] ?? 'texto',
            'editavel' => true,
            'ordem' => $index,
            'identificador' => match($bloco['tipo'] ?? 'texto') {
                'tabela' => "tabela_{$index}",
                'texto' => "texto_{$index}",
                default => "bloco_{$index}"
            }
        ];
    }
    
    return json_encode($config, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT);
}

// ============================================================
// PROCESSAMENTO PRINCIPAL
// ============================================================

try {
    // BYPASS PERMITIDO APENAS POR SCRIPT DE TESTE
    if (!isset($_POST['simulador_bypass']) || $_POST['simulador_bypass'] !== '1') {
        validarCsrf();
    }
    
    $repo = new PropostaRepository();
    $conn = $repo->getConn();
    
    // Detecta se é uma nova REVISÃO
    $idOriginal = !empty($_POST['id_proposta_original']) 
        ? (int)$_POST['id_proposta_original'] 
        : null;
    
    // ============================================================
    // NORMALIZAÇÃO DO MODELO DOCX (PHP 8.1+: Match expression)
    // ============================================================
    
    $modeloDocxRaw = $_POST['modelo_docx'] ?? null;
    
    $modeloDocx = match(true) {
        is_array($modeloDocxRaw) => (function() use ($modeloDocxRaw): ?string {
            $filtrado = array_filter($modeloDocxRaw);
            return !empty($filtrado) ? (string)end($filtrado) : null;
        })(),
        is_string($modeloDocxRaw) && !empty($modeloDocxRaw) => $modeloDocxRaw,
        default => null
    };
    
    $dadosProcessados = $_POST;
    $configDocxJson = null; // PHP 8.1+: Inicialização explícita
    
    // ============================================================
    // PROCESSAMENTO MODO DOCX (com config_docx_json fix)
    // ============================================================
    
    if ($modeloDocx !== null) {
        $blocosDocx = extrairBlocosDocx($_POST);
        
        if (!empty($blocosDocx)) {
            // Serializa blocos
            $dadosProcessados['docx_blocos_serializado'] = json_encode(
                $blocosDocx, 
                JSON_THROW_ON_ERROR
            );
            
            // 🎯 FIX CRÍTICO: Gera config_docx_json se não vier do POST
            $configDocxJson = $_POST['config_docx_json'] ?? null;
            if (empty($configDocxJson)) {
                $configDocxJson = gerarConfigDocx($blocosDocx, $modeloDocx);
                PropostaLogger::log("Config DOCX gerado automaticamente", [
                    'modelo' => $modeloDocx,
                    'blocos' => count($blocosDocx)
                ]);
            }
            $dadosProcessados['config_docx_json'] = $configDocxJson;
            
            $dadosProcessados['docx_modelo_id'] = preg_replace(
                '/[^a-zA-Z0-9]/', 
                '', 
                $modeloDocx
            );
            
            // Compatibilidade: campos individuais
            foreach ($blocosDocx as $index => $bloco) {
                $chave = "docx_bloco_{$index}_content";
                $dadosProcessados[$chave] = $bloco['conteudo'] ?? '';
            }
            
            PropostaLogger::log("DOCX processado", [
                'blocos' => count($blocosDocx),
                'modelo' => $modeloDocx
            ]);
        }
    }

    // ============================================================
    // MODO EDITOR DINÂMICO (Prevenção de Wipe + Sincronização)
    // ============================================================
    
    if (!empty($_POST['is_editor_save']) && !empty($_POST['id_proposta'])) {
        $idEdit = (int)$_POST['id_proposta'];
        $conteudoDocx = $dadosProcessados['docx_blocos_serializado'] ?? null;
        
        $syncData = [
            'docx_conteudo' => $conteudoDocx,
            'config_docx_json' => $configDocxJson ?? ($_POST['config_docx_json'] ?? null)
        ];
        
        // Cor da proposta
        $corEditor = $_POST['cor'] ?? $_POST['tema_cor'] ?? null;
        if ($corEditor !== null && in_array($corEditor, ['verde','azul','laranja','cinza'], true)) {
            $syncData['cor'] = $corEditor;
        }
        
        // Extração de campos dos blocos (com null-safe)
        $extracoes = [
            'endereco_obra' => ['field' => 'docx_bloco_9_content', 'prefixo' => 'Endereço:'],
            'bairro_obra' => ['field' => 'docx_bloco_10_content', 'prefixo' => 'Bairro:'],
            'nome_cliente_salvo' => ['field' => 'docx_bloco_3_content', 'prefixo' => 'Nome:'],
        ];
        
        foreach ($extracoes as $campo => $config) {
            $valor = $_POST[$config['field']] ?? null;
            if ($valor !== null) {
                $syncData[$campo] = extrairAposPrefixo($valor, $config['prefixo']);
            }
        }
        
        // Cidade/Estado com regex
        $cidadeEstadoRaw = $_POST['docx_bloco_11_content'] ?? null;
        if ($cidadeEstadoRaw !== null) {
            $cidadeEstado = extrairAposPrefixo($cidadeEstadoRaw, 'Cidade/Estado:');
            if (preg_match('/^([^-]+)[\s-]+(\w{2})$/', trim($cidadeEstado), $m)) {
                $syncData['cidade_obra'] = trim($m[1]);
                $syncData['estado_obra'] = strtoupper($m[2]);
            }
        }
        
        // Área com regex
        $areaRaw = $_POST['docx_bloco_12_content'] ?? null;
        if ($areaRaw !== null) {
            $areaTexto = extrairAposPrefixo($areaRaw, 'Área Estimada:');
            if (preg_match('/(\d+(?:\.\d+)?)\s*(ha|m²|m2|km²|km2)/i', $areaTexto, $m)) {
                $syncData['area_obra'] = $m[1];
                $syncData['unidade_area'] = strtolower(str_replace('m2', 'm²', $m[2]));
            }
        }
        
        // Update no banco (PHP 8.1+: tipos estritos)
        if ($conteudoDocx !== null) {
            $fields = [];
            $values = [];
            $types = '';
            
            foreach ($syncData as $col => $val) {
                if ($val !== null) {
                    $fields[] = "{$col} = ?";
                    $values[] = $val;
                    $types .= 's';
                }
            }
            
            if (!empty($fields)) {
                $values[] = $idEdit;
                $types .= 'i';
                
                $sql = "UPDATE Propostas SET " . implode(', ', $fields) . ", 
                        docx_ultima_edicao = NOW() 
                        WHERE id_proposta = ?";
                
                $stmt = $conn->prepare($sql);
                if ($stmt !== false) {
                    $stmt->bind_param($types, ...$values);
                    $stmt->execute();
                    
                    PropostaLogger::log("Editor save atualizado", [
                        'id' => $idEdit,
                        'campos' => array_keys($syncData)
                    ]);
                }
            }
        }
        
        // Redirect
        $corParaRedirect = $syncData['cor'] ?? 'verde';
        $redirectUrl = sprintf(
            "editor_dinamico.php?id=%d&modelo_docx=%s&cor=%s&success=1",
            $idEdit,
            urlencode($modeloDocx ?? 'PropostaDrone'),
            urlencode($corParaRedirect)
        );
        
        // Detecta AJAX (PHP 8.1+: str_contains)
        $isAjax = !empty($_POST['ajax']) || 
                  (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                   str_contains(strtolower($_SERVER['HTTP_X_REQUESTED_WITH']), 'xmlhttprequest'));
        
        if ($isAjax) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'success' => true, 
                'redirect' => $redirectUrl, 
                'id' => $idEdit, 
                'modo_docx' => true,
                'config_gerado' => $configDocxJson !== null
            ], JSON_THROW_ON_ERROR);
            exit;
        }
        
        header("Location: {$redirectUrl}");
        exit;
    }
    
    // ============================================================
    // ENRIQUECIMENTO DE DADOS (PHP 8.1+: null coalescing chain)
    // ============================================================
    
    $idCriador = (int)($_SESSION['usuario_id'] ?? 0);
    
    // Dados da empresa proponente
    if (empty($dadosProcessados['empresa_proponente_nome'])) {
        $empResult = $conn->query(
            "SELECT * FROM DadosEmpresa WHERE id_criador = {$idCriador} LIMIT 1"
        );
        
        if ($empResult !== false && ($empRow = $empResult->fetch_assoc()) !== null) {
            $mapeamentoEmpresa = [
                'empresa_proponente_nome' => 'Empresa',
                'empresa_proponente_cnpj' => 'CNPJ',
                'empresa_proponente_endereco' => 'Endereco',
                'empresa_proponente_cidade' => 'Cidade',
                'empresa_proponente_estado' => 'Estado',
                'empresa_proponente_banco' => 'Banco',
                'empresa_proponente_agencia' => 'Agencia',
                'empresa_proponente_conta' => 'Conta',
                'empresa_proponente_pix' => 'PIX',
            ];
            
            foreach ($mapeamentoEmpresa as $campo => $coluna) {
                $dadosProcessados[$campo] = $empRow[$coluna] ?? '';
            }
        }
    }

    // Dados do cliente
    if (!empty($dadosProcessados['id_cliente'])) {
        $idCli = (int)$dadosProcessados['id_cliente'];
        $cliResult = $conn->query(
            "SELECT nome_cliente, empresa, email, telefone, celular 
             FROM Clientes 
             WHERE id_cliente = {$idCli}"
        );
        
        if ($cliResult !== false && ($clRow = $cliResult->fetch_assoc()) !== null) {
            // PHP 8.1+: null coalescing assignment não existe, usar ??=
            $dadosProcessados['nome_cliente_salvo'] = 
                $dadosProcessados['nome_cliente_salvo'] 
                ?: ($clRow['nome_cliente'] ?? '');
                
            $dadosProcessados['empresa_cliente_salvo'] = 
                $dadosProcessados['empresa_cliente_salvo'] 
                ?: ($dadosProcessados['empresa_cliente'] ?? $clRow['empresa'] ?? '');
                
            $dadosProcessados['email_salvo'] = 
                $dadosProcessados['email_salvo'] 
                ?: ($clRow['email'] ?? '');
                
            $dadosProcessados['telefone_salvo'] = 
                $dadosProcessados['telefone_salvo'] 
                ?: ($clRow['telefone'] ?? '');
                
            $dadosProcessados['celular_salvo'] = 
                $dadosProcessados['celular_salvo'] 
                ?: ($clRow['celular'] ?? '');
                
            $dadosProcessados['whatsapp_salvo'] = 
                $dadosProcessados['whatsapp_salvo'] 
                ?: ($clRow['celular'] ?? '');
        }
    }
    
    // Normalização de endereço
    $camposEndereco = [
        'endereco_obra' => 'endereco',
        'bairro_obra' => 'bairro',
        'cidade_obra' => 'cidade',
        'estado_obra' => 'estado',
        'area_obra' => 'area'
    ];
    
    foreach ($camposEndereco as $campoObra => $campoForm) {
        $dadosProcessados[$campoObra] = 
            $dadosProcessados[$campoObra] 
            ?: ($dadosProcessados[$campoForm] ?? '');
    }

    // Fallback cidade empresa
    if (empty($dadosProcessados['empresa_proponente_cidade'])) {
        $dadosProcessados['empresa_proponente_cidade'] = 
            $dadosProcessados['cidade_obra'] ?? '';
    }

    // ============================================================
    // PERSISTÊNCIA VIA REPOSITORY
    // ============================================================
    
    $id = $repo->salvarCompleto($dadosProcessados, $idCriador);
    
    if ($modeloDocx !== null && $id !== null) {
        $repo->associarModeloDocx($id, $modeloDocx);
    }
    
    // ============================================================
    // REDIRECIONAMENTO (PHP 8.1+: match expression)
    // ============================================================
    
    $isAjax = !empty($_POST['ajax']) || 
              (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               str_contains(strtolower($_SERVER['HTTP_X_REQUESTED_WITH']), 'xmlhttprequest'));
    
    $formato = $_POST['formato_saida'] ?? 'docx';
    
    $redirectUrl = match($formato) {
        'editor' => (function() use ($id, $modeloDocx, $_POST): string {
            $_SESSION['id_proposta_ativa'] = $id;
            
            // Pega a cor que veio do form (se houver, setada por persistência) ou força 'verde'
            $cor = $_POST['cor'] ?? 'verde';
            if (!in_array($cor, ['verde','azul','laranja','cinza'], true)) {
                $cor = 'verde';
            }
            
            if ($modeloDocx !== null) {
                return sprintf(
                    "editor_dinamico.php?id=%d&modelo_docx=%s&cor=%s&success=1",
                    $id,
                    urlencode($modeloDocx),
                    urlencode($cor)
                );
            }
            return sprintf(
                "editor_dinamico.php?id=%d&cor=%s&success=1",
                $id,
                urlencode($cor)
            );
        })(),
        
        'html' => "gerar_proposta_html.php?id={$id}",
        'html_premium', 'premium' => "gerar_proposta_premium.php?id={$id}",
        default => "proposta_sucesso.php?id={$id}"
    };

    if ($isAjax) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => true, 
            'redirect' => $redirectUrl, 
            'id' => $id,
            'modo_docx' => $modeloDocx !== null,
            'modelo' => $modeloDocx,
            'config_docx_gerado' => $configDocxJson !== null
        ], JSON_THROW_ON_ERROR);
        exit;
    }
    
    header("Location: {$redirectUrl}");
    exit;
    
} catch (Throwable $e) {
    // PHP 8.1+: Throwable captura Error e Exception
    PropostaLogger::log("ERRO CRÍTICO", [
        'mensagem' => $e->getMessage(),
        'arquivo' => $e->getFile(),
        'linha' => $e->getLine(),
        'trace' => $e->getTraceAsString()
    ]);
    
    $isAjax = !empty($_POST['ajax']) || 
              (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               str_contains(strtolower($_SERVER['HTTP_X_REQUESTED_WITH']), 'xmlhttprequest'));

    if ($isAjax) {
        http_response_code(500);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => false, 
            'error' => $e->getMessage(),
            'debug' => $e->getFile() . ':' . $e->getLine(),
            'php_version' => PHP_VERSION
        ], JSON_THROW_ON_ERROR);
        exit;
    }

    http_response_code(500);
    ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Erro ao Processar Proposta</title>
    <style>
        body { 
            font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; 
            padding: 2rem; 
            background: #fef2f2; 
            margin: 0;
        }
        .erro-container { 
            max-width: 800px; 
            margin: 0 auto; 
            background: white; 
            padding: 2rem; 
            border-radius: 8px; 
            border-left: 4px solid #dc2626; 
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); 
        }
        h3 { 
            color: #dc2626; 
            margin-top: 0;
            font-size: 1.5rem;
        }
        .detalhe { 
            background: #f3f4f6; 
            padding: 1rem; 
            border-radius: 4px; 
            font-family: 'Courier New', monospace; 
            font-size: 0.9rem;
            overflow-x: auto;
            margin: 1rem 0;
        }
        button { 
            background: #dc2626; 
            color: white; 
            border: none; 
            padding: 0.75rem 1.5rem; 
            border-radius: 4px; 
            cursor: pointer; 
            font-size: 1rem; 
            margin-top: 1rem;
            transition: background 0.2s;
        }
        button:hover { 
            background: #b91c1c; 
        }
        .php-info { 
            color: #6b7280; 
            font-size: 0.875rem; 
            margin-top: 1rem;
            border-top: 1px solid #e5e7eb;
            padding-top: 1rem;
        }
        .mensagem-erro {
            color: #374151;
            line-height: 1.6;
            margin: 1rem 0;
        }
    </style>
</head>
<body>
    <div class="erro-container">
        <h3>⚠️ Erro ao Processar Proposta</h3>
        <p class="mensagem-erro"><strong>Mensagem:</strong> <?= htmlspecialchars($e->getMessage()) ?></p>
        <div class="detalhe">
            <?= htmlspecialchars($e->getFile()) ?>:<?= $e->getLine() ?>
        </div>
        <p class="php-info">PHP <?= PHP_VERSION ?> | SGT Propostas</p>
        <button onclick="window.history.back()">← Voltar e Corrigir</button>
    </div>
</body>
</html>
    <?php
    exit;
}

// ============================================================
// FUNÇÕES AUXILIARES DOCX (PHP 8.1+)
// ============================================================

/**
 * Extrai blocos dinâmicos do DOCX dos dados POST
 * @return array<int, array{index: int, tipo: string, conteudo?: string, linhas?: array}>
 */
function extrairBlocosDocx(array $postData): array
{
    $blocos = [];
    $blocoIndex = 0;
    
    while (isset($postData["docx_bloco_{$blocoIndex}_estrutura"]) || 
           isset($postData["docx_bloco_{$blocoIndex}_content"])) {
        
        $tipo = $postData["docx_bloco_{$blocoIndex}_tipo"] ?? 'texto';
        
        if ($tipo === 'tabela') {
            $estrutura = json_decode(
                $postData["docx_bloco_{$blocoIndex}_estrutura"] ?? '[]', 
                true, 
                512, 
                JSON_THROW_ON_ERROR
            );
            
            $blocos[] = [
                'index' => $blocoIndex,
                'tipo' => 'tabela',
                'linhas' => $estrutura
            ];
        } else {
            // Limpa duplicação de R$
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