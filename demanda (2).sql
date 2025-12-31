-- phpMyAdmin SQL Dump
-- version 4.7.7
-- https://www.phpmyadmin.net/
--
-- Host: 186.202.152.243
-- Generation Time: 13-Nov-2025 às 10:54
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
-- Database: `demanda`
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
  `whatsapp` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `Clientes`
--

INSERT INTO `Clientes` (`id_cliente`, `nome_cliente`, `empresa`, `cnpj_cpf`, `email`, `telefone`, `celular`, `whatsapp`) VALUES
(1, 'Retiro das Pedras', 'Retiro das Pedras', '', '', '31 9548-0463', NULL, NULL),
(2, 'Teste Cliente', 'Info Brasol', '37081098649', 'TC@iig.com', '3195480463', NULL, NULL),
(7, 'Haroldo Barbosa Mello', 'Tradicional Empreendimentos Ltda', '47859254102', 'edivaldo@elmtopografia.com.br', '3195480463', NULL, NULL),
(8, 'Lucio Braga Antunes Jr.', '', '45832014598', 'jr@gmail.com', '3188955965', NULL, NULL),
(9, 'Nelson Henrique Giovanni Galvão', 'Tradicional Empreendimentos Ltda', '019.311.916-11', 'nelson_henrique_galvao@mailnull.com', '(31) 2887-6558', NULL, NULL),
(12, 'Edivaldo Lins Macedo', 'ELM Topografia', '32581098645', 'edivaldo@elmtopografia.com.br', '3195480463', NULL, NULL),
(15, 'Flavia Dantas do Amaral', 'Dantas Telefonia Lyda', '24.715.712/0001-55', 'dantas@da.com.br', '61 9 9988-5547', NULL, NULL);

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
  `Banco` varchar(50) NOT NULL,
  `Agencia` varchar(10) NOT NULL,
  `Conta` varchar(12) NOT NULL,
  `PIX` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Extraindo dados da tabela `DadosEmpresa`
--

INSERT INTO `DadosEmpresa` (`id_empresa`, `Empresa`, `Endereco`, `Cidade`, `Estado`, `CNPJ`, `Banco`, `Agencia`, `Conta`, `PIX`) VALUES
(1, 'ELM Serviços Topographical Ltda', 'Avenida Francisco Sá, 787 - Prado', 'Belo Horizonte', 'MG', '14.059.118/0001-08', 'Banco Inter', '0001', '12914298-0', '31 9 9922-2617');

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
  `restante_valor` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `Propostas`
--

