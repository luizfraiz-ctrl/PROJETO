CREATE DATABASE IF NOT EXISTS estacionamento
CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE estacionamento;

CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS veiculos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    placa VARCHAR(10) NOT NULL,
    modelo VARCHAR(100) NOT NULL,
    cor VARCHAR(50) NOT NULL,
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS vagas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero INT NOT NULL UNIQUE,
    status ENUM('livre','ocupada') DEFAULT 'livre',
    tipo VARCHAR(30) DEFAULT 'Comum'
);

CREATE TABLE IF NOT EXISTS estacionamentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    veiculo_id INT NOT NULL,
    vaga_id INT NOT NULL,
    entrada DATETIME NOT NULL,
    saida DATETIME NULL,
    valor DECIMAL(10,2) DEFAULT 0,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (veiculo_id) REFERENCES veiculos(id) ON DELETE CASCADE,
    FOREIGN KEY (vaga_id) REFERENCES vagas(id) ON DELETE RESTRICT
);

INSERT IGNORE INTO vagas (numero, status, tipo) VALUES
(1,'livre','Comum'),(2,'livre','Comum'),(3,'livre','Comum'),
(4,'livre','Comum'),(5,'livre','Comum'),(6,'livre','PCD'),
(7,'livre','Comum'),(8,'livre','Comum'),(9,'livre','Comum'),(10,'livre','Comum');
