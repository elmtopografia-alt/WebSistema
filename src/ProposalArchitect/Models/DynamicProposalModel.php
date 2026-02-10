<?php

namespace ProposalArchitect\Models;

/**
 * Modelo Dinâmico para carregar estruturas do banco de dados.
 * Substitui o uso de Classes Anônimas (incompatíveis com PHP 5.6).
 */
class DynamicProposalModel extends BaseProposalModel
{
    /**
     * @param BlockDefinition[] $blocks
     */
    public function __construct($blocks)
    {
        // Em PHP 5.6 chamamos o construtor pai se necessário, ou apenas definimos as propriedades
        // parent::__construct(); 

        // BaseProposalModel define $structuralSequence. Acessamos diretamente se for public/protected.
        $this->structuralSequence = $blocks;
    }

    public function getModelMetadata()
    {
        return [
            'name' => 'Modelo Dinâmico (Banco de Dados)',
            'version' => '1.0-db',
            'description' => 'Estrutura carregada dinamicamente da tabela proposal_block_templates'
        ];
    }
}
