<?php
/**
 * atualizar_proposta.php
 * Motor de Persistência e Regeneração de Documentos
 * 
 * RESPONSABILIDADE:
 * 1. Receber dados do formulário de edição.
 * 2. Atualizar tabela Propostas (respeitando o isolamento id_criador).
 * 3. Substituir os itens antigos (Salários, Estadia, etc) pelos novos.
 * 4. REGERAR o arquivo .docx físico na pasta.
 */

session_start();
require_once 'config.php';
require_once 'db.php';

// Carrega biblioteca PHPWord (Assumindo que está na pasta vendor na raiz)
require_once __DIR__ . '/vendor/autoload.php';
use PhpOffice\PhpWord\TemplateProcessor;

// 1. Verificação de Segurança
if (!isset($_SESSION['usuario_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: painel.php");
    exit;
}

$id_usuario = $_SESSION['usuario_id'];
$id_proposta = intval($_POST['id_proposta']);
$is_demo = (isset($_SESSION['ambiente']) && $_SESSION['ambiente'] === 'demo');

// Define banco
$conn = $is_demo ? Database::getDemo() : Database::getProd();

// Inicia Transação (Tudo ou Nada)
$conn->begin_transaction();

try {
    // 2. Coleta e Sanitização de Dados do Formulário
    
    // Dados Básicos e Financeiros
    $id_servico = intval($_POST['id_servico']);
    $contato_obra = $_POST['contato_obra'] ?? '';
    $finalidade = $_POST['finalidade'] ?? '';
    $tipo_levantamento = $_POST['tipo_levantamento'] ?? '';
    $area_obra = $_POST['area_obra'] ?? '';
    $endereco_obra = $_POST['endereco_obra'] ?? '';
    $bairro_obra = $_POST['bairro_obra'] ?? '';
    $cidade_obra = $_POST['cidade_obra'] ?? '';
    $estado_obra = $_POST['estado_obra'] ?? '';
    $prazo_execucao = $_POST['prazo_execucao'] ?? '';
    $dias_campo = intval($_POST['dias_campo']);
    $dias_escritorio = intval($_POST['dias_escritorio']);
    
    // Data Retroativa (Importante para a ordenação 12 vs 07)
    $data_criacao = $_POST['data_criacao']; // Formato YYYY-MM-DDTHH:MM
    
    // Totais (Vêm calculados do JS, mas convertemos para float)
    $total_salarios = floatval($_POST['total_custos_salarios']);
    $total_estadia = floatval($_POST['total_custos_estadia']);
    $total_consumos = floatval($_POST['total_custos_consumos']);
    $total_locacao = floatval($_POST['total_custos_locacao']);
    $total_admin = floatval($_POST['total_custos_admin']);
    
    $perc_lucro = floatval($_POST['percentual_lucro']);
    $valor_lucro = floatval($_POST['valor_lucro']);
    $subtotal = floatval($_POST['subtotal_com_lucro']);
    $desconto = floatval($_POST['valor_desconto']);
    $valor_final = floatval($_POST['valor_final_proposta']);
    
    $mob_perc = floatval($_POST['mobilizacao_percentual'] ?? 30);
    $mob_valor = floatval($_POST['mobilizacao_valor']);
    $rest_perc = floatval($_POST['restante_percentual']);
    $rest_valor = floatval($_POST['restante_valor']);
    
    // Função auxiliar para valor por extenso (simples)
    function valorPorExtenso($valor) {
        $fmt = new NumberFormatter('pt_BR', NumberFormatter::SPELLOUT);
        return $fmt->format($valor) . " reais";
    }
    $extenso = valorPorExtenso($valor_final);

    // 3. Atualiza Tabela Mestra (Propostas)
    $sql = "UPDATE Propostas SET 
            id_servico=?, contato_obra=?, finalidade=?, tipo_levantamento=?, area_obra=?, 
            endereco_obra=?, bairro_obra=?, cidade_obra=?, estado_obra=?, prazo_execucao=?, 
            dias_campo=?, dias_escritorio=?, data_criacao=?, 
            total_custos_salarios=?, total_custos_estadia=?, total_custos_consumos=?, total_custos_locacao=?, total_custos_admin=?,
            percentual_lucro=?, valor_lucro=?, subtotal_com_lucro=?, valor_desconto=?, valor_final_proposta=?, 
            Valor_proposta_extenso=?, mobilizacao_percentual=?, mobilizacao_valor=?, restante_percentual=?, restante_valor=?
            WHERE id_proposta=? AND id_criador=?";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('isssssssssiisdddddddddsddddii', 
        $id_servico, $contato_obra, $finalidade, $tipo_levantamento, $area_obra,
        $endereco_obra, $bairro_obra, $cidade_obra, $estado_obra, $prazo_execucao,
        $dias_campo, $dias_escritorio, $data_criacao,
        $total_salarios, $total_estadia, $total_consumos, $total_locacao, $total_admin,
        $perc_lucro, $valor_lucro, $subtotal, $desconto, $valor_final,
        $extenso, $mob_perc, $mob_valor, $rest_perc, $rest_valor,
        $id_proposta, $id_usuario
    );
    $stmt->execute();

    // 4. Limpeza e Reinserção dos Itens (Salários, Estadia, etc)
    // Deleta os antigos para inserir os novos (evita lógica complexa de update linha a linha)
    $conn->query("DELETE FROM Proposta_Salarios WHERE id_proposta = $id_proposta");
    $conn->query("DELETE FROM Proposta_Estadia WHERE id_proposta = $id_proposta");
    $conn->query("DELETE FROM Proposta_Consumos WHERE id_proposta = $id_proposta");
    $conn->query("DELETE FROM Proposta_Locacao WHERE id_proposta = $id_proposta");
    $conn->query("DELETE FROM Proposta_Custos_Administrativos WHERE id_proposta = $id_proposta");

    // Inserção em Lote (Batch Insert Lógica)
    
    // Salários
    if (!empty($_POST['salario_funcao'])) {
        $stmtSal = $conn->prepare("INSERT INTO Proposta_Salarios (id_proposta, funcao, quantidade, salario_base, fator_encargos, dias) VALUES (?, ?, ?, ?, ?, ?)");
        foreach ($_POST['salario_funcao'] as $k => $v) {
            if(empty($v)) continue;
            $qtd = $_POST['salario_qtd'][$k];
            $base = $_POST['salario_valor'][$k];
            $enc = (($_POST['salario_encargos'][$k]/100) + 1); // Converte 86% para 1.86
            $dias = $_POST['salario_dias'][$k];
            $stmtSal->bind_param('isdddd', $id_proposta, $v, $qtd, $base, $enc, $dias);
            $stmtSal->execute();
        }
    }

    // Estadia
    if (!empty($_POST['estadia_tipo'])) {
        $stmtEst = $conn->prepare("INSERT INTO Proposta_Estadia (id_proposta, tipo, quantidade, valor_unitario, dias) VALUES (?, ?, ?, ?, ?)");
        foreach ($_POST['estadia_tipo'] as $k => $v) {
            if(empty($v)) continue;
            $stmtEst->bind_param('isddd', $id_proposta, $v, $_POST['estadia_qtd'][$k], $_POST['estadia_valor'][$k], $_POST['estadia_dias'][$k]);
            $stmtEst->execute();
        }
    }

    // Consumos
    if (!empty($_POST['consumo_tipo'])) {
        $stmtCon = $conn->prepare("INSERT INTO Proposta_Consumos (id_proposta, tipo, quantidade, consumo_kml, valor_litro, km_total) VALUES (?, ?, ?, ?, ?, ?)");
        foreach ($_POST['consumo_tipo'] as $k => $v) {
            if(empty($v)) continue;
            $stmtCon->bind_param('isdddd', $id_proposta, $v, $_POST['consumo_qtd'][$k], $_POST['consumo_kml'][$k], $_POST['consumo_litro'][$k], $_POST['consumo_km'][$k]);
            $stmtCon->execute();
        }
    }

    // Locação
    if (!empty($_POST['locacao_tipo'])) {
        $stmtLoc = $conn->prepare("INSERT INTO Proposta_Locacao (id_proposta, id_marca, quantidade, valor_mensal, dias) VALUES (?, ?, ?, ?, ?)");
        foreach ($_POST['locacao_tipo'] as $k => $v) {
            if(empty($v)) continue;
            $stmtLoc->bind_param('isddd', $id_proposta, $v, $_POST['locacao_qtd'][$k], $_POST['locacao_valor'][$k], $_POST['locacao_dias'][$k]);
            $stmtLoc->execute();
        }
    }

    // Admin
    if (!empty($_POST['admin_tipo'])) {
        $stmtAdm = $conn->prepare("INSERT INTO Proposta_Custos_Administrativos (id_proposta, tipo, quantidade, valor) VALUES (?, ?, ?, ?)");
        foreach ($_POST['admin_tipo'] as $k => $v) {
            if(empty($v)) continue;
            $stmtAdm->bind_param('isdd', $id_proposta, $v, $_POST['admin_qtd'][$k], $_POST['admin_valor'][$k]);
            $stmtAdm->execute();
        }
    }

    // 5. REGENERAÇÃO DO ARQUIVO DOCX
    // Busca dados completos para preencher o template
    $dadosCompleto = $conn->query("SELECT p.*, c.empresa as cliente_empresa, c.cnpj_cpf as cliente_doc, c.endereco as cliente_end, d.logo_caminho, ts.arquivo_modelo 
                                   FROM Propostas p 
                                   LEFT JOIN Clientes c ON p.id_cliente = c.id_cliente 
                                   LEFT JOIN DadosEmpresa d ON p.id_criador = d.id_criador
                                   LEFT JOIN Tipo_Servicos ts ON p.id_servico = ts.id_servico
                                   WHERE p.id_proposta = $id_proposta")->fetch_assoc();

    // Determina o modelo (usando Prod ou Demo)
    $pastaModelos = $is_demo ? 'modelos_demo' : 'modelos_prod';
    $arquivoModelo = !empty($dadosCompleto['arquivo_modelo']) ? $dadosCompleto['arquivo_modelo'] : 'ModeloPropostaPadrao.docx';
    $caminhoModelo = __DIR__ . "/$pastaModelos/" . $arquivoModelo;

    if (file_exists($caminhoModelo)) {
        $template = new TemplateProcessor($caminhoModelo);

        // Substituição de Variáveis
        $template->setValue('NOME_CLIENTE', $dadosCompleto['nome_cliente_salvo']);
        $template->setValue('NUMERO_PROPOSTA', $dadosCompleto['numero_proposta']);
        $template->setValue('VALOR_FINAL', number_format($valor_final, 2, ',', '.'));
        $template->setValue('DATA_HOJE', date('d/m/Y', strtotime($dadosCompleto['data_criacao'])));
        $template->setValue('OBJETO', $dadosCompleto['finalidade']);
        $template->setValue('AREA', $dadosCompleto['area_obra']);
        $template->setValue('CIDADE_OBRA', $dadosCompleto['cidade_obra']);
        $template->setValue('PRAZO', $dadosCompleto['prazo_execucao']);
        
        // Imagem do Logo (Se existir)
        if (!empty($dadosCompleto['logo_caminho']) && file_exists(__DIR__ . '/' . $dadosCompleto['logo_caminho'])) {
            $template->setImageValue('LOGO_EMPRESA', ['path' => __DIR__ . '/' . $dadosCompleto['logo_caminho'], 'width' => 150, 'height' => 80, 'ratio' => true]);
        } else {
            $template->setValue('LOGO_EMPRESA', '');
        }

        // Gera Nome do Arquivo: NomeEmpresa-Ano-Seq.docx
        $nomeEmpresa = trim(explode(' ', $dadosCompleto['empresa_proponente_nome'])[0]);
        $nomeEmpresaClean = preg_replace('/[^a-zA-Z0-9]/', '', iconv('UTF-8', 'ASCII//TRANSLIT', $nomeEmpresa));
        $partesNum = explode('-', $dadosCompleto['numero_proposta']);
        $ano = isset($partesNum[1]) ? $partesNum[1] : date('Y');
        $seq = isset($partesNum[2]) ? $partesNum[2] : '000';
        
        $novoNomeArquivo = "{$nomeEmpresaClean}-{$ano}-{$seq}.docx";
        $caminhoSaida = __DIR__ . '/propostas_emitidas/' . $novoNomeArquivo;

        // Salva
        $template->saveAs($caminhoSaida);
    }

    // Commit Final
    $conn->commit();
    header("Location: painel.php?msg=sucesso");
    exit;

} catch (Exception $e) {
    $conn->rollback();
    die("Erro ao salvar proposta: " . $e->getMessage());
}
?>