<?php
/**
 * SGT Propostas v2 – Interface de Geração e Teste
 * Integrado ao ResolvedorChavesSistema e sessão real do sistema.
 *
 * Acesse: https://elmtopografia.com.br/Orcamento/sgt_propostas_v2.php
 */

session_start();

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/ResolvedorChavesSistema.php';
require_once __DIR__ . '/core/TemaEngine.php';
require_once __DIR__ . '/core/ModeloBase.php';
require_once __DIR__ . '/modelos_prod/PropostaDrone.php';

// ── Autenticação ─────────────────────────────────────────────────────────────
$id_usuario = (int)($_SESSION['id_usuario'] ?? $_SESSION['usuario_id'] ?? 0);

// Modo dev: permite testar sem sessão com dados mock
$modo_dev = !$id_usuario;

// ── Resolvedor ───────────────────────────────────────────────────────────────
if ($modo_dev) {
    // Mock de resolvedor para testes sem banco
    $resolvedor = new class {
        public function resolver(array $vars, int $userId, array $dados): array {
            $mock = [
                'Empresa'               => 'ELM Topografia Ltda',
                'CNPJ'                  => '12.345.678/0001-90',
                'whatsapp'              => '(31) 98765-4321',
                'Cidade'                => 'Belo Horizonte',
                'DataExtenso'           => '27 de fevereiro de 2026',
                'numero_proposta'       => '2026-001',
                'nome_cliente_salvo'    => 'João da Silva',
                'email_salvo'           => 'joao@email.com',
                'telefone_salvo'        => '(31) 3456-7890',
                'celular_salvo'         => '(31) 98765-4321',
                'whatsapp_salvo'        => '(31) 98765-4321',
                'endereco_obra'         => 'Rodovia MG-010, km 35',
                'bairro_obra'           => 'Zona Rural',
                'cidade_obra'           => 'Lagoa Santa',
                'estado_obra'           => 'MG',
                'AreaEstimada'          => '120.000 m²',
                'finalidade'            => 'Levantamento topográfico para implantação de loteamento residencial.',
                'TipoTerreno'           => 'Ondulado com afloramentos rochosos',
                'CoberturaVegetal'      => 'Média – Cerrado com pastagem',
                'AcessoLocal'           => 'Fácil – estrada pavimentada até o limite da área',
                'RestricoesAereas'      => 'Nenhuma – autorização DECEA obtida',
                'Drone'                 => 'DJI Phantom 4 RTK (câmera 20MP)',
                'GPS'                   => 'Trimble R12i (RTK/PPK)',
                'Estacao_Total'         => 'Topcon GPT-9005A',
                'Veiculo'               => 'Toyota Hilux 4x4',
                'ValorProposta'         => '22.000,00',
                'ValorExtenso'          => 'vinte e dois mil reais',
                'mobilizacao_percentual'=> '50',
                'mobilizacao_valor'     => '11.000,00',
                'restante_percentual'   => '50',
                'restante_valor'        => '11.000,00',
                'Banco'                 => 'Banco do Brasil',
                'Agencia'               => '1234-5',
                'Conta'                 => '67890-1',
                'PIX'                   => '12.345.678/0001-90',
            ];
            // Mescla com $dados reais (se houver)
            return array_merge($mock, $dados);
        }
    };
} else {
    $resolvedor = new ResolvedorChavesSistema($conn);
}

// ── Processa formulário ───────────────────────────────────────────────────────
$cor   = filter_input(INPUT_POST, 'cor',  FILTER_SANITIZE_SPECIAL_CHARS) ?? 'verde';
$acao  = filter_input(INPUT_POST, 'acao', FILTER_SANITIZE_SPECIAL_CHARS) ?? '';
$cores = TemaEngine::coresDisponiveis();
$html_proposta = '';

