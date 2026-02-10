<?php
// fix_all.php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once 'config.php';
require_once 'db.php';

echo "<!DOCTYPE html>
<html lang='pt-BR'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Reparação Geral</title>
    <script src='https://cdn.tailwindcss.com'></script>
</head>
<body class='bg-slate-900 text-slate-100 flex items-center justify-center min-h-screen p-4'>
    <div class='max-w-xl w-full bg-slate-800 rounded-xl shadow-2xl p-8 border border-slate-700'>
        <h1 class='text-2xl font-bold mb-6 flex items-center gap-2'>
            🛠️ Atualização do Sistema
        </h1>
        <div class='space-y-4'>
";

try {
    $conn = Database::getProd();
    
    // 1. CORREÇÃO DA COLUNA empresa_cliente_salvo
    echo "<div class='p-4 bg-slate-900/50 rounded-lg border border-slate-700'>";
    echo "<h3 class='font-bold text-blue-400 mb-2'>1. Verificando Tabela Propostas...</h3>";
    
    $result = $conn->query("SHOW COLUMNS FROM Propostas LIKE 'empresa_cliente_salvo'");
    if ($result->num_rows == 0) {
        $sql = "ALTER TABLE Propostas ADD COLUMN empresa_cliente_salvo VARCHAR(255) DEFAULT NULL AFTER nome_cliente_salvo";
        if ($conn->query($sql)) {
            echo "<p class='text-emerald-400'>✅ Coluna 'empresa_cliente_salvo' Criada!</p>";
        } else {
             echo "<p class='text-rose-400'>❌ Erro ao criar coluna: " . $conn->error . "</p>";
        }
    } else {
         echo "<p class='text-blue-300'>ℹ️ Coluna 'empresa_cliente_salvo' já existe.</p>";
    }
    echo "</div>";

    // 1.2. ADICIONANDO COLUNAS DO DRONE (Novos Campos)
    echo "<div class='p-4 bg-slate-900/50 rounded-lg border border-slate-700 mt-4'>";
    echo "<h3 class='font-bold text-blue-400 mb-2'>1.2. Criando Colunas para Dados do Drone...</h3>";
    
    $cols = [
        'tipo_terreno' => 'VARCHAR(50)',
        'cobertura_vegetal' => 'VARCHAR(50)',
        'acesso_local' => 'VARCHAR(50)',
        'restricoes_aereas' => 'VARCHAR(255)',
        'coordenadas_gps' => 'VARCHAR(100)'
    ];

    foreach ($cols as $col => $type) {
        $check = $conn->query("SHOW COLUMNS FROM Propostas LIKE '$col'");
        if ($check->num_rows == 0) {
            if ($conn->query("ALTER TABLE Propostas ADD COLUMN $col $type DEFAULT NULL")) {
                 echo "<p class='text-emerald-400'>✅ Coluna '$col' criada.</p>";
            } else {
                 echo "<p class='text-rose-400'>❌ Erro ao criar '$col': " . $conn->error . "</p>";
            }
        } else {
             echo "<p class='text-blue-300'>ℹ️ Coluna '$col' já existe.</p>";
        }
    }
    echo "</div>";

    // 2. CORREÇÃO DO TEXTO 'Câmera Fotográfica'
    echo "<div class='p-4 bg-slate-900/50 rounded-lg border border-slate-700'>";
    echo "<h3 class='font-bold text-blue-400 mb-2'>2. Limpando Texto de Equipamentos...</h3>";
    
    $sql = "SELECT id, default_content FROM service_type_blocks WHERE block_slug LIKE 'equipamentos%' AND default_content LIKE '%Câmera Fotográfica%'";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        $count = 0;
        while($row = $result->fetch_assoc()) {
            $id = $row['id'];
            $content = $row['default_content'];
            $newContent = preg_replace('/<li[^>]*>\s*<strong[^>]*>Câmera Fotográfica:<\/strong>[^<]*<\/li>/i', '', $content);
            if ($newContent === $content) $newContent = str_replace('Câmera Fotográfica', '', $content);
            
            $stmt = $conn->prepare("UPDATE service_type_blocks SET default_content = ? WHERE id = ?");
            $stmt->bind_param("si", $newContent, $id);
            $stmt->execute();
            $count++;
        }
        echo "<p class='text-emerald-400'>✅ $count blocos corrigidos (Câmera removida).</p>";
    } else {
        echo "<p class='text-blue-300'>ℹ️ Nenhum texto inválido encontrado.</p>";
    }
    echo "</div>";

    // 3. RESTAURAÇÃO DE PLACEHOLDERS (Correção da "Chave que sumiu")
    echo "<div class='p-4 bg-slate-900/50 rounded-lg border border-slate-700'>";
    echo "<h3 class='font-bold text-blue-400 mb-2'>3. Restaurando Placeholders de Equipamentos...</h3>";

    // Template Padrão (Serviços 11-18, 20-25)
    $templatePadrao = '<ul>
