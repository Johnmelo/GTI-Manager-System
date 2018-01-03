CREATE TABLE `servicos`(
  `id` INT(10) PRIMARY KEY AUTO_INCREMENT,
  `nome` VARCHAR(50) NOT NULL,
  `descricao` VARCHAR(100)
);
CREATE TABLE `servicos_roles`(
  `id` INT(10) PRIMARY KEY AUTO_INCREMENT,
  `id_servico` INT(10) NOT NULL,
  `suporte` BOOLEAN,
  `desenvolvimento` BOOLEAN
);
CREATE TABLE `usuarios`(
  `id` INT(10) PRIMARY KEY AUTO_INCREMENT,
  `nome` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL,
  `login` VARCHAR(100) NOT NULL,
  `password` VARCHAR(100) NOT NULL,
  `turno` VARCHAR(20),
  `setor` VARCHAR(100) NOT NULL,
  `matricula` VARCHAR(20) NOT NULL
);
CREATE TABLE `usuarios_roles`(
  `id` INT(10) PRIMARY KEY AUTO_INCREMENT,
  `id_usuario` INT(10) NOT NULL,
  `cliente` BOOLEAN,
  `tecnico` BOOLEAN,
  `gerente` BOOLEAN,
  FOREIGN KEY (id_usuario) REFERENCES usuarios(id)
);

CREATE TABLE `solicitacao_chamado`(
  `id` INT(10) PRIMARY KEY AUTO_INCREMENT,
  `data_solicitacao` DATE NOT NULL,
  `id_cliente` INT(10) NOT NULL,
  `id_servico` INT(10) NOT NULL,
  `descricao` VARCHAR(100) NOT NULL,
  FOREIGN KEY (id_cliente) REFERENCES usuario(id),
  FOREIGN KEY (id_servico) REFERENCES servicos(id)
);

CREATE TABLE `chamados`(
  `id` INT(10) PRIMARY KEY AUTO_INCREMENT,
  `id_servico` INT(10) NOT NULL,
  `id_solicitacao` INT(10) NOT NULL,
  `data_abertura` DATE NOT NULL,
  `data_finalizado` DATE NOT NULL,
  `prazo` INT(1) NOT NULL,
  `id_tecnico_responsavel` INT(10) NOT NULL,
  `id_tecnico_abertura` INT(10) NOT NULL,
  `id_cliente_solicitante` INT(10) NOT NULL,
  `descricao` VARCHAR(200),
  `parecer_tecnico` VARCHAR(300),
  FOREIGN KEY (id_servico) REFERENCES servicos(id),
  FOREIGN KEY (id_tecnico_abertura) REFERENCES tecnicos(id),
  FOREIGN KEY (id_tecnico_responsavel) REFERENCES tecnicos(id),
  FOREIGN KEY (id_cliente_solicitante) REFERENCES clientes(id)
);

CREATE TABLE `solicitacao_cadastro`(
  `id` INT(10) PRIMARY KEY AUTO_INCREMENT,
  `nome` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL,
  `login` VARCHAR(100) NOT NULL,
  `setor` VARCHAR(100) NOT NULL,
  `matricula` VARCHAR(20) NOT NULL,
  `status` VARCHAR(10) NOT NULL
);


INSERT INTO `servicos`(`nome`)VALUES("Instalação de Impressora");
INSERT INTO `servicos`(`nome`)VALUES("Reserva de Câmera");
INSERT INTO `servicos`(`nome`)VALUES("Instalação de programas");
INSERT INTO `servicos`(`nome`)VALUES("Instalação de computador");
INSERT INTO `servicos`(`nome`)VALUES("Compartilhamento de Impressora");
INSERT INTO `servicos`(`nome`)VALUES("Problema - Internet");
INSERT INTO `servicos`(`nome`)VALUES("Problema - Voip");
INSERT INTO `servicos`(`nome`)VALUES("Problema - Projetor");
INSERT INTO `servicos`(`nome`)VALUES("Problema - Nobreak/Estabilizador");
INSERT INTO `servicos`(`nome`)VALUES("Problema - Conexão de Rede");
INSERT INTO `servicos`(`nome`)VALUES("Problema - E-mail");
INSERT INTO `servicos`(`nome`)VALUES("Problema - Energia");
INSERT INTO `servicos`(`nome`)VALUES("Problema - Impressora");
INSERT INTO `servicos`(`nome`)VALUES("Problema - computador");
INSERT INTO `servicos`(`nome`)VALUES("Problema - Outros");
INSERT INTO `servicos`(`nome`)VALUES("Criar Site/Sistema");
INSERT INTO `servicos`(`nome`)VALUES("Atualizar Site/Sistema");
INSERT INTO `servicos`(`nome`)VALUES("Corrigir bug");
