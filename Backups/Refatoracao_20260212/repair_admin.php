<?php
// ARQUIVO: repair_admin.php
// Objetivo: Corrigir formatação de dados do Admin (ID 1)

ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once 'config.php';
require_once 'db.php';

function formatarTelefone($fone) {
    if (!$fone) return $fone;
    $num = preg_replace('/\D/', '', $fone);
    // (XX) XXXXX-XXXX
    if (strlen($num) == 11) {
        return '(' . substr($num, 0, 2) . ') ' . substr($num, 2, 5) . '-' . substr($num, 7);
    }
    // (XX) XXXX-XXXX
    if (strlen($num) == 10) {
        return '(' . substr($num, 0, 2) . ') ' . substr($num, 2, 4) . '-' . substr($num, 6);
    }
    return $fone; // Retorna original se não casar padrão
}

try {
    $conn = Database::getProd();
    echo "Conectado. Iniciando reparo para ID 1...\n";
    
    // Buscar dados atuais
    $res = $conn->query("SELECT * FROM DadosEmpresa WHERE id_criador = 1");
    if ($res && $row = $res->fetch_assoc()) {
        $tel = $row['Telefone'];
        $cel = $row['Celular'];
        $zap = $row['Whatsapp'];
        
        echo "Atuais:\n Tel: $tel\n Cel: $cel\n Zap: $zap\n";
        
        $newTel = formatarTelefone($tel);
        $newCel = formatarTelefone($cel);
        $newZap = formatarTelefone($zap);
        
        echo "Novos:\n Tel: $newTel\n Cel: $newCel\n Zap: $newZap\n";
        
        if ($newTel != $tel || $newCel != $cel || $newZap != $zap) {
            $stmt = $conn->prepare("UPDATE DadosEmpresa SET Telefone=?, Celular=?, Whatsapp=? WHERE id_criador=1");
            $stmt->bind_param('sss', $newTel, $newCel, $newZap);
            if ($stmt->execute()) {
                echo "✅ DADOS ATUALIZADOS COM SUCESSO!\n";
            } else {
                echo "❌ Erro ao atualizar: " . $conn->error . "\n";
            }
        } else {
            echo "ℹ️ Nenhuma alteração necessária (já formatado ou padrão desconhecido).\n";
        }
        
    } else {
        echo "❌ Admin (ID 1) não encontrado em DadosEmpresa!\n";
    }

} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}
?>
