<?php
// teste_loader_banco.php
// Versão usando a conexão oficial do sistema (db.php)

header('Content-Type: text/plain; charset=utf-8');
require_once 'vendor/autoload.php';
require_once 'db.php'; // Usa a conexão centralizada

use ProposalArchitect\Infrastructure\DatabaseStructureLoader;

try {
    echo "=== Teste de Carga via Banco de Dados (MySQLi) ===\n\n";

    // O arquivo db.php já cria a variável $conn (que é um objeto mysqli)
    if (!isset($conn) || !($conn instanceof mysqli)) {
        // Fallback se não existir, tenta pegar direto da classe Static
        $conn = Database::getProd();
    }

    // Teste de conexão básico
    if ($conn->connect_error) {
        throw new Exception("Falha na conexão: " . $conn->connect_error);
    }
    echo "Conexão OK com host: " . $conn->host_info . "\n";

    // 2. Instanciar Loader
    $loader = new DatabaseStructureLoader($conn);

    // 3. Carregar Modelo
    echo "Carregando blocos da tabela 'proposal_block_templates'...\n";
    $virtualModel = $loader->getVirtualModel();
    $metadata = $virtualModel->getModelMetadata();

    echo "Modelo Carregado: " . $metadata['name'] . "\n";
    echo "Descrição: " . $metadata['description'] . "\n\n";

    // 4. Listar Blocos
    $blocks = $virtualModel->structuralSequence;
    echo "Total de Blocos Encontrados: " . count($blocks) . "\n\n";

    echo "--- Lista de Blocos ---\n";
    foreach ($blocks as $block) {
        $req = $block->isRequired ? "[Obrigatório]" : "[Opcional]";
        echo str_pad($block->id, 20) . " | " . str_pad($block->category, 15) . " | $req " . $block->name . "\n";
    }

    echo "\n\n[SUCESSO] O sistema agora sabe ler do banco de dados! 🚀";
} catch (Exception $e) {
    echo "[ERRO] " . $e->getMessage();
}
