<?php

declare(strict_types=1);

namespace ProposalArchitect\Infrastructure;

use ProposalArchitect\Models\BaseProposalModel;

/**
 * BUILDER: Construtor Fluente de Modelos Customizados
 * Permite criar variações sem criar nova classe
 */
class CustomModelBuilder
{
    private array $sequence = [];
    private ?string $baseModel = null;
    private array $metadata = [];

    public function basedOn(string $baseModelClass): self
    {
        $this->baseModel = $baseModelClass;
        $base = new $baseModelClass();
        $this->sequence = $base->getStructuralSequence();
        return $this;
    }

    public function reorder(array $newOrder): self
    {
        // Validar se todos os blocos existem no modelo base
        $this->sequence = $newOrder;
        return $this;
    }

    public function omit(string ...$blockIds): self
    {
        $this->sequence = array_diff($this->sequence, $blockIds);
        return $this;
    }

    public function insertAfter(string $afterBlock, string $newBlock): self
    {
        $pos = array_search($afterBlock, $this->sequence);
        if ($pos !== false) {
            array_splice($this->sequence, $pos + 1, 0, [$newBlock]);
        }
        return $this;
    }

    public function withMetadata(array $metadata): self
    {
        $this->metadata = $metadata;
        return $this;
    }

    public function build(): BaseProposalModel
    {
        return new class($this->sequence, $this->metadata) extends BaseProposalModel {
            private array $customSequence;
            private array $customMetadata;

            public function __construct(array $sequence, array $metadata)
            {
                $this->customSequence = $sequence;
                $this->customMetadata = $metadata;
                parent::__construct();
            }

            protected function initializeBlockCatalog(): void
            {
                parent::initializeBlockCatalog();
                // Na implementação real, precisaríamos instanciar os blocos reais baseados nos IDs
                // Como isso é um mock/adaptação, vamos deixar vazio ou comentar que precisa de lógica de hidratação
                $this->structuralSequence = [];
            }

            public function getModelMetadata(): array
            {
                return $this->customMetadata;
            }
        };
    }
}
