<?php
/**
 * fix_content_variations.php
 * 
 * Script para popular CORRETAMENTE a tabela proposal_content_variations com:
 * 1. Texto Padrão (Topografia)
 * 2. Texto Drone (Aerofotogrametria)
 * 
 * Isso permite remover o código "japonês" (hardcoded) do editor_dinamico.php.
 */

require_once 'vendor/autoload.php';
require_once 'db.php';

// Habilita exibição de erros
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<pre>\n";
echo "=== ATUALIZAÇÃO DE CONTEÚDO (PADRÃO vs DRONE) ===\n\n";

$ambientes = [
    'PRODUCAO' => Database::getProd(),
    'DEMONSTRACAO' => Database::getDemo()
];

// --- TEXTOS PADRÃO (TOPOGRAFIA CONVENCIONAL) ---
$textosPadrao = [
    'apresentacao' => '<p>A <strong>${Empresa}</strong> é referência em serviços topográficos de alta precisão. Com vasta experiência e histórico sólido de projetos concluídos, garantimos segurança e exatidão em cada medição.</p><p>Nosso compromisso é assegurar a conformidade das medidas reais do terreno com a documentação legal e a realidade física.</p>',
    
    'metodologia' => '<h4>Levantamento de Campo</h4><p>Utilizaremos equipamentos de última geração (GPS RTK e/ou Estação Total) para a coleta de dados, garantindo precisão milimétrica nas coordenadas.</p><h4>Processamento de Dados</h4><p>Os dados coletados serão processados em softwares específicos (cálculos topográficos e ajustamento), gerando a nuvem de pontos e o desenho técnico fiel à realidade do terreno.</p><h4>Desenho Técnico</h4><p>Elaboração de plantas topográficas contendo curvas de nível, perímetro, edificações, árvores e demais interferências relevantes.</p>',
    
    'escopo' => '<h4>Detalhamento das Atividades:</h4><ul><li>Levantamento perimétrico do imóvel;</li><li>Coleta de pontos cotados para altimetria;</li><li>Cadastro de elementos físicos (guias, postes, árvores);</li><li>Processamento dos dados brutos e geração de desenhos técnicos.</li></ul>',
    
    'documentacao' => '<p>Serão entregues os seguintes documentos técnicos:</p><ul><li>Planta Topográfica (Formato PDF e DWG);</li><li>Memorial Descritivo do Perímetro;</li><li>ART (Anotação de Responsabilidade Técnica) registrada no CREA;</li><li>Relatório Fotográfico (se aplicável).</li></ul>',
    
    'consideracoes' => '<p>Agradecemos a oportunidade de apresentar nossa proposta. Temos a certeza de que nossa solução técnica fornecerá a base exata necessária para o desenvolvimento do seu projeto.</p><p>Permanecemos à disposição para esclarecimentos adicionais e negociação das condições comerciais.</p><br><p class="closing">Atenciosamente,</p>'
];

