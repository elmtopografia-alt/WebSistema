require_once 'session_validator.php';
require_once 'config.php';
require_once 'ConnectionManager.php';
require_once 'PropostaRepository.php';

// Carrega biblioteca PHPWord
require_once __DIR__ . '/vendor/autoload.php';
use PhpOffice\PhpWord\TemplateProcessor;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: painel.php");
    exit;
}

$id_usuario = $_SESSION['usuario_id'] ?? 0;
$id_proposta = intval($_POST['id_proposta'] ?? 0);

if (!$id_usuario || !$id_proposta) {
    die("Acesso negado ou ID inválido.");
}

try {
    $repo = new PropostaRepository();
    
    // 1. PERSISTÊNCIA (Centralizada no Repo)
    // O método salvar() já gerencia transação, atualização de itens e cálculos.
    $id = $repo->salvar($_POST);

    // 2. REGENERAÇÃO DO ARQUIVO DOCX
    // Busca dados completos (incluindo clientes e metadados de template)
    $dadosCompleto = $repo->buscarPorId($id);
    
    if (!$dadosCompleto) throw new Exception("Erro ao recuperar dados da proposta para o DOCX.");

    // Busca metadados extras (arquivo_modelo, logo) - Poderia estar no buscarPorId, mas mantemos isolado por enquanto
    $conn = ConnectionManager::get();
    $meta = $conn->query("SELECT d.logo_caminho, ts.arquivo_modelo 
                          FROM DadosEmpresa d 
                          JOIN Propostas p ON p.id_criador = d.id_criador
                          LEFT JOIN Tipo_Servicos ts ON p.id_servico = ts.id_servico
                          WHERE p.id_proposta = $id LIMIT 1")->fetch_assoc();

    $ambiente = $_SESSION['ambiente'] ?? 'producao';
    $pastaModelos = ($ambiente === 'demo') ? 'modelos_demo' : 'modelos_prod';
    $arquivoModelo = !empty($meta['arquivo_modelo']) ? $meta['arquivo_modelo'] : 'ModeloPropostaPadrao.docx';
    $caminhoModelo = __DIR__ . "/$pastaModelos/" . $arquivoModelo;

    if (file_exists($caminhoModelo)) {
        $template = new TemplateProcessor($caminhoModelo);

        // Substituição de Variáveis
        $template->setValue('NOME_CLIENTE', $dadosCompleto['nome_cliente_salvo']);
        $template->setValue('NUMERO_PROPOSTA', $dadosCompleto['numero_proposta']);
        $template->setValue('VALOR_FINAL', number_format($dadosCompleto['valor_final_proposta'], 2, ',', '.'));
        $template->setValue('DATA_HOJE', date('d/m/Y', strtotime($dadosCompleto['data_criacao'])));
        $template->setValue('OBJETO', $dadosCompleto['finalidade']);
        $template->setValue('AREA', $dadosCompleto['area_obra']);
        $template->setValue('CIDADE_OBRA', $dadosCompleto['cidade_obra']);
        $template->setValue('PRAZO', $dadosCompleto['prazo_execucao']);
        
        // Logo
        if (!empty($meta['logo_caminho']) && file_exists(__DIR__ . '/' . $meta['logo_caminho'])) {
            $template->setImageValue('LOGO_EMPRESA', [
                'path' => __DIR__ . '/' . $meta['logo_caminho'], 
                'width' => 150, 'height' => 80, 'ratio' => true
            ]);
        } else {
            $template->setValue('LOGO_EMPRESA', '');
        }

        // Nome do Arquivo amigável
        $nomeEmpresa = trim(explode(' ', $dadosCompleto['empresa_proponente_nome'])[0]);
        $nomeEmpresaClean = preg_replace('/[^a-zA-Z0-9]/', '', iconv('UTF-8', 'ASCII//TRANSLIT', $nomeEmpresa));
        $partesNum = explode('-', $dadosCompleto['numero_proposta']);
        $ano = $partesNum[1] ?? date('Y');
        $seq = $partesNum[2] ?? '000';
        
        $novoNomeArquivo = "{$nomeEmpresaClean}-{$ano}-{$seq}.docx";
        $caminhoSaida = __DIR__ . '/propostas_emitidas/' . $novoNomeArquivo;

        $template->saveAs($caminhoSaida);
    }

    header("Location: painel.php?msg=sucesso");
    exit;

} catch (Exception $e) {
    die("Erro ao salvar proposta: " . $e->getMessage());
}