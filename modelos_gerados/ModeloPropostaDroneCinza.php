<?php
/**
 * MODELO GERADO AUTOMATICAMENTE - SGT DOCX Parser
 * Fonte: PropostaDrone.docx
 * VERSÃO CORRIGIDA - Restaura CSS por seção, numeração correta e estrutura de blocos
 */

namespace SGT\Propostas;

require_once __DIR__ . '/BaseModeloDOCX.php';

class ModeloPropostaDroneCinza extends BaseModeloDOCX
{
    const NOME = 'PropostaDroneCinza';

    public function __construct() {
        $this->blocos = array (
  0 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'titulo',
    'conteudo' => '',
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
    'subtipo' => 'texto_geral',
    'conteudo' => '',
    'estilos_css' => 
    array (
      'text-align' => 'right',
    ),
    'variaveis' => 
    array (
    ),
    'nivel_titulo' => 0,
  ),
  2 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'prazos',
    'conteudo' => '${cidade_limpo}, ${DataExtenso}',
    'estilos_css' => 
    array (
    ),
    'variaveis' => 
    array (
      0 => 'cidade_limpo',
      1 => 'DataExtenso',
    ),
    'nivel_titulo' => 0,
  ),
  3 => 
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
  4 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'texto_geral',
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
  5 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'texto_geral',
    'conteudo' => 'Telefone: ${telefone_salvo} ',
    'estilos_css' => 
    array (
    ),
    'variaveis' => 
    array (
      0 => 'telefone_salvo',
    ),
    'nivel_titulo' => 0,
  ),
  6 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'texto_geral',
    'conteudo' => 'Celular: ${celular_salvo}',
    'estilos_css' => 
    array (
    ),
    'variaveis' => 
    array (
      0 => 'celular_salvo',
    ),
    'nivel_titulo' => 0,
  ),
  7 => 
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
  8 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'dados_obra',
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
  9 => 
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
  10 => 
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
  11 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'dados_obra',
    'conteudo' => 'Cidade/Estado: ${cidade_obra} - ${estado_obra}',
    'estilos_css' => 
    array (
    ),
    'variaveis' => 
    array (
      0 => 'estado_obra',
      1 => 'cidade_obra',
    ),
    'nivel_titulo' => 0,
  ),
  12 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'texto_geral',
    'conteudo' => 'Área Estimada: ${AreaEstimada}',
    'estilos_css' => 
    array (
    ),
    'variaveis' => 
    array (
      0 => 'AreaEstimada',
    ),
    'nivel_titulo' => 0,
  ),
  13 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'titulo',
    'conteudo' => '1. Apresentação e Entendimento do Serviço',
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
  14 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'dados_obra',
    'conteudo' => 'A ${Empresa} apresenta esta proposta técnica visando a execução de levantamento topográfico planialtimétrico através de Aerofotogrametria com Drones (VANTs). Diferente de simples filmagens aéreas, este serviço trata-se de Engenharia de Precisão. O objetivo é gerar uma representação digital fiel do terreno, com coordenadas exatas (Latitude, Longitude e Altitude), servindo de base legal e técnica para projetos de arquitetura, loteamentos, regularização fundiária e cálculos de volume, se necessário.',
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
  15 => 
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
  16 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'texto_geral',
    'conteudo' => '${finalidade}
