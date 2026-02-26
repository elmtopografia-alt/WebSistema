<?php
// teste_editor_mock.php
// Mock completo do editor_dinamico com dados fictícios
// Não depende do banco de dados

$modo = $_GET['modo'] ?? 'docx';

// Dados fictícios
$cliente = [
    'nome' => 'Empresa Teste LTDA',
    'cnpj' => '12.345.678/0001-90',
    'endereco' => 'Av. Paulista, 1000',
    'cidade' => 'São Paulo',
    'estado' => 'SP',
    'cep' => '01310-100',
    'telefone' => '(11) 98765-4321',
    'email' => 'contato@empresateste.com.br',
    'responsavel' => 'João da Silva'
];

$proposta = [
    'id' => 999999,
    'numero' => 'PROP-TESTE-001',
    'data_criacao' => date('d/m/Y'),
    'valor_total' => 15000.00,
    'status' => 'rascunho'
];

// Blocos baseados no modo
if ($modo === 'legado') {
    $blocos = [
        'cabecalho' => ['titulo' => 'PROPOSTA LEGADO', 'subtitulo' => 'Modo Hardcoded'],
        'dados_cliente' => $cliente,
        'escopo' => ['descricao' => 'Teste modo legado'],
        'condicoes' => ['pagamento' => '50/50'],
        'assinaturas' => ['empresa' => 'Sua Empresa', 'cliente' => $cliente['responsavel']]
    ];
} else {
    // Modo DOCX - simula estrutura do parser
    $blocos = [
        'docx_bloco_0_content' => json_encode(['tipo' => 'cabecalho', 'campos' => ['titulo' => 'PROPOSTA DOCX', 'data' => date('d/m/Y')]]),
        'docx_bloco_1_content' => json_encode(['tipo' => 'dados_cliente', 'campos' => ['nome' => $cliente['nome'], 'cnpj' => $cliente['cnpj']]]),
        'docx_bloco_2_content' => json_encode(['tipo' => 'escopo', 'campos' => ['descricao' => 'Desenvolvimento sistema teste']]),
        'docx_bloco_3_content' => json_encode(['tipo' => 'valores', 'campos' => ['total' => 'R$ 15.000,00']]),
    ];
    $proposta['modelo_docx_id'] = 1;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Teste Editor - <?php echo strtoupper($modo); ?></title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Segoe UI', sans-serif; background: #f5f5f5; }
        
        /* Banner de teste */
        .test-banner {
            background: linear-gradient(90deg, #ff6b6b, #feca57);
            color: white;
            padding: 15px;
            text-align: center;
            font-weight: bold;
            position: fixed;
            top: 0; left: 0; right: 0;
            z-index: 1000;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
        }
        .test-banner a { color: white; margin: 0 15px; }
        
        /* Container principal */
        .container {
            max-width: 900px;
            margin: 80px auto 20px;
            background: white;
            padding: 40px;
            box-shadow: 0 2px 20px rgba(0,0,0,0.1);
        }
        
        /* Blocos editáveis */
        .bloco {
            border: 2px dashed #ddd;
            margin: 20px 0;
            padding: 20px;
            border-radius: 8px;
            transition: all 0.3s;
        }
        .bloco:hover { border-color: #4ecdc4; background: #f8fffe; }
        .bloco-header {
            background: #2c3e50;
            color: white;
            padding: 10px 15px;
            margin: -20px -20px 15px -20px;
            border-radius: 6px 6px 0 0;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        /* Campos */
        .campo { margin: 15px 0; }
        .campo label {
            display: block;
            font-size: 12px;
            color: #666;
            text-transform: uppercase;
            margin-bottom: 5px;
        }
        .campo input, .campo textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        .campo input:focus, .campo textarea:focus {
            outline: none;
            border-color: #4ecdc4;
        }
        
        /* Ações */
        .acoes {
            position: fixed;
            bottom: 30px;
            right: 30px;
            display: flex;
            gap: 10px;
        }
        .btn {
            padding: 15px 30px;
            border: none;
            border-radius: 50px;
            cursor: pointer;
            font-weight: bold;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            transition: transform 0.2s;
        }
        .btn:hover { transform: translateY(-2px); }
        .btn-primary { background: #4ecdc4; color: white; }
        .btn-secondary { background: #95a5a6; color: white; }
        
        /* Preview modo DOCX */
        .docx-preview {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 4px;
            font-family: monospace;
            font-size: 12px;
            color: #666;
        }
    </style>
</head>
<body>

<div class="test-banner">
    🧪 MODO TESTE (SEM BANCO) | 
    Modo atual: <strong><?php echo strtoupper($modo); ?></strong> |
    <a href="?modo=legado">Switch para Legado</a>
    <a href="?modo=docx">Switch para DOCX</a>
    <a href="editor_dinamico.php?id=1" style="color: #ffeaa7;">← Voltar ao Editor Real</a>
</div>

<div class="container">
    <h1 style="margin-bottom: 30px; color: #2c3e50;">
        Editor de Proposta #<?php echo $proposta['numero']; ?>
        <span style="font-size: 14px; color: #7f8c8d; display: block; margin-top: 5px;">
            Cliente: <?php echo $cliente['nome']; ?>
        </span>
    </h1>

    <form id="formTeste" onsubmit="return false;">
        <input type="hidden" name="proposta_id" value="<?php echo $proposta['id']; ?>">
        <input type="hidden" name="modo" value="<?php echo $modo; ?>">
        
        <?php if ($modo === 'legado'): ?>
            
            <!-- MODO LEGADO: Blocos hardcoded -->
            <?php foreach ($blocos as $nomeBloco => $campos): ?>
            <div class="bloco" data-bloco="<?php echo $nomeBloco; ?>">
                <div class="bloco-header">📦 <?php echo str_replace('_', ' ', strtoupper($nomeBloco)); ?></div>
                <?php foreach ($campos as $key => $val): ?>
                <div class="campo">
                    <label><?php echo $key; ?></label>
                    <?php if (is_array($val)): ?>
                        <textarea rows="3"><?php echo json_encode($val, JSON_PRETTY_PRINT); ?></textarea>
                    <?php else: ?>
                        <input type="text" name="legado[<?php echo $nomeBloco; ?>][<?php echo $key; ?>]" 
                               value="<?php echo htmlspecialchars($val); ?>">
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endforeach; ?>

        <?php else: ?>
            
            <!-- MODO DOCX: Blocos dinâmicos -->
            <?php foreach ($blocos as $fieldName => $jsonContent): 
                $data = json_decode($jsonContent, true);
            ?>
            <div class="bloco" data-bloco-docx="<?php echo $fieldName; ?>">
                <div class="bloco-header" style="background: #e74c3c;">
                    📄 <?php echo $data['tipo']; ?> (<?php echo $fieldName; ?>)
                </div>
                
                <div class="docx-preview" style="margin-bottom: 15px;">
                    JSON original: <?php echo substr($jsonContent, 0, 100); ?>...
                </div>
                
                <?php foreach ($data['campos'] as $campoKey => $campoVal): ?>
                <div class="campo">
                    <label><?php echo $campoKey; ?></label>
                    <input type="text" 
                           name="<?php echo $fieldName; ?>[<?php echo $campoKey; ?>]" 
                           value="<?php echo htmlspecialchars($campoVal); ?>"
                           class="docx-field"
                           data-bloco="<?php echo $data['tipo']; ?>"
                           data-campo="<?php echo $campoKey; ?>">
                </div>
                <?php endforeach; ?>
            </div>
            <?php endforeach; ?>
            
            <!-- Campo hidden com estrutura completa para salvar -->
            <input type="hidden" name="estrutura_docx" id="estruturaDocx" value='<?php echo json_encode($blocos); ?>'>

        <?php endif; ?>
    </form>
</div>

<div class="acoes">
    <button class="btn btn-secondary" onclick="window.location.reload()">🔄 Resetar</button>
    <button class="btn btn-primary" onclick="salvarTeste()">💾 Salvar Teste</button>
</div>

<script>
// Simula o comportamento do salvar_proposta.php
function salvarTeste() {
    const form = document.getElementById('formTeste');
    const formData = new FormData(form);
    const modo = formData.get('modo');
    
    console.log('=== DADOS DO TESTE ===');
    console.log('Modo:', modo);
    console.log('Proposta ID:', formData.get('proposta_id'));
    
    if (modo === 'docx') {
        // Coleta campos dinâmicos DOCX
        const docxFields = {};
        document.querySelectorAll('.docx-field').forEach(input => {
            const bloco = input.dataset.bloco;
            const campo = input.dataset.campo;
            if (!docxFields[bloco]) docxFields[bloco] = {};
            docxFields[bloco][campo] = input.value;
        });
        
        console.log('Campos DOCX:', docxFields);
        
        // Simula o payload que iria para salvar_proposta.php
        const payload = {
            proposta_id: 999999,
            modo: 'docx',
            docx_blocos: docxFields,
            raw: Object.fromEntries(formData)
        };
        
        console.log('Payload completo:', payload);
        alert('Modo DOCX: Dados coletados! Veja console (F12) para o payload completo.\n\nEm produção, isso enviaria para salvar_proposta.php');
        
    } else {
        // Modo legado
        const legadoData = {};
        formData.forEach((val, key) => {
            if (key.startsWith('legado')) legadoData[key] = val;
        });
        console.log('Dados legado:', legadoData);
        alert('Modo LEGADO: Dados coletados! Veja console (F12).');
    }
}

// Atualiza JSON em tempo real quando edita (modo DOCX)
document.querySelectorAll('.docx-field').forEach(input => {
    input.addEventListener('input', function() {
        console.log('Campo alterado:', this.dataset.bloco + '.' + this.dataset.campo, '=', this.value);
    });
});
</script>

</body>
</html>