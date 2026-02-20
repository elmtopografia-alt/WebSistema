<?php
/**
 * recebedor_modelos.php (Para Servidor WEB Remoto)
 * Recebe modelos DOCX gerados localmente e salva na pasta modelos_gerados/
 */

// Chave de segurança para evitar acessos não autorizados
// MANTENHA ESTA CHAVE EM SEGREDO E IGUAL À CONFIGURADA NO GERADOR LOCAL
define('CHAVE_SYNC_DOCX', 'SGT_DOCX_SYNC_77A9B2C3X');

header('Content-Type: application/json');

// 1. Verifica a Autenticação
if (!isset($_POST['chave']) || $_POST['chave'] !== CHAVE_SYNC_DOCX) {
    http_response_code(403);
    echo json_encode(['erro' => 'Acesso Negado. Chave de sincronização inválida.']);
    exit;
}

// 2. Verifica se um arquivo foi enviado
if (!isset($_FILES['modelo_php']) || $_FILES['modelo_php']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['erro' => 'Nenhum arquivo recebido ou erro no upload.']);
    exit;
}

$arquivo = $_FILES['modelo_php'];
$nomeArquivo = basename($arquivo['name']);

// 3. Validação rigorosa de segurança (Apenas arquivos .php que começam com Modelo)
if (!preg_match('/^Modelo[a-zA-Z0-9_]+\.php$/', $nomeArquivo)) {
    http_response_code(400);
    echo json_encode(['erro' => 'Nome de arquivo inválido para sincronização.']);
    exit;
}

$dir_modelos = __DIR__ . '/modelos_gerados/';

// Garante que o diretório existe
if (!is_dir($dir_modelos)) {
    if (!@mkdir($dir_modelos, 0755, true)) {
         http_response_code(500);
         echo json_encode(['erro' => 'Falha ao criar diretório modelos_gerados no servidor.']);
         exit;
    }
}

$caminho_final = $dir_modelos . $nomeArquivo;

// 4. Move o arquivo para o destino final
if (move_uploaded_file($arquivo['tmp_name'], $caminho_final)) {
    echo json_encode([
        'sucesso' => true, 
        'mensagem' => "Modelo $nomeArquivo sincronizado com o servidor Web com sucesso!"
    ]);
} else {
    http_response_code(500);
    echo json_encode(['erro' => 'Falha ao salvar o modelo no disco do servidor.']);
}