',
    'estilos_css' => 
    array (
      'text-align' => 'justify',
    ),
    'variaveis' => 
    array (
      0 => 'finalidade',
    ),
    'nivel_titulo' => 0,
  ),
  17 => 
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
  18 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'texto_geral',
    'conteudo' => 'Levantamento Fotogramétrico com Drone',
    'estilos_css' => 
    array (
    ),
    'variaveis' => 
    array (
    ),
    'nivel_titulo' => 0,
  ),
  19 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'dados_obra',
    'conteudo' => 'Execução de levantamento na área de ${AreaEstimada} ${unidade_area}, localizada em ${cidade_obra} -${estado_obra} , com as seguintes características:',
    'estilos_css' => 
    array (
    ),
    'variaveis' => 
    array (
      0 => 'estado_obra',
      1 => 'unidade_area',
      2 => 'cidade_obra',
      3 => 'AreaEstimada',
    ),
    'nivel_titulo' => 0,
  ),
  20 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'dados_obra',
    'conteudo' => 'Tipo de Terreno: ${TipoTerreno};',
    'estilos_css' => 
    array (
    ),
    'variaveis' => 
    array (
      0 => 'TipoTerreno',
    ),
    'nivel_titulo' => 0,
  ),
  21 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'texto_geral',
    'conteudo' => 'Cobertura Vegetal: ${CoberturaVegetal};',
    'estilos_css' => 
    array (
    ),
    'variaveis' => 
    array (
      0 => 'CoberturaVegetal',
    ),
    'nivel_titulo' => 0,
  ),
  22 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'dados_obra',
    'conteudo' => 'Acesso: ${AcessoLocal};',
    'estilos_css' => 
    array (
    ),
    'variaveis' => 
    array (
      0 => 'AcessoLocal',
    ),
    'nivel_titulo' => 0,
  ),
  23 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'texto_geral',
    'conteudo' => 'Restrições: ${RestricoesAereas}.',
    'estilos_css' => 
    array (
    ),
    'variaveis' => 
    array (
      0 => 'RestricoesAereas',
    ),
    'nivel_titulo' => 0,
  ),
  24 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'texto_geral',
    'conteudo' => 'O serviço compreende:',
    'estilos_css' => 
    array (
    ),
    'variaveis' => 
    array (
    ),
    'nivel_titulo' => 0,
  ),
  25 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'dados_obra',
    'conteudo' => 'Planejamento de voo e estudo de viabilidade aérea (consulta DECEA para ${cidade_obra} -${estado_obra}',
    'estilos_css' => 
    array (
    ),
    'variaveis' => 
    array (
      0 => 'estado_obra',
      1 => 'cidade_obra',
    ),
    'nivel_titulo' => 0,
  ),
  26 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'texto_geral',
    'conteudo' => 'Implantação de Pontos de Controle Terrestre (GCPs) com GPS RTK;',
    'estilos_css' => 
    array (
    ),
    'variaveis' => 
    array (
    ),
    'nivel_titulo' => 0,
  ),
  27 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'texto_geral',
    'conteudo' => 'Captura de imagens aéreas com sobreposição adequada (80% longitudinal, 70% lateral);',
    'estilos_css' => 
    array (
    ),
    'variaveis' => 
    array (
    ),
    'nivel_titulo' => 0,
  ),
  28 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'texto_geral',
    'conteudo' => 'Processamento fotogramétrico e geração de Ortomosaico georreferenciado;',
    'estilos_css' => 
    array (
    ),
    'variaveis' => 
    array (
    ),
    'nivel_titulo' => 0,
  ),
  29 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'dados_obra',
    'conteudo' => 'Geração de MDT (Modelo Digital do Terreno) e MDS (Modelo Digital de Superfície);',
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
    'conteudo' => 'Extração de curvas de nível e elementos vetoriais;',
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
    'conteudo' => 'Cálculo de volumes (quando solicitado).',
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
    'subtipo' => 'titulo',
    'conteudo' => '4. Metodologia: O Passo a Passo do Mapeamento',
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
  33 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'texto_geral',
    'conteudo' => 'Para garantir que o produto final tenha validade topográfica, seguimos um rigoroso fluxo de trabalho dividido em etapas de campo e escritório. Abaixo, detalhamos cada fase para total compreensão do processo contratado:',
    'estilos_css' => 
    array (
      'text-align' => 'justify',
    ),
    'variaveis' => 
    array (
    ),
    'nivel_titulo' => 0,
  ),
  34 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'titulo',
    'conteudo' => 'FASE 1: Planejamento e Configuração de Voo (Escritório)',
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
  35 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'texto_geral',
    'conteudo' => 'Antes de ir a campo, realizamos o estudo da área via satélite.',
    'estilos_css' => 
    array (
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
    'conteudo' => 'O que é: Definimos a altura de voo para garantir a resolução desejada (GSD) e a área de abrangência.',
    'estilos_css' => 
    array (
    ),
    'variaveis' => 
    array (
    ),
    'nivel_titulo' => 0,
  ),
  37 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'dados_obra',
    'conteudo' => 'Para o Leigo: O drone não voa aleatoriamente. Ele segue uma "grade" programada via GPS, garantindo que nenhuma parte do terreno fique sem cobertura.',
    'estilos_css' => 
    array (
    ),
    'variaveis' => 
    array (
    ),
    'nivel_titulo' => 0,
  ),
  38 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'texto_geral',
    'conteudo' => 'Critério Técnico: Configuração de sobreposição (overlap) frontal e lateral (geralmente 75/80%) para garantir a estereoscopia (visão 3D) no processamento.',
    'estilos_css' => 
    array (
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
    'conteudo' => 'FASE 2: Apoio Terrestre - Pontos de Controle (Campo) Esta é a etapa que diferencia uma foto comum de um mapa topográfico.',
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
  40 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'texto_geral',
    'conteudo' => 'O Procedimento: Antes do drone decolar, nossa equipe distribui e pinta alvos no chão ou utiliza marcos naturais. As coordenadas exatas do centro desses alvos são coletadas com GPS Geodésico de Alta Precisão (RTK).',
    'estilos_css' => 
    array (
    ),
    'variaveis' => 
    array (
    ),
    'nivel_titulo' => 0,
  ),
  41 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'texto_geral',
    'conteudo' => 'Importância: Esses pontos servem como "âncoras" que amarram as fotos do drone ao sistema de coordenadas do mundo real, corrigindo distorções e garantindo precisão centimétrica.',
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
    'conteudo' => 'Checklist: Verificação da fixação dos pontos, nivelamento do bastão GPS e tempo de rastreio dos satélites.',
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
    'subtipo' => 'texto_geral',
    'conteudo' => 'FASE 3: Evolução do Voo e Captura de Dados (Campo) * Checklist de Segurança: Verificação de baterias, hélices, cartões de memória, interferência magnética (bússola), condições do vento e autorizações de voo (DECEA).',
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
  44 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'texto_geral',
    'conteudo' => 'O Voo: O drone percorre a rota autônoma, capturando centenas de fotos em ângulos verticais (nadir) e oblíquos.',
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
    'subtipo' => 'prazos',
    'conteudo' => 'Resultado: Coleta de imagens brutas (Raw Data) que, isoladamente, não possuem escala, mas que juntas formarão o mapa.',
    'estilos_css' => 
    array (
    ),
    'variaveis' => 
    array (
    ),
    'nivel_titulo' => 0,
  ),
  46 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'texto_geral',
    'conteudo' => 'FASE 4: Processamento Fotogramétrico (Escritório) Utilizamos supercomputadores (Workstations) e softwares específicos para transformar as fotos em geometria.',
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
  47 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'texto_geral',
    'conteudo' => 'Etapas do Processamento:',
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
    'subtipo' => 'texto_geral',
    'conteudo' => 'Alinhamento: O software encontra milhares de pontos em comum entre as fotos.',
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
    'subtipo' => 'texto_geral',
    'conteudo' => 'Nuvem de Pontos Densa: Criação de milhões de pontos coloridos no espaço 3D, representando o solo, árvores e construções.',
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
    'subtipo' => 'texto_geral',
    'conteudo' => 'Georreferenciamento: Inserção dos Pontos de Controle (da Fase 2) para corrigir a posição da nuvem de pontos com precisão milimétrica.',
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
    'subtipo' => 'titulo',
    'conteudo' => 'FASE 5: Vetorização e Desenho Técnico (Escritório - CAD)',
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
  52 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'texto_geral',
    'conteudo' => 'O que é: Um desenhista técnico utiliza o modelo 3D gerado para "desenhar" o mapa final em software CAD (AutoCAD/Civil 3D',
    'estilos_css' => 
    array (
    ),
    'variaveis' => 
    array (
    ),
    'nivel_titulo' => 0,
  ),
  53 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'dados_obra',
    'conteudo' => 'O Trabalho:  Vetorização de guias, cercas, edificações, postes, árvores e, principalmente, a geração das Curvas de Nível (linhas que representam a altura do terreno).',
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
    'subtipo' => 'titulo',
    'conteudo' => '5. Equipamentos Previstos',
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
  55 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'texto_geral',
    'conteudo' => 'Para garantir a acurácia descrita nesta proposta, utilizaremos:',
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
    'conteudo' => 'Aeronave: ${Drone} (Câmera de Alta Resolução).',
    'estilos_css' => 
    array (
    ),
    'variaveis' => 
    array (
      0 => 'Drone',
    ),
    'nivel_titulo' => 0,
  ),
  57 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'texto_geral',
    'conteudo' => 'GPS - Geodésia: ${GPS} (Receptor GNSS RTK/PPK para Pontos de Controle).',
    'estilos_css' => 
    array (
    ),
    'variaveis' => 
    array (
      0 => 'GPS',
    ),
    'nivel_titulo' => 0,
  ),
  58 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'texto_geral',
    'conteudo' => 'Estação Total para Apoio: ${Estacao_Total} (Se necessário para áreas de sombra de GPS).',
    'estilos_css' => 
    array (
    ),
    'variaveis' => 
    array (
      0 => 'Estacao_Total',
    ),
    'nivel_titulo' => 0,
  ),
  59 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'texto_geral',
    'conteudo' => 'Processamento: Workstations com placas gráficas de alto desempenho.',
    'estilos_css' => 
    array (
    ),
    'variaveis' => 
    array (
    ),
    'nivel_titulo' => 0,
  ),
  60 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'texto_geral',
    'conteudo' => 'Veiculo: ${Veiculo} para apoio a locomoção.',
    'estilos_css' => 
    array (
    ),
    'variaveis' => 
    array (
      0 => 'Veiculo',
    ),
    'nivel_titulo' => 0,
  ),
  61 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'titulo',
    'conteudo' => '6. Produtos Entregues (O que você recebe)',
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
  62 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'texto_geral',
    'conteudo' => 'Ao final do processo, entregamos um pacote completo de dados técnicos:',
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
    'conteudo' => 'Ortomosaico Georreferenciado (TIF/JPG): Uma "foto" gigante de toda a área, livre de distorções de perspectiva e em escala real. É possível medir distâncias e áreas diretamente sobre ela.',
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
    'subtipo' => 'dados_obra',
    'conteudo' => 'MDT (Modelo Digital de Terreno): Representação 3D apenas do solo (sem árvores/prédios), essencial para projetos de terraplenagem.',
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
    'subtipo' => 'dados_obra',
    'conteudo' => 'Curvas de Nível (DWG/DXF): Arquivo pronto para abertura em AutoCAD, contendo a topografia do terreno com equidistância definida (ex: 1 em 1 metro).',
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
    'subtipo' => 'texto_geral',
    'conteudo' => 'Planta Topográfica Planialtimétrica (PDF/Plotagem): Mapa finalizado com legendas, norte, carimbo e dados técnicos.',
    'estilos_css' => 
    array (
    ),
    'variaveis' => 
    array (
    ),
    'nivel_titulo' => 0,
  ),
  67 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'texto_geral',
    'conteudo' => 'Relatório de Processamento: Documento comprovando a precisão alcançada nos Pontos de Controle.',
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
    'subtipo' => 'texto_geral',
    'conteudo' => 'ART (Anotação de Responsabilidade Técnica): Documento registrado no CREA garantindo a responsabilidade técnica do engenheiro sobre o levantamento.',
    'estilos_css' => 
    array (
    ),
    'variaveis' => 
    array (
    ),
    'nivel_titulo' => 0,
  ),
  69 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'prazos',
    'conteudo' => '7. Prazos Estimados',
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
  70 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'prazos',
    'conteudo' => 'O cumprimento dos prazos depende de condições climáticas favoráveis (ausência de chuva e ventos fortes) para a etapa de campo.',
    'estilos_css' => 
    array (
    ),
    'variaveis' => 
    array (
    ),
    'nivel_titulo' => 0,
  ),
  71 => 
  array (
    'tipo' => 'tabela',
    'linhas' => 
    array (
      0 => 
      array (
        0 => 
        array (
          'texto' => 'Etapa',
          'colspan' => 1,
          'estilos' => 
          array (
            'border' => '1px solid #dee2e6',
            'padding' => '12px 15px',
            'background' => '#f8f9fa',
            'vertical-align' => 'top',
          ),
        ),
        1 => 
        array (
          'texto' => 'Descrição',
          'colspan' => 1,
          'estilos' => 
          array (
            'border' => '1px solid #dee2e6',
            'padding' => '12px 15px',
            'background' => '#f8f9fa',
            'vertical-align' => 'top',
          ),
        ),
        2 => 
        array (
          'texto' => 'Prazo Estimado',
          'colspan' => 1,
          'estilos' => 
          array (
            'border' => '1px solid #dee2e6',
            'padding' => '12px 15px',
            'background' => '#f8f9fa',
            'vertical-align' => 'top',
          ),
        ),
      ),
      1 => 
      array (
        0 => 
        array (
          'texto' => '1. Mobilização',
          'colspan' => 1,
          'estilos' => 
          array (
            'border' => '1px solid #dee2e6',
            'padding' => '12px 15px',
            'background' => 'transparent',
            'vertical-align' => 'top',
          ),
        ),
        1 => 
        array (
          'texto' => 'Planejamento e ida a campo',
          'colspan' => 1,
          'estilos' => 
          array (
            'border' => '1px solid #dee2e6',
            'padding' => '12px 15px',
            'background' => 'transparent',
            'vertical-align' => 'top',
          ),
        ),
        2 => 
        array (
          'texto' => 'Até 02 dias após aceite',
          'colspan' => 1,
          'estilos' => 
          array (
            'border' => '1px solid #dee2e6',
            'padding' => '12px 15px',
            'background' => 'transparent',
            'vertical-align' => 'top',
          ),
        ),
      ),
      2 => 
      array (
        0 => 
        array (
          'texto' => '2. Campo',
          'colspan' => 1,
          'estilos' => 
          array (
            'border' => '1px solid #dee2e6',
            'padding' => '12px 15px',
            'background' => 'transparent',
            'vertical-align' => 'top',
          ),
        ),
        1 => 
        array (
          'texto' => 'Instalação de pontos e Voo',
          'colspan' => 1,
          'estilos' => 
          array (
            'border' => '1px solid #dee2e6',
            'padding' => '12px 15px',
            'background' => 'transparent',
            'vertical-align' => 'top',
          ),
        ),
        2 => 
        array (
          'texto' => '01 dia (por lote de voo)',
          'colspan' => 1,
          'estilos' => 
          array (
            'border' => '1px solid #dee2e6',
            'padding' => '12px 15px',
            'background' => 'transparent',
            'vertical-align' => 'top',
          ),
        ),
      ),
      3 => 
      array (
        0 => 
        array (
          'texto' => '3. Processamento',
          'colspan' => 1,
          'estilos' => 
          array (
            'border' => '1px solid #dee2e6',
            'padding' => '12px 15px',
            'background' => 'transparent',
            'vertical-align' => 'top',
          ),
        ),
        1 => 
        array (
          'texto' => 'Geração da nuvem e modelos digitais',
          'colspan' => 1,
          'estilos' => 
          array (
            'border' => '1px solid #dee2e6',
            'padding' => '12px 15px',
            'background' => 'transparent',
            'vertical-align' => 'top',
          ),
        ),
        2 => 
        array (
          'texto' => '03 a 05 dias úteis',
          'colspan' => 1,
          'estilos' => 
          array (
            'border' => '1px solid #dee2e6',
            'padding' => '12px 15px',
            'background' => 'transparent',
            'vertical-align' => 'top',
          ),
        ),
      ),
      4 => 
      array (
        0 => 
        array (
          'texto' => '4. Desenho (CAD)',
          'colspan' => 1,
          'estilos' => 
          array (
            'border' => '1px solid #dee2e6',
            'padding' => '12px 15px',
            'background' => 'transparent',
            'vertical-align' => 'top',
          ),
        ),
        1 => 
        array (
          'texto' => 'Vetorização e Planta Final',
          'colspan' => 1,
          'estilos' => 
          array (
            'border' => '1px solid #dee2e6',
            'padding' => '12px 15px',
            'background' => 'transparent',
            'vertical-align' => 'top',
          ),
        ),
        2 => 
        array (
          'texto' => '03 a 05 dias úteis',
          'colspan' => 1,
          'estilos' => 
          array (
            'border' => '1px solid #dee2e6',
            'padding' => '12px 15px',
            'background' => 'transparent',
            'vertical-align' => 'top',
          ),
        ),
      ),
      5 => 
      array (
        0 => 
        array (
          'texto' => 'TOTAL ESTIMADO',
          'colspan' => 1,
          'estilos' => 
          array (
            'border' => '1px solid #dee2e6',
            'padding' => '12px 15px',
            'background' => 'transparent',
            'vertical-align' => 'top',
          ),
        ),
        1 => 
        array (
          'texto' => 'Do aceite à entrega final',
          'colspan' => 1,
          'estilos' => 
          array (
            'border' => '1px solid #dee2e6',
            'padding' => '12px 15px',
            'background' => 'transparent',
            'vertical-align' => 'top',
          ),
        ),
        2 => 
        array (
          'texto' => '07 a 12 dias úteis',
          'colspan' => 1,
          'estilos' => 
          array (
            'border' => '1px solid #dee2e6',
            'padding' => '12px 15px',
            'background' => 'transparent',
            'vertical-align' => 'top',
          ),
        ),
      ),
    ),
    'estilos' => 
    array (
      'width' => '100%',
      'border-collapse' => 'collapse',
      'margin' => '25px 0',
      'font-size' => '14px',
    ),
    'variaveis' => 
    array (
    ),
  ),
  72 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'valores',
    'conteudo' => '8. Investimento',
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
    'conteudo' => 'O valor total para execução dos serviços descritos, incluindo equipe técnica, equipamentos (Drone, GPS RTK, Estação de Trabalho), deslocamento e impostos, é de: R$ ${ValorProposta} (${ValorExtenso}) ',
    'estilos_css' => 
    array (
    ),
    'variaveis' => 
    array (
      0 => 'ValorExtenso',
      1 => 'ValorProposta',
    ),
    'nivel_titulo' => 0,
  ),
  74 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'valores',
    'conteudo' => 'Este investimento reflete o custo-benefício da tecnologia: maior riqueza de dados (milhões de pontos) em menor tempo de execução comparado à topografia tradicional.',
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
    'subtipo' => 'pagamento',
    'conteudo' => '9. Condições de Pagamento',
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
    'conteudo' => 'Mobilização (Sinal): ${mobilizacao_percentual}% – R$ ${mobilizacao_valor} (No aceite da proposta).',
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
    'conteudo' => 'Entrega Final: ${restante_percentual}% – R$ ${restante_valor} (Na entrega dos arquivos digitais e físicos).',
    'estilos_css' => 
    array (
    ),
    'variaveis' => 
    array (
      0 => 'restante_percentual',
      1 => 'restante_valor',
    ),
    'nivel_titulo' => 0,
  ),
  78 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'titulo',
    'conteudo' => 'Dados Bancários:',
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
    'subtipo' => 'texto_geral',
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
    'conteudo' => 'Conta: ${Conta}',
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
    'conteudo' => 'Titular: ${Empresa} | CNPJ: ${CNPJ}',
    'estilos_css' => 
    array (
    ),
    'variaveis' => 
    array (
      0 => 'Empresa',
      1 => 'CNPJ',
    ),
    'nivel_titulo' => 0,
  ),
  83 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'pagamento',
    'conteudo' => 'PIX: ${PIX}',
    'estilos_css' => 
    array (
    ),
    'variaveis' => 
    array (
      0 => 'PIX',
    ),
    'nivel_titulo' => 0,
  ),
  84 => 
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
  85 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'dados_obra',
    'conteudo' => 'Esta proposta tem validade de 15 dias. A ${Empresa} traz em seu DNA uma sólida vivência prática em obras complexas, aplicando o rigor da topografia clássica às inovações do mapeamento aéreo. Entendemos a responsabilidade que um levantamento planialtimétrico carrega em projetos de engenharia e infraestrutura. Por isso, nossa equipe coloca-se à disposição para alinhar as melhores soluções técnicas para a sua demanda, assegurando a entrega de um produto final de altíssima confiabilidade e precisão.',
    'estilos_css' => 
    array (
    ),
    'variaveis' => 
    array (
      0 => 'Empresa',
    ),
    'nivel_titulo' => 0,
  ),
  86 => 
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
  87 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'texto_geral',
    'conteudo' => '_________________________________________________',
    'estilos_css' => 
    array (
      'text-align' => 'center',
    ),
    'variaveis' => 
    array (
    ),
    'nivel_titulo' => 0,
  ),
  88 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'texto_geral',
    'conteudo' => '${Empresa} - ${whatsapp}',
    'estilos_css' => 
    array (
      'text-align' => 'center',
    ),
    'variaveis' => 
    array (
      0 => 'whatsapp',
      1 => 'Empresa',
    ),
    'nivel_titulo' => 0,
  ),
);
        $this->variaveisDetectadas = array (
  0 => 'AcessoLocal',
  1 => 'Agencia',
  2 => 'AreaEstimada',
  3 => 'Banco',
  4 => 'CNPJ',
  5 => 'Cidade',
  6 => 'CoberturaVegetal',
  7 => 'Conta',
  8 => 'DataExtenso',
  9 => 'Drone',
  10 => 'Empresa',
  11 => 'Estacao_Total',
  12 => 'GPS',
  13 => 'PIX',
  14 => 'RestricoesAereas',
  15 => 'TipoTerreno',
  16 => 'ValorExtenso',
  17 => 'ValorProposta',
  18 => 'Veiculo',
  19 => 'bairro_obra',
  20 => 'celular_salvo',
  21 => 'cidade_obra',
  22 => 'cidade_limpo',
  23 => 'email_salvo',
  24 => 'endereco_obra',
  25 => 'estado_obra',
  26 => 'finalidade',
  27 => 'mobilizacao_percentual',
  28 => 'mobilizacao_valor',
  29 => 'nome_cliente_salvo',
  30 => 'numero_proposta',
  31 => 'restante_percentual',
  32 => 'restante_valor',
  33 => 'telefone_salvo',
  34 => 'unidade_area',
  35 => 'whatsapp',
  36 => 'whatsapp_salvo',
);
        $this->cssCustom = "
        .modelo-docx { font-family: 'Segoe UI', Arial, sans-serif; line-height: 1.6; color: #333; max-width: 900px; margin: 0 auto; }
        
        .modelo-docx h1 { 
            color: #1f2937 !important; 
            border-bottom: 3px solid #1f2937 !important; 
            padding-bottom: 10px; 
            font-size: 24px; 
            font-weight: bold; 
            text-align: center;
        }
        
        .modelo-docx h2 { 
            color: #374151 !important; 
            margin-top: 25px; 
            font-size: 18px; 
            font-weight: bold; 
            border-left: 4px solid #6b7280 !important; 
            padding-left: 12px; 
        }
        
        .modelo-docx h3 { 
            color: #4b5563 !important; 
            font-size: 14px; 
            font-weight: bold; 
            margin-top: 20px; 
        }
        
        .tabela-proposta th { background: #1f2937; color: white !important; }
        .dados_cliente, .dados_obra { background: #f8fafc; padding: 15px; border-radius: 8px; margin: 10px 0; }
        ";
    }


    public function getConfig(): array {
        return [
            'nome' => self::NOME,
            'blocos' => $this->blocos,
            'variaveis' => $this->variaveisDetectadas,
            'css' => $this->cssCustom
        ];
    }
}


