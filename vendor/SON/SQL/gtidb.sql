SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `gtichamados`
--

-- --------------------------------------------------------

--
-- Table structure for table `chamados`
--

CREATE TABLE `chamados` (
  `id` int(10) NOT NULL,
  `id_servico` int(10) NOT NULL,
  `id_local` int(10) NOT NULL,
  `id_solicitacao` int(10) NOT NULL,
  `data_abertura` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `data_finalizado` datetime DEFAULT NULL,
  `data_assumido` datetime DEFAULT NULL,
  `prazo` datetime DEFAULT NULL,
  `id_tecnico_abertura` int(10) NOT NULL,
  `id_cliente_solicitante` int(10) NOT NULL,
  `id_tecnico_fechamento` int(10) DEFAULT NULL,
  `descricao` text COLLATE utf8mb4_unicode_ci,
  `parecer_tecnico` text COLLATE utf8mb4_unicode_ci,
  `status` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'AGUARDANDO'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `chamado_tecnico_xref`
--

CREATE TABLE `chamado_tecnico_xref` (
  `id` int(10) NOT NULL,
  `id_chamado` int(10) NOT NULL,
  `id_tecnico` int(10) NOT NULL,
  `atividade` text COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `locais`
--

CREATE TABLE `locais` (
  `id` int(10) NOT NULL,
  `nome` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ativo` bit(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `servicos`
--

CREATE TABLE `servicos` (
  `id` int(10) NOT NULL,
  `nome` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descricao` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `servicos`
--

INSERT INTO `servicos` (`id`, `nome`, `tipo`, `descricao`) VALUES
(1, 'Instalação de Impressora', 'Instalação', NULL),
(2, 'Reserva de Câmera', 'Reserva', NULL),
(3, 'Instalação de Programas', 'Instalação', NULL),
(4, 'Instalação de Computador', 'Instalação', NULL),
(5, 'Compartilhamento de Impressora', 'Instalação', NULL),
(6, 'Problema - Internet', 'Problema', NULL),
(7, 'Problema - Voip', 'Problema', NULL),
(8, 'Problema - Projetor', 'Problema', NULL),
(9, 'Problema - Nobreak/Estabilizador', 'Problema', NULL),
(10, 'Problema - Conexão de Rede', 'Problema', NULL),
(11, 'Problema - E-mail', 'Problema', NULL),
(12, 'Problema - Energia', 'Problema', NULL),
(13, 'Problema - Impressora', 'Problema', NULL),
(14, 'Problema - Computador', 'Problema', NULL),
(15, 'Problema - Outros', 'Problema', NULL),
(16, 'Criar Site/Sistema', 'Sites', NULL),
(17, 'Atualizar Site/Sistema', 'Sites', NULL),
(18, 'Corrigir bug', 'Sites', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `servicos_roles`
--

CREATE TABLE `servicos_roles` (
  `id` int(10) NOT NULL,
  `id_servico` int(10) NOT NULL,
  `suporte` tinyint(1) DEFAULT NULL,
  `desenvolvimento` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `solicitacao_cadastro`
--

CREATE TABLE `solicitacao_cadastro` (
  `id` int(10) NOT NULL,
  `nome` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `login` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `setor` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `matricula` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `data_solicitacao` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'AGUARDANDO',
  `data_recusado` datetime DEFAULT NULL,
  `id_recusante` int(10) DEFAULT NULL,
  `motivo_recusa` text COLLATE utf8mb4_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `solicitacao_chamado`
--

CREATE TABLE `solicitacao_chamado` (
  `id` int(10) NOT NULL,
  `data_solicitacao` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `id_cliente` int(10) NOT NULL,
  `id_servico` int(10) NOT NULL,
  `id_local` int(10) NOT NULL,
  `descricao` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'AGUARDANDO',
  `data_recusado` datetime DEFAULT NULL,
  `id_recusante` int(10) DEFAULT NULL,
  `motivo_recusa` text COLLATE utf8mb4_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(10) NOT NULL,
  `nome` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `login` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password_hash` char(128) COLLATE utf8mb4_unicode_ci NOT NULL,
  `turno` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `setor` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `matricula` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `data_cadastro` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `data_ultimo_login` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Administrator user
--

INSERT INTO `usuarios` (`id`, `nome`, `email`, `login`, `password_hash`, `turno`, `setor`, `matricula`, `data_ultimo_login`) VALUES
(1, 'Administrator', 'admin@email.com', 'admin', '$argon2id$v=19$m=62,t=3,p=1$K9G0838cLxCp2NEb/aA4/g$H6iWzgoEP+OHmB2UFOPtdVQcDquGisa0829/WP7+VRQ', NULL, '', '', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `usuarios_roles`
--

CREATE TABLE `usuarios_roles` (
  `id` int(10) NOT NULL,
  `id_usuario` int(10) NOT NULL,
  `cliente` tinyint(1) DEFAULT NULL,
  `tecnico` tinyint(1) DEFAULT NULL,
  `gerente` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Admin user role
--

INSERT INTO `usuarios_roles` (`id`, `id_usuario`, `cliente`, `tecnico`, `gerente`) VALUES
(1, 1, 0, 0, 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `chamados`
--
ALTER TABLE `chamados`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_servico` (`id_servico`),
  ADD KEY `id_tecnico_abertura` (`id_tecnico_abertura`),
  ADD KEY `id_cliente_solicitante` (`id_cliente_solicitante`);
  ADD KEY `id_tecnico_fechamento` (`id_tecnico_fechamento`);

--
-- Indexes for table `chamado_tecnico_xref`
--
ALTER TABLE `chamado_tecnico_xref`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `locais`
--
ALTER TABLE `locais`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `servicos`
--
ALTER TABLE `servicos`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `servicos_roles`
--
ALTER TABLE `servicos_roles`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `solicitacao_cadastro`
--
ALTER TABLE `solicitacao_cadastro`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `solicitacao_chamado`
--
ALTER TABLE `solicitacao_chamado`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_cliente` (`id_cliente`),
  ADD KEY `id_servico` (`id_servico`);

--
-- Indexes for table `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `login` (`login`);

--
-- Indexes for table `usuarios_roles`
--
ALTER TABLE `usuarios_roles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `chamados`
--
ALTER TABLE `chamados`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `chamado_tecnico_xref`
--
ALTER TABLE `chamado_tecnico_xref`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `locais`
--
ALTER TABLE `locais`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `servicos`
--
ALTER TABLE `servicos`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `servicos_roles`
--
ALTER TABLE `servicos_roles`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `solicitacao_cadastro`
--
ALTER TABLE `solicitacao_cadastro`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `solicitacao_chamado`
--
ALTER TABLE `solicitacao_chamado`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `usuarios_roles`
--
ALTER TABLE `usuarios_roles`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
