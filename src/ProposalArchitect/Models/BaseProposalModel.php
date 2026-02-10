<?php

declare(strict_types=1);

namespace ProposalArchitect\Models;

/**
 * Base class for all proposal models.
 */
abstract class BaseProposalModel
{
    public $structuralSequence = [];

    public function __construct()
    {
        $this->initializeBlockCatalog();
    }

    /**
     * Initializes the default block catalog/sequence for this model.
     */
    protected function initializeBlockCatalog()
    {
        // To be implemented by concrete classes or populated differently
    }

    /**
     * Returns the ordered list of BlockDefinitions.
     * @return BlockDefinition[]
     */
    public function getOrderedBlocks()
    {
        return $this->structuralSequence;
    }

    /**
     * Returns metadata about the model (codename, friendly name, etc).
     */
    abstract public function getModelMetadata();

    /**
     * Returns identifiers of the structure sequence
     */
    public function getStructuralSequence()
    {
        return array_map(function ($b) {
            return $b->identifier;
        }, $this->structuralSequence);
    }
}
