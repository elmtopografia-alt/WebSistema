require_once 'session_validator.php';
require_once 'config.php';
require_once 'PropostaRepository.php';

if (!isset($_GET['id'])) {
    header("Location: painel.php");
    exit;
}

$id_usuario = $_SESSION['usuario_id'];
$id_proposta = intval($_GET['id']);

try {
    $repo = new PropostaRepository();
    if ($repo->deletar($id_proposta, $id_usuario)) {
        header("Location: painel.php?msg=excluido");
    } else {
        die("Erro ao excluir: Proposta não encontrada ou permissão negada.");
    }
} catch (Exception $e) {
    die("Erro no sistema: " . $e->getMessage());
}