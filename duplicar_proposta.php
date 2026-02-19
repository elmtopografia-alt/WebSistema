require_once 'session_validator.php';
require_once 'config.php';
require_once 'PropostaRepository.php';

if (!isset($_GET['id'])) {
    header("Location: painel.php");
    exit;
}

$id_usuario = $_SESSION['usuario_id'];
$id_origem = intval($_GET['id']);

try {
    $repo = new PropostaRepository();
    $id_novo = $repo->duplicar($id_origem, $id_usuario);
    
    if ($id_novo) {
        header("Location: painel.php?msg=duplicado");
    } else {
        die("Erro ao duplicar: Proposta não encontrada ou permissão negada.");
    }
} catch (Exception $e) {
    die("Erro no sistema: " . $e->getMessage());
}