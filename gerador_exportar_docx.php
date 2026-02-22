<?php
/**
 * Gerador e Exportador DOCX (Wrapper do Motor V3)
 * Recebe o ID da proposta, gera o arquivo físico usando a rotina da API
 * e força o download direto no navegador.
 */

session_start();
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/classes/PropostaPHPWord.php';

// Verificação de segurança
if (!isset($_SESSION['usuario_id'])) {
    die("<div style='padding:20px;font-family:sans-serif;color:#856404;background:#fff3cd;'>Acesso negado. Sessão expirada ou usuário não autenticado.</div>");
}

$id_usuario = $_SESSION['usuario_id'];
$is_demo = (isset($_SESSION['ambiente']) && $_SESSION['ambiente'] === 'demo');

try {
    $conn = $is_demo ? Database::getDemo() : Database::getProd();
} catch (Exception $e) {
    die("<div style='padding:20px;font-family:sans-serif;color:#721c24;background:#f8d7da;'>Erro de conexão com o banco de dados.</div>");
}

$id_proposta = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id_proposta) {
    die("ID da proposta não informado na URL.");
}

// Busca proposta do usuário
$stmt = $conn->prepare("SELECT * FROM Propostas WHERE id_proposta = ? AND id_criador = ?");
$stmt->bind_param("ii", $id_proposta, $id_usuario);
$stmt->execute();
$proposta = $stmt->get_result()->fetch_assoc();

if (!$proposta) {
    die("<div style='padding:20px;font-family:sans-serif;color:#721c24;background:#f8d7da;'>Proposta não encontrada ou você não tem permissão para acessá-la.</div>");
}

// Busca os dados do cliente para mesclar no corpo do arquivo Word (caso hajam variáveis soltas do CRM)
$stmt = $conn->prepare("SELECT * FROM Clientes WHERE id_cliente = ?");
$stmt->bind_param("i", $proposta['id_cliente']);
$stmt->execute();
$cliente = $stmt->get_result()->fetch_assoc();

$dados = array_merge($proposta, $cliente ?? []);

// Definindo Nomenclatura do arquivo Final
$numero_proposta = $proposta['numero_proposta'] ?? $id_proposta;
$nome_arquivo = 'Proposta_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $numero_proposta) . '.docx';
$pasta_saida = __DIR__ . '/propostas_geradas/';
$caminho_completo = $pasta_saida . $nome_arquivo;

// Garante que o diretório de destino existe e tem permissão
if (!is_dir($pasta_saida)) {
    mkdir($pasta_saida, 0755, true);
}

try {
    // Instancia o Engine de DOCX Dinâmico V3 e constrói o arquivo no Disco (templates_gerados)
    $gerador = new PropostaPHPWord($dados);
    $gerador->gerar($caminho_completo);
    
    // Header forçando Download do arquivo Gerado para o Front-End
    if (file_exists($caminho_completo)) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        header('Content-Disposition: attachment; filename="' . basename($caminho_completo) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($caminho_completo));
        
        // Limpa cache de output buffer do PHP para evitar arquivos DOCX corrompidos com HTML invisíveis (Brancos)
        if (ob_get_length()) {
            ob_clean();
        }
        flush();
        
        readfile($caminho_completo);
        exit;
    } else {
        die("Erro Geração: O motor processou com sucesso mas o arquivo não pôde ser encontrado no diretório 'propostas_geradas'.");
    }
} catch (Exception $e) {
    die("Falha Catastrófica ao gerar DOCX V3: " . htmlspecialchars($e->getMessage()));
}
