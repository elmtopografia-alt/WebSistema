<?php
/**
 * PROPOSTA REPOSITORY v3.0
 * 
 * Faz a ponte entre:
 * - Formulário (chaves v3.0)
 * - Banco de dados (colunas legado)
 */

class PropostaRepositoryV3 {
    
    private $pdo;
    private $mapeamento;
    
    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
        $this->mapeamento = require __DIR__ . '/../config/mapeamento_v3.php';
    }
    
    /**
     * Salva proposta recebendo dados em formato v3.0
     * Mas persiste em colunas legado do banco
     */
    public function salvar(array $dadosV3) {
        
        // Traduzir v3.0 → banco legado
        $dadosBanco = $this->traduzirParaBanco($dadosV3);
        
        $sql = "INSERT INTO propostas (
            nome_cliente_salvo,
            email_salvo,
            whatsapp_salvo,
            telefone_salvo,
            empresa,
            cnpj,
            cidade,
            numero_proposta,
            data_emissao,
            endereco_obra,
            cidade_obra,
            estado_obra,
            finalidade,
            valor_final_proposta,
            valorextenso,
            mobilizacao_valor,
            restante_valor,
            banco,
            agencia,
            conta,
            created_at
        ) VALUES (
            :nome_cliente_salvo,
            :email_salvo,
            :whatsapp_salvo,
            :telefone_salvo,
            :empresa,
            :cnpj,
            :cidade,
            :numero_proposta,
            :data_emissao,
            :endereco_obra,
            :cidade_obra,
            :estado_obra,
            :finalidade,
            :valor_final_proposta,
            :valorextenso,
            :mobilizacao_valor,
            :restante_valor,
            :banco,
            :agencia,
            :conta,
            NOW()
        )";
        
        $stmt = $this->pdo->prepare($sql);
        
        // Bind dos valores traduzidos
        foreach ($dadosBanco as $coluna => $valor) {
            $stmt->bindValue(':' . $coluna, $valor);
        }
        
        $stmt->execute();
        return $this->pdo->lastInsertId();
    }
    
    /**
     * Busca proposta e retorna em formato v3.0
     */
    public function buscarPorId($id) {
        $sql = "SELECT * FROM propostas WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        $dadosBanco = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$dadosBanco) {
            return null;
        }
        
        // Traduzir banco legado → v3.0
        return $this->traduzirParaV3($dadosBanco);
    }
    
    /**
     * Traduz array v3.0 → colunas banco legado
     */
    private function traduzirParaBanco(array $v3) {
        $banco = [];
        
        foreach ($this->mapeamento as $chaveV3 => $config) {
            // Pula campos calculados (não persistem)
            if ($config['banco'] === null) {
                continue;
            }
            
            $colunaLegado = $config['banco'];
            $valor = $v3[$chaveV3] ?? null;
            
            $banco[$colunaLegado] = $valor;
        }
        
        return $banco;
    }
    
    /**
     * Traduz colunas banco legado → array v3.0
     */
    private function traduzirParaV3(array $banco) {
        $v3 = [];
        
        foreach ($this->mapeamento as $chaveV3 => $config) {
            if ($config['banco'] === null) {
                // Campo calculado - será processado depois
                $v3[$chaveV3] = null;
                continue;
            }
            
            $colunaLegado = $config['banco'];
            $v3[$chaveV3] = $banco[$colunaLegado] ?? null;
        }
        
        // Processar campos calculados
        if (isset($v3['data_emissao'])) {
            $v3['data_emissao_extenso'] = $this->calcularDataExtenso($v3['data_emissao']);
        }
        
        return $v3;
    }
    
    private function calcularDataExtenso($data) {
        $meses = [
            'janeiro', 'fevereiro', 'março', 'abril', 'maio', 'junho',
            'julho', 'agosto', 'setembro', 'outubro', 'novembro', 'dezembro'
        ];
        
        $timestamp = strtotime((string)$data);
        if (!$timestamp) $timestamp = time();
        
        $dia = date('j', $timestamp);
        $mes = $meses[date('n', $timestamp) - 1];
        $ano = date('Y', $timestamp);
        
        return "{$dia} de {$mes} de {$ano}";
    }
}
