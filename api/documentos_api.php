<?php
// api/documentos_api.php - Gestão de Documentos

header('Content-Type: application/json');
require_once '../db.php';
require_once '../session_validator.php';

if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(['erro' => 'Não autorizado']);
    exit;
}

$id_usuario = $_SESSION['usuario_id'];
$conn = (isset($_SESSION['ambiente']) && $_SESSION['ambiente'] === 'demo') ? Database::getDemo() : Database::getProd();
$acao = $_POST['acao'] ?? $_GET['acao'] ?? '';

// Configurações de upload
define('UPLOAD_MAX_SIZE', 10 * 1024 * 1024); // 10MB
define('UPLOAD_ALLOWED_TYPES', [
    'application/pdf' => 'pdf',
    'image/jpeg' => 'jpg',
    'image/png' => 'png',
    'image/webp' => 'webp',
    'application/msword' => 'doc',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
    'application/vnd.ms-excel' => 'xls',
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx'
]);

// Diretório de upload relativo à raiz do site (garantir permissões)
$uploadBase = __DIR__ . '/../uploads/documentos';
if (!is_dir($uploadBase)) {
    mkdir($uploadBase, 0755, true);
}

// Subdiretório por ano/mês para organização
$subDir = date('Y/m');
$uploadDir = $uploadBase . '/' . $subDir;
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

