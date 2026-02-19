require_once __DIR__ . '/session_validator.php';
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/PropostaRepository.php';

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Método inválido.");
    }

    $data = $_POST;
    
    // Identifica ID: pode vir como id_proposta ou id_proposta_original
    $id_proposta = !empty($data['id_proposta']) ? intval($data['id_proposta']) : 
                  (!empty($data['id_proposta_original']) ? intval($data['id_proposta_original']) : null);
    
    if ($id_proposta) {
        $data['id_proposta'] = $id_proposta;
    }

    $repo = new PropostaRepository();
    
    // O método salvar() já lida com:
    // 1. Criação de nova se ID for nulo
    // 2. Update se ID existir
    // 3. Conteúdo personalizado (_content)
    // 4. Metadados (area_obra, etc)
    
    $id_salvo = $repo->salvar($data);

    echo json_encode([
        'success' => true,
        'message' => 'Rascunho salvo com sucesso!',
        'id_proposta' => $id_salvo,
        'is_new' => !$id_proposta
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao salvar rascunho: ' . $e->getMessage()
    ]);
}
?>
