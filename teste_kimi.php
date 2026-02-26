<?php
require_once __DIR__ . '/session_validator.php';
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/PropostaRepository.php';

// Simula um POST do editor
$_POST = [
    'id_proposta' => $_GET['id'] ?? 0,
    'id_proposta_original' => $_GET['id'] ?? 0,
    'modelo_docx' => $_GET['modelo'] ?? 'PropostaDrone',
    'docx_bloco_0_content' => 'Teste de conteúdo',
    'ajax' => '1'
];

echo "<h2>Teste de Salvamento</h2>";
echo "<pre>";

try {
    $data = $_POST;
    $id_proposta = !empty($data['id_proposta']) ? intval($data['id_proposta']) : null;
    
    echo "ID Proposta: $id_proposta\n";
    echo "Modelo DOCX: " . ($data['modelo_docx'] ?? 'N/A') . "\n\n";
    
    // Processa blocos DOCX
    $blocosDocx = [];
    foreach ($data as $key => $value) {
        if (preg_match('/^docx_bloco_(\d+)_content$/', $key, $matches)) {
            $idx = intval($matches[1]);
            $blocosDocx[$idx] = [
                'tipo' => 'texto',
                'conteudo' => $value,
                'ordem' => $idx
            ];
        }
    }
    
    echo "Blocos encontrados: " . count($blocosDocx) . "\n";
    
    if (!empty($blocosDocx)) {
        ksort($blocosDocx);
        $data['docx_blocos_serializado'] = json_encode(array_values($blocosDocx));
        echo "JSON serializado: " . $data['docx_blocos_serializado'] . "\n\n";
    }
    
    // Tenta salvar
    echo "Criando PropostaRepository...\n";
    $repo = new PropostaRepository();
    
    echo "Chamando salvar()...\n";
    $id_salvo = $repo->salvar($data);
    
    echo "\n✅ SUCESSO! ID salvo: $id_salvo\n";
    
} catch (Throwable $e) {
    echo "\n❌ ERRO: " . $e->getMessage() . "\n";
    echo "Arquivo: " . $e->getFile() . "\n";
    echo "Linha: " . $e->getLine() . "\n";
    echo "\nStack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "</pre>";