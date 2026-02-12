<?php
// prospector.php
declare(strict_types=1);
require 'SGT_ETHICS_BIBLE.php';
require 'conexao.php';

final class Prospector {
    private PDO $pdo;
    
    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }
    
    public function processar(array $dados): array {
        $site = SgtEthics::validarUrl($dados['site'] ?? '');
        if (!$site) return ['erro' => 'URL inválida'];
        
        // Duplicata?
        if ($this->existe($site)) return ['status' => 'ignorado', 'motivo' => 'duplicata'];
        
        // Canal ético?
        $canal = array_filter($dados['canais'] ?? [], fn($c) => SgtEthics::permitido($c));
        if (!$canal) return ['status' => 'rejeitado', 'motivo' => 'canal_nao_etico'];
        
        // Inserir
        $stmt = $this->pdo->prepare("
            INSERT INTO leads_prospeccao 
            (nome_empresa, site_origem, ramo_atuacao, whatsapp, metodo_captura)
            VALUES (?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            substr($dados['nome'], 0, 255),
            $site,
            substr($dados['ramo'] ?? '', 0, 100),
            preg_replace('/\D/', '', $dados['whatsapp'] ?? ''),
            array_values($canal)[0]
        ]);
        
        return ['status' => 'capturado', 'id' => (int)$this->pdo->lastInsertId()];
    }
    
    private function existe(string $site): bool {
        $stmt = $this->pdo->prepare("SELECT 1 FROM leads_prospeccao WHERE site_origem = ? LIMIT 1");
        $stmt->execute([$site]);
        return (bool)$stmt->fetch();
    }
}

// Uso (se chamado diretamente via POST para teste/integração)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $prospector = new Prospector($pdo);
    $resultado = $prospector->processar($_POST);
    header('Content-Type: application/json');
    echo json_encode($resultado);
}
