-- phpMyAdmin SQL Dump
-- version 4.7.7
-- https://www.phpmyadmin.net/
--
-- Host: 186.202.152.243
-- Generation Time: 25-Nov-2025 às 13:07
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
(15, 'Flavia Dantas do Amaral', 'Dantas Telefonia Lyda', '24.715.712/0001-55', 'dantas@da.com.br', '61 9 9988-5547', NULL, NULL),
(16, 'Miguel Ryan Augusto Castro', 'Castro Empreendemos Imobiliarios Lyda. ', '971.070.666-76', 'miguel_ryan_castro@fertau.com.br', '(31) 2761-0109', NULL, NULL),
(17, 'Alberto Barbosa Cruello Draz', 'Grullez Paraguai So', '470.810.985-48', 'abc@grullez.com', '(35) 9 9947-5527', NULL, NULL),
(18, 'Tarciso Gomes Trigueiro', 'TGT - Bras Hotel', '985.965.874-23', 'tgt@gmail.com', '31 3254-1456', '31 9 9952-5478', '31 9 9952-5478'),
(19, 'Davi Mendes Santos', '', '370.444.567-33', 'davi@deuslhepague.com', '38 2222-5465', '38  98866-3312', '36 98877-4532');

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
(8, 'ELM-2025-007', 8, 'Lucio Braga Antunes Jr.', 'jr@gmail.com', '3188955965', NULL, NULL, 'ELM Serviços Topographical Ltda', '14.059.118/0001-08', 'Avenida Francisco Sá, 787 - Prado', 'Belo Horizonte', 'MG', 'Banco Inter', '0001', '12914298-0', '31 9 9922-2617', 17, 'Lucio 3188955965', 'Topografia Marca no terreno os eixos e limites definidos em projeto, assegurando a correta posição e alinhamento das construções', 'Levantamento Locação de Obra', '25ha', 'Rua Alameda das Rosas 145', 'Alphaville', 'Nova Lima', 'MG', '5 dias', 1, 4, '2025-11-11 12:41:14', 'Recusada', 741.00, 60.00, 0.00, 163.33, 110.00, 30.00, 322.30, 1396.63, 96.63, 1300.00, NULL, 30.00, 390.00, 70.00, 910.00),
(9, 'ELM-2025-008', 9, 'Nelson Henrique Giovanni Galvão', 'nelson_henrique_galvao@mailnull.com', '(31) 2887-6558', NULL, NULL, 'ELM Serviços Topographical Ltda', '14.059.118/0001-08', 'Avenida Francisco Sá, 787 - Prado', 'Belo Horizonte', 'MG', 'Banco Inter', '0001', '12914298-0', '31 9 9922-2617', 20, 'Nelson (31) 2887-6558', 'Topografia Define e demarca novas divisas para a subdivisão de um imóvel, atendendo às exigências legais e cartoriais.', 'Levantamento Desdobramento', '360m²', 'Rua Nossa Senhora da Oenha, 103', 'Da Penha', 'Rio de Janeiro', 'RJ', '5 dias', 1, 4, '2025-11-11 23:14:09', 'Editada', 841.20, 210.00, 1020.60, 726.67, 510.00, 30.00, 992.54, 4301.01, 1.01, 4300.00, NULL, 30.00, 1290.00, 70.00, 3010.00),
(10, 'ELM-2025-009', 7, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 21, '', '', '', '', '', '', '', 'MG', '5', 1, 4, '2025-11-13 15:06:19', 'Recusada', 5010.00, 0.00, 0.00, 0.00, 0.00, 30.00, 1503.00, 6513.00, 0.00, 6513.00, NULL, 30.00, 1953.90, 70.00, 4559.10),
(11, 'ELM-2025-010', 8, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 21, '', '', '', '', '', '', '', 'MG', '5', 1, 4, '2025-11-13 15:15:39', 'Aceita', 1732.50, 0.00, 0.00, 0.00, 0.00, 30.00, 519.75, 2252.25, 0.00, 2252.25, NULL, 30.00, 675.68, 70.00, 1576.58),
(12, 'ELM-2025-011', 9, 'Nelson Henrique Giovanni Galvão', 'nelson_henrique_galvao@mailnull.com', '(31) 2887-6558', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 21, 'Nelson (31) 2887-6558', 'Topografia Verifica medidas, limites e referências do terreno para confirmar a exatidão de levantamentos ou implantações anteriores.', 'Levantamento Conferência', '360m²', 'Rodia 262 Km34', 'Penha', 'Betim', 'MG', '5', 1, 4, '2025-11-13 21:42:44', 'Enviada', 1772.10, 890.00, 185.22, 490.00, 110.00, 30.00, 1034.20, 4481.52, 1.52, 4480.00, NULL, 30.00, 1344.00, 70.00, 3136.00),
(13, 'ELM-2025-012', 7, 'Haroldo Barbosa Mello', 'edivaldo@elmtopografia.com.br', '3195480463', '3195480463', '3195480463', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 21, 'Haroldo 3195480463', 'Topografia Verifica medidas, limites e referências do terreno para confirmar a exatidão de levantamentos ou implantações anteriores.', 'Levantamento Conferência', '500m²', 'Portaria Condominio Retiro das Pedras', 'Alphaville', 'Nova Lima', 'MG', '5', 1, 4, '2025-11-14 09:27:13', 'Em elaboração', 590.70, 30.00, 63.00, 163.33, 110.00, 30.00, 287.11, 1244.14, 44.14, 1200.00, NULL, 30.00, 360.00, 70.00, 840.00),
(14, 'ELM-2025-013', 7, 'Haroldo Barbosa Mello', 'edivaldo@elmtopografia.com.br', '3195480463', '3195480463', '3195480463', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 13, 'Haroldo 3195480463', 'Topografia Mede e representa com precisão os limites, relevo e detalhes do terreno, fornecendo base completa para projetos e obras de engenharia.', 'Levantamento Planialtimétrico', '360m²', 'Portaria Condominio Retiro das Pedras', 'Alphaville', 'Nova Lima', 'MG', '5', 1, 4, '2025-11-14 09:36:59', 'Em elaboração', 590.70, 90.00, 54.18, 163.33, 110.00, 30.00, 302.46, 1310.68, 10.68, 1300.00, NULL, 30.00, 390.00, 70.00, 910.00),
(15, 'ELM-2025-014', 7, 'Haroldo Barbosa Mello', 'edivaldo@elmtopografia.com.br', '3195480463', '3195480463', '3195480463', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 21, 'Haroldo 3195480463', 'Topografia Verifica medidas, limites e referências do terreno para confirmar a exatidão de levantamentos ou implantações anteriores.', 'Levantamento Conferência', '360m²', 'Portaria Condominio Retiro das Pedras', 'Alphaville', 'Nova Lima', 'MG', '5', 1, 4, '2025-11-14 09:37:19', 'Em elaboração', 590.70, 90.00, 54.18, 163.33, 110.00, 30.00, 302.46, 1310.68, 10.68, 1300.00, NULL, 30.00, 390.00, 70.00, 910.00),
(16, 'ELM-2025-015', 9, 'Nelson Henrique Giovanni Galvão', 'nelson_henrique_galvao@mailnull.com', '(31) 2887-6558', '31 9 9999-1478', '31 9 9999-1478', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 21, 'Nelson 31 9 9999-1478', 'Topografia Verifica medidas, limites e referências do terreno para confirmar a exatidão de levantamentos ou implantações anteriores.', 'Levantamento Conferência', '360m²', 'Portaria Condominio Retiro das Pedras', 'Alphaville', 'Nova Lima', 'MG', '5', 1, 4, '2025-11-14 10:26:50', 'Em elaboração', 590.70, 90.00, 56.70, 163.33, 110.00, 30.00, 303.22, 1313.95, 13.95, 1300.00, NULL, 30.00, 390.00, 70.00, 910.00),
(17, 'ELM-2025-016', 15, 'Flavia Dantas do Amaral', 'dantas@da.com.br', '61 9 9988-5547', '61 9 9988-5547', '61 9 9988-5547', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 20, 'Flavia 61 9 9988-5547', 'Topografia Define e demarca novas divisas para a subdivisão de um imóvel, atendendo às exigências legais e cartoriais.', 'Levantamento Desdobramento', '360m²', 'GOVOVERNADOR BENEDITO VALADARES, 701', 'VILA OESTE', 'BELO HORIZONTE', 'MG', '5', 1, 4, '2025-11-15 15:47:55', 'Em elaboração', 590.70, 90.00, 37.80, 163.33, 110.00, 30.00, 297.55, 1289.38, 39.38, 1250.00, NULL, 30.00, 375.00, 70.00, 875.00),
(18, 'ELM-2025-017', 7, 'Haroldo Barbosa Mello', 'edivaldo@elmtopografia.com.br', '3195480463', '3195480463', '3195480463', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 21, 'Haroldo 3195480463', 'Topografia Verifica medidas, limites e referências do terreno para confirmar a exatidão de levantamentos ou implantações anteriores.', 'Levantamento Conferência', '360m²', 'Treze de Setembro, 345', 'Alpes', 'BELO HORIZONTE', 'MG', '5', 1, 4, '2025-11-16 20:33:36', 'Em elaboração', 590.70, 90.00, 78.00, 163.33, 110.00, 30.00, 309.61, 1341.64, 41.64, 1300.00, NULL, 30.00, 390.00, 70.00, 910.00),
(19, 'ELM-2025-018', 9, 'Nelson Henrique Giovanni Galvão', 'nelson_henrique_galvao@mailnull.com', '(31) 2887-6558', '31 9 9999-1478', '31 9 9999-1478', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 21, 'Nelson 31 9 9999-1478', 'Topografia Verifica medidas, limites e referências do terreno para confirmar a exatidão de levantamentos ou implantações anteriores.', 'Levantamento Conferência', '360m²', 'Treze de Setembro, 345', 'Alpes', 'BELO HORIZONTE', 'MG', '5', 1, 4, '2025-11-16 20:57:54', 'Em elaboração', 590.70, 100.00, 42.60, 163.33, 110.00, 30.00, 301.99, 1308.62, 8.62, 1300.00, NULL, 30.00, 390.00, 70.00, 910.00),
(20, 'ELM-2025-019', 17, 'Alberto Barbosa Cruello Draz', 'abc@grullez.com', '(35) 9 9947-5527', '(35) 9 9947-5520', '(35) 9 9947-5540', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 21, 'Alberto (35) 9 9947-5520', 'Topografia Verifica medidas, limites e referências do terreno para confirmar a exatidão de levantamentos ou implantações anteriores.', 'Levantamento Conferência', '450m²', 'Ruta Leste del Vale', 'Berroz', 'Cristalina', 'AC', '5', 1, 4, '2025-11-17 06:53:59', 'Em elaboração', 590.70, 180.00, 93.60, 163.33, 110.00, 30.00, 341.29, 1478.92, 78.92, 1400.00, NULL, 30.00, 420.00, 70.00, 980.00),
(21, 'ELM-2025-020', 17, 'Alberto Barbosa Cruello Draz', 'abc@grullez.com', '(35) 9 9947-5527', '(35) 9 9947-5520', '(35) 9 9947-5540', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 21, 'Alberto (35) 9 9947-5520', 'Topografia Verifica medidas, limites e referências do terreno para confirmar a exatidão de levantamentos ou implantações anteriores.', 'Levantamento Conferência', '360m²', 'GOVOVERNADOR BENEDITO VALADARES, 701', 'VILA OESTE', 'BELO HORIZONTE', 'MG', '5', 1, 4, '2025-11-17 14:26:06', 'Em elaboração', 590.70, 1410.00, 936.00, 640.00, 110.00, 30.00, 1106.01, 4792.71, 92.71, 4700.00, NULL, 30.00, 1410.00, 70.00, 3290.00),
(22, 'ELM-2025-021', 12, 'Edivaldo Lins Macedo', 'edivaldo@elmtopografia.com.br', '3195480463', '3195480463', '3195480463', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 21, 'Edivaldo 3195480463', 'Auditoria e verificação de dados topográficos para garantia de qualidade e fidelidade às referências originais do projeto.', 'Levantamento Conferência', '360m²', 'Avenida Francisco Sá, 787', 'Prado', 'Minas Gerais - Belo Horizonte', 'MG', '5', 1, 4, '2025-11-20 08:17:40', 'Em elaboração', 590.70, 90.00, 60.00, 163.33, 110.00, 30.00, 304.21, 1318.24, 18.24, 1300.00, NULL, 30.00, 390.00, 70.00, 910.00),
(23, 'ELM-2025-022', 15, 'Flavia Dantas do Amaral', 'dantas@da.com.br', '61 9 9988-5547', '61 9 9988-5547', '61 9 9988-5547', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 13, 'Flavia 61 9 9988-5547', 'Mede e representa com precisão os limites, relevo e detalhes do terreno, fornecendo base completa para projetos e obras de engenharia.', 'Levantamento Planialtimétrico', '360m²', 'Avenida Francisco Sá, 787', 'Prado', 'Minas Gerais - Belo Horizonte', 'MG', '5', 1, 4, '2025-11-20 08:27:57', 'Em elaboração', 590.70, 90.00, 86.40, 163.33, 110.00, 30.00, 312.13, 1352.56, 52.56, 1300.00, NULL, 30.00, 390.00, 70.00, 910.00),
(24, 'ELM-2025-023', 12, 'Edivaldo Lins Macedo', 'edivaldo@elmtopografia.com.br', '3195480463', '3195480463', '3195480463', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 20, 'Edivaldo 3195480463', 'Determinação analítica dos novos limites e áreas para o fracionamento do terreno, em estrito cumprimento às normas de registro.', 'Levantamento Desdobramento', '1000m²', 'Rua Daniel Dantas, 45', 'Gloria', 'Atalaia', 'MG', '5', 1, 4, '2025-11-20 09:55:24', 'Em elaboração', 590.70, 60.00, 34.20, 163.33, 110.00, 30.00, 287.47, 1245.70, 0.00, 1245.70, NULL, 30.00, 373.71, 70.00, 871.99),
(25, 'ELM-2025-024', 12, 'Edivaldo Lins Macedo', 'edivaldo@elmtopografia.com.br', '3195480463', '3195480463', '3195480463', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 19, 'Edivaldo 3195480463', 'Execução de mapeamento aéreo de alta precisão para geração de ortofotos e modelos tridimensionais (3D), subsidiando análises e planejamentos topográficos.', 'Levantamento Drone', '32ha', 'Estrada Municipal Pedro Leopoldo, km 47', 'Ourinho', 'Ouro Preto', 'MG', '5', 1, 4, '2025-11-20 10:01:16', 'Em elaboração', 991.50, 270.00, 97.20, 150.00, 110.00, 30.00, 485.61, 2104.31, 4.31, 2100.00, NULL, 30.00, 630.00, 70.00, 1470.00),
(26, 'ELM-2025-025', 12, 'Edivaldo Lins Macedo', 'edivaldo@elmtopografia.com.br', '3195480463', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 21, 'Edivaldo Lins Macedo 3195480463', 'Auditoria e verificação de dados topográficos para garantia de qualidade e fidelidade às referências originais do projeto.', 'Levantamento Conferência', '450m²', 'Avenida Francisco Sá, 787', 'Prado', 'Rusalina da Mata - RD', 'MG', '5 dias', 1, 4, '2025-11-22 14:58:37', 'Em elaboração', 590.70, 90.00, 151.20, 213.33, 110.00, 30.00, 346.57, 1501.80, 3.00, 1498.80, NULL, 30.00, 449.64, 70.00, 1049.16),
(27, 'ELM-2025-026', 15, 'Flavia Dantas do Amaral', 'dantas@da.com.br', '61 9 9988-5547', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 21, 'Flavia Dantas do Amaral 61 9 9988-5547', 'Auditoria e verificação de dados topográficos para garantia de qualidade e fidelidade às referências originais do projeto.', 'Levantamento Conferência', '360m²', 'Treze de Setembro, 345', 'Alpes', 'BELO HORIZONTE', 'MG', '5 dias', 1, 4, '2025-11-22 15:13:57', 'Em elaboração', 590.70, 90.00, 75.60, 213.33, 110.00, 30.00, 323.89, 1403.52, 3.52, 1400.00, NULL, 30.00, 420.00, 70.00, 980.00),
(28, 'ELM-2025-027', 12, 'Edivaldo Lins Macedo', 'edivaldo@elmtopografia.com.br', '3195480463', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 20, 'Edivaldo Lins Macedo 3195480463', 'Determinação analítica dos novos limites e áreas para o fracionamento do terreno, em estrito cumprimento às normas de registro.', 'Levantamento Desdobramento', '360m²', 'Treze de Setembro, 345', 'Alpes', 'BELO HORIZONTE', 'MG', '5 dias', 1, 4, '2025-11-22 15:34:01', 'Em elaboração', 590.70, 120.00, 151.20, 213.33, 210.00, 30.00, 385.57, 1670.80, 20.80, 1650.00, NULL, 30.00, 495.00, 70.00, 1155.00),
(29, 'ELM-2025-028', 12, 'Edivaldo Lins Macedo', 'edivaldo@elmtopografia.com.br', '3195480463', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 20, 'Edivaldo Lins Macedo 3195480463', 'Determinação analítica dos novos limites e áreas para o fracionamento do terreno, em estrito cumprimento às normas de registro.', 'Levantamento Desdobramento', '360m²', 'Treze de Setembro, 345', 'Alpes', 'BELO HORIZONTE', 'MG', '5 dias', 1, 4, '2025-11-22 15:36:20', 'Em elaboração', 590.70, 120.00, 151.20, 213.33, 711.00, 30.00, 535.87, 2322.10, 22.10, 2300.00, NULL, 30.00, 690.00, 70.00, 1610.00),
(30, 'ELM-2025-029', 7, 'Haroldo Barbosa Mello', 'edivaldo@elmtopografia.com.br', '3195480463', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 17, 'Haroldo Barbosa Mello 3195480463', 'Materialização dos elementos de projeto em campo (eixos e gabaritos) para assegurar a fidelidade geométrica e o alinhamento estrutural da obra.', 'Levantamento Locação de Obra', '450m²', 'Avenida Francisco Sá, 787', 'Prado', 'Minas Gerais - Belo Horizonte', 'MG', '5 dias', 1, 4, '2025-11-22 15:44:57', 'Em elaboração', 590.70, 90.00, 100.80, 80.00, 110.00, 30.00, 291.45, 1262.95, 12.95, 1250.00, NULL, 30.00, 375.00, 70.00, 875.00),
(31, 'ELM-2025-030', 12, 'Edivaldo Lins Macedo', 'edivaldo@elmtopografia.com.br', '3195480463', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 17, 'Edivaldo Lins Macedo 3195480463', 'Materialização dos elementos de projeto em campo (eixos e gabaritos) para assegurar a fidelidade geométrica e o alinhamento estrutural da obra.', 'Levantamento Locação de Obra', '450m²', 'Treze de Setembro, 345', 'Alpes', 'BELO HORIZONTE', 'MG', '5 dias', 1, 4, '2025-11-22 20:38:20', 'Em elaboração', 590.70, 90.00, 59.22, 163.33, 110.00, 30.00, 303.98, 1317.23, 17.23, 1300.00, NULL, 30.00, 390.00, 70.00, 910.00),
(32, 'ELM-2025-031', 15, 'Flavia Dantas do Amaral', 'dantas@da.com.br', '61 9 9988-5547', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 20, 'Flavia 61 9 9988-5547', 'Determinação analítica dos novos limites e áreas para o fracionamento do terreno, em estrito cumprimento às normas de registro.', 'Levantamento Desdobramento', '450m²', 'Treze de Setembro, 345', 'Alpes', 'BELO HORIZONTE', 'MG', '5 dias', 1, 4, '2025-11-22 22:03:45', 'Em elaboração', 590.70, 135.00, 98.28, 163.33, 210.00, 30.00, 359.19, 1556.51, 56.51, 1500.00, NULL, 30.00, 450.00, 70.00, 1050.00),
(34, 'ELM-2025-032', 15, 'Flavia Dantas do Amaral', 'dantas@da.com.br', '61 9 9988-5547', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 21, 'Flavia 61 9 9988-5547', 'Auditoria e verificação de dados topográficos para garantia de qualidade e fidelidade às referências originais do projeto.', 'Levantamento Conferência', '1000m²', 'Avenida Francisco Sá, 787', 'VILA OESTE', 'Rusalina da Mata - RD', 'MG', '5 dias', 1, 4, '2025-11-23 10:54:29', 'Em elaboração', 590.70, 90.00, 93.24, 163.33, 110.00, 30.00, 314.18, 1361.46, 61.46, 1300.00, NULL, 30.00, 390.00, 70.00, 910.00),
(35, 'ELM-2025-033', 15, 'Flavia Dantas do Amaral', 'dantas@da.com.br', '61 9 9988-5547', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 19, 'Flavia 61 9 9988-5547', 'Auditoria e verificação de dados topográficos para garantia de qualidade e fidelidade às referências originais do projeto.', 'Levantamento Drone', '1000m²', 'Avenida Francisco Sá, 787', 'VILA OESTE', 'Rusalina da Mata - RD', 'MG', '5 dias', 1, 4, '2025-11-23 11:12:08', 'Em elaboração', 590.70, 90.00, 93.24, 163.33, 110.00, 30.00, 314.18, 1361.46, 61.46, 1300.00, NULL, 30.00, 390.00, 70.00, 910.00),
(36, 'ELM-2025-034', 15, 'Flavia Dantas do Amaral', 'dantas@da.com.br', '61 9 9988-5547', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 19, 'Flavia 61 9 9988-5547', 'Auditoria e verificação de dados topográficos para garantia de qualidade e fidelidade às referências originais do projeto.', 'Levantamento Drone', '1000m²', 'Avenida Francisco Sá, 787', 'VILA OESTE', 'Rusalina da Mata - RD', 'MG', '5 dias', 1, 4, '2025-11-23 11:13:55', 'Em elaboração', 590.70, 90.00, 93.24, 180.00, 110.00, 30.00, 319.18, 1383.12, 61.46, 1321.66, NULL, 30.00, 396.50, 70.00, 925.16),
(37, 'ELM-2025-035', 7, 'Haroldo Barbosa Mello', 'edivaldo@elmtopografia.com.br', '3195480463', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 19, 'Haroldo 3195480463', 'Execução de mapeamento aéreo de alta precisão para geração de ortofotos e modelos tridimensionais (3D), subsidiando análises e planejamentos topográficos.', 'Levantamento Drone', '450m²', 'Avenida Francisco Sá, 787', 'Prado', 'Minas Gerais - Belo Horizonte', 'MG', '5 dias', 1, 4, '2025-11-23 11:20:02', 'Em elaboração', 590.70, 60.00, 0.00, 163.33, 110.00, 30.00, 277.21, 1201.24, 1.24, 1200.00, NULL, 30.00, 360.00, 70.00, 840.00),
(38, 'ELM-2025-036', 8, 'Lucio Braga Antunes Jr.', 'jr@gmail.com', '3188955965', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 17, 'Lucio 3188955965', 'Materialização dos elementos de projeto em campo (eixos e gabaritos) para assegurar a fidelidade geométrica e o alinhamento estrutural da obra.', 'Levantamento Locação de Obra', '450m²', 'Avenida Francisco Sá, 787', 'Prado', 'Rusalina da Mata - RD', 'MG', '5 dias', 1, 4, '2025-11-23 11:44:08', 'Em elaboração', 590.70, 0.00, 71.82, 163.33, 110.00, 30.00, 280.76, 1216.61, 16.61, 1200.00, NULL, 30.00, 360.00, 70.00, 840.00),
(39, 'ELM-2025-037', 15, 'Flavia Dantas do Amaral', 'dantas@da.com.br', '61 9 9988-5547', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 13, 'Flavia 61 9 9988-5547', 'Mede e representa com precisão os limites, relevo e detalhes do terreno, fornecendo base completa para projetos e obras de engenharia.', 'Levantamento Planialtimétrico', '1000m²', 'Avenida Francisco Sá, 787', 'Prado', 'Minas Gerais - Belo Horizonte', 'MG', '5 dias', 1, 4, '2025-11-23 12:43:13', 'Em elaboração', 590.70, 135.00, 69.30, 163.33, 110.00, 30.00, 320.50, 1388.83, 38.83, 1350.00, NULL, 30.00, 405.00, 70.00, 945.00),
(40, 'ELM-2025-038', 15, 'Flavia Dantas do Amaral', 'dantas@da.com.br', '61 9 9988-5547', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 13, 'Flavia 61 9 9988-5547', 'Mede e representa com precisão os limites, relevo e detalhes do terreno, fornecendo base completa para projetos e obras de engenharia.', 'Levantamento Planialtimétrico', '1000m²', 'Avenida Francisco Sá, 787', 'Prado', 'Minas Gerais - Belo Horizonte', 'MG', '5 dias', 1, 4, '2025-11-23 12:45:01', 'Em elaboração', 590.70, 135.00, 69.30, 163.33, 110.00, 30.00, 320.50, 1388.83, 38.83, 1350.00, NULL, 30.00, 405.00, 70.00, 945.00),
(41, 'ELM-2025-039', 7, 'Haroldo Barbosa Mello', 'edivaldo@elmtopografia.com.br', '3195480463', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 20, 'Haroldo 3195480463', 'Determinação analítica dos novos limites e áreas para o fracionamento do terreno, em estrito cumprimento às normas de registro.', 'Levantamento Desdobramento', '360m²', 'Avenida Francisco Sá, 787', 'Alpes', 'Rusalina da Mata - RD', 'MG', '5 dias', 1, 4, '2025-11-23 12:50:00', 'Em elaboração', 590.70, 120.00, 75.60, 163.33, 110.00, 30.00, 317.89, 1377.52, 77.52, 1300.00, NULL, 30.00, 390.00, 70.00, 910.00),
(42, 'ELM-2025-040', 7, 'Haroldo Barbosa Mello', 'edivaldo@elmtopografia.com.br', '3195480463', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 13, 'Haroldo 3195480463', 'Determinação analítica dos novos limites e áreas para o fracionamento do terreno, em estrito cumprimento às normas de registro.', 'Levantamento Planialtimétrico', '360m²', 'Avenida Francisco Sá, 787', 'Alpes', 'Rusalina da Mata - RD', 'MG', '5 dias', 1, 4, '2025-11-23 12:51:03', 'Em elaboração', 590.70, 120.00, 75.60, 163.33, 110.00, 30.00, 317.89, 1377.52, 77.52, 1300.00, NULL, 30.00, 390.00, 70.00, 910.00),
(43, 'ELM-2025-041', 12, 'Edivaldo Lins Macedo', 'edivaldo@elmtopografia.com.br', '3195480463', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 13, 'Edivaldo 3195480463', 'Mede e representa com precisão os limites, relevo e detalhes do terreno, fornecendo base completa para projetos e obras de engenharia.', 'Levantamento Planialtimétrico', '32ha', 'GOVOVERNADOR BENEDITO VALADARES, 701', 'VILA OESTE', 'BELO HORIZONTE', 'MG', '5 dias', 1, 4, '2025-11-23 13:12:20', 'Em elaboração', 590.70, 60.00, 83.16, 163.33, 110.00, 30.00, 302.16, 1309.35, 9.35, 1300.00, NULL, 30.00, 390.00, 70.00, 910.00),
(44, 'ELM-2025-042', 18, 'Tarciso Gomes Trigueiro', 'tgt@gmail.com', '31 3254-1456', '31 9 9952-5478', '31 9 9952-5478', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 13, 'Tarciso 31 3254-1456 31 9 9952-5478', 'Mede e representa com precisão os limites, relevo e detalhes do terreno, fornecendo base completa para projetos e obras de engenharia.', 'Levantamento Planialtimétrico', '457,36m²', 'GOVOVERNADOR BENEDITO VALADARES, 701', 'VILA OESTE', 'BELO HORIZONTE', 'MG', '5 dias', 1, 4, '2025-11-23 13:51:52', 'Em elaboração', 450.90, 180.00, 65.52, 163.33, 110.00, 30.00, 290.93, 1260.68, 60.68, 1200.00, NULL, 30.00, 360.00, 70.00, 840.00),
(45, 'ELM-2025-043', 18, 'Tarciso Gomes Trigueiro', 'tgt@gmail.com', '31 3254-1456', '31 9 9952-5478', '31 9 9952-5478', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 13, 'Tarciso 31 3254-1456 31 9 9952-5478', 'Mede e representa com precisão os limites, relevo e detalhes do terreno, fornecendo base completa para projetos e obras de engenharia.', 'Levantamento Planialtimétrico', '457,36m²', 'GOVOVERNADOR BENEDITO VALADARES, 701', 'VILA OESTE', 'BELO HORIZONTE', 'MG', '5 dias', 1, 4, '2025-11-23 14:05:28', 'Em elaboração', 0.00, 0.00, 0.00, 0.00, 0.00, 30.00, 0.00, 0.00, 60.68, 0.00, NULL, 30.00, 0.00, 70.00, 0.00),
(46, 'ELM-2025-044', 16, 'Miguel Ryan Augusto Castro', 'miguel_ryan_castro@fertau.com.br', '(31) 2761-0109', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 13, 'Miguel (31) 2761-0109', 'Mede e representa com precisão os limites, relevo e detalhes do terreno, fornecendo base completa para projetos e obras de engenharia.', 'Levantamento Planialtimétrico', '', 'Rua 3', 'Nação ', 'Betim', 'MG', '5 dias', 1, 4, '2025-11-23 14:16:59', 'Em elaboração', 590.70, 90.00, 46.62, 163.33, 110.00, 30.00, 300.20, 1300.85, 0.85, 1300.00, NULL, 30.00, 390.00, 70.00, 910.00),
(47, 'ELM-2025-045', 8, 'Lucio Braga Antunes Jr.', 'jr@gmail.com', '3188955965', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 11, 'Lucio 3188955965', 'Medição e demarcação precisa do imóvel, definindo limites, confrontantes e área, para regularização da posse e registro no processo de usucapião.', 'Levantamento Usucapião', '2987.34m2', 'Estrada da Boa Viagem km 37', 'Cruzes', 'Barra', 'PA', '5 dias', 1, 4, '2025-11-23 14:42:51', 'Em elaboração', 590.70, 90.00, 54.18, 163.33, 110.00, 30.00, 302.46, 1310.68, 10.68, 1300.00, NULL, 30.00, 390.00, 70.00, 910.00),
(52, 'ELM-2025-046', 12, 'Edivaldo Lins Macedo', 'edivaldo@elmtopografia.com.br', '3195480463', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 19, 'Edivaldo 3195480463', 'Execução de mapeamento aéreo de alta precisão para geração de ortofotos e modelos tridimensionais (3D), subsidiando análises e planejamentos topográficos.', 'Levantamento Drone', '2ha', 'Avenida Francisco Sá, 787', 'Prado', 'Minas Gerais - Belo Horizonte', 'MG', '5 dias', 1, 4, '2025-11-23 14:56:53', 'Em elaboração', 590.70, 30.00, 81.27, 163.33, 110.00, 30.00, 292.59, 1267.89, 17.89, 1250.00, NULL, 30.00, 375.00, 70.00, 875.00),
(53, 'ELM-2025-047', 16, 'Miguel Ryan Augusto Castro', 'miguel_ryan_castro@fertau.com.br', '(31) 2761-0109', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 21, 'Miguel (31) 2761-0109', 'Auditoria e verificação de dados topográficos para garantia de qualidade e fidelidade às referências originais do projeto.', 'Levantamento Conferência', '2ha', 'Avenida Francisco Sá, 787', 'Prado', 'Minas Gerais - Belo Horizonte', 'MG', '5 dias', 1, 4, '2025-11-24 10:35:20', 'Em elaboração', 590.70, 30.00, 93.24, 163.33, 110.00, 30.00, 296.18, 1283.46, 33.45, 1250.01, NULL, 30.00, 375.00, 70.00, 875.00),
(54, 'ELM-2025-048', 12, 'Edivaldo Lins Macedo', 'edivaldo@elmtopografia.com.br', '3195480463', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 20, 'Edivaldo 3195480463', 'Determinação analítica dos novos limites e áreas para o fracionamento do terreno, em estrito cumprimento às normas de registro.', 'Levantamento Desdobramento', '550m²', 'Avenida Francisco Sá, 787', 'Alpes', 'Rusalina da Mata - RD', 'MG', '5 dias', 1, 4, '2025-11-24 10:52:04', 'Em elaboração', 590.70, 90.00, 60.48, 163.33, 110.00, 30.00, 304.35, 1318.87, 18.87, 1300.00, NULL, 30.00, 390.00, 70.00, 910.00),
(55, 'ELM-2025-049', 9, 'Nelson Henrique Giovanni Galvão', 'nelson_henrique_galvao@mailnull.com', '(31) 2887-6558', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 13, 'Nelson (31) 2887-6558', 'Mede e representa com precisão os limites, relevo e detalhes do terreno, fornecendo base completa para projetos e obras de engenharia.', 'Levantamento Planialtimétrico', '', 'Rua 3', 'Fiel', 'Betim', 'MG', '5 dias', 1, 4, '2025-11-24 14:02:22', 'Enviada', 590.70, 0.00, 56.70, 213.33, 110.00, 30.00, 291.22, 1261.95, 61.95, 1200.00, NULL, 30.00, 360.00, 70.00, 840.00),
(56, 'ELM-2025-050', 19, 'Davi Mendes Santos', 'davi@deuslhepague.com', '38 2222-5465', '38  98866-3312', '36 98877-4532', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 19, 'Davi 38 2222-5465 38  98866-3312', 'Execução de mapeamento aéreo de alta precisão para geração de ortofotos e modelos tridimensionais (3D), subsidiando análises e planejamentos topográficos.', 'Levantamento Drone', '78ha', 'Mina do Socorro', 'Henatita', 'Antonio Dias', 'MG', '15 dias', 3, 12, '2025-11-24 21:12:44', 'Em elaboração', 2473.50, 1350.00, 117.18, 240.00, 110.00, 30.00, 1287.20, 5577.88, 77.88, 5500.00, NULL, 30.00, 1650.00, 70.00, 3850.00);

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
(5, 4, 3, '', 2, 10.00, 6.30, 69.00),
(6, 12, 3, '', 2, 10.00, 6.30, 147.00),
(7, 13, 3, '', 1, 10.00, 6.30, 100.00),
(8, 14, 3, '', 2, 10.00, 6.30, 43.00),
(9, 15, 3, '', 2, 10.00, 6.30, 43.00),
(10, 16, 3, '', 2, 10.00, 6.30, 45.00),
(11, 17, 3, '', 1, 10.00, 6.30, 60.00),
(12, 18, 3, '', 2, 10.00, 6.00, 65.00),
(13, 19, 3, '', 1, 10.00, 6.00, 71.00),
(14, 20, 3, '', 2, 10.00, 6.00, 78.00),
(15, 21, 3, '', 2, 10.00, 6.00, 780.00),
(16, 22, 3, '', 1, 10.00, 6.00, 100.00),
(17, 23, 3, '', 2, 10.00, 6.00, 72.00),
(18, 24, 3, '', 1, 10.00, 6.00, 57.00),
(19, 25, 3, '', 2, 10.00, 6.00, 81.00),
(20, 26, 3, '', 2, 10.00, 6.30, 120.00),
(21, 27, 3, '', 1, 10.00, 6.30, 120.00),
(22, 28, 3, '', 2, 10.00, 6.30, 120.00),
(23, 29, 3, '', 2, 10.00, 6.30, 120.00),
(24, 30, 3, '', 2, 10.00, 6.30, 80.00),
(25, 31, 3, '', 2, 10.00, 6.30, 47.00),
(26, 32, 3, '', 2, 10.00, 6.30, 78.00),
(27, 34, 3, '', 2, 10.00, 6.30, 74.00),
(28, 35, 3, '', 2, 10.00, 6.30, 74.00),
(29, 36, 3, '', 2, 10.00, 6.30, 74.00),
(30, 37, 3, '', 1, 10.00, 6.30, 0.00),
(31, 38, 3, '', 2, 10.00, 6.30, 57.00),
(32, 39, 3, '', 2, 10.00, 6.30, 55.00),
(33, 40, 3, '', 2, 10.00, 6.30, 55.00),
(34, 41, 3, '', 2, 10.00, 6.30, 60.00),
(35, 42, 3, '', 2, 10.00, 6.30, 60.00),
(36, 43, 3, '', 2, 10.00, 6.30, 66.00),
(37, 44, 3, '', 2, 10.00, 6.30, 52.00),
(38, 46, 3, '', 2, 10.00, 6.30, 37.00),
(39, 47, 3, '', 2, 10.00, 6.30, 43.00),
(44, 52, 3, '', 1, 10.00, 6.30, 129.00),
(45, 53, 3, '', 2, 10.00, 6.30, 74.00),
(46, 54, 3, '', 2, 10.00, 6.30, 48.00),
(47, 55, 3, '', 2, 10.00, 6.30, 45.00),
(48, 56, 3, '', 2, 10.00, 6.30, 93.00);

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
(5, 4, 3, '', 1, 110.00),
(6, 12, 3, '', 1, 110.00),
(7, 13, 3, '', 1, 110.00),
(8, 14, 3, '', 1, 110.00),
(9, 15, 3, '', 1, 110.00),
(10, 16, 3, '', 1, 110.00),
(11, 17, 3, '', 1, 110.00),
(12, 18, 3, '', 1, 110.00),
(13, 19, 3, '', 1, 110.00),
(14, 20, 3, '', 1, 110.00),
(15, 21, 3, '', 1, 110.00),
(16, 22, 3, '', 1, 110.00),
(17, 23, 3, '', 1, 110.00),
(18, 24, 3, '', 1, 110.00),
(19, 25, 3, '', 1, 110.00),
(20, 26, 3, '', 1, 110.00),
(21, 27, 3, '', 1, 110.00),
(22, 28, 3, '', 1, 210.00),
(23, 29, 3, '', 1, 210.00),
(24, 29, 2, '', 1, 501.00),
(25, 30, 3, '', 1, 110.00),
(26, 31, 3, '', 1, 110.00),
(27, 32, 3, '', 1, 110.00),
(28, 32, 2, '', 1, 100.00),
(29, 34, 3, '', 1, 110.00),
(30, 35, 3, '', 1, 110.00),
(31, 36, 3, '', 1, 110.00),
(32, 37, 3, '', 1, 110.00),
(33, 38, 3, '', 1, 110.00),
(34, 39, 3, '', 1, 110.00),
(35, 40, 3, '', 1, 110.00),
(36, 41, 3, '', 1, 110.00),
(37, 42, 3, '', 1, 110.00),
(38, 43, 3, '', 1, 110.00),
(39, 44, 3, '', 1, 110.00),
(40, 46, 3, '', 1, 110.00),
(41, 47, 3, '', 1, 110.00),
(42, 52, 3, '', 1, 110.00),
(43, 53, 3, '', 1, 110.00),
(44, 54, 3, '', 1, 110.00),
(45, 55, 3, '', 1, 110.00),
(46, 56, 3, '', 1, 110.00);

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
(6, 5, 2, '', 1, 30.00, 1),
(7, 12, 1, '', 2, 100.00, 3),
(8, 12, 4, '', 1, 10.00, 2),
(9, 12, 2, '', 3, 30.00, 3),
(10, 13, 2, '', 1, 30.00, 1),
(11, 14, 2, '', 3, 30.00, 1),
(12, 15, 2, '', 3, 30.00, 1),
(13, 16, 2, '', 3, 30.00, 1),
(14, 17, 2, '', 3, 30.00, 1),
(15, 18, 2, '', 3, 30.00, 1),
(16, 19, 2, '', 3, 30.00, 1),
(17, 19, 4, '', 1, 10.00, 1),
(18, 20, 2, '', 4, 30.00, 1),
(19, 20, 3, '', 4, 15.00, 1),
(20, 21, 1, '', 3, 70.00, 3),
(21, 21, 4, '', 2, 120.00, 1),
(22, 21, 2, '', 6, 30.00, 3),
(23, 22, 2, '', 3, 30.00, 1),
(24, 23, 2, '', 3, 30.00, 1),
(25, 24, 2, '', 2, 30.00, 1),
(26, 25, 2, '', 3, 30.00, 3),
(27, 26, 2, '', 3, 30.00, 1),
(28, 27, 2, '', 3, 30.00, 1),
(29, 28, 2, '', 3, 30.00, 1),
(30, 28, 3, '', 2, 15.00, 1),
(31, 29, 2, '', 3, 30.00, 1),
(32, 29, 3, '', 2, 15.00, 1),
(33, 30, 2, '', 3, 30.00, 1),
(34, 31, 2, '', 3, 30.00, 1),
(35, 32, 2, '', 3, 30.00, 1),
(36, 32, 3, '', 3, 15.00, 1),
(37, 34, 2, '', 3, 30.00, 1),
(38, 35, 2, '', 3, 30.00, 1),
(39, 36, 2, '', 3, 30.00, 1),
(40, 37, 2, '', 2, 30.00, 1),
(41, 39, 2, '', 3, 30.00, 1),
(42, 39, 3, '', 3, 15.00, 1),
(43, 40, 2, '', 3, 30.00, 1),
(44, 40, 3, '', 3, 15.00, 1),
(45, 41, 2, '', 4, 30.00, 1),
(46, 42, 2, '', 4, 30.00, 1),
(47, 43, 2, '', 2, 30.00, 1),
(48, 44, 2, '', 3, 30.00, 2),
(49, 46, 2, '', 3, 30.00, 1),
(50, 47, 2, '', 3, 30.00, 1),
(55, 52, 2, '', 1, 30.00, 1),
(56, 53, 2, '', 1, 30.00, 1),
(57, 54, 2, '', 1, 30.00, 3),
(58, 55, 4, '', 3, 0.00, 1),
(59, 56, 2, '', 15, 30.00, 3);

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

