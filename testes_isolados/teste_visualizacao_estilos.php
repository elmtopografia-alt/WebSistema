<?php
// Configuração
$css_param = $_GET['estilo'] ?? '1';
$css_file = "estilo_{$css_param}.css";

// Título do estilo
$titulos = [
    '1' => 'Clássico / Elegante',
    '2' => 'Drone / Tecnológico',
    '3' => 'Moderno / Minimalista'
];
$titulo_atual = $titulos[$css_param] ?? 'Desconhecido';

// Carrega o conteúdo base
$html_base = file_get_contents('modelo_base.html');

// Carrega o CSS selecionado
$css_content = '';
if (file_exists($css_file)) {
    $css_content = file_get_contents($css_file);
} else {
    $css_content = "/* Erro: Arquivo $css_file não encontrado */";
}

// Injeção de CSS no HTML
$html_final = str_replace(
    '/* [COLE AQUI O CSS DO TEMA ESCOLHIDO] */', 
    $css_content, 
    $html_base
);

// Injeção da Barra de Controle (interface de teste)
$barra_controle = "
<style>
    .barra-controle-teste {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        background: #1e293b;
        color: white;
        padding: 10px 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        z-index: 9999;
        font-family: sans-serif;
        box-shadow: 0 4px 12px rgba(0,0,0,0.3);
    }
    .barra-controle-teste h1 {
        margin: 0;
        font-size: 14px;
        font-weight: normal;
        color: #94a3b8;
    }
    .barra-controle-teste strong {
        color: white;
    }
    .botoes-teste {
        display: flex;
        gap: 10px;
    }
    .btn-teste {
        background: #334155;
        color: white;
        border: 1px solid #475569;
        padding: 6px 12px;
        border-radius: 4px;
        text-decoration: none;
        font-size: 12px;
        transition: 0.2s;
    }
    .btn-teste:hover {
        background: #475569;
    }
    .btn-teste.ativo {
        background: #2563eb;
        border-color: #3b82f6;
        font-weight: bold;
    }
    body {
        margin-top: 60px !important; /* Espaço para a barra */
    }
    @media print {
        .barra-controle-teste { display: none; }
        body { margin-top: 0 !important; }
    }
</style>
<div class='barra-controle-teste no-print'>
    <h1>Testando Estilo: <strong>{$titulo_atual}</strong></h1>
    <div class='botoes-teste'>
        <a href='?estilo=1' class='btn-teste " . ($css_param == '1' ? 'ativo' : '') . "'>Estilo 1 (Clássico)</a>
        <a href='?estilo=2' class='btn-teste " . ($css_param == '2' ? 'ativo' : '') . "'>Estilo 2 (Drone)</a>
        <a href='?estilo=3' class='btn-teste " . ($css_param == '3' ? 'ativo' : '') . "'>Estilo 3 (Moderno)</a>
    </div>
</div>
";

// Insere a barra logo após <body>
$html_final = str_replace('<body>', '<body>' . $barra_controle, $html_final);

// Renderiza
echo $html_final;
?>