INSERT INTO `Propostas` (`id_proposta`, `numero_proposta`, `id_cliente`, `nome_cliente_salvo`, `email_salvo`, `telefone_salvo`, `celular_salvo`, `whatsapp_salvo`, `empresa_proponente_nome`, `empresa_proponente_cnpj`, `empresa_proponente_endereco`, `empresa_proponente_cidade`, `empresa_proponente_estado`, `empresa_proponente_banco`, `empresa_proponente_agencia`, `empresa_proponente_conta`, `empresa_proponente_pix`, `id_servico`, `contato_obra`, `finalidade`, `tipo_levantamento`, `area_obra`, `endereco_obra`, `bairro_obra`, `cidade_obra`, `estado_obra`, `prazo_execucao`, `dias_campo`, `dias_escritorio`, `data_criacao`, `status`, `total_custos_salarios`, `total_custos_estadia`, `total_custos_consumos`, `total_custos_locacao`, `total_custos_admin`, `percentual_lucro`, `valor_lucro`, `subtotal_com_lucro`, `valor_desconto`, `valor_final_proposta`, `Valor_proposta_extenso`, `mobilizacao_percentual`, `mobilizacao_valor`, `restante_percentual`, `restante_valor`) VALUES
(1, 'ELM-2025-001', 7, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 13, 'Haroldo 3195480463', 'Topografia Mede e representa com precisão os limites, relevo e detalhes do terreno, fornecendo base completa para projetos e obras de engenharia.', 'Levantamento Planialtimétrico', '2ha', 'Rua Alameda das Rosas 145', 'Alphaville', 'Nova Lima', NULL, '5', 1, 4, '2025-11-10 09:12:18', 'Cancelada', 590.70, 90.00, 63.60, 163.33, 110.00, 30.00, 305.29, 1322.92, 22.92, 1300.00, NULL, 30.00, 390.00, 70.00, 910.00),
(2, 'ELM-2025-002', 8, 'Lucio Braga Antunes Jr.', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 13, 'Lucio 3188955965', 'Topografia Mede e representa com precisão os limites, relevo e detalhes do terreno, fornecendo base completa para projetos e obras de engenharia.', 'Levantamento Planialtimétrico', '360m²', 'Rua Barbara Eleodora, 302 ', 'Lourdes', 'Belo Horizonte', NULL, '5', 1, 4, '2025-11-10 10:07:27', 'Recusada', 741.00, 120.00, 75.60, 163.33, 110.00, 30.00, 362.98, 1572.91, 22.91, 1550.00, NULL, 30.00, 465.00, 70.00, 1085.00),
(3, 'ELM-2025-003', 8, 'Lucio Braga Antunes Jr.', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 13, 'Lucio 3188955965', 'Topografia Mede e representa com precisão os limites, relevo e detalhes do terreno, fornecendo base completa para projetos e obras de engenharia.', 'Levantamento Planialtimétrico', '360m²', 'Rua Barbara Eleodora, 302 ', 'Lourdes', 'Belo Horizonte', NULL, '5', 1, 4, '2025-11-10 10:07:27', 'Enviada', 741.00, 120.00, 75.60, 163.33, 110.00, 30.00, 362.98, 1572.91, 22.91, 1550.00, NULL, 30.00, 465.00, 70.00, 1085.00),
(4, 'ELM-2025-004', 8, 'Lucio Braga Antunes Jr.', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 12, 'Lucio 3188955965', 'Topografia Mede e representa com precisão os limites e detalhes horizontais do terreno, servindo de base para projetos, regularizações e obras civis.', 'Levantamento Planimétrico', '360m²', 'Rua Ernesto Graves da Costa, 147', 'Lardim das Acacias', 'Belo Horizonte', NULL, '5', 1, 4, '2025-11-10 10:43:53', 'Em Elaboração', 1431.90, 180.00, 86.94, 213.33, 110.00, 30.00, 606.65, 2628.83, 128.83, 2500.00, NULL, 30.00, 750.00, 70.00, 1750.00),
(5, 'ELM-DIAG-1762868078', 8, 'Lucio Braga Antunes Jr.', 'jr@gmail.com', '3188955965', NULL, NULL, 'ELM Serviços Topographical Ltda', '14.059.118/0001-08', 'Avenida Francisco Sá, 787 - Prado', 'Belo Horizonte', 'MG', 'Banco Inter', '0001', '12914298-0', '31 9 9922-2617', 15, '0', 'Topografia Fornece base topográfica precisa para implantação, nivelamento e controle geométrico de estruturas em áreas industriais.', 'Levantamento Obra Industrial', '', 'Portaria Condominio Retiro das Pedras', 'Penha', 'Nova Lima', 'MG', '5 dias', 1, 4, '2025-11-11 10:34:38', 'Em Elaboração', 490.00, 30.00, 31.50, 133.33, 110.00, 30.00, 238.60, 1033.93, 33.93, 1000.00, NULL, 30.00, 300.00, 70.00, 700.00),
(6, 'ELM-2025-005', 12, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 13, 'Edivaldo 3195480463', 'Topografia Mede e representa com precisão os limites, relevo e detalhes do terreno, fornecendo base completa para projetos e obras de engenharia.', 'Levantamento Planialtimétrico', '450m²', 'Rua Alameda das Rosas 145', 'Alphaville', 'Nova Lima', 'MG', '5 dias', 1, 4, '2025-11-11 11:25:56', 'Em Elaboração', 590.70, 60.00, 98.28, 163.33, 110.00, 30.00, 306.69, 1329.01, 29.01, 1300.00, NULL, 30.00, 390.00, 70.00, 910.00),
(7, 'ELM-2025-006', 12, 'Edivaldo Lins Macedo', 'edivaldo@elmtopografia.com.br', '3195480463', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 13, 'Edivaldo 3195480463', 'Topografia Mede e representa com precisão os limites, relevo e detalhes do terreno, fornecendo base completa para projetos e obras de engenharia.', 'Levantamento Planialtimétrico', '250ha', 'Portaria Condominio Retiro das Pedras', '', 'Nova Lima', 'MG', '5 dias', 1, 4, '2025-11-11 12:30:04', 'Em Elaboração', 590.70, 60.00, 90.72, 163.33, 110.00, 30.00, 304.43, 1319.18, 19.18, 1300.00, NULL, 30.00, 390.00, 70.00, 910.00),
(8, 'ELM-2025-007', 8, 'Lucio Braga Antunes Jr.', 'jr@gmail.com', '3188955965', NULL, NULL, 'ELM Serviços Topographical Ltda', '14.059.118/0001-08', 'Avenida Francisco Sá, 787 - Prado', 'Belo Horizonte', 'MG', 'Banco Inter', '0001', '12914298-0', '31 9 9922-2617', 17, 'Lucio 3188955965', 'Topografia Marca no terreno os eixos e limites definidos em projeto, assegurando a correta posição e alinhamento das construções', 'Levantamento Locação de Obra', '25ha', 'Rua Alameda das Rosas 145', 'Alphaville', 'Nova Lima', 'MG', '5 dias', 1, 4, '2025-11-11 12:41:14', 'Em Elaboração', 741.00, 60.00, 0.00, 163.33, 110.00, 30.00, 322.30, 1396.63, 96.63, 1300.00, NULL, 30.00, 390.00, 70.00, 910.00),
(9, 'ELM-2025-008', 9, 'Nelson Henrique Giovanni Galvão', 'nelson_henrique_galvao@mailnull.com', '(31) 2887-6558', NULL, NULL, 'ELM Serviços Topographical Ltda', '14.059.118/0001-08', 'Avenida Francisco Sá, 787 - Prado', 'Belo Horizonte', 'MG', 'Banco Inter', '0001', '12914298-0', '31 9 9922-2617', 20, 'Nelson (31) 2887-6558', 'Topografia Define e demarca novas divisas para a subdivisão de um imóvel, atendendo às exigências legais e cartoriais.', 'Levantamento Desdobramento', '360m²', 'Rua Nossa Senhora da Oenha, 103', 'Da Penha', 'Rio de Janeiro', 'RJ', '5 dias', 1, 4, '2025-11-11 23:14:09', 'Enviada', 841.20, 210.00, 1020.60, 726.67, 510.00, 30.00, 992.54, 4301.01, 1.01, 4300.00, NULL, 30.00, 1290.00, 70.00, 3010.00);

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

