<?php
/**
 * MODELO GERADO AUTOMATICAMENTE - SGT DOCX Parser
 * Fonte: PropostaDronewebMarrom.docx
 */

namespace SGT\Propostas;

require_once __DIR__ . '/../ResolvedorChavesSistema.php';

class ModeloPropostaDronewebMarrom 
{
    const NOME = 'PropostaDronewebMarrom';
    
    protected $blocos;
    protected $variaveisDetectadas;
    protected $cssCustom;

    public function __construct() {
        $this->blocos = array (
  0 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'texto_geral',
    'conteudo' => '🖨️',
    'estilos_css' => 
    array (
      'text-align' => 'center'
    ),
    'variaveis' => 
    array (
    ),
    'nivel_titulo' => 0
  ),
  1 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'titulo',
    'conteudo' => 'Proposta Técnica',
    'estilos_css' => 
    array (
      'font-size' => '20.0px',
      'color' => '#0F4761',
      'text-align' => 'right'
    ),
    'variaveis' => 
    array (
    ),
    'nivel_titulo' => 1
  ),
  2 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'texto_geral',
    'conteudo' => ', 21 de Fevereiro de 2026Nº GEOMETRPOLE-2026-030-Rv03',
    'estilos_css' => 
    array (
    ),
    'variaveis' => 
    array (
    ),
    'nivel_titulo' => 0
  ),
  3 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'titulo',
    'conteudo' => 'Proposta de Serviços – Topografia e Mapeamento Aéreo',
    'estilos_css' => 
    array (
      'font-size' => '20.0px',
      'color' => '#0F4761',
      'text-align' => 'center'
    ),
    'variaveis' => 
    array (
    ),
    'nivel_titulo' => 1
  ),
  4 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'texto_geral',
    'conteudo' => 'Proposta Nº: GEOMETRPOLE-2026-030-Rv03',
    'estilos_css' => 
    array (
      'text-align' => 'right'
    ),
    'variaveis' => 
    array (
      0 => 'numero_proposta'
    ),
    'nivel_titulo' => 0
  ),
  5 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'texto_geral',
    'conteudo' => '[Cidade], 21 de fevereiro de 2026',
    'estilos_css' => 
    array (
    ),
    'variaveis' => 
    array (
    ),
    'nivel_titulo' => 0
  ),
  6 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'dados_cliente',
    'conteudo' => 'Nome:',
    'estilos_css' => 
    array (
    ),
    'variaveis' => 
    array (
    ),
    'nivel_titulo' => 0
  ),
  7 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'dados_cliente',
    'conteudo' => 'E-mail:',
    'estilos_css' => 
    array (
    ),
    'variaveis' => 
    array (
    ),
    'nivel_titulo' => 0
  ),
  8 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'texto_geral',
    'conteudo' => 'Telefone:',
    'estilos_css' => 
    array (
    ),
    'variaveis' => 
    array (
    ),
    'nivel_titulo' => 0
  ),
  9 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'texto_geral',
    'conteudo' => 'Celular:',
    'estilos_css' => 
    array (
    ),
    'variaveis' => 
    array (
    ),
    'nivel_titulo' => 0
  ),
  10 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'texto_geral',
    'conteudo' => 'WhatsApp:',
    'estilos_css' => 
    array (
    ),
    'variaveis' => 
    array (
    ),
    'nivel_titulo' => 0
  ),
  11 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'titulo',
    'conteudo' => 'Local da Obra',
    'estilos_css' => 
    array (
      'font-size' => '16.0px',
      'color' => '#0F4761'
    ),
    'variaveis' => 
    array (
    ),
    'nivel_titulo' => 2
  ),
  12 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'dados_obra',
    'conteudo' => 'Endereço:',
    'estilos_css' => 
    array (
    ),
    'variaveis' => 
    array (
    ),
    'nivel_titulo' => 0
  ),
  13 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'dados_obra',
    'conteudo' => 'Bairro:',
    'estilos_css' => 
    array (
    ),
    'variaveis' => 
    array (
    ),
    'nivel_titulo' => 0
  ),
  14 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'dados_obra',
    'conteudo' => 'Cidade/Estado: -',
    'estilos_css' => 
    array (
    ),
    'variaveis' => 
    array (
    ),
    'nivel_titulo' => 0
  ),
  15 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'texto_geral',
    'conteudo' => 'Área Estimada: m²',
    'estilos_css' => 
    array (
    ),
    'variaveis' => 
    array (
    ),
    'nivel_titulo' => 0
  ),
  16 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'titulo',
    'conteudo' => '1. Apresentação e Entendimento do Serviço',
    'estilos_css' => 
    array (
      'font-size' => '16.0px',
      'color' => '#0F4761'
    ),
    'variaveis' => 
    array (
    ),
    'nivel_titulo' => 2
  ),
  17 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'dados_obra',
    'conteudo' => 'A GeoMetrópole Engenharia e Topografia Ltda. apresenta esta proposta técnica visando a execução de levantamento topográfico planialtimétrico através de Aerofotogrametria com Drones (VANTs). Diferente de simples filmagens aéreas, este serviço trata-se de Engenharia de Precisão. O objetivo é gerar uma representação digital fiel do terreno, com coordenadas exatas (Latitude, Longitude e Altitude), servindo de base legal e técnica para projetos de arquitetura, loteamentos, regularização fundiária e cálculos de volume, se necessário.',
    'estilos_css' => 
    array (
      'text-align' => 'justify'
    ),
    'variaveis' => 
    array (
    ),
    'nivel_titulo' => 0
  ),
  18 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'titulo',
    'conteudo' => '2. Finalidade',
    'estilos_css' => 
    array (
      'font-size' => '16.0px',
      'color' => '#0F4761'
    ),
    'variaveis' => 
    array (
    ),
    'nivel_titulo' => 2
  ),
  19 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'titulo',
    'conteudo' => '3. Escopo do Serviço',
    'estilos_css' => 
    array (
      'font-size' => '16.0px',
      'color' => '#0F4761'
    ),
    'variaveis' => 
    array (
    ),
    'nivel_titulo' => 2
  ),
  20 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'equipamentos',
    'conteudo' => 'Levantamento Fotogramétrico com Drone',
    'estilos_css' => 
    array (
    ),
    'variaveis' => 
    array (
    ),
    'nivel_titulo' => 0
  ),
  21 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'dados_obra',
    'conteudo' => 'Execução de levantamento na área de m² m², localizada em - , com as seguintes características:',
    'estilos_css' => 
    array (
    ),
    'variaveis' => 
    array (
    ),
    'nivel_titulo' => 0
  ),
  22 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'dados_obra',
    'conteudo' => 'Tipo de Terreno: Não informado;',
    'estilos_css' => 
    array (
    ),
    'variaveis' => 
    array (
    ),
    'nivel_titulo' => 0
  ),
  23 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'texto_geral',
    'conteudo' => 'Cobertura Vegetal: Não informado;',
    'estilos_css' => 
    array (
    ),
    'variaveis' => 
    array (
    ),
    'nivel_titulo' => 0
  ),
  24 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'texto_geral',
    'conteudo' => 'Acesso: Não informado;',
    'estilos_css' => 
    array (
    ),
    'variaveis' => 
    array (
    ),
    'nivel_titulo' => 0
  ),
  25 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'texto_geral',
    'conteudo' => 'Restrições: Não informado.',
    'estilos_css' => 
    array (
    ),
    'variaveis' => 
    array (
    ),
    'nivel_titulo' => 0
  ),
  26 => 
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
    'nivel_titulo' => 0
  ),
  27 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'texto_geral',
    'conteudo' => 'Planejamento de voo e estudo de viabilidade aérea (consulta DECEA para -',
    'estilos_css' => 
    array (
    ),
    'variaveis' => 
    array (
    ),
    'nivel_titulo' => 0
  ),
  28 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'equipamentos',
    'conteudo' => 'Implantação de Pontos de Controle Terrestre (GCPs) com GPS RTK;',
    'estilos_css' => 
    array (
    ),
    'variaveis' => 
    array (
    ),
    'nivel_titulo' => 0
  ),
  29 => 
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
    'nivel_titulo' => 0
  ),
  30 => 
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
    'nivel_titulo' => 0
  ),
  31 => 
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
    'nivel_titulo' => 0
  ),
  32 => 
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
    'nivel_titulo' => 0
  ),
  33 => 
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
    'nivel_titulo' => 0
  ),
  34 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'titulo',
    'conteudo' => '4. Metodologia: O Passo a Passo do Mapeamento',
    'estilos_css' => 
    array (
      'font-size' => '16.0px',
      'color' => '#0F4761'
    ),
    'variaveis' => 
    array (
    ),
    'nivel_titulo' => 2
  ),
  35 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'prazos',
    'conteudo' => 'Para garantir que o produto final tenha validade topográfica, seguimos um rigoroso fluxo de trabalho dividido em etapas de campo e escritório. Abaixo, detalhamos cada fase para total compreensão do processo contratado:',
    'estilos_css' => 
    array (
      'text-align' => 'justify'
    ),
    'variaveis' => 
    array (
    ),
    'nivel_titulo' => 0
  ),
  36 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'titulo',
    'conteudo' => 'FASE 1: Planejamento e Configuração de Voo (Escritório)',
    'estilos_css' => 
    array (
      'font-size' => '14.0px',
      'color' => '#0F4761'
    ),
    'variaveis' => 
    array (
    ),
    'nivel_titulo' => 3
  ),
  37 => 
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
    'nivel_titulo' => 0
  ),
  38 => 
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
    'nivel_titulo' => 0
  ),
  39 => 
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
    'nivel_titulo' => 0
  ),
  40 => 
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
    'nivel_titulo' => 0
  ),
  41 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'titulo',
    'conteudo' => 'FASE 2: Apoio Terrestre - Pontos de Controle (Campo) Esta é a etapa que diferencia uma foto comum de um mapa topográfico.',
    'estilos_css' => 
    array (
      'font-size' => '14.0px',
      'color' => '#0F4761'
    ),
    'variaveis' => 
    array (
    ),
    'nivel_titulo' => 3
  ),
  42 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'equipamentos',
    'conteudo' => 'O Procedimento: Antes do drone decolar, nossa equipe distribui e pinta alvos no chão ou utiliza marcos naturais. As coordenadas exatas do centro desses alvos são coletadas com GPS Geodésico de Alta Precisão (RTK).',
    'estilos_css' => 
    array (
    ),
    'variaveis' => 
    array (
    ),
    'nivel_titulo' => 0
  ),
  43 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'equipamentos',
    'conteudo' => 'Importância: Esses pontos servem como "âncoras" que amarram as fotos do drone ao sistema de coordenadas do mundo real, corrigindo distorções e garantindo precisão centimétrica.',
    'estilos_css' => 
    array (
    ),
    'variaveis' => 
    array (
    ),
    'nivel_titulo' => 0
  ),
  44 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'equipamentos',
    'conteudo' => 'Checklist: Verificação da fixação dos pontos, nivelamento do bastão GPS e tempo de rastreio dos satélites.',
    'estilos_css' => 
    array (
    ),
    'variaveis' => 
    array (
    ),
    'nivel_titulo' => 0
  ),
  45 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'titulo',
    'conteudo' => 'FASE 3: Evolução do Voo e Captura de Dados (Campo) * Checklist de Segurança: Verificação de baterias, hélices, cartões de memória, interferência magnética (bússola), condições do vento e autorizações de voo (DECEA).',
    'estilos_css' => 
    array (
      'font-size' => '14.0px',
      'color' => '#0F4761'
    ),
    'variaveis' => 
    array (
    ),
    'nivel_titulo' => 3
  ),
  46 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'equipamentos',
    'conteudo' => 'O Voo: O drone percorre a rota autônoma, capturando centenas de fotos em ângulos verticais (nadir) e oblíquos.',
    'estilos_css' => 
    array (
    ),
    'variaveis' => 
    array (
    ),
    'nivel_titulo' => 0
  ),
  47 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'texto_geral',
    'conteudo' => 'Resultado: Coleta de imagens brutas (Raw Data) que, isoladamente, não possuem escala, mas que juntas formarão o mapa.',
    'estilos_css' => 
    array (
    ),
    'variaveis' => 
    array (
    ),
    'nivel_titulo' => 0
  ),
  48 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'titulo',
    'conteudo' => 'FASE 4: Processamento Fotogramétrico (Escritório) Utilizamos supercomputadores (Workstations) e softwares específicos para transformar as fotos em geometria.',
    'estilos_css' => 
    array (
      'font-size' => '14.0px',
      'color' => '#0F4761'
    ),
    'variaveis' => 
    array (
    ),
    'nivel_titulo' => 3
  ),
  49 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'prazos',
    'conteudo' => 'Etapas do Processamento:',
    'estilos_css' => 
    array (
    ),
    'variaveis' => 
    array (
    ),
    'nivel_titulo' => 0
  ),
  50 => 
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
    'nivel_titulo' => 0
  ),
  51 => 
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
    'nivel_titulo' => 0
  ),
  52 => 
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
    'nivel_titulo' => 0
  ),
  53 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'titulo',
    'conteudo' => 'FASE 5: Vetorização e Desenho Técnico (Escritório - CAD)',
    'estilos_css' => 
    array (
      'font-size' => '14.0px',
      'color' => '#0F4761'
    ),
    'variaveis' => 
    array (
    ),
    'nivel_titulo' => 3
  ),
  54 => 
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
    'nivel_titulo' => 0
  ),
  55 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'dados_obra',
    'conteudo' => 'O Trabalho: Vetorização de guias, cercas, edificações, postes, árvores e, principalmente, a geração das Curvas de Nível (linhas que representam a altura do terreno).',
    'estilos_css' => 
    array (
    ),
    'variaveis' => 
    array (
    ),
    'nivel_titulo' => 0
  ),
  56 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'titulo',
    'conteudo' => '5. Equipamentos Previstos',
    'estilos_css' => 
    array (
    ),
    'variaveis' => 
    array (
    ),
    'nivel_titulo' => 2
  ),
  57 => 
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
    'nivel_titulo' => 0
  ),
  58 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'texto_geral',
    'conteudo' => 'Aeronave: Não aplicável (Câmera de Alta Resolução).',
    'estilos_css' => 
    array (
    ),
    'variaveis' => 
    array (
    ),
    'nivel_titulo' => 0
  ),
  59 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'equipamentos',
    'conteudo' => 'GPS - Geodésia: Par de Receptores GNSS RTK (Receptor GNSS RTK/PPK para Pontos de Controle).',
    'estilos_css' => 
    array (
    ),
    'variaveis' => 
    array (
    ),
    'nivel_titulo' => 0
  ),
  60 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'equipamentos',
    'conteudo' => 'Estação Total para Apoio: Não inclusa (Se necessário para áreas de sombra de GPS).',
    'estilos_css' => 
    array (
    ),
    'variaveis' => 
    array (
    ),
    'nivel_titulo' => 0
  ),
  61 => 
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
    'nivel_titulo' => 0
  ),
  62 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'texto_geral',
    'conteudo' => 'Veiculo: Não incluso para apoio a locomoção.',
    'estilos_css' => 
    array (
    ),
    'variaveis' => 
    array (
    ),
    'nivel_titulo' => 0
  ),
  63 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'titulo',
    'conteudo' => '6. Produtos Entregues (O que você recebe)',
    'estilos_css' => 
    array (
      'font-size' => '16.0px',
      'color' => '#0F4761'
    ),
    'variaveis' => 
    array (
    ),
    'nivel_titulo' => 2
  ),
  64 => 
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
    'nivel_titulo' => 0
  ),
  65 => 
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
    'nivel_titulo' => 0
  ),
  66 => 
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
    'nivel_titulo' => 0
  ),
  67 => 
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
    'nivel_titulo' => 0
  ),
  68 => 
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
    'nivel_titulo' => 0
  ),
  69 => 
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
    'nivel_titulo' => 0
  ),
  70 => 
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
    'nivel_titulo' => 0
  ),
  71 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'titulo',
    'conteudo' => '8. Prazos Estimados',
    'estilos_css' => 
    array (
      'font-size' => '16.0px',
      'color' => '#0F4761'
    ),
    'variaveis' => 
    array (
    ),
    'nivel_titulo' => 2
  ),
  72 => 
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
    'nivel_titulo' => 0
  ),
  73 => 
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
            'vertical-align' => 'top'
          )
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
            'vertical-align' => 'top'
          )
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
            'vertical-align' => 'top'
          )
        )
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
            'vertical-align' => 'top'
          )
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
            'vertical-align' => 'top'
          )
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
            'vertical-align' => 'top'
          )
        )
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
            'vertical-align' => 'top'
          )
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
            'vertical-align' => 'top'
          )
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
            'vertical-align' => 'top'
          )
        )
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
            'vertical-align' => 'top'
          )
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
            'vertical-align' => 'top'
          )
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
            'vertical-align' => 'top'
          )
        )
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
            'vertical-align' => 'top'
          )
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
            'vertical-align' => 'top'
          )
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
            'vertical-align' => 'top'
          )
        )
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
            'vertical-align' => 'top'
          )
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
            'vertical-align' => 'top'
          )
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
            'vertical-align' => 'top'
          )
        )
      )
    ),
    'estilos' => 
    array (
      'width' => '100%',
      'border-collapse' => 'collapse',
      'margin' => '25px 0',
      'font-size' => '14px'
    ),
    'variaveis' => 
    array (
    )
  ),
  74 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'titulo',
    'conteudo' => '9. Investimento',
    'estilos_css' => 
    array (
      'font-size' => '16.0px',
      'color' => '#0F4761'
    ),
    'variaveis' => 
    array (
    ),
    'nivel_titulo' => 2
  ),
  75 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'valores',
    'conteudo' => 'O valor total para execução dos serviços descritos, incluindo equipe técnica, equipamentos (Drone, GPS RTK, Estação de Trabalho), deslocamento e impostos, é de: R$ 11.500,00 (ONZE MIL E QUINHENTOS REAIS)',
    'estilos_css' => 
    array (
    ),
    'variaveis' => 
    array (
    ),
    'nivel_titulo' => 0
  ),
  76 => 
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
    'nivel_titulo' => 0
  ),
  77 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'titulo',
    'conteudo' => '10. Condições de Pagamento',
    'estilos_css' => 
    array (
      'font-size' => '16.0px',
      'color' => '#0F4761'
    ),
    'variaveis' => 
    array (
    ),
    'nivel_titulo' => 2
  ),
  78 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'valores',
    'conteudo' => 'Mobilização (Sinal): 30.00% – R$ 3.450,00 (No aceite da proposta).',
    'estilos_css' => 
    array (
    ),
    'variaveis' => 
    array (
    ),
    'nivel_titulo' => 0
  ),
  79 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'valores',
    'conteudo' => 'Entrega Final: 70.00% – R$ 8.050,00 (Na entrega dos arquivos digitais e físicos).',
    'estilos_css' => 
    array (
    ),
    'variaveis' => 
    array (
    ),
    'nivel_titulo' => 0
  ),
  80 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'titulo',
    'conteudo' => 'Dados Bancários:',
    'estilos_css' => 
    array (
      'font-size' => '16.0px',
      'color' => '#0F4761'
    ),
    'variaveis' => 
    array (
    ),
    'nivel_titulo' => 2
  ),
  81 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'pagamento',
    'conteudo' => 'Banco: Itaú Unibanco S.A.',
    'estilos_css' => 
    array (
    ),
    'variaveis' => 
    array (
    ),
    'nivel_titulo' => 0
  ),
  82 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'pagamento',
    'conteudo' => 'Agência: 2934',
    'estilos_css' => 
    array (
    ),
    'variaveis' => 
    array (
    ),
    'nivel_titulo' => 0
  ),
  83 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'pagamento',
    'conteudo' => 'Conta: 56789-0',
    'estilos_css' => 
    array (
    ),
    'variaveis' => 
    array (
    ),
    'nivel_titulo' => 0
  ),
  84 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'texto_geral',
    'conteudo' => 'Titular: GeoMetrópole Engenharia e Topografia Ltda. | CNPJ: 45.123.890/0001-56',
    'estilos_css' => 
    array (
    ),
    'variaveis' => 
    array (
    ),
    'nivel_titulo' => 0
  ),
  85 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'titulo',
    'conteudo' => 'PIX: financeiro@geometropolesp.com.',
    'estilos_css' => 
    array (
      'font-size' => '20.0px',
      'color' => '#0F4761'
    ),
    'variaveis' => 
    array (
    ),
    'nivel_titulo' => 1
  ),
  86 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'titulo',
    'conteudo' => '11. Considerações Finais',
    'estilos_css' => 
    array (
      'font-size' => '16.0px',
      'color' => '#0F4761'
    ),
    'variaveis' => 
    array (
    ),
    'nivel_titulo' => 2
  ),
  87 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'dados_obra',
    'conteudo' => 'Esta proposta tem validade de 15 dias. A GeoMetrópole Engenharia e Topografia Ltda. traz em seu DNA uma sólida vivência prática em obras complexas, aplicando o rigor da topografia clássica às inovações do mapeamento aéreo. Entendemos a responsabilidade que um levantamento planialtimétrico carrega em projetos de engenharia e infraestrutura. Por isso, nossa equipe coloca-se à disposição para alinhar as melhores soluções técnicas para a sua demanda, assegurando a entrega de um produto final de altíssima confiabilidade e precisão.',
    'estilos_css' => 
    array (
    ),
    'variaveis' => 
    array (
    ),
    'nivel_titulo' => 0
  ),
  88 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'texto_geral',
    'conteudo' => 'Atenciosamente,',
    'estilos_css' => 
    array (
      'text-align' => 'center'
    ),
    'variaveis' => 
    array (
    ),
    'nivel_titulo' => 0
  ),
  89 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'texto_geral',
    'conteudo' => '_________________________________________________',
    'estilos_css' => 
    array (
      'text-align' => 'center'
    ),
    'variaveis' => 
    array (
    ),
    'nivel_titulo' => 0
  ),
  90 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'texto_geral',
    'conteudo' => 'GeoMetrópole Engenharia e Topografia Ltda. - (31) 3254-8890',
    'estilos_css' => 
    array (
      'text-align' => 'center'
    ),
    'variaveis' => 
    array (
    ),
    'nivel_titulo' => 0
  ),
  91 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'texto_geral',
    'conteudo' => 'Atenciosamente,',
    'estilos_css' => 
    array (
      'text-align' => 'center'
    ),
    'variaveis' => 
    array (
    ),
    'nivel_titulo' => 0
  ),
  92 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'texto_geral',
    'conteudo' => 'ELM Serviços Topográficos Ltda.',
    'estilos_css' => 
    array (
      'text-align' => 'center'
    ),
    'variaveis' => 
    array (
    ),
    'nivel_titulo' => 0
  ),
  93 => 
  array (
    'tipo' => 'texto',
    'subtipo' => 'texto_geral',
    'conteudo' => 'Belo Horizonte - MG • (31) 3625-4769 • contato@geometropole.com.br',
    'estilos_css' => 
    array (
      'text-align' => 'center'
    ),
    'variaveis' => 
    array (
    ),
    'nivel_titulo' => 0
  )
);
        $this->variaveisDetectadas = array (
  0 => 'DataExtenso',
  1 => 'cidade_limpo',
  2 => 'numero_proposta',
  3 => 'whatsapp'
);
        $this->cssCustom = "
        .modelo-docx-container h1 { color: #1e3a8a !important; border-bottom: 2px solid #3b82f6 !important; padding-bottom: 5px; }
        .modelo-docx-container h2 { color: #1e40af !important; margin-top: 20px; border-left: 4px solid #3b82f6 !important; padding-left: 10px; }
        .modelo-docx-container h3 { color: #3b82f6 !important; }
        .modelo-docx-container p { line-height: 1.6; color: #333; }
        .modelo-docx-container table { border: 1px solid #3b82f6 !important; }
        .modelo-docx-container th { background-color: #1e3a8a; color: white !important; }
    

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

    public function getConfig(): array
    {
        return array(
            'nome' => self::NOME,
            'blocos' => $this->blocos,
            'variaveis' => $this->variaveisDetectadas,
            'css' => $this->cssCustom
        );
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