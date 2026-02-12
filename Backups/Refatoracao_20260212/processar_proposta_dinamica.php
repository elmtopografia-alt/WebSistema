<?php
header('Content-Type: application/json');
require_once 'vendor/autoload.php';

use ProposalArchitect\Models\CorporativoPremiumModel;
use ProposalArchitect\Infrastructure\StructureValidator;

try {
    // 1. Receber Payload JSON
    $inputJSON = file_get_contents('php://input');
    $input = json_decode($inputJSON, true);

    if (!$input) {
        throw new Exception("Payload vazio ou JSON inválido");
    }

    // 2. Instanciar Modelo e Componentes
    $model = new CorporativoPremiumModel();
    $validator = new StructureValidator();

    // 3. (Futuro) Hidratação de Dados
    // Aqui pegaríamos os campos do $input (ex: $input['cover']['client_name']) 
    // e injetaríamos no status dos blocos. 
    // Como nossa implementação atual de blocos é stateless (definição), 
    // vamos pular a hidratação e focar na validação estrutural padrão.

    // 4. Validação
    $violations = $validator->validate($model);

    // Simulação: Verificar campos obrigatórios básicos no payload
    $missingFields = [];
    $requiredFields = ['client_name', 'total_value']; // Simplificação para teste

    // Varredura simples no payload plano para achar chaves
    $flattenedInput = [];
    array_walk_recursive($input, function ($v, $k) use (&$flattenedInput) {
        $flattenedInput[$k] = $v;
    });

    foreach ($requiredFields as $field) {
        if (empty($flattenedInput[$field])) {
            $missingFields[] = "Campo obrigatório ausente: {$field}";
        }
    }

    if (count($violations) > 0 || count($missingFields) > 0) {
        echo json_encode([
            'success' => false,
            'message' => 'A proposta não pôde ser gerada devido a regras de validação.',
            'violations' => array_merge($violations, $missingFields)
        ]);
        exit;
    }

    // 5. Sucesso (Simulação de Geração)
    echo json_encode([
        'success' => true,
        'message' => 'Estrutura validada com sucesso! Proposta pronta para geração.',
        'data' => [
            'model' => $model->getModelMetadata()['name'],
            'received_blocks' => count($input),
            'timestamp' => date('Y-m-d H:i:s')
        ]
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro interno: ' . $e->getMessage()
    ]);
}
