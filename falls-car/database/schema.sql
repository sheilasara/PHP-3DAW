-- =====================================================================
-- Falls Car - Sistema de Locação de Veículos
-- Modelagem do Banco de Dados (Área do Cliente)
-- Baseado no minimundo "XptoTec" e no documento de Requisitos do Sistema
-- =====================================================================
-- DECISÕES DE PROJETO (documentadas por ambiguidade do minimundo):
--
-- 1) O minimundo prevê usuários "Administrador" e "Cliente", mas o escopo
--    solicitado pelo professor é APENAS a área do cliente. Por isso não
--    foi criada a tabela "usuarios" genérica de administração; a
--    autenticação é feita diretamente na tabela "clientes".
--
-- 2) "Alocação por proximidade e mesma cidade" foi interpretada como:
--    o sistema somente lista/permite reservar veículos que estejam em
--    lojas localizadas na MESMA CIDADE escolhida pelo cliente para a
--    retirada (não há, no minimundo, uma métrica de distância definida
--    entre cidades diferentes).
--
-- 3) "Devolução na mesma cidade" foi modelada exigindo que a loja de
--    devolução tenha a mesma cidade da loja de retirada (podendo ser
--    uma loja diferente, desde que na mesma cidade).
--
-- 4) Períodos de locação são fixos em 7, 15 ou 30 dias (ENUM/CHECK).
--
-- 5) "Locação só inicia após retirada do veículo" foi modelado com uma
--    tabela separada "locacoes", que só passa a existir/ser preenchida
--    quando o cliente confirma a retirada física do carro. Até lá, o
--    registro em "reservas" representa apenas a intenção/reserva.
--
-- 6) Pagamento antecipado obrigatório: uma reserva só muda de
--    'pendente_pagamento' para 'confirmada' após o registro de um
--    pagamento com status 'aprovado' cobrindo o valor total da reserva.
--
-- 7) Cancelamento até 24h antes: validado em nível de aplicação
--    (PHP), comparando a data/hora atual com data_inicio_prevista.
-- =====================================================================

CREATE DATABASE IF NOT EXISTS falls_car CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE falls_car;