--
-- Extraindo dados da tabela `Proposta_Consumos`
--

INSERT INTO `Proposta_Consumos` (`id_item_consumo`, `id_proposta`, `id_consumo`, `tipo`, `quantidade`, `consumo_kml`, `valor_litro`, `km_total`) VALUES
(2, 1, 3, '', 2, 10.00, 5.30, 60.00),
(3, 2, 3, '', 2, 10.00, 6.30, 60.00),
(4, 3, 3, '', 2, 10.00, 6.30, 60.00),
(5, 4, 3, '', 2, 10.00, 6.30, 69.00);

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

--
-- Extraindo dados da tabela `Proposta_Custos_Administrativos`
--

INSERT INTO `Proposta_Custos_Administrativos` (`id_item_custo_admin`, `id_proposta`, `id_custo_admin`, `tipo`, `quantidade`, `valor`) VALUES
(2, 1, 3, '', 1, 110.00),
(3, 2, 3, '', 1, 110.00),
(4, 3, 3, '', 1, 110.00),
(5, 4, 3, '', 1, 110.00);

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

--
-- Extraindo dados da tabela `Proposta_Estadia`
--

INSERT INTO `Proposta_Estadia` (`id_item_estadia`, `id_proposta`, `id_estadia`, `tipo`, `quantidade`, `valor_unitario`, `dias`) VALUES
(2, 1, 2, '', 3, 30.00, 1),
(3, 2, 2, '', 4, 30.00, 1),
(4, 3, 2, '', 4, 30.00, 1),
(5, 4, 2, '', 6, 30.00, 1),
(6, 5, 2, '', 1, 30.00, 1);

-- --------------------------------------------------------

--
-- Estrutura da tabela `Proposta_Locacao`
--

