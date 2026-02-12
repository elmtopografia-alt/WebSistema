<?php
// Nome do Arquivo: atualizar_modelos_banco.php
// Função: Ler arquivos na pasta modelos_prod e atualizar os bancos Prod e Demo.

require_once 'config.php';
require_once 'db.php';

// Caminho absoluto da pasta de modelos
$dir_path = __DIR__ . '/modelos_prod';

echo "<h1>🚀 Atualização de Modelos de Proposta</h1>";
echo "<p>📂 Lendo pasta: <strong>$dir_path</strong></p>";

if (!is_dir($dir_path)) {
    die("<h3 style='color:red'>❌ Erro: Pasta 'modelos_prod' não encontrada!</h3>");
}

// 1. Listar arquivos que começam com "ModeloProposta" e terminam com ".docx"
$arquivos = scandir($dir_path);
$arquivos_validos = [];

foreach ($arquivos as $arquivo) {
    if (strpos($arquivo, 'ModeloProposta') === 0 && strtolower(pathinfo($arquivo, PATHINFO_EXTENSION)) === 'docx') {
        $arquivos_validos[] = $arquivo;
    }
}

$qtd = count($arquivos_validos);
echo "<p>✅ Encontrados <strong>$qtd</strong> arquivos de modelo válidos.</p><hr>";

if ($qtd === 0) {
    die("<h3>Nenhum arquivo para processar. Encerrando.</h3>");
}

// 2. Mapeamento Inteligente (Nome do Arquivo -> Palavra-chave do Serviço)
// A chave é o texto que vamos buscar no nome do arquivo para saber qual serviço é.
// O valor é a string que vamos buscar no banco (LIKE %valor%)
$mapa_servicos = [
    'Avulso' => 'Avulso',
    'Conferência' => 'Conferência', // Pode precisar tratar acentuação dependendo do nome do arquivo
    'Conferencia' => 'Conferência',
    'Desdobramento' => 'Desdobramento',
    'Drone' => 'Drone',
    'Georreferenciamento' => 'Georreferenciamento',
    'Locação de Obra' => 'Locação de Obra',
    'Locacao de Obra' => 'Locação de Obra',
    'LocacaodeObra' => 'Locação de Obra',
    'Locação Terraplenagem' => 'Locação Terraplenagem',
    'Locacao Terraplenagem' => 'Locação Terraplenagem',
    'LocacaoTerraplenagem' => 'Locação Terraplenagem',
    'Obra Civil' => 'Obra Civil',
    'ObraCivil' => 'Obra Civil',
    'Obra Industrial' => 'Obra Industrial',
    'ObraIndustrial' => 'Obra Industrial',
    'Obra Terraplanagem' => 'Obra Terraplanagem',
    'ObraTerraplanagem' => 'Obra Terraplanagem',
    'Planialtimétrico' => 'Planialtimétrico',
    'Planialtimetrico' => 'Planialtimétrico',
    'Planimétrico' => 'Planimétrico',
    'Planimetrico' => 'Planimétrico',
    'Retificação' => 'Retificação',
    'Retificacao' => 'Retificação',
    'Revisão' => 'Revisão',
    'Revisao' => 'Revisão',
    'Usucapião' => 'Usucapião',
    'Usucapiao' => 'Usucapião'
];

// Função para identificar o serviço com base no nome do arquivo
function identificarServico($nome_arquivo, $mapa)
{
    // Remove "ModeloProposta" e extensão para facilitar a busca
    $clean_name = str_replace(['ModeloProposta', '.docx'], '', $nome_arquivo);

    // Tenta encontrar correspondencia direta ou parcial
    foreach ($mapa as $chave_arquivo => $nome_servico_banco) {
        if (stripos($clean_name, $chave_arquivo) !== false) {
            return $nome_servico_banco;
        }
    }
    return null;
}

// Função para atualizar o banco
function atualizarBanco($conn, $nome_banco, $arquivos_validos, $mapa_servicos)
{
    echo "<h3>📡 Conectando ao Banco: $nome_banco</h3>";

    // Verifica se a coluna existe, se não, cria
    try {
        $conn->query("ALTER TABLE Tipo_Servicos ADD COLUMN arquivo_modelo VARCHAR(255) NULL");
    } catch (Exception $e) {
        // Coluna já existe
    }

    $atualizados = 0;

    foreach ($arquivos_validos as $arquivo) {
        $servico_alvo = identificarServico($arquivo, $mapa_servicos);

        if ($servico_alvo) {
            // Atualiza no banco
            $sql = "UPDATE Tipo_Servicos SET arquivo_modelo = '$arquivo' WHERE nome LIKE '%$servico_alvo%'";
            if ($conn->query($sql)) {
                if ($conn->affected_rows > 0) {
                    echo "<div style='color:green'>✔ [$nome_banco] Arquivo <b>$arquivo</b> vinculado ao serviço contendo '<b>$servico_alvo</b>'</div>";
                    $atualizados++;
                } else {
                    // Check if it was already set correctly to avoid false negatives? Or just ignore.
                    // Often affected_rows is 0 if value is same.
                    echo "<div style='color:gray'>- [$nome_banco] <b>$arquivo</b> (Serviço '$servico_alvo' encontrado, mas valor já era idêntico ou serviço não existe)</div>";
                }
            } else {
                echo "<div style='color:red'>❌ Erro SQL ao vincular $arquivo: " . $conn->error . "</div>";
            }
        } else {
            echo "<div style='color:orange'>⚠ Não consegui identificar o serviço para o arquivo: <b>$arquivo</b></div>";
        }
    }
    echo "<p><strong>Total atualizados em $nome_banco: $atualizados</strong></p><br>";
}

// 3. Executar para PROD
try {
    $connProd = Database::getProd();
    atualizarBanco($connProd, "PRODUÇÃO", $arquivos_validos, $mapa_servicos);
} catch (Exception $e) {
    echo "<p style='color:red'>Erro ao conectar em Produção: " . $e->getMessage() . "</p>";
}

// 4. Executar para DEMO
try {
    $connDemo = Database::getDemo();
    atualizarBanco($connDemo, "DEMONSTRAÇÃO", $arquivos_validos, $mapa_servicos);
} catch (Exception $e) {
    echo "<p style='color:red'>Erro ao conectar em Demonstração: " . $e->getMessage() . "</p>";
}

echo "<hr><h2>✨ Processo Finalizado!</h2>";