<li><strong>Estação Total:</strong> ${Estacao_Total};</li>
<li><strong>GPS/GNSS:</strong> ${GPS};</li>
<li><strong>Nível:</strong> Nível óptico ou digital;</li>
<li><strong>Veículo:</strong> ${Veiculo}.</li>
</ul>';

    // Template Drone (Serviço 19)
    $templateDrone = '<ul>
<li><strong>Drone VANT:</strong> ${Drone} (câmera de alta resolução, sistema RTK para precisão de posição do drone);</li>
<li><strong>GPS de Apoio (RTK):</strong> ${GPS} (coleta de GCPs com precisão centimétrica horizontal e vertical);</li>
<li><strong>Estação Total:</strong> ${Estacao_Total} (verificação de pontos de controle e coleta de detalhes);</li>
<li><strong>Software Fotogramétrico:</strong> Licenciado para processamento de imagens;</li>
<li><strong>Workstation:</strong> Computador de alta performance para processamento;</li>
<li><strong>Veículo:</strong> ${Veiculo} (transporte da equipe e equipamentos).</li>
</ul>';

    // Atualiza Padrão
    $stmt = $conn->prepare("UPDATE service_type_blocks SET default_content = ? WHERE block_slug = 'equipamentos' AND service_type_id != 19");
    $stmt->bind_param("s", $templatePadrao);
    if ($stmt->execute()) {
        echo "<p class='text-emerald-400'>✅ Placeholders restaurados para serviços padrão (Afetados: " . $stmt->affected_rows . ")</p>";
    }

    // Atualiza Drone
    $stmtDrone = $conn->prepare("UPDATE service_type_blocks SET default_content = ? WHERE block_slug = 'equipamentos' AND service_type_id = 19");
    $stmtDrone->bind_param("s", $templateDrone);
    if ($stmtDrone->execute()) {
        echo "<p class='text-emerald-400'>✅ Placeholders restaurados para serviço Drone (Afetados: " . $stmtDrone->affected_rows . ")</p>";
    }

    echo "</div>";

    echo "</div>";

    // 4. ATUALIZAÇÃO REVOLUCIONÁRIA DO DRONE (Solução do Usuário + Correção de Ordem)
    echo "<div class='p-4 bg-slate-900/50 rounded-lg border border-slate-700'>";
    echo "<h3 class='font-bold text-blue-400 mb-2'>4. Aplicando Solução Avançada para Drone (Serviço 19)...</h3>";

    // --- PARTE A: REMOÇÃO E REORDENAÇÃO GERAL ---
    // Remove dados_cliente antigos (para evitar duplicatas ou versões velhas)
    $conn->query("DELETE FROM service_type_blocks WHERE block_slug = 'dados_cliente'");
    $conn->query("DELETE FROM service_type_blocks WHERE block_slug = 'local_obra'"); // Remove pois foi unificado

    // Garante ordem correta para Apresentação (Move para 2)
    $conn->query("UPDATE service_type_blocks SET display_order = 2 WHERE block_slug = 'apresentacao'");
    $conn->query("UPDATE service_type_blocks SET display_order = 0 WHERE block_slug = 'cabecalho'");

    // --- PARTE B: INSERÇÃO DADOS CLIENTE (GERAL - Serviços 11-18, 20-25) ---
    // Cria template padrão
    $defaultClientBlock = '<div class="dados-cliente">