CREATE TABLE `Proposta_Locacao` (
  `id_item_locacao` int(11) NOT NULL,
  `id_proposta` int(11) NOT NULL,
  `id_locacao` int(11) DEFAULT NULL COMMENT 'Link para a tabela Tipo_Locacao',
  `tipo` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Ex: Veículo, Estação Total, GPS RTK',
  `quantidade` int(11) DEFAULT '1',
  `valor_mensal` decimal(10,2) DEFAULT '0.00' COMMENT 'Custo de locação por 30 dias (Ex: 3000.00)',
  `dias` int(11) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `Proposta_Locacao`
--

INSERT INTO `Proposta_Locacao` (`id_item_locacao`, `id_proposta`, `id_locacao`, `tipo`, `quantidade`, `valor_mensal`, `dias`) VALUES
(4, 1, 4, '', 1, 3000.00, 1),
(5, 1, 3, '', 1, 1000.00, 1),
(6, 1, 2, '', 1, 900.00, 1),
(7, 2, 4, '', 1, 3000.00, 1),
(8, 2, 3, '', 1, 1000.00, 1),
(9, 2, 2, '', 1, 900.00, 1),
(10, 3, 4, '', 1, 3000.00, 1),
(11, 3, 3, '', 1, 1000.00, 1),
(12, 3, 2, '', 1, 900.00, 1),
(13, 4, 4, '', 1, 3000.00, 1),
(14, 4, 3, '', 1, 1000.00, 1),
(15, 4, 2, '', 1, 900.00, 1),
(16, 4, 1, '', 1, 1500.00, 1);

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

--
-- Extraindo dados da tabela `Proposta_Salarios`
--

INSERT INTO `Proposta_Salarios` (`id_salario`, `id_proposta`, `id_funcao`, `funcao`, `quantidade`, `salario_base`, `fator_encargos`, `dias`) VALUES
(7, 1, 10, '', 1, 4500.00, 67.00, 1.0),
(8, 1, 9, '', 1, 4311.38, 67.00, 1.0),
(9, 1, 8, '', 1, 1800.00, 67.00, 1.0),
(10, 2, 10, '', 2, 4500.00, 67.00, 1.0),
(11, 2, 9, '', 1, 4311.38, 67.00, 1.0),
(12, 2, 7, '', 1, 0.00, 67.00, 1.0),
(13, 3, 10, '', 2, 4500.00, 67.00, 1.0),
(14, 3, 9, '', 1, 4311.38, 67.00, 1.0),
(15, 3, 7, '', 1, 0.00, 67.00, 1.0),
(16, 4, 10, '', 1, 4500.00, 67.00, 3.0),
(17, 4, 9, '', 1, 4311.38, 67.00, 2.0),
(18, 4, 8, '', 1, 1800.00, 67.00, 2.0),
(19, 5, 10, '', 1, 4500.00, 67.00, 1.0),
(20, 5, 9, '', 1, 4311.38, 67.00, 1.0);

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
(7, 'Avulso', NULL),
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
(15, 'Obra Industrial', 'Fornece base topográfica precisa para implantação, nivelamento e controle geométrico de estruturas em áreas industriais.'),
(16, 'Obra Civil', 'Garante a precisão na locação e controle das construções, servindo de base para fundações, alinhamentos e nivelamentos da obra.'),
(17, 'Locação de Obra', 'Marca no terreno os eixos e limites definidos em projeto, assegurando a correta posição e alinhamento das construções'),
(18, 'Locação Terraplenagem', 'Demarca cotas e referências no terreno para orientar cortes, aterros e nivelamento conforme o projeto de terraplenagem.'),
(19, 'Drone', 'Realiza mapeamento aéreo de alta precisão com drone, gerando ortofotos e modelos 3D para análise e planejamento topográfico.'),
(20, 'Desdobramento', 'Define e demarca novas divisas para a subdivisão de um imóvel, atendendo às exigências legais e cartoriais.'),
(21, 'Conferência', 'Verifica medidas, limites e referências do terreno para confirmar a exatidão de levantamentos ou implantações anteriores.');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `Clientes`
--
ALTER TABLE `Clientes`
  ADD PRIMARY KEY (`id_cliente`),
  ADD UNIQUE KEY `cnpj_cpf_unico` (`cnpj_cpf`);

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
  ADD KEY `fk_proposta_servico` (`id_servico`);

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
  ADD KEY `fk_locacao_tipo` (`id_locacao`);

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
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `Clientes`
--
ALTER TABLE `Clientes`
  MODIFY `id_cliente` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

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
-- AUTO_INCREMENT for table `modelos_proposta`
--
ALTER TABLE `modelos_proposta`
  MODIFY `id_modelo` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `Propostas`
--
ALTER TABLE `Propostas`
  MODIFY `id_proposta` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `Proposta_Consumos`
--
ALTER TABLE `Proposta_Consumos`
  MODIFY `id_item_consumo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `Proposta_Custos_Administrativos`
--
ALTER TABLE `Proposta_Custos_Administrativos`
  MODIFY `id_item_custo_admin` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `Proposta_Estadia`
--
ALTER TABLE `Proposta_Estadia`
  MODIFY `id_item_estadia` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `Proposta_Locacao`
--
ALTER TABLE `Proposta_Locacao`
  MODIFY `id_item_locacao` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `Proposta_Salarios`
--
ALTER TABLE `Proposta_Salarios`
  MODIFY `id_salario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

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
  MODIFY `id_estadia` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `Tipo_Funcoes`
--
ALTER TABLE `Tipo_Funcoes`
  MODIFY `id_funcao` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `Tipo_Locacao`
--
ALTER TABLE `Tipo_Locacao`
  MODIFY `id_locacao` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `Tipo_Servicos`
--
ALTER TABLE `Tipo_Servicos`
  MODIFY `id_servico` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- Constraints for dumped tables
--

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
