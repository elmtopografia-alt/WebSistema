<?php
/**
 * TESTE PILOTO DO MOTOR DE VENDAS SGT v0.1
 * Objetivo: Demonstrar a aplicação prática da SGT_ETHICS_BIBLE
 */

require_once 'SGT_ETHICS_BIBLE.php';

// Simulação de Dados de Entrada (O "Copo")
$alvos_teste = [
    ['nome' => 'Construtora Teste A', 'site' => 'https://exemplo-construtora-a.com', 'canais' => ['public_email', 'public_form']],
    ['nome' => 'Empresa Fechada B',   'site' => 'https://exemplo-fechada.com',     'canais' => ['hidden_data']], // Deve ser rejeitada
    ['nome' => 'Engenharia C',        'site' => 'https://exemplo-engenharia.com',  'canais' => ['public_form']]
];

echo "--- INICIANDO MOTOR SGT PILOTO ---\n";
echo "Carregando regras de: " . SgtEthics::POLICY_VERSION . "\n\n";

$copo_cheio = [];

foreach ($alvos_teste as $alvo) {
    echo "Analisando: {$alvo['nome']}...\n";
    
    $autorizado = false;
    $canal_encontrado = '';

    // Verifica cada canal disponível no site contra a Bíblia
    foreach ($alvo['canais'] as $canal) {
        if (SgtEthics::checkPermission($canal)) {
            $autorizado = true;
            $canal_encontrado = $canal;
            break; // Achou uma porta aberta, pode entrar
        }
    }

    if ($autorizado) {
        echo "✅ ACESSO PERMITIDO! Porta aberta via: $canal_encontrado\n";
        $copo_cheio[] = $alvo;
    } else {
        echo "🚫 ACESSO NEGADO. Nenhuma porta pública encontrada. Respeitando a privacidade.\n";
    }
    echo "---------------------------------------------------\n";
}

echo "\n--- RESULTADO FINAL ---\n";
echo "Leads Qualificados e Éticos no Copo: " . count($copo_cheio) . "\n";
print_r($copo_cheio);
?>
