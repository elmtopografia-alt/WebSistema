<?php
/**
 * simulador_fluxo.php
 * Simula o fluxo completo: Dados → Salvar → Editor
 * Bypassa o CSRF para testes. Use: /Orcamento/simulador_fluxo.php
 */
require_once __DIR__ . '/session_validator.php';
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/ConnectionManager.php';
require_once __DIR__ . '/PropostaRepository.php';

$repo = new PropostaRepository();
$conn = $repo->getConn();
$idUsuario = $_SESSION['usuario_id'] ?? 0;

// Busca dados da empresa do usuário logado
$empresa = $conn->query("SELECT * FROM DadosEmpresa WHERE id_criador = $idUsuario LIMIT 1")->fetch_assoc();

// Busca clientes do usuário para o select
$clientes = [];
$res = $conn->query("SELECT id_cliente, nome_cliente, empresa, email, telefone FROM Clientes WHERE id_criador = $idUsuario ORDER BY nome_cliente ASC LIMIT 50");
if ($res) while ($r = $res->fetch_assoc()) $clientes[] = $r;

$cores = ['verde', 'azul', 'laranja', 'cinza'];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Simulador de Fluxo | SGT</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Segoe UI', sans-serif; background: #0f172a; color: #e2e8f0; min-height: 100vh; padding: 30px 20px; }
        h1 { color: #10b981; margin-bottom: 4px; font-size: 1.4rem; }
        .sub { color: #64748b; font-size: 0.82rem; margin-bottom: 24px; }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
        .full { grid-column: 1 / -1; }
        .card { background: rgba(30,41,59,0.85); border: 1px solid rgba(255,255,255,0.08); border-radius: 12px; padding: 20px; margin-bottom: 16px; }
        .section-title { color: #60a5fa; font-size: 0.78rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 14px; display: flex; align-items: center; gap: 6px; }
        label { display: block; font-size: 0.75rem; color: #94a3b8; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 5px; }
        input, select, textarea {
            width: 100%; background: rgba(15,23,42,0.7); border: 1px solid rgba(255,255,255,0.1);
            color: #e2e8f0; padding: 9px 12px; border-radius: 8px; font-size: 0.85rem;
            outline: none; transition: border-color 0.2s;
        }
        input:focus, select:focus, textarea:focus { border-color: #10b981; }
        select option { background: #1e293b; }
        .field { margin-bottom: 12px; }

        /* Seletor de cor visual */
        .cor-selector { display: flex; gap: 10px; flex-wrap: wrap; }
        .cor-btn { display: flex; flex-direction: column; align-items: center; gap: 4px; cursor: pointer; }
        .cor-btn input[type=radio] { display: none; }
        .cor-circulo {
            width: 42px; height: 42px; border-radius: 50%; border: 3px solid transparent;
            transition: all 0.2s; display: flex; align-items: center; justify-content: center;
        }
        .cor-btn input:checked + .cor-circulo { border-color: #fff; transform: scale(1.15); box-shadow: 0 0 12px rgba(255,255,255,0.3); }
        .verde  .cor-circulo { background: linear-gradient(135deg, #10b981, #059669); }
        .azul   .cor-circulo { background: linear-gradient(135deg, #3b82f6, #1d4ed8); }
        .laranja .cor-circulo { background: linear-gradient(135deg, #f97316, #ea580c); }
        .cinza  .cor-circulo { background: linear-gradient(135deg, #6b7280, #4b5563); }
        .cor-nome { font-size: 0.7rem; color: #94a3b8; text-transform: capitalize; }

        .btn-submit {
            width: 100%; padding: 14px; background: linear-gradient(135deg, #10b981, #059669);
            color: #fff; font-size: 1rem; font-weight: 700; border: none; border-radius: 10px;
            cursor: pointer; transition: all 0.2s; display: flex; align-items: center; justify-content: center; gap: 8px;
        }
        .btn-submit:hover { background: linear-gradient(135deg, #059669, #047857); transform: translateY(-1px); }
        .info-box { background: rgba(59,130,246,0.1); border: 1px solid rgba(59,130,246,0.2); border-radius: 8px; padding: 10px 14px; font-size: 0.78rem; color: #93c5fd; }
        .warn-box { background: rgba(245,158,11,0.1); border: 1px solid rgba(245,158,11,0.2); border-radius: 8px; padding: 10px 14px; font-size: 0.78rem; color: #fcd34d; margin-bottom: 16px; }
    </style>
</head>
<body>

<h1><i class="bi bi-play-circle"></i> Simulador de Fluxo — Criar Proposta</h1>
<p class="sub">Preencha os dados abaixo para criar uma proposta de teste e abrir direto no editor.</p>

<div class="warn-box">
    <i class="bi bi-exclamation-triangle"></i>
    <strong>Modo Teste:</strong> Esta proposta será criada de verdade no banco. Delete depois se não precisar.
</div>

<form method="POST" action="salvar_proposta.php">
    <!-- Campos de bypass -->
    <input type="hidden" name="simulador_bypass" value="1">
    <input type="hidden" name="formato_saida" value="editor">
    <input type="hidden" name="modelo_docx" value="PropostaDrone">
    <input type="hidden" name="is_demo" value="0">

    <!-- 1. CLIENTE -->
    <div class="card">
        <div class="section-title"><i class="bi bi-person"></i> Cliente</div>
        <div class="grid">
            <div class="field">
                <label>Selecionar Cliente Existente</label>
                <select name="id_cliente" id="sel-cliente" onchange="preencherCliente(this)">
                    <option value="">— Selecione ou preencha abaixo —</option>
                    <?php foreach ($clientes as $c): ?>
                        <option value="<?= $c['id_cliente'] ?>" 
                            data-nome="<?= htmlspecialchars($c['nome_cliente']) ?>"
                            data-empresa="<?= htmlspecialchars($c['empresa'] ?? '') ?>"
                            data-email="<?= htmlspecialchars($c['email'] ?? '') ?>"
                            data-telefone="<?= htmlspecialchars($c['telefone'] ?? '') ?>">
                            <?= htmlspecialchars($c['nome_cliente']) ?>
                            <?= $c['empresa'] ? '— '.$c['empresa'] : '' ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label>Nome do Cliente</label>
                <input type="text" name="nome_cliente_salvo" id="f-nome" placeholder="João da Silva" required>
            </div>
            <div class="field">
                <label>Email</label>
                <input type="email" name="email_salvo" id="f-email" placeholder="joao@empresa.com">
            </div>
            <div class="field">
                <label>Telefone</label>
                <input type="text" name="telefone_salvo" id="f-tel" placeholder="(31) 99999-9999">
            </div>
            <div class="field full">
                <label>Empresa do Cliente</label>
                <input type="text" name="empresa_cliente_salvo" id="f-emp" placeholder="Empresa Ltda.">
            </div>
        </div>
    </div>

    <!-- 2. LOCAL DA OBRA -->
    <div class="card">
        <div class="section-title"><i class="bi bi-geo-alt"></i> Local da Obra</div>
        <div class="grid">
            <div class="field full">
                <label>Endereço</label>
                <input type="text" name="endereco_obra" placeholder="Rua das Flores, 123" value="Rodovia BR-040, Km 12">
            </div>
            <div class="field">
                <label>Bairro</label>
                <input type="text" name="bairro_obra" placeholder="Centro" value="Zona Rural">
            </div>
            <div class="field">
                <label>Cidade</label>
                <input type="text" name="cidade_obra" placeholder="Belo Horizonte" value="Belo Horizonte">
            </div>
            <div class="field">
                <label>Estado (UF)</label>
                <input type="text" name="estado_obra" placeholder="MG" maxlength="2" value="MG">
            </div>
            <div class="field">
                <label>Área Estimada</label>
                <input type="text" name="area_obra" placeholder="250" value="250">
            </div>
            <div class="field">
                <label>Unidade</label>
                <select name="unidade_area">
                    <option value="ha">ha</option>
                    <option value="m²" selected>m²</option>
                    <option value="km²">km²</option>
                </select>
            </div>
        </div>
    </div>

    <!-- 3. ESCOPO TÉCNICO -->
    <div class="card">
        <div class="section-title"><i class="bi bi-tools"></i> Escopo Técnico</div>
        <div class="grid">
            <div class="field">
                <label>Tipo de Levantamento</label>
                <input type="text" name="tipo_levantamento" value="Levantamento Aerofotogramétrico com Drone">
            </div>
            <div class="field">
                <label>Finalidade</label>
                <input type="text" name="finalidade" value="Mapeamento topográfico para projeto de terraplenagem">
            </div>
            <div class="field">
                <label>Tipo de Terreno</label>
                <input type="text" name="tipo_terreno" value="Acidentado">
            </div>
            <div class="field">
                <label>Cobertura Vegetal</label>
                <input type="text" name="cobertura_vegetal" value="Moderada">
            </div>
            <div class="field">
                <label>Acesso ao Local</label>
                <input type="text" name="acesso_local" value="Estrada de terra">
            </div>
            <div class="field">
                <label>Restrições Aéreas</label>
                <input type="text" name="restricoes_aereas" value="Nenhuma restrição identificada">
            </div>
        </div>
    </div>

    <!-- 4. EQUIPAMENTOS -->
    <div class="card">
        <div class="section-title"><i class="bi bi-cpu"></i> Equipamentos</div>
        <div class="grid">
            <div class="field">
                <label>Drone</label>
                <input type="text" name="modelo_drone" value="DJI Phantom 4 RTK">
            </div>
            <div class="field">
                <label>GPS / GNSS</label>
                <input type="text" name="modelo_gps" value="Trimble R10">
            </div>
            <div class="field">
                <label>Estação Total</label>
                <input type="text" name="modelo_estacao_total" value="Leica TS16">
            </div>
            <div class="field">
                <label>Veículo</label>
                <input type="text" name="modelo_veiculo" value="Toyota Hilux">
            </div>
        </div>
    </div>

    <!-- 5. VALORES -->
    <div class="card">
        <div class="section-title"><i class="bi bi-currency-dollar"></i> Valores</div>
        <div class="grid">
            <div class="field">
                <label>Valor Final (R$)</label>
                <input type="number" name="valor_proposta_manual" step="0.01" value="18500.00" placeholder="18500.00">
                <input type="hidden" name="percentual_lucro" value="30">
                <input type="hidden" name="mobilizacao_percentual" value="30">
                <input type="hidden" name="valor_desconto" value="0">
            </div>
            <div class="field">
                <label>Prazo de Execução</label>
                <input type="text" name="prazo_execucao" value="30 dias">
            </div>
            <div class="field">
                <label>Dias em Campo</label>
                <input type="number" name="dias_campo" value="5">
            </div>
            <div class="field">
                <label>Dias no Escritório</label>
                <input type="number" name="dias_escritorio" value="10">
            </div>
        </div>
    </div>

    <!-- 6. COR DO TEMA -->
    <div class="card">
        <div class="section-title"><i class="bi bi-palette"></i> Cor do Tema</div>
        <div class="cor-selector">
            <?php foreach ($cores as $i => $c): ?>
            <label class="cor-btn <?= $c ?>">
                <input type="radio" name="cor" value="<?= $c ?>" <?= $i === 0 ? 'checked' : '' ?>>
                <div class="cor-circulo"></div>
                <span class="cor-nome"><?= ucfirst($c) ?></span>
            </label>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- SUBMIT -->
    <div class="info-box" style="margin-bottom:16px;">
        <i class="bi bi-info-circle"></i>
        Ao clicar em <strong>Criar e Abrir Editor</strong>, o sistema vai: (1) salvar a proposta no banco, (2) associar o modelo <code>PropostaDrone</code> e a cor escolhida, (3) redirecionar para o editor dinâmico.
    </div>

    <button type="submit" class="btn-submit">
        <i class="bi bi-play-circle-fill"></i>
        Criar Proposta e Abrir no Editor
    </button>
</form>

<script>
function preencherCliente(sel) {
    const opt = sel.options[sel.selectedIndex];
    document.getElementById('f-nome').value  = opt.dataset.nome     || '';
    document.getElementById('f-email').value = opt.dataset.email    || '';
    document.getElementById('f-tel').value   = opt.dataset.telefone || '';
    document.getElementById('f-emp').value   = opt.dataset.empresa  || '';
}
</script>

</body>
</html>