<h4>DADOS DO CLIENTE</h4>
<p><strong>Nome:</strong> ${ClienteNome}</p>
<p><strong>E-mail:</strong> ${ClienteEmail}</p>
<p><strong>Local da Obra:</strong></p>
<p>Endereço: ${ClienteEndereco}</p>
<p>Bairro: ${ClienteBairro}</p>
<p>Cidade/UF: ${ClienteCidadeUF}</p>
<p><strong>Área Estimada:</strong> ${area_obra}</p>
</div>';
    $defaultClientVars = '["ClienteNome","ClienteEmail","ClienteEndereco","ClienteBairro","ClienteCidadeUF","area_obra"]';

    $stmtDefault = $conn->prepare("INSERT INTO service_type_blocks (service_type_id, block_slug, block_title, category, display_order, is_required, default_content, allowed_vars, is_active) VALUES (?, 'dados_cliente', 'Dados do Cliente', 'layout', 1, 1, ?, ?, 1)");
    
    $ids = [11,12,13,14,15,16,17,18,20,21,22,23,24,25]; // IDs padrão (sem Drone)
    foreach($ids as $sid) {
        $stmtDefault->bind_param("iss", $sid, $defaultClientBlock, $defaultClientVars);
        $stmtDefault->execute();
    }
    echo "<p class='text-emerald-400'>✅ Dados do Cliente inseridos para serviços padrão.</p>";


    // --- PARTE C: INSERÇÃO DADOS CLIENTE DRONE (Serviço 19) ---
    $contentDadosCliente = '<div class="dados-cliente-drone">
<h4>DADOS DO CLIENTE</h4>
<p><strong>Nome:</strong> ${ClienteNome}</p>
<p><strong>E-mail:</strong> ${ClienteEmail}</p>
<p><strong>Telefone:</strong> ${ClienteTelefone}</p>

<h4>LOCAL DA OBRA</h4>
<p>Endereço: ${ClienteEndereco}</p>
<p>Bairro: ${ClienteBairro}</p>
<p>Cidade/UF: ${ClienteCidadeUF}</p>
<p><strong>Coordenadas Aproximadas:</strong> ${CoordenadasGPS}</p>

<h4>CARACTERÍSTICAS DA ÁREA</h4>
<p><strong>Área Estimada:</strong> ${area_obra}</p>
<p><strong>Tipo de Terreno:</strong> ${TipoTerreno} (Plano/Acidentado/Misto)</p>
<p><strong>Cobertura Vegetal:</strong> ${CoberturaVegetal} (Baixa/Média/Alta/Densa)</p>
<p><strong>Acesso ao Local:</strong> ${AcessoLocal} (Fácil/Moderado/Difícil)</p>
<p><strong>Restrições Aéreas:</strong> ${RestricoesAereas} (Próximo a aeroporto, linhas de alta tensão, etc)</p>
</div>';
    
    $varsDadosCliente = '["ClienteNome","ClienteEmail","ClienteTelefone","ClienteEndereco","ClienteBairro","ClienteCidadeUF","CoordenadasGPS","area_obra","TipoTerreno","CoberturaVegetal","AcessoLocal","RestricoesAereas"]';

    // Insere DRONE (Ordem 1)
    $stmtDrone = $conn->prepare("INSERT INTO service_type_blocks (service_type_id, block_slug, block_title, category, display_order, is_required, default_content, allowed_vars, is_active) VALUES (19, 'dados_cliente', 'Dados do Cliente e da Área', 'layout', 1, 1, ?, ?, 1)");
    $stmtDrone->bind_param("ss", $contentDadosCliente, $varsDadosCliente);
    $stmtDrone->execute();
    echo "<p class='text-emerald-400'>✅ Dados do Cliente DRONE inseridos com sucesso.</p>";


    // --- PARTE D: ATUALIZAÇÕES DOS BLOCOS EXISTENTES DO DRONE ---

    // 1. ESCOPO
    $contentEscopo = '<h4>Levantamento Fotogramétrico com Drone</h4>
<p>Execução de levantamento na área de <strong>${area_obra}</strong>, localizada em <strong>${ClienteCidadeUF}</strong>, com as seguintes características:</p>
<ul>
<li><strong>Tipo de Terreno:</strong> ${TipoTerreno};</li>
<li><strong>Cobertura Vegetal:</strong> ${CoberturaVegetal};</li>
<li><strong>Acesso:</strong> ${AcessoLocal};</li>
<li><strong>Restrições:</strong> ${RestricoesAereas}.</li>
</ul>
<p>O serviço compreende:</p>
<ul>
<li>Planejamento de voo e estudo de viabilidade aérea (consulta DECEA para ${ClienteCidadeUF});</li>
<li>Implantação de Pontos de Controle Terrestre (GCPs) com GPS RTK;</li>
<li>Captura de imagens aéreas com sobreposição adequada (80% longitudinal, 70% lateral);</li>
<li>Processamento fotogramétrico e geração de ortomosaico georreferenciado;</li>
<li>Geração de MDT (Modelo Digital do Terreno) e MDS (Modelo Digital de Superfície);</li>
<li>Extração de curvas de nível e elementos vetoriais;</li>
<li>Cálculo de volumes (quando solicitado).</li>
</ul>';
    $varsEscopo = '["area_obra","ClienteCidadeUF","TipoTerreno","CoberturaVegetal","AcessoLocal","RestricoesAereas"]';
    
    $stmt = $conn->prepare("UPDATE service_type_blocks SET default_content = ?, allowed_vars = ? WHERE service_type_id = 19 AND block_slug = 'escopo'");
    $stmt->bind_param("ss", $contentEscopo, $varsEscopo);
    $stmt->execute();
    echo "<p class='text-emerald-400'>✅ Bloco 'Escopo' atualizado.</p>";


    // 2. METODOLOGIA
    $contentMetodologia = '<h4>Etapa 1: Planejamento e Análise de Viabilidade</h4>
