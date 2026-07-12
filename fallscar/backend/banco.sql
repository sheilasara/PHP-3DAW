CREATE DATABASE IF NOT EXISTS locadora_fallscar;
USE locadora_fallscar;

CREATE TABLE lojas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    cidade VARCHAR(100) NOT NULL,
    endereco VARCHAR(150) NOT NULL
);


CREATE TABLE clientes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    cpf VARCHAR(14) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    habilitacao VARCHAR(20) NOT NULL,
    endereco VARCHAR(150) NOT NULL,
    data_cadastro DATETIME DEFAULT CURRENT_TIMESTAMP
);


CREATE TABLE veiculos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    modelo VARCHAR(60) NOT NULL,
    marca VARCHAR(60) NOT NULL,
    categoria VARCHAR(30) NOT NULL,
    placa VARCHAR(10) NOT NULL UNIQUE,
    ano INT NOT NULL,
    cor VARCHAR(30) NOT NULL,
    valor_diaria DECIMAL(10,2) NOT NULL,
    disponivel TINYINT(1) DEFAULT 1,
    loja_id INT NOT NULL,
    imagem VARCHAR(150) DEFAULT 'carro_padrao.jpg',
    FOREIGN KEY (loja_id) REFERENCES lojas(id)
);


CREATE TABLE reservas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT NOT NULL,
    veiculo_id INT NOT NULL,
    loja_retirada_id INT NOT NULL,
    loja_devolucao_id INT NOT NULL,
    data_inicio DATE NOT NULL,
    data_fim DATE NOT NULL,
    periodo_dias INT NOT NULL,
    valor_total DECIMAL(10,2) NOT NULL,
    status VARCHAR(20) DEFAULT 'pendente',
    data_reserva DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id),
    FOREIGN KEY (veiculo_id) REFERENCES veiculos(id),
    FOREIGN KEY (loja_retirada_id) REFERENCES lojas(id),
    FOREIGN KEY (loja_devolucao_id) REFERENCES lojas(id)
);


CREATE TABLE motoristas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reserva_id INT NOT NULL,
    nome VARCHAR(100) NOT NULL,
    cpf VARCHAR(14) NOT NULL,
    habilitacao VARCHAR(20) NOT NULL,
    FOREIGN KEY (reserva_id) REFERENCES reservas(id)
);


CREATE TABLE pagamentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reserva_id INT NOT NULL,
    metodo VARCHAR(20) NOT NULL,
    nome_impresso VARCHAR(100),
    numero_cartao VARCHAR(20),
    validade_cartao VARCHAR(7),
    valor DECIMAL(10,2) NOT NULL,
    status VARCHAR(20) DEFAULT 'aprovado',
    data_pagamento DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (reserva_id) REFERENCES reservas(id)
);


INSERT INTO lojas (nome, cidade, endereco) VALUES
('Falls Car Centro', 'Rio de Janeiro', 'Av. Rio Branco, 100'),
('Falls Car Barra', 'Rio de Janeiro', 'Av. das Americas, 2000'),
('Falls Car Niteroi', 'Niteroi', 'Rua da Conceicao, 50');

INSERT INTO veiculos (modelo, marca, categoria, placa, ano, cor, valor_diaria, disponivel, loja_id) VALUES
('Onix', 'Chevrolet', 'Economico', 'ABC1D23', 2023, 'Prata', 120.00, 1, 1),
('HB20', 'Hyundai', 'Economico', 'DEF4G56', 2022, 'Branco', 110.00, 1, 1),
('Corolla', 'Toyota', 'Sedan', 'GHI7H89', 2023, 'Preto', 220.00, 1, 2),
('Compass', 'Jeep', 'SUV', 'JKL0I12', 2024, 'Cinza', 320.00, 1, 2),
('Kwid', 'Renault', 'Economico', 'MNO3J45', 2022, 'Vermelho', 95.00, 1, 3),
('Civic', 'Honda', 'Sedan', 'PQR6K78', 2023, 'Azul', 250.00, 1, 3);
