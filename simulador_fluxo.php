<?php
// simulador_fluxo.php - Ferramenta de Teste de Integração SGT V3.0
// Evitamos dar 'require session_validator' direto nele pois a regra do SGT 
// diz que todo POST consome e gera nova token rotativa (One-Time-Use).
session_start();
if (!isset($_SESSION['usuario_id'])) {
    die("Você precisa estar logado no SGT para rodar esse teste.");
}

$csrf_token = $_SESSION['csrf_token'] ?? '';

require_once __DIR__ . '/ConnectionManager.php';
$conn = ConnectionManager::get();

// Força a inserção de um cliente "dummy" se não houver NENHUM cliente real no banco 
// (Para evitar o erro FK na tabela Propostas que exige um id_cliente ativo)
$resCli = $conn->query("SELECT id_cliente, nome_cliente FROM Clientes ORDER BY id_cliente DESC LIMIT 1");
$clienteReal = $resCli->fetch_assoc();

if (!$clienteReal) {
    // Insere cliente provisório pro Simulador funcionar
    $conn->query("INSERT INTO Clientes (nome_cliente, status) VALUES ('Cliente Simulador API', 'Ativo')");
    $id_cli = $conn->insert_id;
    $nome_cli = 'Cliente Simulador API';
} else {
    $id_cli = $clienteReal['id_cliente'];
    $nome_cli = $clienteReal['nome_cliente'];
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>SGT Simulador de Fluxo de Criação</title>
    <style>
        body { font-family: 'Inter', sans-serif; background: #0f172a; color: #fff; padding: 40px; }
        .box { background: #1e293b; padding: 30px; border-radius: 12px; border: 1px solid #334155; margin-bottom: 20px; }
        h1 { color: #38bdf8; font-size: 24px; margin-bottom: 10px; }
        p { color: #94a3b8; margin-bottom: 20px; line-height: 1.5; }
        button { padding: 12px 24px; font-weight: bold; border-radius: 8px; border: none; cursor: pointer; transition: all 0.2s; font-size: 16px; }
        .btn-legacy { background: #f97316; color: white; }
        .btn-legacy:hover { background: #ea580c; }
        .btn-docx { background: #10b981; color: white; }
        .btn-docx:hover { background: #059669; }
        .data-preview { background: rgba(0,0,0,0.3); padding: 15px; border-radius: 8px; color: #a1a1aa; font-family: monospace; font-size: 12px; margin-top: 20px; }
    </style>
</head>
<body>

    <div style="max-width: 800px; margin: 0 auto;">
        <h1>🧪 Simulador de Redirecionamento </h1>
        <p>Estes botões enviam dados falsos diretamente para o <b>salvar_proposta.php</b>, cortando o passo-a-passo. É a forma mais rápida de descobrir se o sistema está desviando a rota pro lugar certo.</p>

        <!-- SIMULAÇÃO 1: FLUXO ANTIGO LEGACY -->
        <div class="box">
            <h2 style="color: #fdba74;">Situação 1: "Gerar Proposta Tradicional (Legacy)"</h2>
            <p>Se você não preencher <code>modelo_docx</code> e disser que o formato de saída é 'html', o sistema tem que gerar uma ID, salvar os custos e te mandar para a <b>gerar_proposta_html.php</b> (visualização antiga com barra lateral).</p>
            
            <form action="salvar_proposta.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                <!-- Dados Falsos -->
                <input type="hidden" name="id_cliente" value="<?= $id_cli ?>">
                <input type="hidden" name="nome_cliente_salvo" value="<?= $nome_cli ?>">
                <input type="hidden" name="id_servico" value="1">
                <input type="hidden" name="cidade_obra" value="Belo Horizonte">
                
                <!-- Informações cruciais da rota 1 -->
                <input type="hidden" name="formato_saida" value="html"> 
                <!-- Sem modelo_docx inserido! -->
                
                <!-- Bypass de Teste Anti-Fraude (Válido apenas para esse simulador) -->
                <input type="hidden" name="simulador_bypass" value="1">
                <button type="submit" class="btn-legacy">Testar Fluxo Tradicional ➡️</button>
            </form>
            
            <div class="data-preview">
                Dados enviados:<br>
                - formato_saida = 'html'<br>
                - modelo_docx = VAZIO<br>
                <b>Destino Esperado:</b> gerar_proposta_html.php?id=[NOVA_ID]
            </div>
        </div>

        <!-- SIMULAÇÃO 2: FLUXO PREMIUM DOCX -->
        <div class="box">
            <h2 style="color: #6ee7b7;">Situação 2: "Editor Avançado ✨ (DOCX)"</h2>
            <p>Se você enviar com <code>modelo_docx = PropostaDrone</code> e <code>formato_saida = editor</code>, o sistema TEM que salvar a mesma proposta no banco associada à esse layout, e ao invés do html antigo, ele te proíbe de abrir a tela antiga e te taca de volta para o <b>editor_dinamico.php</b> preenchido.</p>
            
            <form action="salvar_proposta.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                <input type="hidden" name="id_cliente" value="<?= $id_cli ?>">
                <input type="hidden" name="nome_cliente_salvo" value="<?= $nome_cli ?>">
                <input type="hidden" name="id_servico" value="1">
                <input type="hidden" name="cidade_obra" value="São Paulo">
                
                <!-- Informações cruciais da rota 2 (Botão atual editado) -->
                <input type="hidden" name="formato_saida" value="editor">
                <input type="hidden" name="modelo_docx" value="PropostaDrone">
                
                <!-- Bypass de Teste Anti-Fraude (Válido apenas para esse simulador) -->
                <input type="hidden" name="simulador_bypass" value="1">
                <button type="submit" class="btn-docx">Testar Fluxo Editor DOCX ➡️</button>
            </form>
            
             <div class="data-preview">
                Dados enviados:<br>
                - formato_saida = 'editor'<br>
                - modelo_docx = 'PropostaDrone'<br>
                <b>Destino Esperado:</b> editor_dinamico.php?id=[NOVA_ID]&modelo_docx=PropostaDrone&success=1
            </div>
        </div>
        
    </div>

</body>
</html>