--
-- Extraindo dados da tabela `Proposta_Locacao`
--

INSERT INTO `Proposta_Locacao` (`id_item_locacao`, `id_proposta`, `id_locacao`, `id_marca`, `quantidade`, `valor_mensal`, `dias`) VALUES
(4, 1, 4, NULL, 1, 3000.00, 1),
(5, 1, 3, NULL, 1, 1000.00, 1),
(6, 1, 2, NULL, 1, 900.00, 1),
(7, 2, 4, NULL, 1, 3000.00, 1),
(8, 2, 3, NULL, 1, 1000.00, 1),
(9, 2, 2, NULL, 1, 900.00, 1),
(10, 3, 4, NULL, 1, 3000.00, 1),
(11, 3, 3, NULL, 1, 1000.00, 1),
(12, 3, 2, NULL, 1, 900.00, 1),
(13, 4, 4, NULL, 1, 3000.00, 1),
(14, 4, 3, NULL, 1, 1000.00, 1),
(15, 4, 2, NULL, 1, 900.00, 1),
(16, 4, 1, NULL, 1, 1500.00, 1),
(17, 12, 4, NULL, 1, 3000.00, 3),
(18, 12, 3, NULL, 1, 1000.00, 3),
(19, 12, 2, NULL, 1, 900.00, 3),
(20, 13, 4, NULL, 1, 3000.00, 1),
(21, 13, 3, NULL, 1, 1000.00, 1),
(22, 13, 2, NULL, 1, 900.00, 1),
(23, 14, 4, NULL, 1, 3000.00, 1),
(24, 14, 3, NULL, 1, 1000.00, 1),
(25, 14, 2, NULL, 1, 900.00, 1),
(26, 15, 4, NULL, 1, 3000.00, 1),
(27, 15, 3, NULL, 1, 1000.00, 1),
(28, 15, 2, NULL, 1, 900.00, 1),
(29, 16, 4, NULL, 1, 3000.00, 1),
(30, 16, 3, NULL, 1, 1000.00, 1),
(31, 16, 2, NULL, 1, 900.00, 1),
(32, 17, 4, NULL, 1, 3000.00, 1),
(33, 17, 3, NULL, 1, 1000.00, 1),
(34, 17, 2, NULL, 1, 900.00, 1),
(35, 18, 4, NULL, 1, 3000.00, 1),
(36, 18, 3, NULL, 1, 1000.00, 1),
(37, 18, 2, NULL, 1, 900.00, 1),
(38, 19, 4, NULL, 1, 3000.00, 1),
(39, 19, 3, NULL, 1, 1000.00, 1),
(40, 19, 2, NULL, 1, 900.00, 1),
(41, 20, 4, NULL, 1, 3000.00, 1),
(42, 20, 3, NULL, 1, 1000.00, 1),
(43, 20, 2, NULL, 1, 900.00, 1),
(44, 21, 4, NULL, 1, 3000.00, 3),
(45, 21, 3, NULL, 1, 1000.00, 3),
(46, 21, 2, NULL, 1, 900.00, 3),
(47, 21, 1, NULL, 1, 1500.00, 3),
(48, 22, 4, NULL, 1, 3000.00, 1),
(49, 22, 3, NULL, 1, 1000.00, 1),
(50, 22, 2, NULL, 1, 900.00, 1),
(51, 23, 4, NULL, 1, 3000.00, 1),
(52, 23, 3, NULL, 1, 1000.00, 1),
(53, 23, 2, NULL, 1, 900.00, 1),
(54, 24, 4, NULL, 1, 3000.00, 1),
(55, 24, 3, NULL, 1, 1000.00, 1),
(56, 24, 2, NULL, 1, 900.00, 1),
(57, 25, 4, NULL, 1, 3000.00, 1),
(58, 25, 1, NULL, 1, 1500.00, 1),
(59, 26, 4, 13, 1, 3000.00, 1),
(60, 26, 3, 5, 1, 1000.00, 1),
(61, 26, 2, 11, 1, 900.00, 1),
(62, 26, 1, 3, 1, 1500.00, 1),
(63, 27, 4, 13, 1, 3000.00, 1),
(64, 27, 1, 1, 1, 1500.00, 1),
(65, 27, 3, 6, 1, 1000.00, 1),
(66, 27, 2, 11, 1, 900.00, 1),
(67, 28, 4, 15, 1, 3000.00, 1),
(68, 28, 1, 4, 1, 1500.00, 1),
(69, 28, 3, 5, 1, 1000.00, 1),
(70, 28, 2, 11, 1, 900.00, 1),
(71, 29, 4, 15, 1, 3000.00, 1),
(72, 29, 1, 4, 1, 1500.00, 1),
(73, 29, 3, 5, 1, 1000.00, 1),
(74, 29, 2, 11, 1, 900.00, 1),
(75, 30, 1, 1, 1, 1500.00, 1),
(76, 30, 2, 8, 1, 900.00, 1),
(77, 31, 4, NULL, 1, 3000.00, 1),
(78, 31, 3, NULL, 1, 1000.00, 1),
(79, 31, 2, NULL, 1, 900.00, 1),
(80, 32, 4, 14, 1, 3000.00, 1),
(81, 32, 3, 5, 1, 1000.00, 1),
(82, 32, 2, 10, 1, 900.00, 1),
(86, 34, 4, 13, 1, 3000.00, 1),
(87, 34, 3, 5, 1, 1000.00, 1),
(88, 34, 2, 11, 1, 900.00, 1),
(89, 35, 4, 13, 1, 3000.00, 1),
(90, 35, 3, 5, 1, 1000.00, 1),
(91, 35, 2, 11, 1, 900.00, 1),
(92, 36, 4, 13, 1, 3000.00, 1),
(93, 36, 2, 11, 1, 900.00, 1),
(94, 36, 1, 3, 1, 1500.00, 1),
(95, 37, 4, 15, 1, 3000.00, 1),
(96, 37, 3, 5, 1, 1000.00, 1),
(97, 37, 2, 11, 1, 900.00, 1),
(98, 38, 4, 13, 1, 3000.00, 1),
(99, 38, 3, 5, 1, 1000.00, 1),
(100, 38, 2, 8, 1, 900.00, 1),
(101, 39, 4, 15, 1, 3000.00, 1),
(102, 39, 3, 5, 1, 1000.00, 1),
(103, 39, 2, 11, 1, 900.00, 1),
(104, 40, 4, 15, 1, 3000.00, 1),
(105, 40, 3, 5, 1, 1000.00, 1),
(106, 40, 2, 11, 1, 900.00, 1),
(107, 41, 4, 14, 1, 3000.00, 1),
(108, 41, 3, 5, 1, 1000.00, 1),
(109, 41, 2, 11, 1, 900.00, 1),
(110, 42, 4, 14, 1, 3000.00, 1),
(111, 42, 3, 5, 1, 1000.00, 1),
(112, 42, 2, 11, 1, 900.00, 1),
(113, 43, 4, 13, 1, 3000.00, 1),
(114, 43, 3, 6, 1, 1000.00, 1),
(115, 43, 2, 10, 1, 900.00, 1),
(116, 44, 4, 14, 1, 3000.00, 1),
(117, 44, 3, 7, 1, 1000.00, 1),
(118, 44, 2, 11, 1, 900.00, 1),
(119, 46, 4, 12, 1, 3000.00, 1),
(120, 46, 3, 5, 1, 1000.00, 1),
(121, 46, 2, 11, 1, 900.00, 1),
(122, 47, 4, 15, 1, 3000.00, 1),
(123, 47, 3, 6, 1, 1000.00, 1),
(124, 47, 2, 8, 1, 900.00, 1),
(141, 52, 4, 13, 1, 3000.00, 1),
(142, 52, 3, 7, 1, 1000.00, 1),
(143, 52, 2, 10, 1, 900.00, 1),
(144, 53, 4, 13, 1, 3000.00, 1),
(145, 53, 3, 6, 1, 1000.00, 1),
(146, 53, 2, 11, 1, 900.00, 1),
(147, 54, 4, 15, 1, 3000.00, 1),
(148, 54, 3, 5, 1, 1000.00, 1),
(149, 54, 2, 11, 1, 900.00, 1),
(150, 55, 4, 14, 1, 3000.00, 1),
(151, 55, 3, 6, 1, 1000.00, 1),
(152, 55, 2, 11, 1, 900.00, 1),
(153, 55, 1, 1, 1, 1500.00, 1),
(154, 56, 1, 1, 1, 1500.00, 3),
(155, 56, 2, 11, 1, 900.00, 3);

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
(20, 5, 9, '', 1, 4311.38, 67.00, 1.0),
(21, 10, 10, '', 20, 4500.00, 67.00, 1.0),
(22, 11, 10, '', 5, 4500.00, 67.00, 1.0),
(23, 11, 9, '', 2, 4311.38, 67.00, 1.0),
(24, 12, 10, '', 1, 4500.00, 67.00, 3.0),
(25, 12, 9, '', 1, 4311.38, 67.00, 3.0),
(26, 12, 8, '', 1, 1800.00, 67.00, 3.0),
(27, 13, 10, '', 1, 4500.00, 67.00, 1.0),
(28, 13, 9, '', 1, 4311.38, 67.00, 1.0),
(29, 13, 8, '', 1, 1800.00, 67.00, 1.0),
(30, 14, 10, '', 1, 4500.00, 67.00, 1.0),
(31, 14, 9, '', 1, 4311.38, 67.00, 1.0),
(32, 14, 8, '', 1, 1800.00, 67.00, 1.0),
(33, 15, 10, '', 1, 4500.00, 67.00, 1.0),
(34, 15, 9, '', 1, 4311.38, 67.00, 1.0),
(35, 15, 8, '', 1, 1800.00, 67.00, 1.0),
(36, 16, 10, '', 1, 4500.00, 67.00, 1.0),
(37, 16, 9, '', 1, 4311.38, 67.00, 1.0),
(38, 16, 8, '', 1, 1800.00, 67.00, 1.0),
(39, 17, 10, '', 1, 4500.00, 67.00, 1.0),
(40, 17, 9, '', 1, 4311.38, 67.00, 1.0),
(41, 17, 8, '', 1, 1800.00, 67.00, 1.0),
(42, 18, 10, '', 1, 4500.00, 67.00, 1.0),
(43, 18, 9, '', 1, 4311.38, 67.00, 1.0),
(44, 18, 8, '', 1, 1800.00, 67.00, 1.0),
(45, 19, 10, '', 1, 4500.00, 67.00, 1.0),
(46, 19, 9, '', 1, 4311.38, 67.00, 1.0),
(47, 19, 8, '', 1, 1800.00, 67.00, 1.0),
(48, 20, 10, '', 1, 4500.00, 67.00, 1.0),
(49, 20, 9, '', 1, 4311.38, 67.00, 1.0),
(50, 20, 8, '', 1, 1800.00, 67.00, 1.0),
(51, 21, 10, '', 1, 4500.00, 67.00, 1.0),
(52, 21, 9, '', 1, 4311.38, 67.00, 1.0),
(53, 21, 8, '', 1, 1800.00, 67.00, 1.0),
(54, 22, 10, '', 1, 4500.00, 67.00, 1.0),
(55, 22, 9, '', 1, 4311.38, 67.00, 1.0),
(56, 22, 8, '', 1, 1800.00, 67.00, 1.0),
(57, 23, 10, '', 1, 4500.00, 67.00, 1.0),
(58, 23, 9, '', 1, 4311.38, 67.00, 1.0),
(59, 23, 8, '', 1, 1800.00, 67.00, 1.0),
(60, 24, 10, '', 1, 4500.00, 67.00, 1.0),
(61, 24, 9, '', 1, 4311.38, 67.00, 1.0),
(62, 24, 8, '', 1, 1800.00, 67.00, 1.0),
(63, 25, 10, '', 3, 4500.00, 67.00, 1.0),
(64, 25, 9, '', 1, 4311.38, 67.00, 1.0),
(65, 26, 10, '', 1, 4500.00, 67.00, 1.0),
(66, 26, 9, '', 1, 4311.38, 67.00, 1.0),
(67, 26, 8, '', 1, 1800.00, 67.00, 1.0),
(68, 27, 10, '', 1, 4500.00, 67.00, 1.0),
(69, 27, 9, '', 1, 4311.38, 67.00, 1.0),
(70, 27, 8, '', 1, 1800.00, 67.00, 1.0),
(71, 28, 10, '', 1, 4500.00, 67.00, 1.0),
(72, 28, 9, '', 1, 4311.38, 67.00, 1.0),
(73, 28, 8, '', 1, 1800.00, 67.00, 1.0),
(74, 29, 10, '', 1, 4500.00, 67.00, 1.0),
(75, 29, 9, '', 1, 4311.38, 67.00, 1.0),
(76, 29, 8, '', 1, 1800.00, 67.00, 1.0),
(77, 30, 10, '', 1, 4500.00, 67.00, 1.0),
(78, 30, 9, '', 1, 4311.38, 67.00, 1.0),
(79, 30, 8, '', 1, 1800.00, 67.00, 1.0),
(80, 31, 10, '', 1, 4500.00, 67.00, 1.0),
(81, 31, 9, '', 1, 4311.38, 67.00, 1.0),
(82, 31, 8, '', 1, 1800.00, 67.00, 1.0),
(83, 32, 10, '', 1, 4500.00, 67.00, 1.0),
(84, 32, 9, '', 1, 4311.38, 67.00, 1.0),
(85, 32, 8, '', 1, 1800.00, 67.00, 1.0),
(88, 34, 10, '', 1, 4500.00, 67.00, 1.0),
(89, 34, 9, '', 1, 4311.38, 67.00, 1.0),
(90, 34, 8, '', 1, 1800.00, 67.00, 1.0),
(91, 35, 10, '', 1, 4500.00, 67.00, 1.0),
(92, 35, 9, '', 1, 4311.38, 67.00, 1.0),
(93, 35, 8, '', 1, 1800.00, 67.00, 1.0),
(94, 36, 10, '', 1, 4500.00, 67.00, 1.0),
(95, 36, 9, '', 1, 4311.38, 67.00, 1.0),
(96, 36, 8, '', 1, 1800.00, 67.00, 1.0),
(97, 37, 10, '', 1, 4500.00, 67.00, 1.0),
(98, 37, 9, '', 1, 4311.38, 67.00, 1.0),
(99, 37, 8, '', 1, 1800.00, 67.00, 1.0),
(100, 38, 10, '', 1, 4500.00, 67.00, 1.0),
(101, 38, 9, '', 1, 4311.38, 67.00, 1.0),
(102, 38, 8, '', 1, 1800.00, 67.00, 1.0),
(103, 39, 10, '', 1, 4500.00, 67.00, 1.0),
(104, 39, 9, '', 1, 4311.38, 67.00, 1.0),
(105, 39, 8, '', 1, 1800.00, 67.00, 1.0),
(106, 40, 10, '', 1, 4500.00, 67.00, 1.0),
(107, 40, 9, '', 1, 4311.38, 67.00, 1.0),
(108, 40, 8, '', 1, 1800.00, 67.00, 1.0),
(109, 41, 10, '', 1, 4500.00, 67.00, 1.0),
(110, 41, 9, '', 1, 4311.38, 67.00, 1.0),
(111, 41, 8, '', 1, 1800.00, 67.00, 1.0),
(112, 42, 10, '', 1, 4500.00, 67.00, 1.0),
(113, 42, 9, '', 1, 4311.38, 67.00, 1.0),
(114, 42, 8, '', 1, 1800.00, 67.00, 1.0),
(115, 43, 10, '', 1, 4500.00, 67.00, 1.0),
(116, 43, 9, '', 1, 4311.38, 67.00, 1.0),
(117, 43, 8, '', 1, 1800.00, 67.00, 1.0),
(118, 44, 10, '', 1, 4500.00, 67.00, 1.0),
(119, 44, 8, '', 1, 1800.00, 67.00, 1.0),
(120, 44, 8, '', 1, 1800.00, 67.00, 1.0),
(121, 46, 10, '', 1, 4500.00, 67.00, 1.0),
(122, 46, 9, '', 1, 4311.38, 67.00, 1.0),
(123, 46, 8, '', 1, 1800.00, 67.00, 1.0),
(124, 47, 10, '', 1, 4500.00, 67.00, 1.0),
(125, 47, 9, '', 1, 4311.38, 67.00, 1.0),
(126, 47, 8, '', 1, 1800.00, 67.00, 1.0),
(139, 52, 10, '', 1, 4500.00, 67.00, 1.0),
(140, 52, 9, '', 1, 4311.38, 67.00, 1.0),
(141, 52, 8, '', 1, 1800.00, 67.00, 1.0),
(142, 53, 10, '', 1, 4500.00, 67.00, 1.0),
(143, 53, 9, '', 1, 4311.38, 67.00, 1.0),
(144, 53, 8, '', 1, 1800.00, 67.00, 1.0),
(145, 54, 10, '', 1, 4500.00, 67.00, 1.0),
(146, 54, 9, '', 1, 4311.38, 67.00, 1.0),
(147, 54, 8, '', 1, 1800.00, 67.00, 1.0),
(148, 55, 10, '', 1, 4500.00, 67.00, 1.0),
(149, 55, 9, '', 1, 4311.38, 67.00, 1.0),
(150, 55, 8, '', 1, 1800.00, 67.00, 1.0),
(151, 56, 10, '', 1, 4500.00, 67.00, 7.0),
(152, 56, 9, '', 1, 4311.38, 67.00, 3.0);

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
  `data_cadastro` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Extraindo dados da tabela `Usuarios`
--

INSERT INTO `Usuarios` (`id_usuario`, `usuario`, `senha`, `nome_completo`, `setup_concluido`, `data_cadastro`) VALUES
(1, 'admin', '123456', 'Administrador', 1, '2025-11-23 09:27:23');

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
  MODIFY `id_cliente` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

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
  MODIFY `id_proposta` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

--
-- AUTO_INCREMENT for table `Proposta_Consumos`
--
ALTER TABLE `Proposta_Consumos`
  MODIFY `id_item_consumo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT for table `Proposta_Custos_Administrativos`
--
ALTER TABLE `Proposta_Custos_Administrativos`
  MODIFY `id_item_custo_admin` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT for table `Proposta_Estadia`
--
ALTER TABLE `Proposta_Estadia`
  MODIFY `id_item_estadia` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=60;

--
-- AUTO_INCREMENT for table `Proposta_Locacao`
--
ALTER TABLE `Proposta_Locacao`
  MODIFY `id_item_locacao` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=156;

--
-- AUTO_INCREMENT for table `Proposta_Salarios`
--
ALTER TABLE `Proposta_Salarios`
  MODIFY `id_salario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=153;

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
-- AUTO_INCREMENT for table `Usuarios`
--
ALTER TABLE `Usuarios`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

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
