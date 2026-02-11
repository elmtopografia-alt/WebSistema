<?php
// salvar_rascunho.php
// Script para salvar rascunho da proposta (autosave / manual)

session_start();
header('Content-Type: application/json');

require_once 'db.php';

// Pre-flight check para a coluna unidade_area
try {
    $conn->query("SELECT unidade_area FROM Propostas LIMIT 1");
} catch (mysqli_sql_exception $e) {
    if (strpos($e->getMessage(), 'Unknown column') !== false) {
        $conn->query("ALTER TABLE Propostas ADD COLUMN unidade_area VARCHAR(10) DEFAULT 'm²' AFTER area_obra");
    }
}

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Método inválido.");
    }

    // Verifica sessão
    // if (!isset($_SESSION['usuario_id'])) { throw new Exception("Sessão expirada."); }

    $data = $_POST;
    
    // ID da Proposta: Pode vir do POST ou ser criado agora
    $id_proposta = isset($data['id_proposta_original']) && !empty($data['id_proposta_original']) 
        ? intval($data['id_proposta_original']) 
        : null;

    $novo = false;

    // 1. Se não tem ID, cria uma nova Proposta Rascunho
    if (!$id_proposta) {
        $novo = true;
        $id_criador = $_SESSION['usuario_id'] ?? 1; // Fallback 1 se sem sessão (dev)
        $cliente_id = isset($data['id_cliente']) ? intval($data['id_cliente']) : null;
        $servico_id = !empty($data['id_servico']) ? intval($data['id_servico']) : (isset($data['tipo_servico']) ? intval($data['tipo_servico']) : null);
        
        // [FIX SGT] Gerar número de proposta para evitar duplicatas vazias no banco (Unique Constraint)
        // Precisamos das funções de limpeza e geração
        require_once 'salvar_proposta.php';
        
        $emp = $conn->query("SELECT Empresa FROM DadosEmpresa WHERE id_criador = $id_criador LIMIT 1")->fetch_assoc();
        $num_proposta = gerarNumero($conn, ($emp['Empresa'] ?? 'SGT'));

        $stmtInit = $conn->prepare("INSERT INTO Propostas (numero_proposta, id_criador, id_cliente, id_servico, status, data_criacao) VALUES (?, ?, ?, ?, 'Rascunho', NOW())");
        $stmtInit->bind_param('siii', $num_proposta, $id_criador, $cliente_id, $servico_id);
        
        if (!$stmtInit->execute()) {
            throw new Exception("Erro ao criar rascunho: " . $stmtInit->error);
        }
        $id_proposta = $stmtInit->insert_id;
        error_log("DEBUG SGT: Rascunho criado com número $num_proposta. id_proposta = $id_proposta");
        $stmtInit->close();
    }

    // 2. Atualiza Campos Básicos na Tabela Propostas (opcional, se quiser salvar titulo, cliente, etc)
    // Para simplificar, vamos focar no CONTEÚDO PERSONALIZADO que é o crítico do editor.
    // Mas é bom atualizar variáveis chaves se vierem.
    
    // ATUALIZAÇÃO SGT: Persiste Área e Unidade no Rascunho se vierem no POST
    if (isset($data['area']) || isset($data['unidade_area'])) {
        $area = $data['area'] ?? null;
        $unidade = $data['unidade_area'] ?? 'm²';
        $sqlUp = "UPDATE Propostas SET area_obra = ?, unidade_area = ? WHERE id_proposta = ?";
        try {
            $stmtUp = $conn->prepare($sqlUp);
            $stmtUp->execute([$area, $unidade, $id_proposta]);
            $stmtUp->close();
        } catch (mysqli_sql_exception $e) {
            if (strpos($e->getMessage(), 'unidade_area') !== false) {
                // Auto-fix: Cria a coluna se não existir
                $conn->query("ALTER TABLE Propostas ADD COLUMN unidade_area VARCHAR(10) DEFAULT 'm²' AFTER area_obra");
                // Retry
                $stmtUp = $conn->prepare($sqlUp);
                $stmtUp->execute([$area, $unidade, $id_proposta]);
                $stmtUp->close();
            } else {
                throw $e;
            }
        }
    }
    
    // 3. Salva Conteúdo dos Blocos
    $camposIgnorados = ['id_proposta_original', 'acao', 'total_custos_salarios', 'image']; // etc
    
    foreach ($data as $key => $value) {
        // Interessa apenas campos que terminam em _content ou são específicos
        // Mas o editor manda tudo. Vamos salvar tudo que parecer conteúdo de bloco?
        // Melhor: Salvar TUDO que vier do form na tabela auxiliar? Não, muito lixo.
        // Vamos salvar apenas o que for texto rico ou inputs relevantes do editor.
        
        // Estratégia: Se a chave termina em '_content', é bloco de texto.
        if (strpos($key, '_content') !== false) {
             salvarConteudoBloco($conn, $id_proposta, $key, $value);
        }
    }

    echo json_encode([
        'success' => true,
        'message' => 'Rascunho salvo com sucesso!',
        'id_proposta' => $id_proposta,
        'is_new' => $novo
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erro: ' . $e->getMessage()
    ]);
}

function salvarConteudoBloco($conn, $id_proposta, $block_id, $conteudo) {
    // Upsert (Insert or Update)
    // MySQL: INSERT ... ON DUPLICATE KEY UPDATE
    
    $stmt = $conn->prepare("INSERT INTO Proposta_Conteudo_Personalizado (id_proposta, block_id, conteudo_texto) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE conteudo_texto = VALUES(conteudo_texto)");
    $stmt->bind_param('iss', $id_proposta, $block_id, $conteudo);
    $stmt->execute();
    $stmt->close();
}
?>
