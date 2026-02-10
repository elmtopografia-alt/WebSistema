<?php

namespace ProposalArchitect\Infrastructure;

use ProposalArchitect\Models\BlockDefinition;
use ProposalArchitect\Models\BaseProposalModel;

/**
 * Carregador de Estrutura via Banco de Dados (Versão MySQLi).
 * Substitui os Modelos Estáticos (PHP) por Modelos Dinâmicos (SQL).
 */
class DatabaseStructureLoader
{
    /** @var \mysqli */
    private $mysqli;

    public function __construct(\mysqli $mysqli)
    {
        $this->mysqli = $mysqli;
    }

    /**
     * Carrega a estrutura completa do banco de dados.
     * 
     * @return BlockDefinition[] Array de blocos raiz
     */
    public function loadActiveStructure()
    {
        // 1. Buscar todos os templates ativos
        $sql = "SELECT * FROM proposal_block_templates WHERE is_active = 1 ORDER BY id ASC";
        $result = $this->mysqli->query($sql);

        if (!$result) {
            throw new \Exception("Erro ao carregar templates: " . $this->mysqli->error);
        }

        $blocks = [];

        // 2. Hidratar objetos (MySQLi way)
        while ($row = $result->fetch_assoc()) {
            $json = json_decode($row['default_content_json'], true);

            // Garantir defaults caso JSON falhe/esteja vazio
            $level = isset($json['level']) ? $json['level'] : 'root';
            // Conversão forçada de string "1"/"0" para boolean
            $isRequired = isset($json['is_required']) ? (bool)$json['is_required'] : true;
            $allowedVars = isset($json['allowed_vars']) ? $json['allowed_vars'] : [];

            $block = new BlockDefinition(
                $row['slug'],
                $row['name'],
                $level,
                $row['category'],
                $isRequired,
                $allowedVars,
                []
            );

            $blocks[] = $block;
        }

        $result->free(); // Liberar memória

        return $blocks;
    }

    /**
     * Converte os blocos carregados do banco em um "Modelo Virtual"
     * para ser usado pelo validador e visualizador existente.
     */
    /**
     * Converte os blocos carregados do banco em um "Modelo Virtual"
     * para ser usado pelo validador e visualizador existente.
     */
    public function getVirtualModel()
    {
        $blocks = $this->loadActiveStructure();

        // Retorna uma instância da classe concreta, compatível com PHP 5.6
        return new \ProposalArchitect\Models\DynamicProposalModel($blocks);
    }
}
