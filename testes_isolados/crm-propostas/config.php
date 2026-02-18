<?php
/**
 * CONFIGURAÇÃO DO SISTEMA DE PROPOSTAS
 * GeoMetrópole - SGT Propostas
 */

// Evita acesso direto
if (!defined('SGT_PROPOSTAS')) {
    define('SGT_PROPOSTAS', true);
}

// ============================================
// CONFIGURAÇÕES DA EMPRESA
// ============================================
$CONFIG = [
    'empresa' => [
        'nome'      => 'GeoMetrópole Engenharia e Topografia Ltda.',
        'cnpj'      => 'XX.XXX.XXX/0001-XX',
        'endereco'  => 'Belo Horizonte - MG',
        'telefone'  => '(31) 3625-4769',
        'whatsapp'  => '(31) 99874-5889',
        'email'     => 'contato@geometropole.com.br',
        'logo'      => 'uploads/logos/logo_5_864a2f9992e071eb9c61ec92f1be253e.png',
    ],
    
    'banco' => [
        'nome'      => 'Itaú Unibanco S.A.',
        'agencia'   => '2934',
        'conta'     => '56789-0',
        'pix'       => 'financeiro@geometropolesp.com',
    ],
    
    // Cores personalizadas por cliente (opcional)
    'cores_personalizadas' => [
        // 'cliente_id' => '#cor_hex',
    ],
];

// ============================================
// MAPEAMENTO DE TEMAS
// ============================================
$TEMAS = [
    'classico' => [
        'nome'          => 'Clássico',
        'descricao'     => 'Marrom elegante, ideal para topografia tradicional',
        'arquivo_css'   => 'tema-classico.css',
        'fontes'        => ['Inter', 'Playfair Display'],
        'header_template' => 'header-classico.php',
    ],
    'drone' => [
        'nome'          => 'Drone/Tech',
        'descricao'     => 'Azul tecnológico, para mapeamento aéreo',
        'arquivo_css'   => 'tema-drone.css',
        'fontes'        => ['Inter', 'JetBrains Mono'],
        'header_template' => 'header-drone.php',
    ],
    'moderno' => [
        'nome'          => 'Moderno',
        'descricao'     => 'Minimalista preto, para arquitetura/corporativo',
        'arquivo_css'   => 'tema-moderno.css',
        'fontes'        => ['Inter'],
        'header_template' => 'header-moderno.php',
    ],
];

// ============================================
// MAPEAMENTO DE SERVIÇOS PARA TEMAS
// ============================================
$MAPA_SERVICOS = [
    // Serviços de drone → Tema drone
    'drone'                     => 'drone',
    'aerofotogrametria'         => 'drone',
    'vant'                      => 'drone',
    'mapeamento_aereo'          => 'drone',
    'levantamento_drone'        => 'drone',
    
    // Topografia tradicional → Tema clássico
    'topografia'                => 'classico',
    'topografia_tradicional'    => 'classico',
    'georreferenciamento'       => 'classico',
    'levantamento_topografico'  => 'classico',
    'demarcacao'                => 'classico',
    'nivelamento'               => 'classico',
    
    // Arquitetura e projetos → Tema moderno
    'arquitetura'               => 'moderno',
    'projeto_arquitetonico'     => 'moderno',
    'design'                    => 'moderno',
    'consultoria'               => 'moderno',
    
    // Padrão
    'default'                   => 'classico',
];

// ============================================
// FUNÇÕES AUXILIARES
// ============================================

/**
 * Detecta o tema adequado baseado no tipo de serviço
 */
function detectarTema(string $tipoServico): string {
    global $MAPA_SERVICOS;
    
    $tipo = strtolower(trim($tipoServico));
    $tipo = removerAcentos($tipo);
    $tipo = str_replace([' ', '-', '_'], '_', $tipo);
    
    return $MAPA_SERVICOS[$tipo] ?? $MAPA_SERVICOS['default'];
}

/**
 * Carrega o CSS do tema especificado
 */
function carregarTema(string $tema): array {
    global $TEMAS;
    
    if (!isset($TEMAS[$tema])) {
        $tema = 'classico';
    }
    
    $info = $TEMAS[$tema];
    $caminhoCSS = __DIR__ . "/assets/css/{$info['arquivo_css']}";
    
    // Se arquivo não existe, retorna CSS inline básico
    if (!file_exists($caminhoCSS)) {
        $info['css_inline'] = gerarCSSFallback($tema);
    } else {
        $info['css_inline'] = file_get_contents($caminhoCSS);
    }
    
    return $info;
}

/**
 * Gera CSS fallback se arquivo não existir
 */
function gerarCSSFallback(string $tema): string {
    $cores = [
        'classico' => ['#b45f06', '#d4a574', '#92400e', '#fdf8f3'],
        'drone'    => ['#0ea5e9', '#38bdf8', '#0369a1', '#f0f9ff'],
        'moderno'  => ['#18181b', '#71717a', '#09090b', '#fafafa'],
    ];
    
    $c = $cores[$tema] ?? $cores['classico'];
    
    return ":root {
        --brand: {$c[0]};
        --brand-light: {$c[1]};
        --brand-dark: {$c[2]};
        --brand-bg: {$c[3]};
        --text-primary: #1a1a2e;
        --text-secondary: #4a4a68;
        --text-muted: #6b7280;
        --border: #e5e7eb;
        --border-light: #f3f4f6;
        --surface: #f8fafc;
    }";
}

/**
 * Remove acentos de strings
 */
function removerAcentos(string $str): string {
    $mapa = [
        'á' => 'a', 'à' => 'a', 'ã' => 'a', 'â' => 'a', 'ä' => 'a',
        'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e',
        'í' => 'i', 'ì' => 'i', 'î' => 'i', 'ï' => 'i',
        'ó' => 'o', 'ò' => 'o', 'õ' => 'o', 'ô' => 'o', 'ö' => 'o',
        'ú' => 'u', 'ù' => 'u', 'û' => 'u', 'ü' => 'u',
        'ç' => 'c', 'ñ' => 'n',
        'Á' => 'A', 'À' => 'A', 'Ã' => 'A', 'Â' => 'A', 'Ä' => 'A',
        'É' => 'E', 'È' => 'E', 'Ê' => 'E', 'Ë' => 'E',
        'Í' => 'I', 'Ì' => 'I', 'Î' => 'I', 'Ï' => 'I',
        'Ó' => 'O', 'Ò' => 'O', 'Õ' => 'O', 'Ô' => 'O', 'Ö' => 'O',
        'Ú' => 'U', 'Ù' => 'U', 'Û' => 'U', 'Ü' => 'U',
        'Ç' => 'C', 'Ñ' => 'N',
    ];
    return strtr($str, $mapa);
}

/**
 * Formata valor para extenso (simplificado)
 */
function valorPorExtenso(float $valor): string {
    // Integração com sua biblioteca existente ou implementação simples
    // Por enquanto, retorna placeholder
    $extenso = [
        3500.00 => 'três mil e quinhentos reais',
        5000.00 => 'cinco mil reais',
        8500.50 => 'oito mil quinhentos reais e cinquenta centavos',
    ];
    
    return $extenso[$valor] ?? 'valor por extenso';
}

/**
 * Gera número da proposta formatado
 */
function gerarNumeroProposta(int $id, string $prefixo = 'GEOMETROPOLE'): string {
    $ano = date('Y');
    $seq = str_pad($id, 3, '0', STR_PAD_LEFT);
    $mes = date('m');
    return "{$prefixo}-{$ano}-{$mes}{$seq}";
}
