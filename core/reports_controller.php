<?php
/**
 * core/reports_controller.php
 * Controlador da Página de Relatórios
 * Responsável por: Exportação CSV e Validação de Acesso
 */

require_once __DIR__ . '/../session_validator.php';
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../db.php';

$id_usuario = $_SESSION['usuario_id'];

// Lógica de Exportação CSV
if (isset($_GET['exportar']) && $_GET['exportar'] == 'csv') {
    $filename = "Relatorio_SGT_" . date('Y-m-d') . ".csv";
    
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=' . $filename);
    
    $output = fopen('php://output', 'w');
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM para Excel
    fputcsv($output, ['Numero', 'Cliente', 'Servico', 'Data', 'Status', 'Valor (R$)', 'Cidade'], ';');
    
    try {
        $is_demo = (isset($_SESSION['ambiente']) && $_SESSION['ambiente'] === 'demo');
        $conn = $is_demo ? Database::getDemo() : Database::getProd();
        
        $sql = "SELECT p.numero_proposta, p.nome_cliente_salvo, s.nome as servico, p.data_criacao, p.status, p.valor_final_proposta, p.cidade_obra 
                FROM Propostas p 
                LEFT JOIN Tipo_Servicos s ON p.id_servico = s.id_servico 
                WHERE p.id_criador = ? 
                ORDER BY p.data_criacao DESC";
                
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $id_usuario);
        $stmt->execute();
        $res = $stmt->get_result();
        
        while ($row = $res->fetch_assoc()) { 
            fputcsv($output, [
                $row['numero_proposta'], 
                $row['nome_cliente_salvo'], 
                $row['servico'], 
                date('d/m/Y', strtotime($row['data_criacao'])), 
                $row['status'], 
                number_format($row['valor_final_proposta'], 2, ',', ''), 
                $row['cidade_obra']
            ], ';'); 
        }
        
    } catch (Exception $e) {
        // Em caso de erro no CSV, apenas logar (headers já enviados)
        error_log("Erro export CSV: " . $e->getMessage());
    }
    
    fclose($output);
    exit;
}

// Retorna dados básicos se necessário para a view inicial (Dashboard Controller já tem dados do usuário, 
// mas podemos precisar de algo específico aqui futuramente. Por enquanto, limpo).
return [
    'usuario_id' => $id_usuario
];
