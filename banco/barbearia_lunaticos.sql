-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 19/11/2026 às 04:49
-- Versão do servidor: 10.11.16-MariaDB
-- Versão do PHP: 8.4.27

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `barbearia_lunaticos`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `agendamentos`
--

CREATE TABLE `agendamentos` (
  `id` int(10) UNSIGNED NOT NULL,
  `servico_id` int(10) UNSIGNED NOT NULL,
  `preco` decimal(8,2) NOT NULL,
  `nome` varchar(120) NOT NULL,
  `telefone` varchar(11) NOT NULL,
  `observacoes` varchar(500) DEFAULT NULL,
  `inicio` datetime NOT NULL,
  `status` enum('pendente','confirmado','cancelado') NOT NULL DEFAULT 'pendente',
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `agendamentos`
--

INSERT INTO `agendamentos` (`id`, `servico_id`, `preco`, `nome`, `telefone`, `observacoes`, `inicio`, `status`, `criado_em`) VALUES
(1, 1, 45.00, 'Teste 1', '62995095551', 'Preferencia por ambiente gelado', '2026-11-23 12:00:00', 'pendente', '2026-11-19 03:45:32');

-- --------------------------------------------------------

--
-- Estrutura para tabela `horarios_ocupados`
--

CREATE TABLE `horarios_ocupados` (
  `inicio` datetime NOT NULL,
  `agendamento_id` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `horarios_ocupados`
--

INSERT INTO `horarios_ocupados` (`inicio`, `agendamento_id`) VALUES
('2026-11-23 12:00:00', 1);

-- --------------------------------------------------------

--
-- Estrutura para tabela `mensagens_contato`
--

CREATE TABLE `mensagens_contato` (
  `id` int(10) UNSIGNED NOT NULL,
  `nome` varchar(120) NOT NULL,
  `email` varchar(254) NOT NULL,
  `mensagem` text NOT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `produtos`
--

CREATE TABLE `produtos` (
  `id` int(10) UNSIGNED NOT NULL,
  `codigo` varchar(60) NOT NULL,
  `nome` varchar(120) NOT NULL,
  `preco` decimal(8,2) NOT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `produtos`
--

INSERT INTO `produtos` (`id`, `codigo`, `nome`, `preco`, `ativo`) VALUES
(1, 'pomada-modeladora-matte', 'Pomada Modeladora Matte', 40.00, 1),
(2, 'oleo-para-barba-hidratante', 'Óleo para Barba Hidratante', 35.00, 1),
(3, 'shampoo-refrescante-3-em-1', 'Shampoo Refrescante 3 em 1', 30.00, 1);

-- --------------------------------------------------------

--
-- Estrutura para tabela `reservas_produtos`
--

CREATE TABLE `reservas_produtos` (
  `id` int(10) UNSIGNED NOT NULL,
  `produto_id` int(10) UNSIGNED NOT NULL,
  `nome` varchar(120) NOT NULL,
  `email` varchar(254) NOT NULL,
  `preco` decimal(8,2) NOT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `reservas_produtos`
--

INSERT INTO `reservas_produtos` (`id`, `produto_id`, `nome`, `email`, `preco`, `criado_em`) VALUES
(1, 2, 'lucas moises', 'lucasmoises2007@gmail.com', 35.00, '2026-11-19 03:45:49');

-- --------------------------------------------------------

--
-- Estrutura para tabela `servicos`
--

CREATE TABLE `servicos` (
  `id` int(10) UNSIGNED NOT NULL,
  `codigo` varchar(40) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `preco` decimal(8,2) NOT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `servicos`
--

INSERT INTO `servicos` (`id`, `codigo`, `nome`, `preco`, `ativo`) VALUES
(1, 'corte-classico-degrade', 'Corte Clássico / Degradê', 45.00, 1),
(2, 'barba-tradicional', 'Barba Tradicional', 35.00, 1),
(3, 'combo-dos-lunaticos', 'Combo dos Lunáticos', 70.00, 1);

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(10) UNSIGNED NOT NULL,
  `nome` varchar(120) NOT NULL,
  `email` varchar(254) NOT NULL,
  `senha_hash` varchar(255) NOT NULL,
  `perfil` enum('cliente','admin') NOT NULL DEFAULT 'cliente',
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `agendamentos`
--
ALTER TABLE `agendamentos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_agendamentos_servicos` (`servico_id`);

--
-- Índices de tabela `horarios_ocupados`
--
ALTER TABLE `horarios_ocupados`
  ADD PRIMARY KEY (`inicio`),
  ADD UNIQUE KEY `agendamento_id` (`agendamento_id`);

--
-- Índices de tabela `mensagens_contato`
--
ALTER TABLE `mensagens_contato`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `produtos`
--
ALTER TABLE `produtos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `codigo` (`codigo`);

--
-- Índices de tabela `reservas_produtos`
--
ALTER TABLE `reservas_produtos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_reservas_produtos` (`produto_id`);

--
-- Índices de tabela `servicos`
--
ALTER TABLE `servicos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `codigo` (`codigo`);

--
-- Índices de tabela `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `agendamentos`
--
ALTER TABLE `agendamentos`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `mensagens_contato`
--
ALTER TABLE `mensagens_contato`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `produtos`
--
ALTER TABLE `produtos`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `reservas_produtos`
--
ALTER TABLE `reservas_produtos`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `servicos`
--
ALTER TABLE `servicos`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `agendamentos`
--
ALTER TABLE `agendamentos`
  ADD CONSTRAINT `fk_agendamentos_servicos` FOREIGN KEY (`servico_id`) REFERENCES `servicos` (`id`);

--
-- Restrições para tabelas `horarios_ocupados`
--
ALTER TABLE `horarios_ocupados`
  ADD CONSTRAINT `fk_horarios_agendamentos` FOREIGN KEY (`agendamento_id`) REFERENCES `agendamentos` (`id`);

--
-- Restrições para tabelas `reservas_produtos`
--
ALTER TABLE `reservas_produtos`
  ADD CONSTRAINT `fk_reservas_produtos` FOREIGN KEY (`produto_id`) REFERENCES `produtos` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
