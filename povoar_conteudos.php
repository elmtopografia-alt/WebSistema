<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'db.php';
header('Content-Type: text/plain; charset=utf-8');

try {
    $conn = Database::getProd();
    if ($conn->connect_error) {
        die("Falha na conexão: " . $conn->connect_error);
    }

echo "=== POVOANDO VARIAÇÕES DE CONTEÚDO ===\n\n";

$variacoes = [
    // --- USUCAPIÃO (ID 11) ---
    [
        'block_slug' => 'executive_summary',
        'service_type_id' => 11,
        'content_text' => "A \${Empresa} é uma empresa prestadora de serviços técnicos especializada exclusivamente em Engenharia de Agrimensura e Topografia.\n\nNosso foco é a elaboração precisa das peças técnicas (plantas, memoriais e laudos) que servem como base física para o processo de Usucapião, garantindo segurança jurídica e precisão técnica.",
        'allowed_vars' => '["Empresa"]'
    ],
    [
        'block_slug' => 'technical_scope',
        'service_type_id' => 11,
        'content_text' => "O serviço consiste no levantamento topográfico completo para fins de Usucapião, incluindo:\n\n**ESTÁ INCLUÍDO:**\n- Medição em campo com equipamentos de precisão;\n- Confecção de plantas e memoriais descritivos conforme normas técnicas;\n- Transcrição dos dados dos confrontantes;\n- Emissão de ART (Anotação de Responsabilidade Técnica);\n- Arquivos digitais em PDF e DWG.\n\n**NÃO ESTÁ INCLUÍDO:**\n- Protocolização em cartórios ou prefeituras;\n- Assessoria jurídica ou advocatícia;\n- Coleta de assinaturas de confrontantes;\n- Garantia de titulação da propriedade.",
        'allowed_vars' => '[]'
    ],

    // --- DRONE (ID 19) ---
    [
        'block_slug' => 'executive_summary',
        'service_type_id' => 19,
        'content_text' => "A \${Empresa} apresenta esta proposta técnica visando a execução de levantamento topográfico planialtimétrico através de Aerofotogrametria com Drones (VANTs).\n\nDiferente de simples filmagens aéreas, este serviço trata-se de Engenharia de Precisão. O objetivo é gerar uma representação digital fiel do terreno, com coordenadas exatas, servindo de base legal e técnica para projetos e cálculos de volume.",
        'allowed_vars' => '["Empresa"]'
    ],
    [
        'block_slug' => 'methodology',
        'service_type_id' => 19,
        'content_text' => "Seguimos um rigoroso fluxo de trabalho dividido em 5 etapas:\n\n1. **Planejamento e Configuração de Voo**: Estudo da área via satélite e definição da grade de voo.\n2. **Apoio Terrestre**: Distribuição de pontos de controle coletados com GPS Geodésico RTK para precisão centimétrica.\n3. **Execução do Voo**: Captura autônoma de centenas de fotos em alta resolução.\n4. **Processamento Fotogramétrico**: Geração de nuvem de pontos densa e georreferenciamento milimétrico.\n5. **Vetorização e CAD**: Desenho técnico final e geração de curvas de nível.",
        'allowed_vars' => '[]'
    ],
    [
        'block_slug' => 'technical_scope',
        'service_type_id' => 19,
        'content_text' => "Os entregáveis finais deste serviço incluem:\n- Ortomosaico Georreferenciado (Foto em escala real);\n- Modelo Digital de Terreno (MDT) para terraplenagem;\n- Curvas de Nível em formato DWG/DXF;\n- Planta Topográfica Planialtimétrica em PDF;\n- Relatório de Processamento e Precisão;\n- ART de responsabilidade técnica.",
        'allowed_vars' => '[]'
    ]
];

foreach ($variacoes as $var) {
    $slug = $var['block_slug'];
    $st_id = $var['service_type_id'];
    $text = $conn->real_escape_string($var['content_text']);
    $vars = $var['allowed_vars'];

    // Upsert (se existir atualiza, se não insere)
    $check = $conn->query("SELECT id FROM proposal_content_variations WHERE block_slug = '$slug' AND service_type_id = $st_id");
    if ($check->num_rows > 0) {
        $row = $check->fetch_assoc();
        $id = $row['id'];
        $sql = "UPDATE proposal_content_variations SET content_text = '$text', is_active = 1 WHERE id = $id";
    } else {
        $sql = "INSERT INTO proposal_content_variations (block_slug, service_type_id, content_text, allowed_vars, is_active) 
                VALUES ('$slug', $st_id, '$text', '$vars', 1)";
    }

    if ($conn->query($sql)) {
        echo "✅ OK: $slug para Serviço $st_id\n";
    } else {
        echo "❌ ERRO em $slug: " . $conn->error . "\n";
    }
}

} catch (Exception $e) {
    echo "❌ EXCEÇÃO: " . $e->getMessage() . "\n";
}

echo "\n=== PROCESSO CONCLUÍDO ===\n";
?>
