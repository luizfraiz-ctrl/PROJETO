-- ============================================
-- Banco de dados: ParkPoint
-- Sistema de Gestão de Vagas de Estacionamento
-- ============================================

CREATE DATABASE IF NOT EXISTS parkpoint CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE parkpoint;

-- Usuários do sistema (motoristas, funcionários, administradores)
CREATE TABLE usuarios (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(120) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  senha VARCHAR(255) NOT NULL,
  telefone VARCHAR(20),
  tipo ENUM('motorista','funcionario','admin') NOT NULL DEFAULT 'motorista',
  criado_em DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Veículos cadastrados, vinculados a um usuário
CREATE TABLE veiculos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  usuario_id INT NOT NULL,
  placa VARCHAR(8) NOT NULL UNIQUE,
  modelo VARCHAR(80),
  cor VARCHAR(40),
  criado_em DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Vagas do estacionamento (ex: A1, A2, B3, B4...)
CREATE TABLE vagas (
  id INT AUTO_INCREMENT PRIMARY KEY,
  codigo VARCHAR(5) NOT NULL UNIQUE,
  setor VARCHAR(2) NOT NULL,
  status ENUM('disponivel','ocupada') NOT NULL DEFAULT 'disponivel',
  atualizado_em DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Catálogo de serviços oferecidos (lavagem, manobrista, etc.)
CREATE TABLE servicos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(80) NOT NULL,
  descricao VARCHAR(255),
  preco DECIMAL(8,2) NOT NULL DEFAULT 0
) ENGINE=InnoDB;

-- Registro de entrada e saída de veículos nas vagas
CREATE TABLE movimentacoes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  veiculo_id INT NOT NULL,
  vaga_id INT NOT NULL,
  entrada DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  saida DATETIME NULL,
  valor_total DECIMAL(8,2) NULL,
  FOREIGN KEY (veiculo_id) REFERENCES veiculos(id),
  FOREIGN KEY (vaga_id) REFERENCES vagas(id)
) ENGINE=InnoDB;

-- Serviços consumidos durante uma movimentação (N:N)
CREATE TABLE movimentacao_servicos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  movimentacao_id INT NOT NULL,
  servico_id INT NOT NULL,
  quantidade INT NOT NULL DEFAULT 1,
  FOREIGN KEY (movimentacao_id) REFERENCES movimentacoes(id) ON DELETE CASCADE,
  FOREIGN KEY (servico_id) REFERENCES servicos(id)
) ENGINE=InnoDB;

-- Dados iniciais: vagas exibidas no site (mapa A1-B4)
INSERT INTO vagas (codigo, setor, status) VALUES
('A1','A','disponivel'),
('A2','A','disponivel'),
('A3','A','ocupada'),
('A4','A','disponivel'),
('B1','B','disponivel'),
('B2','B','disponivel'),
('B3','B','disponivel'),
('B4','B','ocupada');

-- Dados iniciais: serviços do plano/estacionamento
INSERT INTO servicos (nome, descricao, preco) VALUES
('Lavagem simples','Lavagem externa do veículo',25.00),
('Manobrista','Serviço de manobrista na entrada e saída',10.00),
('Vaga coberta','Reserva de vaga coberta',15.00);