function registrarDocumentoTimeline($conn, $id_proposta, $id_cliente, $acao, $nome_arquivo, $id_usuario) {
    $stmt = $conn->prepare("
        INSERT INTO Historico_Interacoes 
        (id_proposta, id_cliente, tipo, conteudo, canal, id_usuario) 
        VALUES (?, ?, 'arquivo_anexado', ?, 'sistema', ?)
    ");
    $conteudo = "Arquivo {$acao}: {$nome_arquivo}";
    $stmt->bind_param("iisi", $id_proposta, $id_cliente, $conteudo, $id_usuario);
    return $stmt->execute();
}

function formatBytes($bytes) {
    if ($bytes === 0) return '0 Bytes';
    $k = 1024;
    $sizes = ['Bytes', 'KB', 'MB', 'GB'];
    $i = floor(log($bytes) / log($k));
    return round($bytes / pow($k, $i), 2) . ' ' . $sizes[$i];
}

try {
    switch ($acao) {
        case 'upload':
            if (empty($_FILES['arquivo'])) {
                throw new Exception("Nenhum arquivo enviado");
            }

            $file = $_FILES['arquivo'];
            $id_proposta = intval($_POST['id_proposta'] ?? 0);
            $categoria = $_POST['categoria'] ?? 'outro';
            $descricao = $_POST['descricao'] ?? '';

            if ($id_proposta === 0) {
                throw new Exception("ID da proposta obrigatório");
            }

            // Validações
            if ($file['size'] > UPLOAD_MAX_SIZE) {
                throw new Exception("Arquivo muito grande. Máximo: 10MB");
            }
            if (!isset(UPLOAD_ALLOWED_TYPES[$file['type']]) && !array_search(pathinfo($file['name'], PATHINFO_EXTENSION), UPLOAD_ALLOWED_TYPES)) {
                 // Fallback: verifica extensão se o mime type falhar (comum em servidores compartilhados)
                 $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                 if(!in_array($ext, UPLOAD_ALLOWED_TYPES)) {
                     throw new Exception("Tipo de arquivo não permitido.");
                 }
            }

            // Verifica proposta
            $stmt = $conn->prepare("SELECT id_cliente FROM Propostas WHERE id_proposta = ? AND id_criador = ?");
            $stmt->bind_param("ii", $id_proposta, $id_usuario);
            $stmt->execute();
            $proposta = $stmt->get_result()->fetch_assoc();

            if (!$proposta) {
                throw new Exception("Proposta não encontrada ou sem permissão");
            }

            $id_cliente = $proposta['id_cliente'];

            // Gera nome único
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $nomeUnico = uniqid() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
            $caminhoCompleto = $uploadDir . '/' . $nomeUnico;

            // Move arquivo
            if (!move_uploaded_file($file['tmp_name'], $caminhoCompleto)) {
                throw new Exception("Erro ao salvar arquivo no servidor");
            }

            // Caminho relativo para salvar no banco
            $caminhoRelativo = 'uploads/documentos/' . $subDir . '/' . $nomeUnico;

            // Salva no banco
            $stmt = $conn->prepare("
                INSERT INTO Documentos 
                (id_proposta, id_cliente, id_usuario, nome_original, nome_arquivo, tipo_arquivo, 
                 categoria, tamanho_bytes, caminho, descricao) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $tipo = $file['type'];
            $tamanho = $file['size'];
            $stmt->bind_param("iiissssiis",
                $id_proposta, $id_cliente, $id_usuario,
                $file['name'], $nomeUnico, $tipo,
                $categoria, $tamanho, $caminhoRelativo, $descricao
            );
            $stmt->execute();
            $id_documento = $conn->insert_id;

            // Registra na timeline
            registrarDocumentoTimeline($conn, $id_proposta, $id_cliente, 'anexado', $file['name'], $id_usuario);

            echo json_encode([
                'sucesso' => true,
                'id_documento' => $id_documento,
                'mensagem' => 'Arquivo enviado com sucesso'
            ]);
            break;

        case 'listar':
            $id_proposta = intval($_GET['id_proposta'] ?? 0);
            
            $stmt = $conn->prepare("
                SELECT d.*, u.nome as nome_usuario,
                       DATE_FORMAT(d.data_upload, '%d/%m/%Y %H:%i') as data_formatada
                FROM Documentos d
                LEFT JOIN Usuarios u ON d.id_usuario = u.id
                WHERE d.id_proposta = ? AND d.id_usuario = ?
                ORDER BY d.is_principal DESC, d.data_upload DESC
            ");
            $stmt->bind_param("ii", $id_proposta, $id_usuario);
            $stmt->execute();
            $documentos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

            // Agrupa por categoria
            $agrupado = [];
            foreach ($documentos as $doc) {
                $cat = $doc['categoria'];
                if (!isset($agrupado[$cat])) {
                    $agrupado[$cat] = [];
                }
                $doc['tamanho_formatado'] = formatBytes($doc['tamanho_bytes']);
                $agrupado[$cat][] = $doc;
            }

            echo json_encode([
                'sucesso' => true,
                'documentos' => $agrupado
            ]);
            break;
            
        case 'download':
             $id_documento = intval($_GET['id_documento'] ?? 0);
            
            $stmt = $conn->prepare("SELECT * FROM Documentos WHERE id_documento = ? AND id_usuario = ?");
            $stmt->bind_param("ii", $id_documento, $id_usuario);
            $stmt->execute();
            $doc = $stmt->get_result()->fetch_assoc();

            if (!$doc) throw new Exception("Documento não encontrado");

            // URL pública
            // Ajustar se o uploads estiver fora da raiz pública
            $url = '../' . $doc['caminho'];
            
            echo json_encode([
                'sucesso' => true,
                'url' => $doc['caminho'], // Caminho relativo para o frontend montar o link
                'nome' => $doc['nome_original']
            ]);
            break;
            
        case 'excluir':
            $input = json_decode(file_get_contents('php://input'), true);
            $id_documento = intval($input['id_documento'] ?? 0);
            
            $stmt = $conn->prepare("SELECT * FROM Documentos WHERE id_documento = ? AND id_usuario = ?");
            $stmt->bind_param("ii", $id_documento, $id_usuario);
            $stmt->execute();
            $doc = $stmt->get_result()->fetch_assoc();
            
            if (!$doc) throw new Exception("Documento não encontrado");
            
            // Remove arquivo
            $path = __DIR__ . '/../' . $doc['caminho'];
            if(file_exists($path)) unlink($path);
            
            // Remove do banco
            $conn->query("DELETE FROM Documentos WHERE id_documento = $id_documento");
            
            registrarDocumentoTimeline($conn, $doc['id_proposta'], $doc['id_cliente'], 'removido', $doc['nome_original'], $id_usuario);
            
            echo json_encode(['sucesso' => true]);
            break;

        default:
            throw new Exception("Ação inválida");
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['erro' => $e->getMessage()]);
}
?>
