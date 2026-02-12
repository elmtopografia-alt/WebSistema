<?php
// ARQUIVO: diagnostico_admin.php
// Objetivo: Verificar integridade dos dados para usuários (especialmente Admin)

// Forçar exibição de erros
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Tenta carregar configurações
if (file_exists('config.php')) require_once 'config.php';
if (file_exists('db.php')) require_once 'db.php';

// Função auxiliar para CLI
function logLine($msg) {
    echo $msg . PHP_EOL;
}

try {
    $conn = Database::getProd();
    logLine("=== DIAGNÓSTICO DE INTEGRIDADE DE USUÁRIOS ===");
    logLine("Conectado ao banco de dados com sucesso.");

    // 1. Listar Usuários (Todos ou filtro Admin)
    $sql = "SELECT id_usuario, usuario, nome_completo, tipo_perfil FROM Usuarios WHERE tipo_perfil = 'admin' OR usuario LIKE '%admin%' OR usuario LIKE '%renato%' LIMIT 20";
    $result = $conn->query($sql);
    
    $users = [];
    if ($result->num_rows > 0) {
        logLine("\n[ Usuários Encontrados ]");
        while($row = $result->fetch_assoc()) {
            $users[] = $row;
            logLine("ID: {$row['id_usuario']} | User: {$row['usuario']} | Nome: {$row['nome_completo']} | Perfil: {$row['tipo_perfil']}");
        }
    } else {
        logLine("Nenhum usuário encontrado com filtro. Listando TODOS (limit 10):");
        $result = $conn->query("SELECT id_usuario, usuario, nome_completo, tipo_perfil FROM Usuarios LIMIT 10");
        while($row = $result->fetch_assoc()) {
            $users[] = $row;
            logLine("ID: {$row['id_usuario']} | User: {$row['usuario']} | Nome: {$row['nome_completo']} | Perfil: {$row['tipo_perfil']}");
        }
    }

    // 2. Verificar Tabelas Críticas para cada usuário
    $tabelas_verificar = [
        'DadosEmpresa' => 'id_criador',
        'Tipo_Servicos' => 'id_criador', // Assumindo que essas tabelas usam id_criador / usuario
        'Tipo_Funcoes' => 'id_criador', // Se não tiverem essa coluna, vai dar erro no count, trataremos no catch ou check logic
        'Tipo_Estadia' => 'id_criador', 
        'Tipo_Consumo' => 'id_criador',
        'Tipo_Locacao' => 'id_criador',
        'Tipo_Custo_Admin' => 'id_criador',
        'Clientes' => 'id_criador'
    ];

    // Verificar se as colunas existem antes de query
    $validatabelas = [];
    foreach($tabelas_verificar as $tab => $col) {
        $check = $conn->query("SHOW COLUMNS FROM $tab LIKE '$col'");
        if ($check && $check->num_rows > 0) {
            $validatabelas[$tab] = $col;
        } else {
             // Tenta 'id_usuario' se 'id_criador' não existir, ou ignora
             $check2 = $conn->query("SHOW COLUMNS FROM $tab LIKE 'id_usuario'");
             if ($check2 && $check2->num_rows > 0) {
                 $validatabelas[$tab] = 'id_usuario';
             } else {
                 logLine("Check: Tabela $tab não parece ter coluna de vinculo com usuário (id_criador/id_usuario). Pulando.");
             }
        }
    }

    logLine("\n[ Verificando Dados Vinculados ]");
    foreach ($users as $u) {
        $uid = $u['id_usuario'];
        $ulogin = $u['usuario'];
        
        logLine("\n--- Analisando Usuário: $ulogin (ID: $uid) ---");
        
        foreach ($validatabelas as $tabela => $coluna) {
            $countSql = "SELECT COUNT(*) as total FROM $tabela WHERE $coluna = $uid";
            $countRes = $conn->query($countSql);
            $total = 0;
            if ($countRes) {
                $row = $countRes->fetch_assoc();
                $total = $row['total'];
            }
            
            $status = ($total > 0) ? "OK ($total registros)" : "VAZIO (0 registros) ⚠️";
            // DadosEmpresa é crítico ter pelo menos 1
            if ($tabela == 'DadosEmpresa' && $total > 0) {
                 $dqs = "SELECT * FROM DadosEmpresa WHERE id_criador = $uid";
                 $dqr = $conn->query($dqs);
                 if($dqr && $rowD = $dqr->fetch_assoc()) {
                     logLine("   -> DadosEmpresa: " . json_encode($rowD, JSON_UNESCAPED_UNICODE));
                 }
            }
            if ($tabela == 'DadosEmpresa' && $total > 0) {
                 $dqs = "SELECT * FROM DadosEmpresa WHERE id_criador = $uid";
                 $dqr = $conn->query($dqs);
                 // if($dqr && $rowD = $dqr->fetch_assoc()) {
                 //    logLine("   -> DadosEmpresa: " . json_encode($rowD, JSON_UNESCAPED_UNICODE));
                 // }
            }

            if ($tabela == 'Tipo_Servicos' && $total == 0) $status .= " [CRÍTICO - Sem serviços]";
            
            logLine(str_pad($tabela, 20) . ": " . $status);
        }
    }
    
    // Extra: Verificar estrutura de Tipo_Servicos e totais globais
    logLine("\n[ Verificação Global ]");
    $resTS = $conn->query("SELECT * FROM Tipo_Servicos LIMIT 1");
    if ($resTS && $row = $resTS->fetch_assoc()) {
        logLine("Tipo_Servicos (Exemplo): " . json_encode($row, JSON_UNESCAPED_UNICODE));
    } else {
        logLine("Tipo_Servicos: VAZIA ou Erro.");
    }
    
    $resCount = $conn->query("SELECT COUNT(*) as t FROM Tipo_Servicos");
    if ($resCount) {
        $rowCount = $resCount->fetch_assoc();
        logLine("Total Tipo_Servicos no banco: " . $rowCount['t']);
    }
    
    // Limpar Cache
    if (function_exists('apcu_clear_cache')) {
        apcu_clear_cache();
        logLine("Cache APCu limpo.");
    } else {
        logLine("APCu não detectado (sem cache para limpar).");
    }

} catch (Exception $e) {
    logLine("ERRO FATAL: " . $e->getMessage());
}
?>

} catch (Exception $e) {
    logLine("ERRO FATAL: " . $e->getMessage());
}
?>
