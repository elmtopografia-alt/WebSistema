<?php
/**
 * teste_visualizacao_real.php
 * Endpoint de Teste: Usa o renderizador *real* com dados COMPLETOS.
 */

session_start();
$_SESSION['usuario_id'] = 1;

// Texto Lorem Ipsum / Realista para preencher os blocos
$txtApresentacao = "<p>Prezados Senhores,</p><p>A <strong>SGT TOPOGRAFIA</strong> tem a satisfação de apresentar esta Proposta Técnica e Comercial para prestação de serviços de Engenharia de Agrimensura e Cartografia.</p><p>Nossa empresa conta com equipamentos de alta tecnologia e profissionais qualificados para garantir a excelência nos resultados.</p>";

$txtEscopo = "<ul>
<li>Levantamento planimétrico cadastral da área de intervenção;</li>
<li>Amarração geodésica ao Sistema Geodésico Brasileiro (SGB);</li>
<li>Cadastro de interferências visíveis (postes, árvores, caixas de inspeção);</li>
<li>Geração de Modelo Digital do Terreno (MDT);</li>
<li>Entrega de plantas em formato DWG e PDF.</li>
</ul>";

$txtMetodologia = "<p>Serão utilizados receptores GNSS RTK de dupla frequência para a determinação dos pontos de apoio e poligonal principal. O levantamento dos detalhes será realizado via estação total robótica e/ou aerofotogrametria com Drone, conforme as características do terreno.</p>";

$txtDocumentacao = "<p>Serão entregues os seguintes documentos:</p>
<ol>
<li>Plantas Topográficas em formato A0/A1 (PDF e plotadas);</li>
<li>Arquivos vetoriais (DWG/DXF);</li>
<li>Relatório Técnico com Anotação de Responsabilidade Técnica (ART);</li>
<li>Monografia dos marcos geodésicos implantados.</li>
</ol>";

$txtCondicoes = "<table style='width:100%'>
<thead><tr><th>Fase</th><th>%</th><th>Valor</th><th>Vencimento</th></tr></thead>
<tbody>
<tr><td>Aceite da Proposta (Mobilização)</td><td>30%</td><td>R$ 3.750,00</td><td>Imediato</td></tr>
<tr><td>Entrega Final</td><td>70%</td><td>R$ 8.750,00</td><td>Na entrega dos trabalhos</td></tr>
</tbody>
</table>";

$txtConsideracoes = "<p>Esta proposta tem validade de 15 dias. Os serviços serão iniciados após o aceite formal e pagamento da primeira parcela.</p>
<p>Colocamo-nos à disposição para quaisquer esclarecimentos.</p>";

// MOCK DATA COMPLETO
$mock = [
    // Identificação
    'id_proposta' => 99999,
    'numero_proposta' => 'PROP-TESTE-2024',
    'data_criacao' => date('Y-m-d H:i:s'),
    
    // Cliente
    'nome_cliente' => 'CLIENTE DE TESTE LTDA',
    'nome_cliente_salvo' => 'CLIENTE DE TESTE LTDA',
    'email_salvo' => 'engenharia@clienteteste.com.br',
    'telefone_salvo' => '(11) 3000-0000',
    'celular_salvo' => '(11) 99999-8888',
    
    // Obra
    'cidade_obra' => 'São Paulo',
    'estado_obra' => 'SP',
    'endereco_obra' => 'Avenida Paulista, 1578',
    'bairro_obra' => 'Bela Vista',
    'area_obra' => '15.000',
    'unidade_area' => 'm²',
    'tipo_levantamento' => 'Levantamento Topográfico Planialtimétrico',
    'finalidade' => 'Projeto Executivo de Drenagem',
    'contato_obra' => 'Dr. Engenheiro Chefe',
    
    // Variáveis Específicas
    'veiculo' => 'Caminhonete 4x4',
    'estacao_total' => 'Leica TS06',
    'drone' => 'DJI Phantom 4 RTK',
    'tipo_terreno' => 'Urbano denso',
    
    // Financeiro
    'valor_final_proposta' => 12500.00,
    'valor_proposta' => 12500.00,
    'valor_extenso' => 'doze mil e quinhentos reais',
    'prazo_execucao' => '10 dias úteis',
    'mobilizacao_percentual' => 30,
    'mobilizacao_valor' => 3750.00,
    'restante_valor' => 8750.00,
    
    // CONTEÚDO HTML DOS BLOCOS (O que faltava antes)
    'apresentacao_content' => $txtApresentacao,
    'escopo_content' => $txtEscopo,
    'metodologia_content' => $txtMetodologia,
    'documentacao_content' => $txtDocumentacao,
    'condicoes_texto' => $txtCondicoes,
    'consideracoes_content' => $txtConsideracoes,
    'finalidade_descricao' => 'Atender às exigências da Prefeitura para aprovação de projeto.',
    'dados_bancarios_content' => '<p>Banco do Brasil | Ag: 1234-5 | CC: 99999-9 | PIX: 12.345.678/0001-90</p>',
    
    // Empresa Proponente
    'Empresa' => 'SGT ENGENHARIA E TOPOGRAFIA', // Nome que o sistema espera para negrito
    'CNPJ' => '12.345.678/0001-90',
    'Cidade' => 'Rio de Janeiro',
    'Logo' => 'logo_sgt.png'
];

$_POST = $mock;

// Renderiza
require_once 'gerar_documento_html.php';
?>
