-- =====================================================================
-- Falls Car - Dados de exemplo (seed) para testes da área do cliente
-- =====================================================================
USE falls_car;

INSERT INTO lojas (nome, cidade, estado, endereco, telefone) VALUES
('Falls Car - Centro',        'Rio de Janeiro', 'RJ', 'Av. Rio Branco, 100',     '(21) 3333-1000'),
('Falls Car - Barra',         'Rio de Janeiro', 'RJ', 'Av. das Américas, 5000',  '(21) 3333-1001'),
('Falls Car - Aeroporto SDU', 'Rio de Janeiro', 'RJ', 'Aeroporto Santos Dumont', '(21) 3333-1002'),
('Falls Car - Paulista',      'São Paulo',      'SP', 'Av. Paulista, 900',       '(11) 4444-2000'),
('Falls Car - Guarulhos',     'São Paulo',      'SP', 'Aeroporto de Guarulhos',  '(11) 4444-2001');

INSERT INTO veiculos (placa, marca, modelo, ano, cor, categoria, quilometragem, necessita_revisao, valor_diaria, status, id_loja) VALUES
('ABC1D23', 'Chevrolet', 'Onix',      2023, 'Branco',  'economico',      15000, 0, 120.00, 'disponivel', 1),
('ABC1D24', 'Fiat',      'Argo',      2022, 'Prata',   'economico',      22000, 0, 110.00, 'disponivel', 1),
('DEF2E45', 'Hyundai',   'HB20',      2023, 'Vermelho','economico',       8000, 0, 125.00, 'disponivel', 2),
('GHI3F67', 'Toyota',    'Corolla',   2024, 'Preto',   'intermediario',   5000, 0, 220.00, 'disponivel', 1),
('JKL4G89', 'Honda',     'Civic',     2022, 'Cinza',   'intermediario',  30000, 1, 210.00, 'manutencao', 2),
('MNO5H01', 'Jeep',      'Compass',   2023, 'Branco',  'suv',            12000, 0, 280.00, 'disponivel', 3),
('PQR6I23', 'BMW',       'X1',        2024, 'Azul',    'luxo',            3000, 0, 450.00, 'disponivel', 4),
('STU7J45', 'Chevrolet', 'Onix',      2023, 'Prata',   'economico',      18000, 0, 120.00, 'disponivel', 4),
('VWX8K67', 'Toyota',    'Corolla',   2023, 'Branco',  'intermediario',  16000, 0, 215.00, 'disponivel', 5),
('YZA9L89', 'Jeep',      'Renegade',  2022, 'Preto',   'suv',            25000, 0, 260.00, 'reservado', 5);

-- Cliente de teste (senha: "senha123").
-- IMPORTANTE: o hash abaixo é apenas ilustrativo. Gere um hash real do seu
-- ambiente PHP com: php backend/utils/gerar_hash.php senha123
-- e substitua o valor antes de usar em um ambiente de teste real.
INSERT INTO clientes (nome, cpf, cnh, email, senha_hash, telefone, endereco, id_loja_padrao) VALUES
('Maria Souza', '123.456.789-00', '01234567890', 'maria.souza@email.com',
 '$2y$10$ZAGRhNUBxDSIUfJn7NM1ku2X50zYEe4H3xUPvX1S1tgIsz3EYJQnC', -- gerar novamente com password_hash()
 '(21) 99999-0000', 'Rua das Flores, 45 - Rio de Janeiro/RJ', 1);