CREATE TABLE lojas (
    id_loja        INT AUTO_INCREMENT PRIMARY KEY,
    nome           VARCHAR(100)    NOT NULL,
    cidade         VARCHAR(100)    NOT NULL,
    estado         CHAR(2)         NOT NULL,
    endereco       VARCHAR(200)    NOT NULL,
    telefone       VARCHAR(20),
    criado_em      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE clientes (
    id_cliente      INT AUTO_INCREMENT PRIMARY KEY,
    nome            VARCHAR(150)    NOT NULL,
    cpf             VARCHAR(14)     NOT NULL UNIQUE,
    cnh             VARCHAR(20)     NOT NULL,
    email           VARCHAR(150)    NOT NULL UNIQUE,
    senha_hash      VARCHAR(255)    NOT NULL,
    telefone        VARCHAR(20),
    endereco        VARCHAR(200),
    id_loja_padrao  INT             NULL COMMENT 'Loja de preferência/retirada padrão do cliente',
    criado_em       DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_cliente_loja FOREIGN KEY (id_loja_padrao) REFERENCES lojas(id_loja)
        ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE sessoes (
    id_sessao       INT AUTO_INCREMENT PRIMARY KEY,
    id_cliente      INT             NOT NULL,
    token           VARCHAR(64)     NOT NULL UNIQUE,
    criado_em       DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    expira_em       DATETIME        NOT NULL,
    CONSTRAINT fk_sessao_cliente FOREIGN KEY (id_cliente) REFERENCES clientes(id_cliente)
        ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE veiculos (
    id_veiculo      INT AUTO_INCREMENT PRIMARY KEY,
    placa           VARCHAR(10)     NOT NULL UNIQUE,
    marca           VARCHAR(50)     NOT NULL,
    modelo          VARCHAR(80)     NOT NULL,
    ano             SMALLINT        NOT NULL,
    cor             VARCHAR(30)     NOT NULL,
    categoria       ENUM('economico','intermediario','suv','luxo') NOT NULL DEFAULT 'economico',
    quilometragem   INT             NOT NULL DEFAULT 0,
    necessita_revisao TINYINT(1)    NOT NULL DEFAULT 0,
    valor_diaria    DECIMAL(10,2)   NOT NULL,
    status          ENUM('disponivel','reservado','alugado','manutencao') NOT NULL DEFAULT 'disponivel',
    id_loja         INT             NOT NULL COMMENT 'Loja onde o veículo está atualmente',
    criado_em       DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_veiculo_loja FOREIGN KEY (id_loja) REFERENCES lojas(id_loja)
        ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE reservas (
    id_reserva          INT AUTO_INCREMENT PRIMARY KEY,
    id_cliente          INT             NOT NULL,
    id_veiculo          INT             NOT NULL,
    id_loja_retirada    INT             NOT NULL,
    id_loja_devolucao   INT             NOT NULL,
    periodo_dias        ENUM('7','15','30') NOT NULL,
    data_inicio_prevista  DATETIME      NOT NULL COMMENT 'Data/hora prevista de retirada',
    data_fim_prevista     DATETIME      NOT NULL COMMENT 'Calculada = inicio + periodo_dias',
    valor_diaria_aplicada DECIMAL(10,2) NOT NULL,
    valor_motorista_extra DECIMAL(10,2) NOT NULL DEFAULT 0,
    valor_total          DECIMAL(10,2)  NOT NULL,
    status              ENUM('pendente_pagamento','confirmada','em_andamento','concluida','cancelada')
                        NOT NULL DEFAULT 'pendente_pagamento',
    motivo_cancelamento VARCHAR(255)    NULL,
    criado_em           DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em       DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_reserva_cliente  FOREIGN KEY (id_cliente) REFERENCES clientes(id_cliente),
    CONSTRAINT fk_reserva_veiculo  FOREIGN KEY (id_veiculo) REFERENCES veiculos(id_veiculo),
    CONSTRAINT fk_reserva_loja_ret FOREIGN KEY (id_loja_retirada) REFERENCES lojas(id_loja),
    CONSTRAINT fk_reserva_loja_dev FOREIGN KEY (id_loja_devolucao) REFERENCES lojas(id_loja)
) ENGINE=InnoDB;

CREATE TABLE motoristas_adicionais (
    id_motorista    INT AUTO_INCREMENT PRIMARY KEY,
    id_reserva      INT             NOT NULL,
    nome            VARCHAR(150)    NOT NULL,
    cpf             VARCHAR(14)     NOT NULL,
    cnh             VARCHAR(20)     NOT NULL,
    valor_extra     DECIMAL(10,2)   NOT NULL DEFAULT 50.00,
    criado_em       DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_motorista_reserva FOREIGN KEY (id_reserva) REFERENCES reservas(id_reserva)
        ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE pagamentos (
    id_pagamento    INT AUTO_INCREMENT PRIMARY KEY,
    id_reserva      INT             NOT NULL,
    valor           DECIMAL(10,2)   NOT NULL,
    forma_pagamento ENUM('cartao_credito','cartao_debito','pix') NOT NULL,
    status          ENUM('pendente','aprovado','recusado','estornado') NOT NULL DEFAULT 'pendente',
    data_pagamento  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_pagamento_reserva FOREIGN KEY (id_reserva) REFERENCES reservas(id_reserva)
        ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE locacoes (
    id_locacao          INT AUTO_INCREMENT PRIMARY KEY,
    id_reserva          INT             NOT NULL UNIQUE,
    data_retirada_real  DATETIME        NOT NULL,
    data_devolucao_real DATETIME        NULL,
    km_retirada         INT             NOT NULL DEFAULT 0,
    km_devolucao        INT             NULL,
    status              ENUM('em_andamento','finalizada') NOT NULL DEFAULT 'em_andamento',
    criado_em           DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_locacao_reserva FOREIGN KEY (id_reserva) REFERENCES reservas(id_reserva)
        ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE INDEX idx_veiculos_status_loja ON veiculos(status, id_loja);
CREATE INDEX idx_reservas_cliente ON reservas(id_cliente, status);
CREATE INDEX idx_lojas_cidade ON lojas(cidade);
