-- phpMyAdmin SQL Dump
-- version 4.7.7
-- https://www.phpmyadmin.net/
--
-- Host: 179.188.16.118
-- Generation Time: 27-Nov-2025 às 07:38
-- Versão do servidor: 5.7.32-35-log
-- PHP Version: 5.6.40-0+deb8u12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `proposta`
--

-- --------------------------------------------------------

--
-- Estrutura da tabela `Clientes`
--

CREATE TABLE `Clientes` (
  `id_cliente` int(11) NOT NULL,
  `nome_cliente` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `empresa` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cnpj_cpf` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telefone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `celular` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `whatsapp` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_demo` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `DadosEmpresa`
--

CREATE TABLE `DadosEmpresa` (
  `id_empresa` int(11) NOT NULL,
  `Empresa` varchar(150) NOT NULL,
  `Endereco` varchar(150) NOT NULL,
  `Cidade` varchar(60) NOT NULL,
  `Estado` varchar(2) NOT NULL,
  `CNPJ` varchar(20) NOT NULL,
  `Telefone` varchar(20) DEFAULT NULL,
  `Celular` varchar(20) DEFAULT NULL,
  `Whatsapp` varchar(20) DEFAULT NULL,
  `Banco` varchar(50) NOT NULL,
  `Agencia` varchar(10) NOT NULL,
  `Conta` varchar(12) NOT NULL,
  `PIX` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Extraindo dados da tabela `DadosEmpresa`
--

INSERT INTO `DadosEmpresa` (`id_empresa`, `Empresa`, `Endereco`, `Cidade`, `Estado`, `CNPJ`, `Telefone`, `Celular`, `Whatsapp`, `Banco`, `Agencia`, `Conta`, `PIX`) VALUES
(1, 'ELM Serviços Topographical Ltda', 'Avenida Francisco Sá, 787 - Prado', 'Belo Horizonte', 'MG', '14.059.118/0001-08', '31 9 99585935', '31 9 71875928', '31 9 71875928', 'Banco Inter', '0001', '12914298-0', '31 9 9922-2617');

-- --------------------------------------------------------

--
-- Estrutura da tabela `estados`
--

CREATE TABLE `estados` (
  `id` int(11) NOT NULL,
  `nome` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sigla` char(2) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `estados`
--

INSERT INTO `estados` (`id`, `nome`, `sigla`) VALUES
(1, 'Acre', 'AC'),
(2, 'Alagoas', 'AL'),
(3, 'Amapá', 'AP'),
(4, 'Amazonas', 'AM'),
(5, 'Bahia', 'BA'),
(6, 'Ceará', 'CE'),
(7, 'Distrito Federal', 'DF'),
(8, 'Espírito Santo', 'ES'),
(9, 'Goiás', 'GO'),
(10, 'Maranhão', 'MA'),
(11, 'Mato Grosso', 'MT'),
(12, 'Mato Grosso do Sul', 'MS'),
(13, 'Minas Gerais', 'MG'),
(14, 'Pará', 'PA'),
(15, 'Paraíba', 'PB'),
(16, 'Paraná', 'PR'),
(17, 'Pernambuco', 'PE'),
(18, 'Piauí', 'PI'),
(19, 'Rio de Janeiro', 'RJ'),
(20, 'Rio Grande do Norte', 'RN'),
(21, 'Rio Grande do Sul', 'RS'),
(22, 'Rondônia', 'RO'),
(23, 'Roraima', 'RR'),
(24, 'Santa Catarina', 'SC'),
(25, 'São Paulo', 'SP'),
(26, 'Sergipe', 'SE'),
(27, 'Tocantins', 'TO');

-- --------------------------------------------------------

--
-- Estrutura da tabela `Marcas`
--

CREATE TABLE `Marcas` (
  `id_marca` int(11) NOT NULL,
  `id_locacao` int(11) NOT NULL COMMENT 'Vínculo com a tabela Tipo_Locacao',
  `nome_marca` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Extraindo dados da tabela `Marcas`
--

INSERT INTO `Marcas` (`id_marca`, `id_locacao`, `nome_marca`) VALUES
(1, 1, 'DJI Phantom 4 PRO'),
(2, 1, 'DJI Mavic'),
(3, 1, 'DJI Phantom 4 RTK'),
(4, 1, 'Autel EVO II Pro RTK'),
(5, 3, 'Leica FlexLine TS07 / TS10'),
(6, 3, 'Topcon GM-50 Series'),
(7, 3, 'Trimble C5'),
(8, 2, 'Emlid Reach RS3'),
(9, 2, 'Trimble R12i'),
(10, 2, 'Topcon Hiper VR'),
(11, 2, 'ComNav T300 Plus / T30'),
(12, 4, 'Toyota Hilux '),
(13, 4, 'Fiat Strada'),
(14, 4, 'Mitsubishi L200 Triton Sport'),
(15, 4, 'Ford Ranger');

-- --------------------------------------------------------

--
-- Estrutura da tabela `modelos_proposta`
--

CREATE TABLE `modelos_proposta` (
  `id_modelo` int(11) NOT NULL,
  `nome_modelo` varchar(255) NOT NULL,
  `descricao` varchar(255) DEFAULT NULL,
  `corpo_html_modelo` longtext
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estrutura da tabela `Propostas`
--

CREATE TABLE `Propostas` (
  `id_proposta` int(11) NOT NULL,
  `numero_proposta` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_cliente` int(11) DEFAULT NULL,
  `nome_cliente_salvo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_salvo` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telefone_salvo` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `celular_salvo` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `whatsapp_salvo` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `empresa_proponente_nome` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `empresa_proponente_cnpj` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `empresa_proponente_endereco` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `empresa_proponente_cidade` varchar(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `empresa_proponente_estado` varchar(2) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `empresa_proponente_banco` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `empresa_proponente_agencia` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `empresa_proponente_conta` varchar(12) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `empresa_proponente_pix` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_servico` int(11) DEFAULT NULL,
  `contato_obra` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `finalidade` text COLLATE utf8mb4_unicode_ci,
  `tipo_levantamento` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `area_obra` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `endereco_obra` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bairro_obra` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cidade_obra` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `estado_obra` varchar(2) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `prazo_execucao` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `dias_campo` int(11) DEFAULT NULL,
  `dias_escritorio` int(11) DEFAULT NULL,
  `data_criacao` datetime DEFAULT CURRENT_TIMESTAMP,
  `status` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'Em Elaboração',
  `total_custos_salarios` decimal(10,2) DEFAULT '0.00',
  `total_custos_estadia` decimal(10,2) DEFAULT '0.00',
  `total_custos_consumos` decimal(10,2) DEFAULT '0.00',
  `total_custos_locacao` decimal(10,2) DEFAULT '0.00',
  `total_custos_admin` decimal(10,2) DEFAULT '0.00',
  `total_custos_geral` decimal(10,2) GENERATED ALWAYS AS (((((`total_custos_salarios` + `total_custos_estadia`) + `total_custos_consumos`) + `total_custos_locacao`) + `total_custos_admin`)) STORED,
  `percentual_lucro` decimal(5,2) DEFAULT '30.00',
  `valor_lucro` decimal(10,2) DEFAULT '0.00',
  `subtotal_com_lucro` decimal(10,2) DEFAULT '0.00',
  `valor_desconto` decimal(10,2) DEFAULT '0.00',
  `valor_final_proposta` decimal(10,2) DEFAULT '0.00',
  `Valor_proposta_extenso` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mobilizacao_percentual` decimal(5,2) DEFAULT '30.00',
  `mobilizacao_valor` decimal(10,2) DEFAULT '0.00',
  `restante_percentual` decimal(5,2) DEFAULT NULL,
  `restante_valor` decimal(10,2) DEFAULT NULL,
  `id_criador` int(11) DEFAULT NULL,
  `is_demo` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `Proposta_Consumos`
--

CREATE TABLE `Proposta_Consumos` (
  `id_item_consumo` int(11) NOT NULL,
  `id_proposta` int(11) NOT NULL,
  `id_consumo` int(11) DEFAULT NULL COMMENT 'Link para a tabela Tipo_Consumo',
  `tipo` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Ex: Gasolina, Diesel',
  `quantidade` int(11) DEFAULT '1' COMMENT 'Qtd de veículos/fontes de consumo',
  `consumo_kml` decimal(5,2) DEFAULT NULL COMMENT 'Ex: 10.00 (para 10km/l)',
  `valor_litro` decimal(10,2) DEFAULT NULL COMMENT 'Preço (R$) do combustível',
  `km_total` decimal(10,2) DEFAULT NULL COMMENT 'Distância total a ser percorrida'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `Proposta_Custos_Administrativos`
--

CREATE TABLE `Proposta_Custos_Administrativos` (
  `id_item_custo_admin` int(11) NOT NULL,
  `id_proposta` int(11) NOT NULL,
  `id_custo_admin` int(11) DEFAULT NULL COMMENT 'Link para a tabela Tipo_Custo_Admin',
  `tipo` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Ex: ART, Treinamento, Sala Técnica',
  `quantidade` int(11) DEFAULT '1',
  `valor` decimal(10,2) DEFAULT '0.00' COMMENT 'Custo único deste item'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `Proposta_Estadia`
--

CREATE TABLE `Proposta_Estadia` (
  `id_item_estadia` int(11) NOT NULL,
  `id_proposta` int(11) NOT NULL,
  `id_estadia` int(11) DEFAULT NULL COMMENT 'Link para a tabela Tipo_Estadia',
  `tipo` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Ex: Hotel, Refeição, Pedágio',
  `quantidade` int(11) DEFAULT '1',
  `valor_unitario` decimal(10,2) DEFAULT '0.00' COMMENT 'Valor (R$) de 1 unidade por 1 dia',
  `dias` int(11) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `Proposta_Locacao`
--

CREATE TABLE `Proposta_Locacao` (
  `id_item_locacao` int(11) NOT NULL,
  `id_proposta` int(11) NOT NULL,
  `id_locacao` int(11) DEFAULT NULL COMMENT 'Link para a tabela Tipo_Locacao',
  `id_marca` int(11) DEFAULT NULL,
  `quantidade` int(11) DEFAULT '1',
  `valor_mensal` decimal(10,2) DEFAULT '0.00' COMMENT 'Custo de locação por 30 dias (Ex: 3000.00)',
  `dias` int(11) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `Proposta_Salarios`
--

CREATE TABLE `Proposta_Salarios` (
  `id_salario` int(11) NOT NULL,
  `id_proposta` int(11) NOT NULL,
  `id_funcao` int(11) DEFAULT NULL COMMENT 'Link para a tabela Tipo_Funcoes',
  `funcao` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `quantidade` int(11) DEFAULT '1',
  `salario_base` decimal(10,2) DEFAULT '0.00' COMMENT 'Salário MENSAL base (Ex: 4500.00)',
  `fator_encargos` decimal(5,2) DEFAULT '1.67' COMMENT 'Fator da Porcentagem (Ex: 1.67 para 67%)',
  `dias` decimal(5,1) DEFAULT '1.0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `Tipo_Consumo`
--

CREATE TABLE `Tipo_Consumo` (
  `id_consumo` int(11) NOT NULL,
  `nome` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Ex: Gasolina, Diesel, Etanol',
  `valor_litro_default` decimal(10,2) DEFAULT NULL COMMENT 'Preço (R$) padrão do litro para autocompletar',
  `consumo_kml_default` decimal(5,2) DEFAULT NULL COMMENT 'Consumo padrão (Km/L) para este item'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `Tipo_Consumo`
--

INSERT INTO `Tipo_Consumo` (`id_consumo`, `nome`, `valor_litro_default`, `consumo_kml_default`) VALUES
(1, 'Oleo Diesel', 0.00, 10.00),
(2, 'Alcool', 0.00, 10.00),
(3, 'Gasolina', 6.30, 10.00);

-- --------------------------------------------------------

--
-- Estrutura da tabela `Tipo_Custo_Admin`
--

CREATE TABLE `Tipo_Custo_Admin` (
  `id_custo_admin` int(11) NOT NULL,
  `nome` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Ex: ART, Treinamento, Sala Técnica',
  `valor_default` decimal(10,2) DEFAULT NULL COMMENT 'Custo padrão para este item (Ex: ART = R$ 150)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `Tipo_Custo_Admin`
--

INSERT INTO `Tipo_Custo_Admin` (`id_custo_admin`, `nome`, `valor_default`) VALUES
(1, 'Treinamentos', NULL),
(2, 'Outros', NULL),
(3, 'ART', 110.00);

-- --------------------------------------------------------

--
-- Estrutura da tabela `Tipo_Estadia`
--

CREATE TABLE `Tipo_Estadia` (
  `id_estadia` int(11) NOT NULL,
  `nome` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Ex: Hotel, Refeição, Pedágio, Vale Transporte',
  `valor_unitario_default` decimal(10,2) DEFAULT NULL COMMENT 'Valor padrão para autocompletar no formulário'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `Tipo_Estadia`
--

INSERT INTO `Tipo_Estadia` (`id_estadia`, `nome`, `valor_unitario_default`) VALUES
(1, 'Hotel', 0.00),
(2, 'Refeição', 30.00),
(3, 'Vale Transporte', 15.00),
(4, 'Pedagio', 0.00);

-- --------------------------------------------------------

--
-- Estrutura da tabela `Tipo_Funcoes`
--

CREATE TABLE `Tipo_Funcoes` (
  `id_funcao` int(11) NOT NULL,
  `nome` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Ex: Topógrafo, Auxiliar, Seção Técnica',
  `salario_base_default` decimal(10,2) DEFAULT NULL COMMENT 'Valor padrão para autocompletar no formulário'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `Tipo_Funcoes`
--

INSERT INTO `Tipo_Funcoes` (`id_funcao`, `nome`, `salario_base_default`) VALUES
(7, 'Avulso', 1518.00),
(8, 'Auxiliar do Topografo', 1800.00),
(9, 'Topografo', 4311.38),
(10, 'Seção Técnica', 4500.00);

-- --------------------------------------------------------

--
-- Estrutura da tabela `Tipo_Locacao`
--

CREATE TABLE `Tipo_Locacao` (
  `id_locacao` int(11) NOT NULL,
  `nome` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Ex: Veículo, GPS RTK, Estação Total',
  `valor_mensal_default` decimal(10,2) DEFAULT NULL COMMENT 'Valor padrão para autocompletar no formulário'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `Tipo_Locacao`
--

INSERT INTO `Tipo_Locacao` (`id_locacao`, `nome`, `valor_mensal_default`) VALUES
(1, 'Drone', 1500.00),
(2, 'GPS', 900.00),
(3, 'Estação Total', 1000.00),
(4, 'Veiculo', 3000.00);

-- --------------------------------------------------------

--
-- Estrutura da tabela `Tipo_Servicos`
--

CREATE TABLE `Tipo_Servicos` (
  `id_servico` int(11) NOT NULL,
  `nome` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Ex: Planimétrico, Usucapião, Drone',
  `descricao` text COLLATE utf8mb4_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `Tipo_Servicos`
--

INSERT INTO `Tipo_Servicos` (`id_servico`, `nome`, `descricao`) VALUES
(11, 'Usucapião', 'Medição e demarcação precisa do imóvel, definindo limites, confrontantes e área, para regularização da posse e registro no processo de usucapião.'),
(12, 'Planimétrico', 'Mede e representa com precisão os limites e detalhes horizontais do terreno, servindo de base para projetos, regularizações e obras civis.'),
(13, 'Planialtimétrico', 'Mede e representa com precisão os limites, relevo e detalhes do terreno, fornecendo base completa para projetos e obras de engenharia.'),
(14, 'Obra Terraplanagem', 'Define cotas, volumes e perfis do terreno para orientar cortes e aterros, garantindo precisão na execução da obra de terraplenagem.'),
(15, 'Obra Industrial', 'Apoio topográfico de precisão para locação de eixos, chumbadores e nivelamento de componentes em plantas industriais.'),
(16, 'Obra Civil', 'Garante a precisão na locação e controle das construções, servindo de base para fundações, alinhamentos e nivelamentos da obra.'),
(17, 'Locação de Obra', 'Materialização dos elementos de projeto em campo (eixos e gabaritos) para assegurar a fidelidade geométrica e o alinhamento estrutural da obra.'),
(18, 'Locação Terraplenagem', 'Locação de pontos e níveis de referência para assegurar a fidelidade da execução da terraplenagem em relação ao projeto aprovado.'),
(19, 'Drone', 'Execução de mapeamento aéreo de alta precisão para geração de ortofotos e modelos tridimensionais (3D), subsidiando análises e planejamentos topográficos.'),
(20, 'Desdobramento', 'Determinação analítica dos novos limites e áreas para o fracionamento do terreno, em estrito cumprimento às normas de registro.'),
(21, 'Conferência', 'Auditoria e verificação de dados topográficos para garantia de qualidade e fidelidade às referências originais do projeto.');

-- --------------------------------------------------------

--
-- Estrutura da tabela `Usuarios`
--

CREATE TABLE `Usuarios` (
  `id_usuario` int(11) NOT NULL,
  `usuario` varchar(50) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `nome_completo` varchar(100) DEFAULT NULL,
  `setup_concluido` tinyint(1) DEFAULT '0' COMMENT '0=Não, 1=Sim',
  `data_cadastro` datetime DEFAULT CURRENT_TIMESTAMP,
  `tipo_perfil` varchar(20) DEFAULT 'admin',
  `validade_acesso` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Extraindo dados da tabela `Usuarios`
--

INSERT INTO `Usuarios` (`id_usuario`, `usuario`, `senha`, `nome_completo`, `setup_concluido`, `data_cadastro`, `tipo_perfil`, `validade_acesso`) VALUES
(1, 'admin', '123456', 'Administrador', 1, '2025-11-23 09:27:23', 'admin', NULL),
(999, 'demo', '123', 'Visitante Demo', 1, '2025-11-26 22:10:10', 'demo', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `Clientes`
--
ALTER TABLE `Clientes`
  ADD PRIMARY KEY (`id_cliente`),
  ADD UNIQUE KEY `cnpj_cpf_unico` (`cnpj_cpf`),
  ADD KEY `idx_cliente_demo` (`is_demo`);

--
-- Indexes for table `DadosEmpresa`
--
ALTER TABLE `DadosEmpresa`
  ADD PRIMARY KEY (`id_empresa`);

--
-- Indexes for table `estados`
--
ALTER TABLE `estados`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `Marcas`
--
ALTER TABLE `Marcas`
  ADD PRIMARY KEY (`id_marca`),
  ADD KEY `id_locacao` (`id_locacao`);

--
-- Indexes for table `modelos_proposta`
--
ALTER TABLE `modelos_proposta`
  ADD PRIMARY KEY (`id_modelo`);

--
-- Indexes for table `Propostas`
--
ALTER TABLE `Propostas`
  ADD PRIMARY KEY (`id_proposta`),
  ADD UNIQUE KEY `numero_proposta` (`numero_proposta`),
  ADD KEY `fk_proposta_cliente` (`id_cliente`),
  ADD KEY `fk_proposta_servico` (`id_servico`),
  ADD KEY `idx_proposta_demo` (`is_demo`),
  ADD KEY `idx_criador_proposta` (`id_criador`);

--
-- Indexes for table `Proposta_Consumos`
--
ALTER TABLE `Proposta_Consumos`
  ADD PRIMARY KEY (`id_item_consumo`),
  ADD KEY `fk_consumo_proposta` (`id_proposta`),
  ADD KEY `fk_consumo_tipo` (`id_consumo`);

--
-- Indexes for table `Proposta_Custos_Administrativos`
--
ALTER TABLE `Proposta_Custos_Administrativos`
  ADD PRIMARY KEY (`id_item_custo_admin`),
  ADD KEY `fk_custoadmin_proposta` (`id_proposta`),
  ADD KEY `fk_custoadmin_tipo` (`id_custo_admin`);

--
-- Indexes for table `Proposta_Estadia`
--
ALTER TABLE `Proposta_Estadia`
  ADD PRIMARY KEY (`id_item_estadia`),
  ADD KEY `fk_estadia_proposta` (`id_proposta`),
  ADD KEY `fk_estadia_tipo` (`id_estadia`);

--
-- Indexes for table `Proposta_Locacao`
--
ALTER TABLE `Proposta_Locacao`
  ADD PRIMARY KEY (`id_item_locacao`),
  ADD KEY `fk_locacao_proposta` (`id_proposta`),
  ADD KEY `fk_locacao_tipo` (`id_locacao`),
  ADD KEY `fk_locacao_marca` (`id_marca`);

--
-- Indexes for table `Proposta_Salarios`
--
ALTER TABLE `Proposta_Salarios`
  ADD PRIMARY KEY (`id_salario`),
  ADD KEY `fk_salario_proposta` (`id_proposta`),
  ADD KEY `fk_salario_funcao` (`id_funcao`);

--
-- Indexes for table `Tipo_Consumo`
--
ALTER TABLE `Tipo_Consumo`
  ADD PRIMARY KEY (`id_consumo`);

--
-- Indexes for table `Tipo_Custo_Admin`
--
ALTER TABLE `Tipo_Custo_Admin`
  ADD PRIMARY KEY (`id_custo_admin`);

--
-- Indexes for table `Tipo_Estadia`
--
ALTER TABLE `Tipo_Estadia`
  ADD PRIMARY KEY (`id_estadia`);

--
-- Indexes for table `Tipo_Funcoes`
--
ALTER TABLE `Tipo_Funcoes`
  ADD PRIMARY KEY (`id_funcao`);

--
-- Indexes for table `Tipo_Locacao`
--
ALTER TABLE `Tipo_Locacao`
  ADD PRIMARY KEY (`id_locacao`);

--
-- Indexes for table `Tipo_Servicos`
--
ALTER TABLE `Tipo_Servicos`
  ADD PRIMARY KEY (`id_servico`);

--
-- Indexes for table `Usuarios`
--
ALTER TABLE `Usuarios`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `usuario` (`usuario`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `Clientes`
--
ALTER TABLE `Clientes`
  MODIFY `id_cliente` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `DadosEmpresa`
--
ALTER TABLE `DadosEmpresa`
  MODIFY `id_empresa` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `estados`
--
ALTER TABLE `estados`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `Marcas`
--
ALTER TABLE `Marcas`
  MODIFY `id_marca` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `modelos_proposta`
--
ALTER TABLE `modelos_proposta`
  MODIFY `id_modelo` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `Propostas`
--
ALTER TABLE `Propostas`
  MODIFY `id_proposta` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `Proposta_Consumos`
--
ALTER TABLE `Proposta_Consumos`
  MODIFY `id_item_consumo` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `Proposta_Custos_Administrativos`
--
ALTER TABLE `Proposta_Custos_Administrativos`
  MODIFY `id_item_custo_admin` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `Proposta_Estadia`
--
ALTER TABLE `Proposta_Estadia`
  MODIFY `id_item_estadia` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `Proposta_Locacao`
--
ALTER TABLE `Proposta_Locacao`
  MODIFY `id_item_locacao` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `Proposta_Salarios`
--
ALTER TABLE `Proposta_Salarios`
  MODIFY `id_salario` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `Tipo_Consumo`
--
ALTER TABLE `Tipo_Consumo`
  MODIFY `id_consumo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `Tipo_Custo_Admin`
--
ALTER TABLE `Tipo_Custo_Admin`
  MODIFY `id_custo_admin` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `Tipo_Estadia`
--
ALTER TABLE `Tipo_Estadia`
  MODIFY `id_estadia` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `Tipo_Funcoes`
--
ALTER TABLE `Tipo_Funcoes`
  MODIFY `id_funcao` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `Tipo_Locacao`
--
ALTER TABLE `Tipo_Locacao`
  MODIFY `id_locacao` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `Tipo_Servicos`
--
ALTER TABLE `Tipo_Servicos`
  MODIFY `id_servico` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `Usuarios`
--
ALTER TABLE `Usuarios`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1000;

--
-- Constraints for dumped tables
--

--
-- Limitadores para a tabela `Marcas`
--
ALTER TABLE `Marcas`
  ADD CONSTRAINT `Marcas_ibfk_1` FOREIGN KEY (`id_locacao`) REFERENCES `Tipo_Locacao` (`id_locacao`) ON DELETE CASCADE;

--
-- Limitadores para a tabela `Propostas`
--
ALTER TABLE `Propostas`
  ADD CONSTRAINT `fk_proposta_cliente` FOREIGN KEY (`id_cliente`) REFERENCES `Clientes` (`id_cliente`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_proposta_servico` FOREIGN KEY (`id_servico`) REFERENCES `Tipo_Servicos` (`id_servico`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Limitadores para a tabela `Proposta_Consumos`
--
ALTER TABLE `Proposta_Consumos`
  ADD CONSTRAINT `fk_consumo_proposta` FOREIGN KEY (`id_proposta`) REFERENCES `Propostas` (`id_proposta`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_consumo_tipo` FOREIGN KEY (`id_consumo`) REFERENCES `Tipo_Consumo` (`id_consumo`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Limitadores para a tabela `Proposta_Custos_Administrativos`
--
ALTER TABLE `Proposta_Custos_Administrativos`
  ADD CONSTRAINT `fk_custoadmin_proposta` FOREIGN KEY (`id_proposta`) REFERENCES `Propostas` (`id_proposta`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_custoadmin_tipo` FOREIGN KEY (`id_custo_admin`) REFERENCES `Tipo_Custo_Admin` (`id_custo_admin`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Limitadores para a tabela `Proposta_Estadia`
--
ALTER TABLE `Proposta_Estadia`
  ADD CONSTRAINT `fk_estadia_proposta` FOREIGN KEY (`id_proposta`) REFERENCES `Propostas` (`id_proposta`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_estadia_tipo` FOREIGN KEY (`id_estadia`) REFERENCES `Tipo_Estadia` (`id_estadia`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Limitadores para a tabela `Proposta_Locacao`
--
ALTER TABLE `Proposta_Locacao`
  ADD CONSTRAINT `fk_locacao_marca` FOREIGN KEY (`id_marca`) REFERENCES `Marcas` (`id_marca`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_locacao_proposta` FOREIGN KEY (`id_proposta`) REFERENCES `Propostas` (`id_proposta`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_locacao_tipo` FOREIGN KEY (`id_locacao`) REFERENCES `Tipo_Locacao` (`id_locacao`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Limitadores para a tabela `Proposta_Salarios`
--
ALTER TABLE `Proposta_Salarios`
  ADD CONSTRAINT `fk_salario_funcao` FOREIGN KEY (`id_funcao`) REFERENCES `Tipo_Funcoes` (`id_funcao`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_salario_proposta` FOREIGN KEY (`id_proposta`) REFERENCES `Propostas` (`id_proposta`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
