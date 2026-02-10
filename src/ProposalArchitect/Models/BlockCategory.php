<?php

namespace ProposalArchitect\Models;

/**
 * Categorias funcionais dos blocos.
 * (Refatorado de Enum para Class Const para compatibilidade PHP 5.6)
 */
class BlockCategory
{
    const COMMERCIAL = 'commercial';
    const TECHNICAL = 'technical';
    const LEGAL = 'legal';
    const PRESENTATION = 'presentation';
    const FINANCIAL = 'financial';
}
