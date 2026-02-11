<?php
/**
 * SUGERIR TIPO DE SERVIÇO AUTOMATICAMENTE
 * 
 * Analisa o título/serviço e sugere o tipo mais provável
 * baseado em palavras-chave
 */

require_once 'config.php';
require_once 'config/database.php';

function sugerirTipoServico($textoServico, $db) {
    // Garantir UTF-8
    $texto = mb_strtolower($textoServico, 'UTF-8');
    
    // Mapeamento de palavras-chave para tipos
    // IDs baseados no seed do Geometrpole
    $regras = [
        2 => [ // Aerofotogrametria
            'drone', 'aero', 'voo', 'imagens aéreas', 'fotogrametria',
            'rtk', 'pix4d', 'ortomosaico', 'mapeamento aéreo'
        ],
        1 => [ // Topografia tradicional
            'topografia', 'topográfico', 'estação total', 'nivelamento',
            'altimétrico', 'planialtimétrico', 'poligonal', 'triangulação'
        ],
        3 => [ // Georreferenciamento
            'car', 'sigtap', 'georreferenciamento', 'regularização',
            'certificação', 'demarcação', 'limites', 'confrontantes', 'incra'
        ],
        4 => [ // Cálculo de volume
            'volume', 'cubagem', 'estoque', 'mineração', 'aterro', 'escavação',
            'corte', 'aterro', 'balanceamento', 'massa'
        ],
        6 => [ // As Built
            'as built', 'as-built', 'pós-obra', 'executado', 'construído',
            'obra executada', 'memorial descritivo'
        ],
        8 => [ // Laudo técnico
            'laudo', 'parecer', 'vistoria', 'avaliação técnica', 'perícia'
        ],
        11 => [ // Batimetria
            'batimetria', 'hidrografia', 'lago', 'rio', 'represa', 'profundidade'
        ],
        10 => [ // Vegetação
            'vegetação', 'florestal', 'inventário', 'supressão', 'ambiental'
        ],
        12 => [ // Inspeção
            'inspeção', 'vistoria', 'ponte', 'torre', 'telhado', 'fachada', 'drone'
        ]
    ];
    
    // Pontuação para cada tipo
    $pontuacao = [];
    
    foreach ($regras as $tipoId => $palavras) {
        if (!isset($pontuacao[$tipoId])) $pontuacao[$tipoId] = 0;
        
        foreach ($palavras as $palavra) {
            // Verifica palavra exata
            if (mb_strpos($texto, $palavra, 0, 'UTF-8') !== false) {
                $pontuacao[$tipoId] += 10;
            }
        }
    }
    
    // Ordenar por pontuação
    arsort($pontuacao);
    
    // Retornar o tipo com maior pontuação (se tiver pontos)
    $tipoId = array_key_first($pontuacao);
    
    if ($pontuacao[$tipoId] > 0) {
        return $db->fetch("SELECT * FROM tipos_servico WHERE id = ?", [$tipoId]);
    }
    
    // Fallback: Retorna null ou um padrão
    return null;
}

// Endpoint AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['servico'])) {
    
    try {
        $db = new Database();
        
        $sugestao = sugerirTipoServico($_POST['servico'], $db);
        
        header('Content-Type: application/json');
        
        if ($sugestao) {
            echo json_encode([
                'success' => true,
                'sugestao' => $sugestao,
                'confianca' => 'alta' 
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Nenhuma sugestão clara'
            ]);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}
?>