// --- TEXTOS DRONE (AEROFOTOGRAMETRIA) ---
$textosDrone = [
    'apresentacao' => '<p>A <strong>${Empresa}</strong> apresenta esta proposta técnica visando a execução de levantamento topográfico planialtimétrico através de <strong>Aerofotogrametria com Drones (VANTs)</strong>.</p><p>Diferente de simples filmagens aéreas, este serviço trata-se de <strong>Engenharia de Precisão</strong>. O objetivo é gerar uma representação digital fiel do terreno, com coordenadas exatas (Latitude, Longitude e Altitude), servindo de base legal e técnica para projetos de arquitetura, loteamentos, regularização fundiária e cálculos de volume.</p>',
    
    'metodologia' => '<h4>1. Planejamento e Configuração de Voo (Escritório)</h4><p>Antes de ir a campo, realizamos o estudo da área via satélite. Definimos a altura de voo para garantir a resolução desejada (GSD) e a área de abrangência. O drone segue uma "grade" programada via GPS, garantindo cobertura total do terreno.</p><h4>2. Apoio Terrestre - Pontos de Controle (Campo)</h4><p>Esta é a etapa que diferencia uma foto comum de um mapa topográfico. Nossa equipe distribui e pinta alvos no chão. As coordenadas exatas são coletadas com GPS Geodésico de Alta Precisão (RTK). Esses pontos servem como "âncoras" garantindo precisão centimétrica.</p><h4>3. Execução do Voo e Captura de Dados (Campo)</h4><p>Checklist de segurança: verificação de baterias, hélices, interferência magnética e autorizações DECEA. O drone percorre rota autônoma capturando centenas de fotos em ângulos verticais (nadir) e oblíquos.</p><h4>4. Processamento Fotogramétrico (Escritório)</h4><p>Utilizamos Workstations e softwares específicos: (1) Alinhamento das fotos, (2) Criação da Nuvem de Pontos Densa com milhões de pontos 3D, (3) Georreferenciamento com os Pontos de Controle para precisão milimétrica.</p><h4>5. Vetorização e Desenho Técnico (Escritório - CAD)</h4><p>Desenhista técnico utiliza o modelo 3D para "desenhar" o mapa final em CAD. Vetorização de guias, cercas, edificações, postes, árvores e geração das Curvas de Nível.</p>',
    
    'escopo' => '<h4>Escopo Aerofotogramétrico:</h4><ul><li>Planejamento de voo e autorizações (DECEA);</li><li>Implantação de Pontos de Apoio e Controle (GCPs) com RTK;</li><li>Voo automatizado para captura de imagens de alta resolução;</li><li>Processamento fotogramétrico (Nuvem de pontos, MDS, MDT);</li><li>Vetorização e geração de curvas de nível.</li></ul>',
    
    'documentacao' => '<p>Serão entregues os seguintes produtos técnicos:</p><ul><li><strong>Ortomosaico Georreferenciado (TIF/JPG):</strong> "Foto" gigante da área em escala real;</li><li><strong>MDT (Modelo Digital de Terreno):</strong> Representação 3D do solo para terraplenagem;</li><li><strong>Curvas de Nível (DWG/DXF):</strong> Arquivo CAD com topografia do terreno;</li><li><strong>Planta Topográfica Planialtimétrica (PDF):</strong> Mapa finalizado com legendas;</li><li><strong>Relatório de Processamento:</strong> Comprovação da precisão alcançada;</li><li><strong>ART (Anotação de Responsabilidade Técnica):</strong> Registro no CREA.</li></ul>',

    'consideracoes' => '<p>Agradecemos a oportunidade. A tecnologia de Drones oferece rapidez, detalhamento e um registro visual incomparável do seu terreno.</p><p>Permanecemos à disposição para esclarecimentos.</p><br><p class="closing">Atenciosamente,</p>'
];


foreach ($ambientes as $nomeAmbiente => $conn) {
    if (!$conn) {
        echo "⚠️  PULANDO {$nomeAmbiente}: Conexão falhou.\n";
        continue;
    }

    echo "-------------------------------------------------------\n";
    echo "🔄 PROCESSANDO AMBIENTE: {$nomeAmbiente}\n";
    echo "-------------------------------------------------------\n";

    // Prepara queries para inserir/atualizar
    // Remove variações antigas para evitar duplicidade
    $conn->query("DELETE FROM proposal_content_variations WHERE variation_name IN ('default', 'Drone')");
    
    $stmt = $conn->prepare("INSERT INTO proposal_content_variations (block_slug, variation_name, content_text, is_default) VALUES (?, ?, ?, ?)");

    // 1. Inserir TEXTOS PADRÃO (is_default = 1)
    echo "  > Inserindo Textos Padrão (Topografia)...\n";
    foreach ($textosPadrao as $slug => $texto) {
        $variationName = 'default';
        $isDefault = 1;
        $stmt->bind_param("sssi", $slug, $variationName, $texto, $isDefault);
        if (!$stmt->execute()) {
             echo "    ❌ Erro ao inserir padrão '$slug': " . $stmt->error . "\n";
        }
    }

    // 2. Inserir TEXTOS DRONE (is_default = 0)
    echo "  > Inserindo Textos Drone (Aerofotogrametria)...\n";
    foreach ($textosDrone as $slug => $texto) {
        $variationName = 'Drone';
        $isDefault = 0;
        $stmt->bind_param("sssi", $slug, $variationName, $texto, $isDefault);
        if (!$stmt->execute()) {
             echo "    ❌ Erro ao inserir drone '$slug': " . $stmt->error . "\n";
        }
    }

    $stmt->close();
    echo "  ✅ Concluído para {$nomeAmbiente}.\n\n";
}

echo "🏁 FIM DO SCRIPT.\n";
echo "</pre>";
