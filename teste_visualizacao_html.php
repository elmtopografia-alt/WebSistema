<?php
/**
 * teste_visualizacao_html.php
 * Visualização de Proposta Estática (Mock)
 * Finalidade: Validar layout e geração de HTML independente do banco de dados.
 */

// Configuração Básica de Mock
$mockData = [
    'numero_proposta' => 'TEST-2024-001',
    'data_criacao' => date('Y-m-d'),
    'nome_cliente_salvo' => 'Cliente Exemplo Ltda',
    'empresa_cliente_salvo' => 'Grupo Exemplo S.A.',
    'email_salvo' => 'contato@exemplo.com',
    'telefone_salvo' => '(11) 99999-9999',
    'celular_salvo' => '(11) 98888-8888',
    'cidade_obra' => 'São Paulo',
    'estado_obra' => 'SP',
    'endereco_obra' => 'Av. Paulista, 1000',
    'bairro_obra' => 'Bela Vista',
    'tipo_servico_id' => 1,
    'nome_servico' => 'Levantamento Topográfico Planimétrico',
    'valor_proposta' => 15000.00,
    'valor_extenso' => 'quinze mil reais',
    'prazo_execucao' => '15 dias úteis',
    'validade_proposta' => '30 dias',
    'finalidade' => 'Regularização Fundiária',
    'area_obra' => '5.000 m²',
    'tipo_levantamento' => 'Cadastral',
    
    // Variáveis de Drone (Novas)
    'tipo_terreno' => 'Plano com vegetação rasteira',
    'cobertura_vegetal' => 'Baixa densidade',
    'acesso_local' => 'Via asfaltada',
    'restricoes_aereas' => 'Sem restrições (Classe G)',
    
    // Empresa Proponente
    'Empresa' => 'SGT Topografia & Engenharia',
    'CNPJ' => '12.345.678/0001-90',
    'Cidade' => 'Rio de Janeiro',
    'Logo' => 'logo_sgt.png',
    'Whatsapp' => '(21) 99999-0000'
];

// Funções Auxiliares (Simplificadas)
function formatarMoeda($val) { return 'R$ ' . number_format($val, 2, ',', '.'); }
function dataPorExtenso() { return date('d/m/Y'); }

// Simulação de Blocos de Conteúdo
$conteudoBlocos = [
    'apresentacao' => '<p>Prezados Senhores,</p><p>Apresentamos nossa proposta técnica e comercial para a realização dos serviços de <strong>${nome_servico}</strong>.</p>',
    'escopo' => '<ul><li>Levantamento perimétrico da área de ${area_obra}</li><li>Georreferenciamento de vértices</li><li>Geração de planta topográfica</li></ul>',
    'metodologia' => '<p>Utilizaremos receptores GNSS de dupla frequência e Drones RTK para garantir a precisão milimétrica.</p>',
    'prazos' => '<p>O prazo estimado para a conclusão dos serviços é de <strong>${prazo_execucao}</strong>.</p>',
    'investimento' => '<p>O valor total dos serviços é de <strong>${ValorProposta}</strong> (${ValorExtenso}).</p>'
];

// Processamento de Variáveis
$vars = $mockData;
$vars['ValorProposta'] = formatarMoeda($vars['valor_proposta']);
$vars['ValorExtenso'] = $vars['valor_extenso'];
$vars['ClienteCidadeUF'] = $vars['cidade_obra'] . '-' . $vars['estado_obra'];

function substituir($texto, $mapa) {
    foreach ($mapa as $chave => $valor) {
        $texto = str_ireplace('${' . $chave . '}', $valor, $texto);
    }
    return $texto;
}

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Visualização de Teste</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #525659; margin: 0; padding: 20px; }
        .page { background: white; width: 21cm; min-height: 29.7cm; padding: 2.5cm; margin: 0 auto 20px auto; box-shadow: 0 0 10px rgba(0,0,0,0.5); }
        .header { border-bottom: 2px solid #ddd; padding-bottom: 10px; margin-bottom: 30px; display: flex; justify-content: space-between; align-items: center; }
        .logo { max-height: 60px; }
        h1 { color: #333; font-size: 24px; margin: 0; }
        h2 { color: #f97316; font-size: 18px; border-left: 4px solid #f97316; padding-left: 10px; margin-top: 30px; }
        p, li { line-height: 1.6; color: #444; text-align: justify; }
        .footer { border-top: 1px solid #eee; padding-top: 10px; font-size: 10px; color: #999; text-align: center; margin-top: 50px; }
    </style>
</head>
<body>

    <div class="page">
        <div class="header">
            <div>
                <h1>Proposta Técnica</h1>
                <p style="margin:5px 0;font-size:12px">Ref: <?= $vars['numero_proposta'] ?></p>
            </div>
            <!-- Logo Mock -->
            <div style="background:#eee;width:150px;height:50px;display:flex;align-items:center;justify-content:center;color:#999;font-size:10px;">LOGO EMPRESA</div>
        </div>

        <!-- Renderização dos Blocos -->
        <?php foreach ($conteudoBlocos as $titulo => $conteudo): ?>
            <div class="bloco">
                <?php if($titulo !== 'apresentacao'): ?>
                    <h2><?= ucfirst($titulo) ?></h2>
                <?php endif; ?>
                
                <?= substituir($conteudo, $vars) ?>
            </div>
        <?php endforeach; ?>

        <div class="footer">
            <?= $vars['Empresa'] ?> | <?= $vars['Cidade'] ?> | <?= dataPorExtenso() ?>
        </div>
    </div>

</body>
</html>
