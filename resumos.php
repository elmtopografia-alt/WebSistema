// Define o caminho para a pasta segura
$caminho_resumos = __DIR__ . '/../../resumos_propostas/';

// Garante que o diretório exista (caso não tenha sido criado manualmente)
if (!is_dir($caminho_resumos)) {
    mkdir($caminho_resumos, 0755, true);
}