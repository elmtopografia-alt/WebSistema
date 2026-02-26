<?php
/**
 * simulador_wizard_real.php
 * Simula o fluxo real: escolhendo equipamento -> marca -> enviando para salvar_proposta.php
 */
require_once __DIR__ . '/ConnectionManager.php';
require_once __DIR__ . '/PropostaRepository.php';

$repo = new PropostaRepository();
$data = $repo->getAllLookupData($_SESSION['usuario_id'] ?? 1); // Força um ID se não houver sessão

// Simula a estrutura que o JS 'costs-manager.js' e 'proposta.js' montam
$opcoesLocacao = $data['arrays_js']['Tipo_Locacao'] ?? [];
$marcasPorTipo = $data['marcas'] ?? [];

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>SGT - Simulador de Fluxo Real</title>
    <style>
        body { font-family: sans-serif; background: #1a1a2e; color: #fff; padding: 20px; }
        .card { background: rgba(255,255,255,0.05); padding: 20px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.1); max-width: 600px; margin: auto; }
        select, input { width: 100%; padding: 10px; margin-bottom: 20px; background: #16213e; border: 1px solid #4834d4; color: #fff; border-radius: 4px; }
        label { display: block; margin-bottom: 5px; color: #a29bfe; font-size: 13px; font-weight: bold; }
        button { background: #4834d4; color: white; border: none; padding: 12px 20px; border-radius: 4px; cursor: pointer; font-weight: bold; width: 100%; }
        button:hover { background: #686de0; }
        .debug-box { background: #000; padding: 15px; border-radius: 4px; font-family: monospace; font-size: 12px; margin-top: 20px; color: #00d4ff; overflow-x: auto; }
    </style>
</head>
<body>

<div class="card">
    <h2>Simulador de Fluxo: Proposta #234</h2>
    <p style="font-size: 13px; color: #ccc;">Este simulador envia os dados exatamente como o <b>Wizard Real</b> (usando os novos nomes de campos).</p>
    
    <form action="salvar_proposta.php" method="POST">
        <!-- Bypass de Segurança para o Simular -->
        <input type="hidden" name="simulador_bypass" value="1">
        <input type="hidden" name="id_proposta" value="234">
        <input type="hidden" name="ajax" value="0">
        <input type="hidden" name="formato_saida" value="html">

        <!-- PASSO 1: ESCOLHA O EQUIPAMENTO -->
        <label>1. TIPO DE EQUIPAMENTO (Tipo_Locacao)</label>
        <select id="sel_tipo" name="locacao_id[]" onchange="updateMarcas()" required>
            <option value="">-- Selecione o Equipamento --</option>
            <?php foreach($opcoesLocacao as $opt): ?>
                <option value="<?= $opt['id'] ?>"><?= $opt['nome'] ?></option>
            <?php endforeach; ?>
        </select>

        <!-- PASSO 2: ESCOLHA A MARCA (FILTRADA DINAMICAMENTE) -->
        <label>2. MARCA / MODELO (Marcas)</label>
        <select id="sel_marca" name="locacao_id_marca[]" required disabled>
            <option value="">-- Selecione o Equipamento Primeiro --</option>
        </select>

        <!-- DADOS EXTRAS DO ITEM (COMO NO WIZARD) -->
        <div style="display: flex; gap: 10px;">
            <div style="flex:1">
                <label>QUANTIDADE</label>
                <input type="number" name="locacao_qtd[]" value="1">
            </div>
            <div style="flex:1">
                <label>VALOR MENSAL</label>
                <input type="number" name="locacao_valor[]" value="1500.00">
            </div>
            <div style="flex:1">
                <label>DIAS</label>
                <input type="number" name="locacao_dias[]" value="3">
            </div>
        </div>

        <button type="submit">MANDAR PARA O BANCO (salvar_proposta.php)</button>
    </form>

    <div class="debug-box" id="debug">
        // Pronto para simular...
    </div>
</div>

<script>
    const marcasPorTipo = <?php echo json_encode($marcasPorTipo); ?>;
    
    function updateMarcas() {
        const idTipo = document.getElementById('sel_tipo').value;
        const selMarca = document.getElementById('sel_marca');
        const debug = document.getElementById('debug');
        
        selMarca.innerHTML = '<option value="">-- Selecione a Marca --</option>';
        
        if (idTipo && marcasPorTipo[idTipo]) {
            marcasPorTipo[idTipo].forEach(m => {
                const opt = document.createElement('option');
                opt.value = m.id;
                opt.textContent = m.nome;
                selMarca.appendChild(opt);
            });
            selMarca.disabled = false;
            debug.innerText = "Simulando Wizard: Equipamento ID " + idTipo + " selecionado. Carregando " + marcasPorTipo[idTipo].length + " marcas correspondentes.";
        } else {
            selMarca.disabled = true;
            debug.innerText = "Nenhuma marca encontrada para este equipamento.";
        }
    }
</script>

</body>
</html>
