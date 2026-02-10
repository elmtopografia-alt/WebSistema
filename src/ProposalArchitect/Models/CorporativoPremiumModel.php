<?php

declare(strict_types=1);

namespace ProposalArchitect\Models;

class CorporativoPremiumModel extends BaseProposalModel
{
    public function getModelMetadata(): array
    {
        return [
            'codename' => 'corporativo_premium',
            'name' => 'Proposta Corporativa Premium',
            'description' => 'Modelo completo com ênfase em autoridade e segurança jurídica.'
        ];
    }

    protected function initializeBlockCatalog(): void
    {
        $this->structuralSequence = [
            // 1. Apresentação Impactante
            new BlockDefinition(
                'cover',
                'Capa Personalizada',
                BlockLevel::ROOT,
                BlockCategory::PRESENTATION,
                true,
                ['client_name', 'project_name', 'date']
            ),
            new BlockDefinition(
                'executive_summary',
                'Resumo Executivo',
                BlockLevel::SECTION,
                BlockCategory::PRESENTATION,
                true,
                ['problem_summary', 'solution_highlight']
            ),

            // 2. Contexto Técnico (Autoridade)
            new BlockDefinition(
                'technical_scope',
                'Escopo Técnico Detalhado',
                BlockLevel::SECTION,
                BlockCategory::TECHNICAL,
                true,
                ['services_list', 'methodology_steps'],
                [
                    new BlockDefinition('field_work', 'Levantamento de Campo', BlockLevel::DETAIL, BlockCategory::TECHNICAL),
                    new BlockDefinition('office_work', 'Processamento em Escritório', BlockLevel::DETAIL, BlockCategory::TECHNICAL),
                    new BlockDefinition('deliverables', 'Entregáveis Finais', BlockLevel::DETAIL, BlockCategory::TECHNICAL, true)
                ]
            ),

            // 3. Segurança e Confiança
            new BlockDefinition(
                'methodology',
                'Metodologia e Qualidade',
                BlockLevel::SECTION,
                BlockCategory::TECHNICAL,
                false, // Opcional para projetos simples, mas aqui está
                ['quality_standards'],
                [
                    new BlockDefinition('equipment', 'Equipamentos de Alta Precisão', BlockLevel::DETAIL, BlockCategory::TECHNICAL),
                    new BlockDefinition('team', 'Equipe Técnica', BlockLevel::DETAIL, BlockCategory::TECHNICAL)
                ]
            ),

            // 4. Investimento (A parte "dolorosa" vem depois do valor percebido)
            new BlockDefinition(
                'investment',
                'Investimento e Condições',
                BlockLevel::SECTION,
                BlockCategory::FINANCIAL,
                true,
                ['total_value', 'payment_conditions'],
                [
                    new BlockDefinition('cronograma_fisico_financeiro', 'Cronograma de Desembolso', BlockLevel::DETAIL, BlockCategory::FINANCIAL),
                    new BlockDefinition('banking_data', 'Dados Bancários', BlockLevel::DETAIL, BlockCategory::FINANCIAL, true)
                ]
            ),

            // 5. Fechamento
            new BlockDefinition(
                'legal_terms',
                'Termos e Validade',
                BlockLevel::SECTION,
                BlockCategory::LEGAL,
                true,
                ['validity_days']
            ),
            new BlockDefinition(
                'acceptance',
                'Aceite Digital',
                BlockLevel::ROOT,
                BlockCategory::LEGAL,
                true,
                ['client_signature']
            )
        ];
    }
}
