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
     * Carrega a estrutura completa do banco de dados, incluindo conteúdos específicos.
     * Prioriza service_type_blocks (especializado) e usa proposal_block_templates como fallback.
     * 
     * @param int $serviceTypeId ID da modalidade da proposta
     * @return BlockDefinition[] Array de blocos raiz
     */
    public function loadActiveStructure($serviceTypeId = 0)
    {
        $serviceTypeId = intval($serviceTypeId);
        $blocks = [];

        // 1. Tentar carregar da tabela especializada
        if ($serviceTypeId > 0) {
            $sql = "SELECT * FROM service_type_blocks 
                    WHERE service_type_id = $serviceTypeId AND is_active = 1 
                    ORDER BY display_order ASC";
            $result = $this->mysqli->query($sql);
            
            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $allowedVars = !empty($row['allowed_vars']) ? json_decode($row['allowed_vars'], true) : [];
                    if (!is_array($allowedVars)) $allowedVars = [];

                    // Mapeamento de categoria -> level sugerido (Heurística)
                    $level = ($row['category'] == 'layout') ? 'root' : 'section';

                    $block = new BlockDefinition(
                        $row['block_slug'],
                        $row['block_title'],
                        $level,
                        $row['category'],
                        (bool)$row['is_required'],
                        $allowedVars,
                        $row['default_content'],
                        []
                    );
                    $blocks[] = $block;
                }
                $result->free();
                return $blocks;
            }
        }

        // 2. Fallback para a tabela genérica (original)
        $sql = "SELECT * FROM proposal_block_templates WHERE is_active = 1 ORDER BY `order` ASC";
        $result = $this->mysqli->query($sql);

        if (!$result) {
            throw new \Exception("Erro ao carregar templates: " . $this->mysqli->error);
        }

        while ($row = $result->fetch_assoc()) {
            $json = json_decode($row['default_content_json'], true);
            $level = isset($json['level']) ? $json['level'] : 'root';
            $isRequired = isset($json['is_required']) ? (bool)$json['is_required'] : true;
            $allowedVars = isset($json['allowed_vars']) ? $json['allowed_vars'] : [];

            $block = new BlockDefinition(
                $row['slug'],
                $row['name'],
                $level,
                $row['category'],
                $isRequired,
                $allowedVars,
                '', // Sem conteúdo padrão nesta tabela
                []
            );
            $blocks[] = $block;
        }
        $result->free();

        return $blocks;
    }

    /**
     * Converte os blocos carregados do banco em um "Modelo Virtual"
     */
    public function getVirtualModel($serviceTypeId = 0)
    {
        $blocks = $this->loadActiveStructure($serviceTypeId);
        return new \ProposalArchitect\Models\DynamicProposalModel($blocks);
    }

    /**
     * Cria um modelo virtual a partir de um modelo DOCX gerado
     */
    public function getVirtualModelFromDocx($docxModelId)
    {
        $blocks = $this->loadFromDocxModel($docxModelId);
        return new \ProposalArchitect\Models\DynamicProposalModel($blocks);
    }

    /**
     * Carrega a estrutura a partir de um arquivo de modelo DOCX gerado
     */
    public function loadFromDocxModel($docxModelId)
    {
        $arquivo = __DIR__ . '/../../../modelos_gerados/Modelo' . $docxModelId . '.php';
        if (!file_exists($arquivo)) {
            return [];
        }

        require_once $arquivo;
        $classe = "\\SGT\\Propostas\\Modelo" . $docxModelId;
        if (!class_exists($classe)) {
            return [];
        }

        $instancia = new $classe();
        
        // Usamos reflexão ou um helper para acessar o array privado $blocos
        // Como estamos em PHP 5.6/7/8, podemos usar um truque ou mudar a visibilidade no modelo gerado.
        // Mas o ideal é que o modelo gerado tenha um método public getBlocks().
        
        $blocks = [];
        
        // Tenta pegar via reflexão se for privado
        $refl = new \ReflectionClass($classe);
        if ($refl->hasProperty('blocos')) {
            $prop = $refl->getProperty('blocos');
            $prop->setAccessible(true);
            $rawBlocks = $prop->getValue($instancia);
            
            foreach ($rawBlocks as $index => $b) {
                // Mapeamento simplificado para BlockDefinition
                $slug = (isset($b['subtipo']) ? $b['subtipo'] : 'bloco') . '_' . $index;
                $title = isset($b['conteudo']) ? substr(strip_tags($b['conteudo']), 0, 50) . '...' : "Bloco $index";
                
                // Se for título, usa o conteúdo como título
                if (isset($b['subtipo']) && $b['subtipo'] == 'titulo') {
                    $title = strip_tags($b['conteudo']);
                }

                $blocks[] = new BlockDefinition(
                    $slug,
                    $title,
                    'section', // DOCX parser atual é flat
                    isset($b['subtipo']) ? $b['subtipo'] : 'general',
                    true,
                    isset($b['variaveis']) ? $b['variaveis'] : [],
                    isset($b['conteudo']) ? $b['conteudo'] : ''
                );
            }
        }

        return $blocks;
    }
}
