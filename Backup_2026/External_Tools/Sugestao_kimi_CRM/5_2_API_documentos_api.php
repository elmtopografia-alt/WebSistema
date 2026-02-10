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
$acao = $_GET['acao'] ?? '';
$input = json_decode(file_get_contents('php://input'), true);

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

$uploadDir = __DIR__ . '/../../uploads/documentos/' . date('Y/m');
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

            if (!isset(UPLOAD_ALLOWED_TYPES[$file['type']])) {
                throw new Exception("Tipo de arquivo não permitido. Permitidos: PDF, JPG, PNG, DOC, DOCX, XLS, XLSX");
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
            $ext = UPLOAD_ALLOWED_TYPES[$file['type']];
            $nomeUnico = uniqid() . '_' . bin2hex(random_bytes(8)) . '.' . $ext;
            $caminhoCompleto = $uploadDir . '/' . $nomeUnico;

            // Move arquivo
            if (!move_uploaded_file($file['tmp_name'], $caminhoCompleto)) {
                throw new Exception("Erro ao salvar arquivo");
            }

            // Salva no banco
            $caminhoRelativo = str_replace(__DIR__ . '/../../', '', $caminhoCompleto);
            $stmt = $conn->prepare("
                INSERT INTO Documentos 
                (id_proposta, id_cliente, id_usuario, nome_original, nome_arquivo, tipo_arquivo, 
                 categoria, tamanho_bytes, caminho, descricao) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->bind_param("iiissssiis",
                $id_proposta, $id_cliente, $id_usuario,
                $file['name'], $nomeUnico, $file['type'],
                $categoria, $file['size'], $caminhoRelativo, $descricao
            );
            $stmt->execute();
            $id_documento = $conn->insert_id;

            // Registra na timeline
            registrarDocumentoTimeline($conn, $id_proposta, $id_cliente, 'anexado', $file['name'], $id_usuario);

            echo json_encode([
                'sucesso' => true,
                'id_documento' => $id_documento,
                'mensagem' => 'Arquivo enviado com sucesso',
                'arquivo' => [
                    'nome' => $file['name'],
                    'tamanho' => formatBytes($file['size']),
                    'tipo' => $file['type']
                ]
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
                $doc['icone'] = getIconePorTipo($doc['tipo_arquivo']);
                $doc['cor'] = getCorPorCategoria($doc['categoria']);
                $agrupado[$cat][] = $doc;
            }

            echo json_encode([
                'sucesso' => true,
                'documentos' => $agrupado,
                'total' => count($documentos)
            ]);
            break;

        case 'excluir':
            $id_documento = intval($input['id_documento'] ?? 0);
            
            // Busca documento
            $stmt = $conn->prepare("SELECT * FROM Documentos WHERE id_documento = ? AND id_usuario = ?");
            $stmt->bind_param("ii", $id_documento, $id_usuario);
            $stmt->execute();
            $doc = $stmt->get_result()->fetch_assoc();

            if (!$doc) {
                throw new Exception("Documento não encontrado ou sem permissão");
            }

            // Remove arquivo físico
            $caminhoCompleto = __DIR__ . '/../../' . $doc['caminho'];
            if (file_exists($caminhoCompleto)) {
                unlink($caminhoCompleto);
            }

            // Remove do banco
            $stmt = $conn->prepare("DELETE FROM Documentos WHERE id_documento = ?");
            $stmt->bind_param("i", $id_documento);
            $stmt->execute();

            // Registra na timeline
            registrarDocumentoTimeline($conn, $doc['id_proposta'], $doc['id_cliente'], 'removido', $doc['nome_original'], $id_usuario);

            echo json_encode(['sucesso' => true, 'mensagem' => 'Documento excluído']);
            break;

        case 'definir_principal':
            $id_documento = intval($input['id_documento'] ?? 0);
            
            // Busca proposta do documento
            $stmt = $conn->prepare("SELECT id_proposta FROM Documentos WHERE id_documento = ? AND id_usuario = ?");
            $stmt->bind_param("ii", $id_documento, $id_usuario);
            $stmt->execute();
            $doc = $stmt->get_result()->fetch_assoc();

            if (!$doc) {
                throw new Exception("Documento não encontrado");
            }

            // Remove principal de outros
            $stmt = $conn->prepare("UPDATE Documentos SET is_principal = FALSE WHERE id_proposta = ?");
            $stmt->bind_param("i", $doc['id_proposta']);
            $stmt->execute();

            // Define este como principal
            $stmt = $conn->prepare("UPDATE Documentos SET is_principal = TRUE WHERE id_documento = ?");
            $stmt->bind_param("i", $id_documento);
            $stmt->execute();

            echo json_encode(['sucesso' => true]);
            break;

        case 'download':
            $id_documento = intval($_GET['id_documento'] ?? 0);
            
            $stmt = $conn->prepare("SELECT * FROM Documentos WHERE id_documento = ? AND id_usuario = ?");
            $stmt->bind_param("ii", $id_documento, $id_usuario);
            $stmt->execute();
            $doc = $stmt->get_result()->fetch_assoc();

            if (!$doc) {
                throw new Exception("Documento não encontrado");
            }

            $caminho = __DIR__ . '/../../' . $doc['caminho'];
            if (!file_exists($caminho)) {
                throw new Exception("Arquivo não encontrado no servidor");
            }

            // Retorna URL para download
            echo json_encode([
                'sucesso' => true,
                'url' => $doc['caminho'],
                'nome' => $doc['nome_original']
            ]);
            break;

        case 'estatisticas_armazenamento':
            $stmt = $conn->prepare("
                SELECT 
                    COUNT(*) as total_arquivos,
                    SUM(tamanho_bytes) as total_bytes,
                    categoria,
                    COUNT(*) as quantidade_categoria
                FROM Documentos 
                WHERE id_usuario = ?
                GROUP BY categoria
            ");
            $stmt->bind_param("i", $id_usuario);
            $stmt->execute();
            $stats = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

            $totalBytes = array_sum(array_column($stats, 'total_bytes'));

            echo json_encode([
                'sucesso' => true,
                'total_arquivos' => array_sum(array_column($stats, 'quantidade_categoria')),
                'total_armazenamento' => formatBytes($totalBytes),
                'por_categoria' => $stats
            ]);
            break;

        default:
            throw new Exception("Ação inválida");
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['erro' => $e->getMessage()]);
}

function formatBytes($bytes) {
    if ($bytes === 0) return '0 Bytes';
    $k = 1024;
    $sizes = ['Bytes', 'KB', 'MB', 'GB'];
    $i = floor(log($bytes) / log($k));
    return round($bytes / pow($k, $i), 2) . ' ' . $sizes[$i];
}

function getIconePorTipo($mime) {
    $icones = [
        'application/pdf' => 'ph-file-pdf',
        'image/jpeg' => 'ph-file-image',
        'image/png' => 'ph-file-image',
        'image/webp' => 'ph-file-image',
        'application/msword' => 'ph-file-doc',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'ph-file-doc',
        'application/vnd.ms-excel' => 'ph-file-xls',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'ph-file-xls'
    ];
    return $icones[$mime] ?? 'ph-file';
}

function getCorPorCategoria($cat) {
    $cores = [
        'proposta' => 'blue',
        'contrato' => 'green',
        'comprovante' => 'purple',
        'nota_fiscal' => 'orange',
        'documento_cliente' => 'yellow',
        'outro' => 'gray'
    ];
    return $cores[$cat] ?? 'gray';
}
?>