<?php
/**
 * test_upgrade_v2.php
 * Valida o CRUD v2.0 do SGT Propostas.
 */

require_once 'session_validator.php';
require_once 'PropostaRepository.php';

$id_usuario = $_SESSION['usuario_id'];
$repo = new PropostaRepository();

echo "<h2>Teste de Validação SGT Upgrade v2.0</h2>";

try {
    // 1. Mock de Dados v2.0
    $dadosTeste = [
        'empresa_proponente_nome' => 'TESTE REPO v2',
        'nome_cliente_salvo' => 'Cliente Teste v2',
        'salarios' => [
            'row_1' => ['funcao' => 1, 'quantidade' => 2, 'dias' => 5, 'valor' => 3000, 'encargos' => 67],
            'row_2' => ['funcao' => 2, 'quantidade' => 1, 'dias' => 3, 'valor' => 5000, 'encargos' => 67]
        ],
        'admin' => [
            'row_1' => ['tipo' => 1, 'quantidade' => 1, 'valor' => 500, 'periodo' => 'Mensal']
        ],
        'estadias' => [
            'row_1' => ['tipo' => 1, 'quantidade' => 2, 'noites' => 4, 'valor' => 250]
        ],
        'consumos' => [
            'row_1' => ['tipo' => 1, 'quantidade' => 1, 'kml' => 12, 'valor_litro' => 6.5, 'km' => 200]
        ],
        'locacoes' => [
            'row_1' => ['tipo' => 1, 'marca' => 1, 'quantidade' => 1, 'valor' => 1200, 'dias' => 10]
        ]
    ];

    echo "<li><b>Passo 1:</b> Salvando nova proposta v2.0... ";
    $id = $repo->salvarCompleto($dadosTeste, $id_usuario);
    echo "<span style='color:green'>Sucesso (ID: $id)</span></li>";

    echo "<li><b>Passo 2:</b> Recuperando dados v2.0... ";
    $recuperado = $repo->buscarCompleto($id, $id_usuario);
    if ($recuperado && isset($recuperado['planilha'])) {
        $p = $recuperado['planilha'];
        $erros = [];
        if (count($p['salarios']) !== 2) $erros[] = "Qtd salários incorreta";
        if (count($p['admin']) !== 1) $erros[] = "Qtd admin incorreta";
        
        if (empty($erros)) {
            echo "<span style='color:green'>Dados íntegros!</span></li>";
        } else {
            echo "<span style='color:red'>Falha: " . implode(', ', $erros) . "</span></li>";
        }
    } else {
        echo "<span style='color:red'>Falha ao recuperar planilha.</span></li>";
    }

    echo "<li><b>Passo 3:</b> Testando Atualização (Overwrite)... ";
    $dadosTeste['area'] = 5000;
    $sucesso = $repo->atualizarCompleto($id, $dadosTeste, $id_usuario);
    if ($sucesso) {
        $check = $repo->buscarPorId($id);
        if ($check['area_obra'] == 5000) {
            echo "<span style='color:green'>Sucesso no Update!</span></li>";
        } else {
            echo "<span style='color:red'>Falha na persistência do Update.</span></li>";
        }
    } else {
        echo "<span style='color:red'>Falha no método atualizarCompleto.</span></li>";
    }

    echo "<h3 style='color:green'>Upgrade v2.0 Validado com Sucesso!</h3>";
    echo "<p><a href='editar_proposta.php?id=$id'>Visualizar esta proposta no Editor</a></p>";

} catch (Throwable $e) {
    echo "<h3 style='color:red'>Erro no Teste:</h3>";
    echo "<pre>" . $e->getMessage() . "\n" . $e->getTraceAsString() . "</pre>";
}
