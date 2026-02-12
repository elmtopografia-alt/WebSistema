<?php

declare(strict_types=1);

namespace ProposalArchitect\Infrastructure;

use ProposalArchitect\Models\BaseProposalModel;

/**
 * SERVIÇO: Validador Estrutural Avançado
 * Regras de negócio para estrutura de propostas
 */
class StructureValidator
{
    private $rules = [];

    public function __construct()
    {
        $this->initializeRules();
    }

    private function initializeRules(): void
    {
        // Regra: Bloco comercial não pode vir antes de contexto técnico em modelos técnicos
        $this->rules[] = function (BaseProposalModel $model) {
            $sequence = $model->getOrderedBlocks();
            $ids = array_map(function($b) { return $b->identifier; }, $sequence);

            $investmentPos = array_search('investment', $ids);
            $scopePos = array_search('technical_scope', $ids);

            if ($investmentPos !== false && $scopePos !== false && $investmentPos < $scopePos) {
                // Permitido apenas em modelos comerciais explícitos
                if (!in_array($model->getModelMetadata()['codename'], ['comercial_persuasivo', 'corporativo_premium'])) {
                    return "Bloco 'investment' antecede 'technical_scope' sem estratégia comercial definida";
                }
            }
            return null;
        };

        // Regra: Dados bancários devem estar presentes se houver condição de pagamento
        $this->rules[] = function (BaseProposalModel $model) {
            $sequence = $model->getOrderedBlocks();
            $ids = array_map(function($b) { return $b->identifier; }, $sequence);

            if (in_array('payment_terms', $ids) && !in_array('banking', $ids)) {
                return "Condições de pagamento presentes sem dados bancários";
            }
            return null;
        };

        // Regra: Metodologia deve ter pelo menos 3 sub-blocos se presente
        $this->rules[] = function (BaseProposalModel $model) {
            foreach ($model->getOrderedBlocks() as $block) {
                if ($block->identifier === 'methodology') {
                    if (empty($block->subBlocks) || count($block->subBlocks) < 3) {
                        return "Metodologia incompleta: requer 3 etapas técnicas mínimas";
                    }
                }
            }
            return null;
        };
    }

    public function validate(BaseProposalModel $model): array
    {
        $violations = [];

        foreach ($this->rules as $rule) {
            $result = $rule($model);
            if ($result !== null) {
                $violations[] = $result;
            }
        }

        return $violations;
    }
}
