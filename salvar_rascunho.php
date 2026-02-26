<?php
require_once __DIR__ . '/session_validator.php';
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/PropostaRepository.php';

ini_set('display_errors', 0);
error_reporting(E_ALL);
header('Content-Type: application/json; charset=utf-8');

try {
    // DEBUG TEMPORÁRIO
    $log = fopen(__DIR__ . '/salvar_debug.log', 'a');
    fwrite($log, "\n\n=== " . date('Y-m-d H:i:s') . " ===\n");
    fwrite($log, "POST: " . print_r($_POST, true) . "\n");

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

    // =====================================================
    // CORREÇÃO DOCX: Processar blocos dinâmicos para o Repository
    // =====================================================
    $blocosDocx = [];
    foreach ($data as $key => $value) {
        // Bloco de texto
        if (preg_match('/^docx_bloco_(\d+)_content$/', $key, $matches)) {
            $idx = intval($matches[1]);
            $blocosDocx[$idx] = [
                'tipo' => 'texto',
                'conteudo' => $value,
                'ordem' => $idx
            ];
        }
        // Bloco de tabela
        if (preg_match('/^docx_bloco_(\d+)_estrutura$/', $key, $matches)) {
            $idx = intval($matches[1]);
            $estrutura = json_decode($value, true);
            if (is_array($estrutura)) {
                $blocosDocx[$idx] = [
                    'tipo' => 'tabela',
                    'conteudo' => $estrutura,
                    'ordem' => $idx
                ];
            }
        }
    }

    // Se encontrou blocos DOCX, serializa para o campo esperado pelo PropostaRepository::salvar
    if (!empty($blocosDocx)) {
        ksort($blocosDocx);
        $data['docx_blocos_serializado'] = json_encode(array_values($blocosDocx));
        
        // Garante modelo_docx
        if (empty($data['modelo_docx'])) {
            $data['modelo_docx'] = $_GET['modelo_docx'] ?? $_POST['modelo_docx'] ?? null;
        }
    }

    $repo = new PropostaRepository();

    // =====================================================
    // CORREÇÃO: Buscar dados da proposta existente para manter equipamentos
    // =====================================================
    if ($id_proposta) {
        $dadosExistentes = $repo->buscarPorId($id_proposta);
        
        if ($dadosExistentes) {
            // Preserva equipamentos se não vieram no POST
            $camposEquipamentos = [
                'modelo_drone', 'modelo_gps', 'modelo_estacao_total', 'modelo_veiculo',
                'marca_drone', 'marca_gps', 'marca_estacao_total', 'marca_veiculo'
            ];
            
            foreach ($camposEquipamentos as $campo) {
                if (empty($data[$campo]) && !empty($dadosExistentes[$campo])) {
                    $data[$campo] = $dadosExistentes[$campo];
                }
            }
            
            // Preserva dados do cliente se não vieram
            $camposCliente = ['nome_cliente_salvo', 'email_salvo', 'telefone_salvo', 'celular_salvo'];
            foreach ($camposCliente as $campo) {
                if (empty($data[$campo]) && !empty($dadosExistentes[$campo])) {
                    $data[$campo] = $dadosExistentes[$campo];
                }
            }
        }
    }

    $id_salvo = $repo->salvar($data);

    echo json_encode([
        'success' => true,
        'message' => 'Rascunho salvo com sucesso!',
        'id_proposta' => $id_salvo,
        'is_new' => !$id_proposta
    ]);

} catch (Throwable $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao salvar rascunho: ' . $e->getMessage()
    ]);
}
