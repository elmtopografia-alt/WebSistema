<?php
/**
 * MODELO GERADO AUTOMATICAMENTE - SGT DOCX Parser
 * Fonte: PropostaUsucapiaoCinza.docx
 */

namespace SGT\Propostas;

require_once __DIR__ . '/../ResolvedorChavesSistema.php';

class ModeloPropostaUsucapiaoCinza 
{
    const NOME = 'PropostaUsucapiaoCinza';
    
    protected $blocos;
    protected $variaveisDetectadas;
    protected $cssCustom;

    public function __construct() {
        $this->blocos = array (
  0 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'titulo',
    'conteudo' => 'Proposta de Serviços',
    'estilos_css' => 
    array (
      'font-size' => '24.0px',
      'font-weight' => 'bold',
      'text-align' => 'center',
    ),
    'variaveis' => 
    array (
    ),
    'nivel_titulo' => 1,
  ),
  1 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'titulo',
    'conteudo' => 'Levantamento Topográfico para Usucapião',
    'estilos_css' => 
    array (
      'font-size' => '24.0px',
      'font-weight' => 'bold',
      'text-align' => 'center',
    ),
    'variaveis' => 
    array (
    ),
    'nivel_titulo' => 1,
  ),
  2 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'texto_geral',
    'conteudo' => 'Proposta Nº: ${numero_proposta}',
    'estilos_css' => 
    array (
      'text-align' => 'right',
    ),
    'variaveis' => 
    array (
      0 => 'numero_proposta',
    ),
    'nivel_titulo' => 0,
  ),
  3 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'texto_geral',
    'conteudo' => '${Cidade}, ${DExrenso}',
    'estilos_css' => 
    array (
    ),
    'variaveis' => 
    array (
      0 => 'DExrenso',
      1 => 'Cidade',
    ),
    'nivel_titulo' => 0,
  ),
  4 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'titulo',
    'conteudo' => 'Dados do Cliente',
    'estilos_css' => 
    array (
      'font-size' => '18.0px',
      'font-weight' => 'bold',
    ),
    'variaveis' => 
    array (
    ),
    'nivel_titulo' => 2,
  ),
  5 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'dados_cliente',
    'conteudo' => 'Nome: ${nome_cliente_salvo}',
    'estilos_css' => 
    array (
    ),
    'variaveis' => 
    array (
      0 => 'nome_cliente_salvo',
    ),
    'nivel_titulo' => 0,
  ),
  6 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'dados_cliente',
    'conteudo' => 'E-mail: ${email_salvo}',
    'estilos_css' => 
    array (
    ),
    'variaveis' => 
    array (
      0 => 'email_salvo',
    ),
    'nivel_titulo' => 0,
  ),
  7 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'texto_geral',
    'conteudo' => 'Telefone: ${telefone_salvo} / ${celular_salvo}',
    'estilos_css' => 
    array (
    ),
    'variaveis' => 
    array (
      0 => 'telefone_salvo',
      1 => 'celular_salvo',
    ),
    'nivel_titulo' => 0,
  ),
  8 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'texto_geral',
    'conteudo' => 'WhatsApp: ${whatsapp_salvo}',
    'estilos_css' => 
    array (
    ),
    'variaveis' => 
    array (
      0 => 'whatsapp_salvo',
    ),
    'nivel_titulo' => 0,
  ),
  9 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'titulo',
    'conteudo' => 'Local da Obra',
    'estilos_css' => 
    array (
      'font-size' => '18.0px',
      'font-weight' => 'bold',
    ),
    'variaveis' => 
    array (
    ),
    'nivel_titulo' => 2,
  ),
  10 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'dados_obra',
    'conteudo' => 'Endereço: ${endereco_obra}',
    'estilos_css' => 
    array (
    ),
    'variaveis' => 
    array (
      0 => 'endereco_obra',
    ),
    'nivel_titulo' => 0,
  ),
  11 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'dados_obra',
    'conteudo' => 'Bairro: ${bairro_obra}',
    'estilos_css' => 
    array (
    ),
    'variaveis' => 
    array (
      0 => 'bairro_obra',
    ),
    'nivel_titulo' => 0,
  ),
  12 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'dados_obra',
    'conteudo' => 'Cidade: ${cidade_obra}',
    'estilos_css' => 
    array (
    ),
    'variaveis' => 
    array (
      0 => 'cidade_limpo',
      1 => 'cidade_obra',
    ),
    'nivel_titulo' => 0,
  ),
  13 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'dados_obra',
    'conteudo' => 'Estado: ${estado_obra}',
    'estilos_css' => 
    array (
    ),
    'variaveis' => 
    array (
      0 => 'estado_obra',
    ),
    'nivel_titulo' => 0,
  ),
  14 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'titulo',
    'conteudo' => '1. Apresentação',
    'estilos_css' => 
    array (
      'font-size' => '18.0px',
      'font-weight' => 'bold',
    ),
    'variaveis' => 
    array (
    ),
    'nivel_titulo' => 2,
  ),
  15 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'texto_geral',
    'conteudo' => 'A ${Empresa} é uma empresa prestadora de serviços técnicos especializada exclusivamente em Engenharia de Agrimensura e Topografia.',
    'estilos_css' => 
    array (
      'text-align' => 'justify',
    ),
    'variaveis' => 
    array (
      0 => 'Empresa',
    ),
    'nivel_titulo' => 0,
  ),
  16 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'texto_geral',
    'conteudo' => 'Nosso foco é a elaboração precisa das peças técnicas (plantas, memoriais e laudos) que servem como base física para o processo. Ressaltamos que nossa atuação limita-se estritamente à engenharia. Não somos um escritório de advocacia e não realizamos a regularização jurídica do imóvel.',
    'estilos_css' => 
    array (
      'text-align' => 'justify',
    ),
    'variaveis' => 
    array (
    ),
    'nivel_titulo' => 0,
  ),
  17 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'titulo',
    'conteudo' => 'O Papel da Topografia no Usucapião',
    'estilos_css' => 
    array (
      'font-size' => '14.0px',
      'font-weight' => 'bold',
    ),
    'variaveis' => 
    array (
    ),
    'nivel_titulo' => 3,
  ),
  18 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'dados_cliente',
    'conteudo' => 'Para que o cliente possa dar entrada em seu processo de usucapião (seja judicial ou extrajudicial) com seu advogado, é indispensável apresentar a "fotografia técnica" do imóvel.',
    'estilos_css' => 
    array (
      'text-align' => 'justify',
    ),
    'variaveis' => 
    array (
    ),
    'nivel_titulo' => 0,
  ),
  19 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'texto_geral',
    'conteudo' => 'Nosso trabalho é fornecer essa prova material: definimos com exatidão o perímetro, a área ocupada e os limites físicos. Entregamos os documentos de engenharia necessários para que o proprietário e seu corpo jurídico cuidem dos trâmites legais de regularização junto aos órgãos competentes.',
    'estilos_css' => 
    array (
      'text-align' => 'justify',
    ),
    'variaveis' => 
    array (
    ),
    'nivel_titulo' => 0,
  ),
  20 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'titulo',
    'conteudo' => '2. Finalidade',
    'estilos_css' => 
    array (
      'font-size' => '18.0px',
      'font-weight' => 'bold',
    ),
    'variaveis' => 
    array (
    ),
    'nivel_titulo' => 2,
  ),
  21 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'texto_geral',
    'conteudo' => '${finalidade}',
    'estilos_css' => 
    array (
    ),
    'variaveis' => 
    array (
      0 => 'finalidade',
    ),
    'nivel_titulo' => 0,
  ),
  22 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'texto_geral',
    'conteudo' => 'Delimitar com precisão a área objeto da posse (conforme ocupação real constatada no campo).',
    'estilos_css' => 
    array (
      'text-align' => 'justify',
    ),
    'variaveis' => 
    array (
    ),
    'nivel_titulo' => 0,
  ),
  23 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'dados_cliente',
    'conteudo' => 'Indicar confrontantes na planta e memorial, com base exclusivamente nas informações e documentos (Matrículas/Transcrições) fornecidos pelo Cliente.',
    'estilos_css' => 
    array (
      'text-align' => 'justify',
    ),
    'variaveis' => 
    array (
    ),
    'nivel_titulo' => 0,
  ),
  24 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'texto_geral',
    'conteudo' => 'Determinar perímetro, área e coordenadas georreferenciadas conforme normas técnicas (SIRGAS2000).',
    'estilos_css' => 
    array (
      'text-align' => 'justify',
    ),
    'variaveis' => 
    array (
    ),
    'nivel_titulo' => 0,
  ),
  25 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'dados_cliente',
    'conteudo' => 'Produzir as peças técnicas (Planta e Memorial) que o cliente entregará ao seu advogado para instruir o processo.',
    'estilos_css' => 
    array (
      'text-align' => 'justify',
    ),
    'variaveis' => 
    array (
    ),
    'nivel_titulo' => 0,
  ),
  26 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'dados_cliente',
    'conteudo' => 'Evitar inconsistências técnicas que possam atrapalhar o andamento do processo conduzido pelo cliente.',
    'estilos_css' => 
    array (
      'text-align' => 'justify',
    ),
    'variaveis' => 
    array (
    ),
    'nivel_titulo' => 0,
  ),
  27 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'titulo',
    'conteudo' => '3. Escopo do Serviço',
    'estilos_css' => 
    array (
      'font-size' => '18.0px',
      'font-weight' => 'bold',
    ),
    'variaveis' => 
    array (
    ),
    'nivel_titulo' => 2,
  ),
  28 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'texto_geral',
    'conteudo' => 'O serviço contratado refere-se única e exclusivamente ao Levantamento Topográfico para ${tipo_levantamento}, incluindo a emissão de ART (Anotação de Responsabilidade Técnica) junto ao CREA.',
    'estilos_css' => 
    array (
    ),
    'variaveis' => 
    array (
      0 => 'tipo_levantamento',
    ),
    'nivel_titulo' => 0,
  ),
  29 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'texto_geral',
    'conteudo' => 'O que ESTÁ incluído (Serviços de Engenharia):',
    'estilos_css' => 
    array (
    ),
    'variaveis' => 
    array (
    ),
    'nivel_titulo' => 0,
  ),
  30 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'texto_geral',
    'conteudo' => 'Medição em campo.',
    'estilos_css' => 
    array (
    ),
    'variaveis' => 
    array (
    ),
    'nivel_titulo' => 0,
  ),
  31 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'texto_geral',
    'conteudo' => 'Confecção de plantas e memoriais descritivos.',
    'estilos_css' => 
    array (
    ),
    'variaveis' => 
    array (
    ),
    'nivel_titulo' => 0,
  ),
  32 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'dados_cliente',
    'conteudo' => 'Transcrição dos dados dos confrontantes (vizinhos) para o desenho, baseada na documentação entregue pelo cliente.',
    'estilos_css' => 
    array (
    ),
    'variaveis' => 
    array (
    ),
    'nivel_titulo' => 0,
  ),
  33 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'texto_geral',
    'conteudo' => 'Emissão de ART de serviço topográfico.',
    'estilos_css' => 
    array (
    ),
    'variaveis' => 
    array (
    ),
    'nivel_titulo' => 0,
  ),
  34 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'texto_geral',
    'conteudo' => 'O que NÃO ESTÁ incluído (Serviços Jurídicos/Despachante):',
    'estilos_css' => 
    array (
    ),
    'variaveis' => 
    array (
    ),
    'nivel_titulo' => 0,
  ),
  35 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'texto_geral',
    'conteudo' => 'Não realizamos protocolização de processos em cartórios ou prefeituras.',
    'estilos_css' => 
    array (
      'text-align' => 'justify',
    ),
    'variaveis' => 
    array (
    ),
    'nivel_titulo' => 0,
  ),
  36 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'texto_geral',
    'conteudo' => 'Não prestamos assessoria jurídica ou advocatícia.',
    'estilos_css' => 
    array (
      'text-align' => 'justify',
    ),
    'variaveis' => 
    array (
    ),
    'nivel_titulo' => 0,
  ),
  37 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'texto_geral',
    'conteudo' => 'Não realizamos coleta de assinaturas de confrontantes.',
    'estilos_css' => 
    array (
      'text-align' => 'justify',
    ),
    'variaveis' => 
    array (
    ),
    'nivel_titulo' => 0,
  ),
  38 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'dados_cliente',
    'conteudo' => 'Não solicitamos documentos diretamente aos vizinhos (a documentação dos confrontantes deve ser providenciada pelo cliente).',
    'estilos_css' => 
    array (
      'text-align' => 'justify',
    ),
    'variaveis' => 
    array (
    ),
    'nivel_titulo' => 0,
  ),
  39 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'texto_geral',
    'conteudo' => 'Não garantimos a titulação da propriedade, pois isso depende da análise jurídica e do deferimento do juiz ou oficial de registro.',
    'estilos_css' => 
    array (
      'text-align' => 'justify',
    ),
    'variaveis' => 
    array (
    ),
    'nivel_titulo' => 0,
  ),
  40 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'titulo',
    'conteudo' => 'Normas Técnicas Adotadas:',
    'estilos_css' => 
    array (
      'font-size' => '14.0px',
      'font-weight' => 'bold',
    ),
    'variaveis' => 
    array (
    ),
    'nivel_titulo' => 3,
  ),
  41 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'texto_geral',
    'conteudo' => 'ABNT NBR 13.133: Execução de Levantamento Topográfico.',
    'estilos_css' => 
    array (
    ),
    'variaveis' => 
    array (
    ),
    'nivel_titulo' => 0,
  ),
  42 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'texto_geral',
    'conteudo' => 'Sistema de Referência: SIRGAS2000 UTM.',
    'estilos_css' => 
    array (
    ),
    'variaveis' => 
    array (
    ),
    'nivel_titulo' => 0,
  ),
  43 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'titulo',
    'conteudo' => '4. Metodologia',
    'estilos_css' => 
    array (
      'font-size' => '18.0px',
      'font-weight' => 'bold',
    ),
    'variaveis' => 
    array (
    ),
    'nivel_titulo' => 2,
  ),
  44 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'texto_geral',
    'conteudo' => 'Nossa metodologia de trabalho garante a precisão técnica necessária para que seus documentos de engenharia sejam aceitos sem ressalvas técnicas.',
    'estilos_css' => 
    array (
    ),
    'variaveis' => 
    array (
    ),
    'nivel_titulo' => 0,
  ),
  45 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'titulo',
    'conteudo' => '4.1. Metodologia Aplicada no Campo',
    'estilos_css' => 
    array (
      'font-size' => '14.0px',
      'font-weight' => 'bold',
    ),
    'variaveis' => 
    array (
    ),
    'nivel_titulo' => 3,
  ),
  46 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'prazos',
    'conteudo' => 'Os procedimentos de campo seguem etapas rigorosas:',
    'estilos_css' => 
    array (
    ),
    'variaveis' => 
    array (
    ),
    'nivel_titulo' => 0,
  ),
  47 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'texto_geral',
    'conteudo' => 'Reconhecimento prévio da área: Verificação dos acessos e limites aparentes.',
    'estilos_css' => 
    array (
    ),
    'variaveis' => 
    array (
    ),
    'nivel_titulo' => 0,
  ),
  48 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'equipamentos',
    'conteudo' => 'Implantação de marcos e referência: Utilização de tecnologia de medição (Estação Total/GNSS).',
    'estilos_css' => 
    array (
    ),
    'variaveis' => 
    array (
    ),
    'nivel_titulo' => 0,
  ),
  49 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'dados_cliente',
    'conteudo' => 'Levantamento dos vértices do perímetro ocupado: Pontos coletados observando muros, cercas e limites indicados pelo cliente.',
    'estilos_css' => 
    array (
    ),
    'variaveis' => 
    array (
    ),
    'nivel_titulo' => 0,
  ),
  50 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'equipamentos',
    'conteudo' => 'Medição de benfeitorias: Cadastro de edificações e feições relevantes.',
    'estilos_css' => 
    array (
    ),
    'variaveis' => 
    array (
    ),
    'nivel_titulo' => 0,
  ),
  51 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'texto_geral',
    'conteudo' => 'Registro fotográfico: Documentação visual da visita técnica.',
    'estilos_css' => 
    array (
    ),
    'variaveis' => 
    array (
    ),
    'nivel_titulo' => 0,
  ),
  52 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'titulo',
    'conteudo' => '4.2. Trabalhos de Escritório',
    'estilos_css' => 
    array (
      'font-size' => '14.0px',
      'font-weight' => 'bold',
    ),
    'variaveis' => 
    array (
    ),
    'nivel_titulo' => 3,
  ),
  53 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'texto_geral',
    'conteudo' => 'Após a coleta em campo, realizamos:',
    'estilos_css' => 
    array (
    ),
    'variaveis' => 
    array (
    ),
    'nivel_titulo' => 0,
  ),
  54 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'texto_geral',
    'conteudo' => 'Processamento dos dados e cálculos geodésicos.',
    'estilos_css' => 
    array (
    ),
    'variaveis' => 
    array (
    ),
    'nivel_titulo' => 0,
  ),
  55 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'dados_cliente',
    'conteudo' => 'Identificação de Confrontantes: Inserção dos nomes e dados dos vizinhos na planta, conforme Matrículas ou informações fornecidas pelo cliente.',
    'estilos_css' => 
    array (
    ),
    'variaveis' => 
    array (
    ),
    'nivel_titulo' => 0,
  ),
  56 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'texto_geral',
    'conteudo' => 'Geração da Planta Topográfica contendo as medidas de engenharia.',
    'estilos_css' => 
    array (
    ),
    'variaveis' => 
    array (
    ),
    'nivel_titulo' => 0,
  ),
  57 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'texto_geral',
    'conteudo' => 'Elaboração do Memorial Descritivo (texto técnico com coordenadas e azimutes).',
    'estilos_css' => 
    array (
    ),
    'variaveis' => 
    array (
    ),
    'nivel_titulo' => 0,
  ),
  58 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'dados_cliente',
    'conteudo' => 'Organização do material técnico para entrega ao cliente.',
    'estilos_css' => 
    array (
    ),
    'variaveis' => 
    array (
    ),
    'nivel_titulo' => 0,
  ),
  59 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'titulo',
    'conteudo' => '5. Material Entregue',
    'estilos_css' => 
    array (
      'font-size' => '18.0px',
      'font-weight' => 'bold',
    ),
    'variaveis' => 
    array (
    ),
    'nivel_titulo' => 2,
  ),
  60 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'dados_cliente',
    'conteudo' => 'Ao final da prestação do serviço topográfico, entregamos ao cliente:',
    'estilos_css' => 
    array (
    ),
    'variaveis' => 
    array (
    ),
    'nivel_titulo' => 0,
  ),
  61 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'texto_geral',
    'conteudo' => 'Planta Topográfica, assinada por engenheiro habilitado e com ART.',
    'estilos_css' => 
    array (
    ),
    'variaveis' => 
    array (
    ),
    'nivel_titulo' => 0,
  ),
  62 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'texto_geral',
    'conteudo' => 'Memorial Descritivo técnico com área, perímetro e coordenadas.',
    'estilos_css' => 
    array (
    ),
    'variaveis' => 
    array (
    ),
    'nivel_titulo' => 0,
  ),
  63 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'texto_geral',
    'conteudo' => 'Arquivo digital em formatos PDF e DWG.',
    'estilos_css' => 
    array (
    ),
    'variaveis' => 
    array (
    ),
    'nivel_titulo' => 0,
  ),
  64 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'texto_geral',
    'conteudo' => 'Relatório fotográfico técnico da área.',
    'estilos_css' => 
    array (
    ),
    'variaveis' => 
    array (
    ),
    'nivel_titulo' => 0,
  ),
  65 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'texto_geral',
    'conteudo' => 'ART (Anotação de Responsabilidade Técnica) quitada, referente ao serviço de medição.',
    'estilos_css' => 
    array (
    ),
    'variaveis' => 
    array (
    ),
    'nivel_titulo' => 0,
  ),
  66 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'titulo',
    'conteudo' => '6. Equipamentos Previstos',
    'estilos_css' => 
    array (
      'font-size' => '18.0px',
      'font-weight' => 'bold',
    ),
    'variaveis' => 
    array (
    ),
    'nivel_titulo' => 2,
  ),
  67 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'equipamentos',
    'conteudo' => 'Para a execução da medição, utilizaremos equipamentos calibrados:',
    'estilos_css' => 
    array (
    ),
    'variaveis' => 
    array (
    ),
    'nivel_titulo' => 0,
  ),
  68 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'equipamentos',
    'conteudo' => 'Veículo: ${Veiculo}',
    'estilos_css' => 
    array (
    ),
    'variaveis' => 
    array (
      0 => 'Veiculo',
    ),
    'nivel_titulo' => 0,
  ),
  69 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'equipamentos',
    'conteudo' => 'Estação Total: ${Estacao_Total}',
    'estilos_css' => 
    array (
    ),
    'variaveis' => 
    array (
      0 => 'Estacao_Total',
    ),
    'nivel_titulo' => 0,
  ),
  70 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'equipamentos',
    'conteudo' => 'Receptores GNSS: ${GPS}',
    'estilos_css' => 
    array (
    ),
    'variaveis' => 
    array (
      0 => 'GPS',
    ),
    'nivel_titulo' => 0,
  ),
  71 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'equipamentos',
    'conteudo' => 'Drone (se aplicável): ${Drone}',
    'estilos_css' => 
    array (
    ),
    'variaveis' => 
    array (
      0 => 'Drone',
    ),
    'nivel_titulo' => 0,
  ),
  72 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'titulo',
    'conteudo' => '7. Investimento',
    'estilos_css' => 
    array (
      'font-size' => '18.0px',
      'font-weight' => 'bold',
    ),
    'variaveis' => 
    array (
    ),
    'nivel_titulo' => 2,
  ),
  73 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'valores',
    'conteudo' => 'O valor total para execução dos serviços topográficos descritos é de: ${ValorProposta} (${ValorExtenso})',
    'estilos_css' => 
    array (
    ),
    'variaveis' => 
    array (
      0 => 'ValorProposta',
      1 => 'ValorExtenso',
    ),
    'nivel_titulo' => 0,
  ),
  74 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'dados_cliente',
    'conteudo' => 'Este investimento refere-se à elaboração da documentação técnica de engenharia. Com este material em mãos, o cliente terá a base técnica necessária para contratar seu advogado e buscar a regularização do imóvel junto aos órgãos competentes.',
    'estilos_css' => 
    array (
    ),
    'variaveis' => 
    array (
    ),
    'nivel_titulo' => 0,
  ),
  75 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'titulo',
    'conteudo' => '8. Condições de Pagamento',
    'estilos_css' => 
    array (
      'font-size' => '18.0px',
      'font-weight' => 'bold',
    ),
    'variaveis' => 
    array (
    ),
    'nivel_titulo' => 2,
  ),
  76 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'valores',
    'conteudo' => 'Mobilização: ${mobilizacao_percentual}% – ${mobilizacao_valor} (Pagamento no aceite da proposta para início de campo).',
    'estilos_css' => 
    array (
    ),
    'variaveis' => 
    array (
      0 => 'mobilizacao_percentual',
      1 => 'mobilizacao_valor',
    ),
    'nivel_titulo' => 0,
  ),
  77 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'valores',
    'conteudo' => 'Restante: ${restante_percentual}% – ${restante_valor} (Pagamento na entrega dos documentos técnicos).',
    'estilos_css' => 
    array (
    ),
    'variaveis' => 
    array (
      0 => 'restante_valor',
      1 => 'restante_percentual',
    ),
    'nivel_titulo' => 0,
  ),
  78 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'titulo',
    'conteudo' => '9. Dados Bancários',
    'estilos_css' => 
    array (
      'font-size' => '18.0px',
      'font-weight' => 'bold',
    ),
    'variaveis' => 
    array (
    ),
    'nivel_titulo' => 2,
  ),
  79 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'pagamento',
    'conteudo' => 'Banco: ${Banco}',
    'estilos_css' => 
    array (
    ),
    'variaveis' => 
    array (
      0 => 'Banco',
    ),
    'nivel_titulo' => 0,
  ),
  80 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'pagamento',
    'conteudo' => 'Agência: ${Agencia}',
    'estilos_css' => 
    array (
    ),
    'variaveis' => 
    array (
      0 => 'Agencia',
    ),
    'nivel_titulo' => 0,
  ),
  81 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'texto_geral',
    'conteudo' => 'Conta Corrente: ${Conta}',
    'estilos_css' => 
    array (
    ),
    'variaveis' => 
    array (
      0 => 'Conta',
    ),
    'nivel_titulo' => 0,
  ),
  82 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'texto_geral',
    'conteudo' => 'Titular: ${Empresa}',
    'estilos_css' => 
    array (
    ),
    'variaveis' => 
    array (
      0 => 'Empresa',
    ),
    'nivel_titulo' => 0,
  ),
  83 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'titulo',
    'conteudo' => 'CNPJ: ${CNPJ}',
    'estilos_css' => 
    array (
    ),
    'variaveis' => 
    array (
      0 => 'CNPJ',
    ),
    'nivel_titulo' => 1,
  ),
  84 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'pagamento',
    'conteudo' => 'Chave PIX: ${PIX}',
    'estilos_css' => 
    array (
    ),
    'variaveis' => 
    array (
      0 => 'PIX',
    ),
    'nivel_titulo' => 0,
  ),
  85 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'titulo',
    'conteudo' => '10. Considerações Finais',
    'estilos_css' => 
    array (
      'font-size' => '18.0px',
      'font-weight' => 'bold',
    ),
    'variaveis' => 
    array (
    ),
    'nivel_titulo' => 2,
  ),
  86 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'texto_geral',
    'conteudo' => 'Agradecemos a oportunidade de apresentar nossa proposta de serviços técnicos.',
    'estilos_css' => 
    array (
    ),
    'variaveis' => 
    array (
    ),
    'nivel_titulo' => 0,
  ),
  87 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'texto_geral',
    'conteudo' => 'Reforçamos que a ${Empresa} atua exclusivamente na esfera da engenharia, fornecendo a precisão métrica necessária. Estamos à disposição para entregar um trabalho topográfico de excelência, deixando a documentação técnica pronta para que o senhor(a) possa dar andamento aos trâmites legais de regularização sob sua gestão.',
    'estilos_css' => 
    array (
    ),
    'variaveis' => 
    array (
      0 => 'Empresa',
    ),
    'nivel_titulo' => 0,
  ),
  88 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'texto_geral',
    'conteudo' => 'Atenciosamente,',
    'estilos_css' => 
    array (
      'text-align' => 'center',
    ),
    'variaveis' => 
    array (
    ),
    'nivel_titulo' => 0,
  ),
  89 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'texto_geral',
    'conteudo' => '____________________________________',
    'estilos_css' => 
    array (
      'text-align' => 'center',
    ),
    'variaveis' => 
    array (
    ),
    'nivel_titulo' => 0,
  ),
  90 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'texto_geral',
    'conteudo' => '${Empresa}',
    'estilos_css' => 
    array (
      'text-align' => 'center',
    ),
    'variaveis' => 
    array (
      0 => 'Empresa',
    ),
    'nivel_titulo' => 0,
  ),
  91 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'texto_geral',
    'conteudo' => 'Contato: ${whatsapp}',
    'estilos_css' => 
    array (
      'text-align' => 'center',
    ),
    'variaveis' => 
    array (
      0 => 'whatsapp',
    ),
    'nivel_titulo' => 0,
  ),
);
        $this->variaveisDetectadas = array (
  0 => 'Agencia',
  1 => 'Banco',
  2 => 'CNPJ',
  3 => 'Cidade',
  4 => 'Conta',
  5 => 'DExrenso',
  6 => 'DataExtenso',
  7 => 'Drone',
  8 => 'Empresa',
  9 => 'Estacao_Total',
  10 => 'GPS',
  11 => 'PIX',
  12 => 'ValorExtenso',
  13 => 'ValorProposta',
  14 => 'Veiculo',
  15 => 'bairro_obra',
  16 => 'celular_salvo',
  17 => 'cidade_limpo',
  18 => 'cidade_obra',
  19 => 'email_salvo',
  20 => 'endereco_obra',
  21 => 'estado_obra',
  22 => 'finalidade',
  23 => 'mobilizacao_percentual',
  24 => 'mobilizacao_valor',
  25 => 'nome_cliente_salvo',
  26 => 'numero_proposta',
  27 => 'restante_percentual',
  28 => 'restante_valor',
  29 => 'telefone_salvo',
  30 => 'tipo_levantamento',
  31 => 'whatsapp',
  32 => 'whatsapp_salvo',
);
        $this->cssCustom = "
        .modelo-docx-container h1 { color: #1f2937 !important; border-bottom: 2px solid #6b7280 !important; padding-bottom: 5px; }
        .modelo-docx-container h2 { color: #374151 !important; margin-top: 20px; border-left: 4px solid #6b7280 !important; padding-left: 10px; }
        .modelo-docx-container h3 { color: #4b5563 !important; }
        .modelo-docx-container p { line-height: 1.6; color: #333; }
        .modelo-docx-container table { border: 1px solid #6b7280 !important; }
        .modelo-docx-container th { background-color: #1f2937; color: white !important; }
    

        .modelo-docx { font-family: \'Segoe UI\', Arial, sans-serif; line-height: 1.6; color: #333; max-width: 900px; margin: 0 auto; }
        
        /* Título principal H1 */
        .modelo-docx h1 { 
            color: #1e3a8a; 
            border-bottom: 3px solid #1e3a8a; 
            padding-bottom: 10px; 
            font-size: 24px; 
            font-weight: bold; 
            text-align: center;
        }
        
        /* Subtítulos H3 */
        .modelo-docx h3 { 
            color: #4b5563; 
            font-size: 14px; 
            font-weight: bold; 
            margin-top: 20px; 
        }
        
        /* Classes utilitárias */
        .titulo-principal { color: #1e3a8a; border-bottom: 3px solid #1e3a8a; padding-bottom: 10px; }
        .titulo-secao { color: #1e40af; margin-top: 25px; }
        .var-placeholder { background: #dbeafe; color: #1e40af; padding: 2px 6px; border-radius: 4px; font-family: monospace; border: 1px dashed #3b82f6; }
        .tabela-proposta th { background: #f1f5f9; font-weight: 600; }
        .dados_cliente, .dados_obra { background: #f8fafc; padding: 15px; border-radius: 8px; margin: 10px 0; }
    
        /* Seção 1 - Azul */
        .modelo-docx h2:nth-of-type(1) { 
            color: #1e40af; 
            margin-top: 25px; 
            font-size: 18px; 
            font-weight: bold; 
            border-left: 4px solid #3b82f6; 
            padding-left: 12px; 
        }\\n
        /* Seção 2 - Marrom */
        .modelo-docx h2:nth-of-type(2) { 
            color: #7c2d12; 
            margin-top: 25px; 
            font-size: 18px; 
            font-weight: bold; 
            border-left: 4px solid #92400e; 
            padding-left: 12px; 
        }\\n
        /* Seção 3 - Cinza */
        .modelo-docx h2:nth-of-type(3) { 
            color: #374151; 
            margin-top: 25px; 
            font-size: 18px; 
            font-weight: bold; 
            border-left: 4px solid #6b7280; 
            padding-left: 12px; 
        }\\n
        /* Seção 4 - Azul Escuro */
        .modelo-docx h2:nth-of-type(4) { 
            color: #1e3a8a; 
            margin-top: 25px; 
            font-size: 18px; 
            font-weight: bold; 
            border-left: 4px solid #1d4ed8; 
            padding-left: 12px; 
        }\\n
        /* Seção 5 - Marrom Escuro */
        .modelo-docx h2:nth-of-type(5) { 
            color: #92400e; 
            margin-top: 25px; 
            font-size: 18px; 
            font-weight: bold; 
            border-left: 4px solid #b45309; 
            padding-left: 12px; 
        }\\n
        /* Seção 6 - Cinza Claro */
        .modelo-docx h2:nth-of-type(6) { 
            color: #4b5563; 
            margin-top: 25px; 
            font-size: 18px; 
            font-weight: bold; 
            border-left: 4px solid #9ca3af; 
            padding-left: 12px; 
        }\\n
        /* Seção 7 - Azul Claro */
        .modelo-docx h2:nth-of-type(7) { 
            color: #2563eb; 
            margin-top: 25px; 
            font-size: 18px; 
            font-weight: bold; 
            border-left: 4px solid #60a5fa; 
            padding-left: 12px; 
        }\\n
        /* Seção 8 - Marrom Claro */
        .modelo-docx h2:nth-of-type(8) { 
            color: #78350f; 
            margin-top: 25px; 
            font-size: 18px; 
            font-weight: bold; 
            border-left: 4px solid #a16207; 
            padding-left: 12px; 
        }\\n
        /* Seção 9 - Cinza Escuro */
        .modelo-docx h2:nth-of-type(9) { 
            color: #1f2937; 
            margin-top: 25px; 
            font-size: 18px; 
            font-weight: bold; 
            border-left: 4px solid #4b5563; 
            padding-left: 12px; 
        }\\n
        /* Seção 10 - Azul Médio */
        .modelo-docx h2:nth-of-type(10) { 
            color: #1d4ed8; 
            margin-top: 25px; 
            font-size: 18px; 
            font-weight: bold; 
            border-left: 4px solid #3b82f6; 
            padding-left: 12px; 
        }\\n
        /* Seção 11 - Azul */
        .modelo-docx h2:nth-of-type(11) { 
            color: #1e40af; 
            margin-top: 25px; 
            font-size: 18px; 
            font-weight: bold; 
            border-left: 4px solid #3b82f6; 
            padding-left: 12px; 
        }\\n
        /* Seção 12 - Marrom */
        .modelo-docx h2:nth-of-type(12) { 
            color: #7c2d12; 
            margin-top: 25px; 
            font-size: 18px; 
            font-weight: bold; 
            border-left: 4px solid #92400e; 
            padding-left: 12px; 
        }";
    }

    public function getConfig(): array {
        return [
            'nome' => self::NOME,
            'blocos' => $this->blocos,
            'variaveis' => $this->variaveisDetectadas,
            'css' => $this->cssCustom
        ];
    }

    public function render($dadosManuais, $resolvedor, $id_usuario)
    {
        $dadosSistema = $resolvedor->resolver($this->variaveisDetectadas, $id_usuario, $dadosManuais);
        $contexto = array_merge($dadosManuais, $dadosSistema);
        
        $html = "<div class='modelo-docx-container'>";
        $html .= "<style>{$this->cssCustom}</style>";
        
        foreach ($this->blocos as $bloco) {
            $html .= $this->renderBloco($bloco, $contexto);
        }
        
        $html .= "</div>";
        return $html;
    }

    private function renderBloco($bloco, $contexto)
    {
        if ($bloco['tipo'] === 'texto') {
            $tag = ($bloco['nivel_titulo'] > 0) ? 'h' . $bloco['nivel_titulo'] : 'p';
            $conteudo = $bloco['conteudo'];
            
            foreach ($bloco['variaveis'] as $var) {
                $valor = isset($contexto[$var]) ? $contexto[$var] : "[{$var}]";
                $pattern = '/(\$\{\s*' . preg_quote($var, '/') . '\s*\}|\{\{\s*' . preg_quote($var, '/') . '\s*\}\})/';
                $conteudo = preg_replace($pattern, $valor, $conteudo);
            }
            
            $estilos = $this->mapEstilos($bloco['estilos_css']);
            return "<$tag style='$estilos'>$conteudo</$tag>";
        }
        
        if ($bloco['tipo'] === 'tabela') {
            $html = "<table style='width:100%; border-collapse:collapse; border: 1px solid #dee2e6; margin-bottom: 25px;'>";
            foreach ($bloco['linhas'] as $i => $linha) {
                $html .= "<tr>";
                foreach ($linha as $celula) {
                    $tag = ($i === 0) ? 'th' : 'td';
                    $texto = $celula['texto'];
                    foreach ($bloco['variaveis'] as $var) {
                         $valor = isset($contexto[$var]) ? $contexto[$var] : "[{$var}]";
                         $pattern = '/(\$\{\s*' . preg_quote($var, '/') . '\s*\}|\{\{\s*' . preg_quote($var, '/') . '\s*\}\})/';
                         $texto = preg_replace($pattern, $valor, $texto);
                    }
                    $estilos = $this->mapEstilos($celula['estilos']);
                    $html .= "<$tag colspan='{$celula['colspan']}' style='$estilos'>$texto</$tag>";
                }
                $html .= "</tr>";
            }
            $html .= "</table>";
            return $html;
        }
        return "";
    }

    private function mapEstilos($estilos) {
        $out = "";
        foreach ($estilos as $k => $v) $out .= "$k:$v; ";
        return $out;
    }
}


