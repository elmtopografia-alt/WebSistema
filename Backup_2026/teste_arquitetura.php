<?php

require_once 'vendor/autoload.php';

use ProposalArchitect\Infrastructure\StructureValidator;
use ProposalArchitect\Models\BaseProposalModel;
use ProposalArchitect\Models\BlockDefinition;
use ProposalArchitect\Models\BlockLevel;
use ProposalArchitect\Models\BlockCategory;

// 1. Criar um Modelo Mock para teste
class TestModel extends BaseProposalModel
{
    protected function initializeBlockCatalog(): void
    {
        $this->structuralSequence = [
            new BlockDefinition(
                'header',
                'Cabeçalho',
                BlockLevel::SECTION,
                BlockCategory::PRESENTATION,
                true
            ),
            // Violação intencional: Investment antes de Scope em modelo tecnico
            new BlockDefinition(
                'investment',
                'Investimento',
                BlockLevel::SECTION,
                BlockCategory::FINANCIAL,
                true
            ),
            new BlockDefinition(
                'technical_scope',
                'Escopo Técnico',
                BlockLevel::SECTION,
                BlockCategory::TECHNICAL,
                true
            ),
            // Violação intencional: Payment sem Banking
            new BlockDefinition(
                'payment_terms',
                'Condições de Pagamento',
                BlockLevel::SECTION,
                BlockCategory::FINANCIAL,
                true
            ),
        ];
    }

    public function getModelMetadata(): array
    {
        return ['codename' => 'tecnico_simples', 'name' => 'Modelo Teste'];
    }
}

echo "=== TESTE DE ARQUITETURA PROPOSAL ARCHITECT ===\n";

try {
    $model = new TestModel();
    echo "[OK] Modelo instanciado.\n";

    $validator = new StructureValidator();
    echo "[OK] Validador instanciado.\n";

    echo "Executando validacao...\n";
    $violations = $validator->validate($model);

    if (count($violations) > 0) {
        echo "[SUCESSO] Validador encontrou " . count($violations) . " violacoes esperadas:\n";
        foreach ($violations as $v) {
            echo " - $v\n";
        }
    } else {
        echo "[FALHA] Validador nao encontrou erros (deveria ter encontrado).\n";
    }
} catch (Throwable $e) {
    echo "[ERRO FATAL] " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
