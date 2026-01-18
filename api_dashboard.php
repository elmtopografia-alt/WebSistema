<?php
// ARQUIVO: api_dashboard.php
require_once 'db.php'; 

// 1. SEGURANÇA SAAS: PEGAR O ID DO USUÁRIO LOGADO
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// IMPORTANTE: Ajuste 'id_usuario' para o nome exato da sua sessão (ex: 'id_user', 'user_id')
$id_logado = isset($_SESSION['usuario_id']) ? $_SESSION['usuario_id'] : (isset($_SESSION['id_criador']) ? $_SESSION['id_criador'] : 0);

if ($id_logado == 0) {
    exit("Erro: Usuário não identificado na sessão.");
}

$acao = isset($_GET['acao']) ? $_GET['acao'] : '';

// ---------------------------------------------------------
// CENÁRIO A: PREENCHER A TABELA (DRILL-DOWN)
// ---------------------------------------------------------
if ($acao == 'detalhes') {
    $mes = $_GET['mes']; // Ex: 01/2025
    $status = $_GET['status']; // Ex: Em Elaboração

    // Proteção básica
    $mes_db = $conn->real_escape_string($mes);
    $status_db = $conn->real_escape_string($status);
    $id_logado = (int)$id_logado;

    // QUERY OFICIAL NA TABELA PROPOSTAS
    $sql = "SELECT 
                id_proposta, 
                numero_proposta, 
                nome_cliente_salvo, 
                valor_final_proposta, 
                DATE_FORMAT(data_criacao, '%d/%m/%Y') as data_fmt,
                status
            FROM Propostas 
            WHERE id_criador = $id_logado
            AND DATE_FORMAT(data_criacao, '%m/%Y') = '$mes_db' 
            AND status = '$status_db'
            ORDER BY valor_final_proposta DESC";
    
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            // Definição de cores para o valor monetário
            $corValor = 'text-dark';
            if ($row['status'] == 'Aprovada') $corValor = 'text-success';
            if ($row['status'] == 'Cancelada') $corValor = 'text-danger';
            if ($row['status'] == 'Enviada') $corValor = 'text-primary';

            $val = number_format($row['valor_final_proposta'], 2, ',', '.');
            
            echo "<tr>
                    <td>{$row['numero_proposta']}</td>
                    <td>{$row['nome_cliente_salvo']}</td>
                    <td class='{$corValor}'>R$ {$val}</td>
                    <td>{$row['data_fmt']}</td>
                    <td>{$row['status']}</td>
                    <td>
                        <a href='editar_proposta.php?id={$row['id_proposta']}' class='text-primary' target='_blank'>
                            <i class='bx bx-edit'></i> Abrir
                        </a>
                    </td>
                  </tr>";
        }
    } else {
        echo "<tr><td colspan='6' class='text-center py-3'>Nenhum registro encontrado nesta categoria.</td></tr>";
    }
    
    // Fecha conexão e encerra
    exit;
}
// ---------------------------------------------------------
// CENÁRIO B: MUDAR STATUS (Migrado de mudar_status.php)
// ---------------------------------------------------------
if ($acao == 'mudar_status') {
    // Garante que o PHP devolva JSON limpo
    header('Content-Type: application/json; charset=utf-8');
    
    $response = ['success' => false, 'msg' => 'Acesso negado'];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // 2. Recebe dados
        $id_proposta = intval($_POST['id_proposta'] ?? 0);
        $status_recebido = trim($_POST['novo_status'] ?? '');
        
        // Normaliza para minúsculo para comparar
        $st_lower = mb_strtolower($status_recebido, 'UTF-8');

        // 3. Mapeamento Inteligente
        $status_final = '';

        if (strpos($st_lower, 'aprov') !== false || strpos($st_lower, 'aceit') !== false || strpos($st_lower, 'conclu') !== false) {
            $status_final = 'Aprovada';
        } 
        elseif (strpos($st_lower, 'envia') !== false) {
            $status_final = 'Enviada';
        } 
        elseif (strpos($st_lower, 'cancel') !== false || strpos($st_lower, 'perdid') !== false) {
            $status_final = 'Cancelada';
        } 
        elseif (strpos($st_lower, 'elabor') !== false || strpos($st_lower, 'rascun') !== false) {
            $status_final = 'Em elaboração';
        }

        // 4. Executa a Gravação
        if ($id_proposta > 0 && $status_final !== '') {
            try {
                $sql = "UPDATE Propostas SET status = ? WHERE id_proposta = ? AND id_criador = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param('sii', $status_final, $id_proposta, $id_logado);
                
                if ($stmt->execute()) {
                    if ($stmt->affected_rows > 0) {
                        $response['success'] = true;
                        $response['msg'] = "Status alterado para: $status_final";
                        $response['novo_status_normalizado'] = $status_final;
                    } else {
                        $response['success'] = true; 
                        $response['msg'] = "Status já era $status_final.";
                        $response['novo_status_normalizado'] = $status_final;
                    }
                } else {
                    $response['msg'] = "Erro SQL: " . $stmt->error;
                }
            } catch (Exception $e) {
                $response['msg'] = "Erro técnico: " . $e->getMessage();
            }
        } else {
            $response['msg'] = "Status inválido ou não reconhecido: '$status_recebido'";
        }
    }
    
    echo json_encode($response);
    exit;
}
?>