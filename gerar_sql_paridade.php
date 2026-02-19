<?php
require_once 'db.php';
header('Content-Type: text/plain; charset=utf-8');

$connProd = Database::getProd();
$connDemo = Database::getDemo();

// Tabelas que identificamos que faltam no DEMO
$faltandoNoDemo = [
    'Assinaturas', 'Ciclos_Financeiros', 'Documentos', 'Email_Envios', 
    'Email_Templates', 'Historico_Interacoes', 'Interacoes_CRM', 'Pagamentos', 
    'Proposta_Conteudo_Personalizado', 'Proposta_Cronograma', 'Recibos', 
    'Tarefas_CRM', 'Tokens_Acesso_Rapido', 'Usuarios_Versoes_Vistas', 
    'Versoes_Sistema', 'leads_prospeccao', 'service_type_blocks', 
    'sgt_piloto_teste', 'tipos_servico', 'vw_propostas_com_tipo'
];

echo "=== SCRIPT DE GERAÇÃO DE PARIDADE (SQL) ===\n\n";

foreach ($faltandoNoDemo as $tabela) {
    $res = $connProd->query("SHOW CREATE TABLE `$tabela`");
    if ($res) {
        $row = $res->fetch_array();
        echo "-- Tabela: $tabela\n";
        echo $row[1] . ";\n\n";
    } else {
        echo "-- ERRO: Não foi possível obter estrutura de '$tabela'\n\n";
    }
}

echo "=== FIM DO SCRIPT ===\n";
?>