<p>Análise do local em ${ClienteEndereco}, ${ClienteBairro}, considerando acesso ${AcessoLocal} e cobertura vegetal ${CoberturaVegetal}. Verificação de restrições aéreas junto ao DECEA para a cidade de ${ClienteCidadeUF}.</p>

<h4>Etapa 2: Reconhecimento de Campo</h4>
<p>Visita técnica para avaliação das condições reais do terreno (${TipoTerreno}) e instalação de alvos para GCPs em pontos estratégicos acessíveis.</p>

<h4>Etapa 3: Execução do Voo</h4>
<p>Checklist de segurança considerando condições meteorológicas e restrições (${RestricoesAereas}). O drone executa rota autônoma programada.</p>

<h4>Etapa 4: Processamento Fotogramétrico</h4>
<p>Geração da nuvem de pontos densa, ortomosaico e modelos digitais em workstation dedicada.</p>

<h4>Etapa 5: Vetorização e Entrega</h4>
<p>Desenho técnico final com curvas de nível e elementos do terreno.</p>';
    $varsMetodologia = '["ClienteEndereco","ClienteBairro","ClienteCidadeUF","AcessoLocal","CoberturaVegetal","TipoTerreno","RestricoesAereas"]';
    
    $stmt = $conn->prepare("UPDATE service_type_blocks SET default_content = ?, allowed_vars = ? WHERE service_type_id = 19 AND block_slug = 'metodologia'");
    $stmt->bind_param("ss", $contentMetodologia, $varsMetodologia);
    $stmt->execute();
    echo "<p class='text-emerald-400'>✅ Bloco 'Metodologia' atualizado.</p>";


    // 3. EQUIPAMENTOS
    $contentEquip = '<ul>
<li><strong>Drone VANT:</strong> ${Drone} (câmera de alta resolução, sistema RTK);</li>
<li><strong>GPS de Apoio (RTK):</strong> ${GPS} (coleta de GCPs com precisão centimétrica);</li>
<li><strong>Estação Total:</strong> ${Estacao_Total} (verificação de pontos de controle);</li>
<li><strong>Acessórios Específicos:</strong> 
    <ul>
        <li>Alvos para GCPs (considerando acesso ${AcessoLocal});</li>
        <li>Baterias extras (autonomia para área de ${area_obra});</li>
        <li>Kit de limpeza (para poeira/lama em terreno ${TipoTerreno});</li>
    </ul>
</li>
<li><strong>Software Fotogramétrico:</strong> Licenciado para processamento de imagens;</li>
<li><strong>Workstation:</strong> Computador de alta performance;</li>
<li><strong>Veículo:</strong> ${Veiculo} (acesso ${AcessoLocal} ao local em ${ClienteBairro}).</li>
</ul>';
    $varsEquip = '["Drone","GPS","Estacao_Total","area_obra","AcessoLocal","TipoTerreno","ClienteBairro","Veiculo"]';
    
    $stmt = $conn->prepare("UPDATE service_type_blocks SET default_content = ?, allowed_vars = ? WHERE service_type_id = 19 AND block_slug = 'equipamentos'");
    $stmt->bind_param("ss", $contentEquip, $varsEquip);
    $stmt->execute();
    echo "<p class='text-emerald-400'>✅ Bloco 'Equipamentos' atualizado.</p>";
    
    echo "</div>";

    echo "<div class='mt-6 text-center'>
            <a href='editor_dinamico.php' class='inline-block px-6 py-3 bg-emerald-600 hover:bg-emerald-500 text-white font-bold rounded-lg transition-colors'>
                Voltar para o Editor (Tente Salvar Agora)
            </a>
          </div>";

} catch (Exception $e) {
    echo "<div class='p-4 bg-rose-500/20 text-rose-300 border border-rose-500/30 rounded-lg'>Erro Fatal: " . $e->getMessage() . "</div>";
}

echo "</div></div></body></html>";
?>
