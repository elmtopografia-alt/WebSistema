<?php
/**
 * Resolve chaves {{xxx}} para valores reais
 * Busca em sessão, banco de dados ou gera automaticamente
 */

class ResolvedorChavesSistema
{
    private array $config;
    private $conn; 
    
    public function __construct(array $configChaves, $conexaoDB = null)
    {
        $this->config = $configChaves;
        $this->conn = $conexaoDB;
    }
    
    /**
     * Resolve todas as chaves de um texto/array
     */
    public function resolver(array $chavesNecessarias, int $id_usuario, array $dadosExtras = []): array
    {
        $resolvidas = [];
        
        foreach ($chavesNecessarias as $chave) {
            if (!isset($this->config[$chave])) {
                // Chave não é do sistema, mantém para preenchimento manual
                continue;
            }
            
            $config = $chaveConfig = $this->config[$chave];
            $resolvidas[$chave] = $this->obterValor($config, $id_usuario, $dadosExtras);
        }
        
        return $resolvidas;
    }
    
    private function obterValor(array $config, int $id_usuario, array $dadosExtras): mixed
    {
        switch ($config['fonte']) {
            case 'sessao':
                $campo = $config['campo'];
                return $_SESSION[$campo] ?? $config['padrao'] ?? "[{$config['label']}]";
                
            case 'banco':
                return $this->buscarDoBanco($config, $id_usuario);
                
            case 'sistema':
                return $config['valor'] ?? null;
                
            case 'auto':
                return $this->gerarAutomatico($config, $dadosExtras);
                
            case 'manual':
                $chaveManual = array_search($config, $this->config);
                return $dadosExtras[$chaveManual] ?? $config['padrao'] ?? "[{$config['label']}]";

            default:
                return "[{$config['label']}]";
        }
    }
    
    private function buscarDoBanco(array $config, int $id_usuario): mixed
    {
        if (!$this->conn) return "[Erro Conexão]";
        
        $tabela = $config['tabela'];
        $campo = $config['campo'];
        
        // Mapeamento de chave primária conforme a tabela
        $id_campo = ($tabela === 'Usuarios') ? 'id_usuario' : 'id_criador';
        
        $sql = "SELECT {$campo} FROM {$tabela} WHERE {$id_campo} = ? LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id_usuario);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            return $row[$campo];
        }
        
        return "[{$config['label']} não definido]";
    }
    
    private function gerarAutomatico(array $config, array $dadosExtras): string
    {
        $label = $config['label'];
        
        if ($label === 'Nº da Proposta') {
            return $dadosExtras['numero_proposta'] ?? "PROP-" . date('Ymd-His');
        }
        
        return "[Automático]";
    }
}