if ($acao === 'gerar') {
    $modelo        = new PropostaDrone($cor);
    $html_proposta = $modelo->render([], $resolvedor, $id_usuario);
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SGT Propostas v2 – Gerador</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; }

        body {
            font-family: 'Segoe UI', system-ui, sans-serif;
            margin: 0;
            padding: 20px;
            background: #0f172a;
            color: #e2e8f0;
            min-height: 100vh;
        }

        .container { max-width: 1100px; margin: 0 auto; }

        h1 {
            color: #10b981;
            font-size: 1.5rem;
            margin-top: 0;
        }

        .card {
            background: #1e293b;
            border: 1px solid #334155;
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 24px;
        }

        .badge-dev {
            display: inline-block;
            background: #f59e0b;
            color: #000;
            font-size: 0.7rem;
            font-weight: 700;
            padding: 2px 8px;
            border-radius: 99px;
            margin-left: 8px;
            vertical-align: middle;
        }

        /* Seletor de cores */
        .cores { display: flex; gap: 12px; flex-wrap: wrap; margin: 16px 0; }

        .cor-opcao {
            position: relative;
            cursor: pointer;
        }

        .cor-opcao input[type=radio] { display: none; }

        .cor-label {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 6px;
            padding: 14px 20px;
            border-radius: 10px;
            font-size: 0.875rem;
            font-weight: 600;
            border: 2px solid transparent;
            transition: all 0.2s;
            cursor: pointer;
            min-width: 110px;
        }

        .cor-label:hover { transform: translateY(-2px); }

        .cor-opcao input:checked + .cor-label {
            border-color: #fff;
            box-shadow: 0 0 0 2px rgba(255,255,255,0.3);
        }

        .cor-azul    { background: #1e3a8a; color: #fff; }
        .cor-verde   { background: #065f46; color: #fff; }
        .cor-laranja { background: #7c2d12; color: #fff; }
        .cor-cinza   { background: #1f2937; color: #fff; }

        .icone { font-size: 1.5rem; }

        /* Botão */
        .btn-gerar {
            background: #10b981;
            color: #fff;
            border: none;
            padding: 12px 28px;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }

        .btn-gerar:hover { background: #059669; }

        /* Preview */
        .preview-wrapper {
            background: #fff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 24px rgba(0,0,0,0.3);
        }

        .preview-header {
            background: #0f172a;
            padding: 12px 20px;
            font-size: 0.875rem;
            color: #94a3b8;
            border-bottom: 1px solid #1e293b;
        }

        /* Info */
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 12px;
            margin-top: 8px;
        }

        .info-item {
            background: #0f172a;
            padding: 12px;
            border-radius: 8px;
            font-size: 0.8rem;
        }

        .info-item strong {
            display: block;
            color: #10b981;
            margin-bottom: 4px;
        }
    </style>
</head>
<body>
<div class="container">

    <div class="card">
        <h1>🎯 SGT Propostas v2
            <?php if ($modo_dev): ?>
                <span class="badge-dev">MODO DEV (sem sessão)</span>
            <?php endif; ?>
        </h1>

        <div class="info-grid">
            <div class="info-item">
                <strong>Modelo ativo</strong>
                PropostaDrone – Topografia
            </div>
            <div class="info-item">
                <strong>Usuário</strong>
                <?= $modo_dev ? 'Mock (dev)' : "ID {$id_usuario}" ?>
            </div>
            <div class="info-item">
                <strong>Arquitetura</strong>
                SGT Template Engine v2
            </div>
            <div class="info-item">
                <strong>Cores disponíveis</strong>
                <?= count($cores) ?> temas
            </div>
        </div>
    </div>

    <div class="card">
        <form method="post" id="form-gerar">
            <h3 style="margin-top:0;">Escolha o tema de cores:</h3>

            <div class="cores">
                <label class="cor-opcao">
                    <input type="radio" name="cor" value="azul" <?= $cor === 'azul' ? 'checked' : '' ?>>
                    <span class="cor-label cor-azul">
                        <span class="icone">🏢</span>Corporativo
                    </span>
                </label>
                <label class="cor-opcao">
                    <input type="radio" name="cor" value="verde" <?= $cor === 'verde' ? 'checked' : '' ?>>
                    <span class="cor-label cor-verde">
                        <span class="icone">🌿</span>Topografia
                    </span>
                </label>
                <label class="cor-opcao">
                    <input type="radio" name="cor" value="laranja" <?= $cor === 'laranja' ? 'checked' : '' ?>>
                    <span class="cor-label cor-laranja">
                        <span class="icone">⚡</span>Energia
                    </span>
                </label>
                <label class="cor-opcao">
                    <input type="radio" name="cor" value="cinza" <?= $cor === 'cinza' ? 'checked' : '' ?>>
                    <span class="cor-label cor-cinza">
                        <span class="icone">📋</span>Institucional
                    </span>
                </label>
            </div>

            <button type="submit" name="acao" value="gerar" class="btn-gerar">
                📄 Gerar Proposta Preview
            </button>
        </form>
    </div>

    <?php if ($html_proposta): ?>
    <div class="card" style="padding:0;">
        <div class="preview-header">
            📄 Preview – Tema: <strong><?= ucfirst($cor) ?></strong> &nbsp;|&nbsp;
            Modelo: PropostaDrone
        </div>
        <div class="preview-wrapper">
            <?= $html_proposta ?>
        </div>
    </div>
    <?php endif; ?>

</div>

<script>
// Destaca o radio selecionado visualmente
document.querySelectorAll('.cor-opcao input').forEach(radio => {
    radio.addEventListener('change', () => {
        document.getElementById('form-gerar').submit();
    });
});
</script>
</body>
</html>
