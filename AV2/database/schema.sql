SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";
SET NAMES utf8mb4;

CREATE DATABASE IF NOT EXISTS `falls_car`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `falls_car`;

CREATE TABLE `usuarios` (
    `id`         INT AUTO_INCREMENT PRIMARY KEY,
    `nome`       VARCHAR(100) NOT NULL,
    `email`      VARCHAR(100) UNIQUE NOT NULL,
    `senha`      VARCHAR(255) NOT NULL,   -- bcrypt hash
    `tipo`       ENUM('admin','funcionario','cliente') NOT NULL DEFAULT 'cliente',
    `ativo`      TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `tokens` (
    `id`         INT AUTO_INCREMENT PRIMARY KEY,
    `usuario_id` INT NOT NULL,
    `token`      VARCHAR(255) UNIQUE NOT NULL,
    `expires_at` TIMESTAMP NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`usuario_id`) REFERENCES `usuarios`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `lojas` (
    `id`        INT AUTO_INCREMENT PRIMARY KEY,
    `nome`      VARCHAR(100) NOT NULL,
    `endereco`  VARCHAR(200) NOT NULL,
    `cidade`    VARCHAR(100) NOT NULL,
    `estado`    VARCHAR(2) NOT NULL,
    `cep`       VARCHAR(9),
    `telefone`  VARCHAR(20),
    `tipo`      ENUM('aeroporto','cidade') NOT NULL DEFAULT 'cidade',
    `ativa`     TINYINT(1) NOT NULL DEFAULT 1,
    `latitude`  DECIMAL(10,8),
    `longitude` DECIMAL(11,8),
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `clientes` (
    `id`              INT AUTO_INCREMENT PRIMARY KEY,
    `usuario_id`      INT NOT NULL UNIQUE,
    `cpf`             VARCHAR(14) UNIQUE NOT NULL,
    `telefone`        VARCHAR(20),
    `data_nascimento` DATE,
    `endereco`        VARCHAR(200),
    `cidade`          VARCHAR(100),
    `estado`          VARCHAR(2),
    `cep`             VARCHAR(9),
    `cnh`             VARCHAR(20) NOT NULL,
    `validade_cnh`    DATE,
    `created_at`      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at`      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`usuario_id`) REFERENCES `usuarios`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `administradores` (
    `id`         INT AUTO_INCREMENT PRIMARY KEY,
    `usuario_id` INT NOT NULL UNIQUE,
    `cargo`      VARCHAR(100),
    `loja_id`    INT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`usuario_id`) REFERENCES `usuarios`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`loja_id`) REFERENCES `lojas`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `veiculos` (
    `id`                    INT AUTO_INCREMENT PRIMARY KEY,
    `placa`                 VARCHAR(10) UNIQUE NOT NULL,
    `modelo`                VARCHAR(100) NOT NULL,
    `marca`                 VARCHAR(100) NOT NULL,
    `ano`                   INT NOT NULL,
    `cor`                   VARCHAR(50),
    `categoria`             ENUM('economico','compacto','sedan','suv','luxo','pickup') NOT NULL DEFAULT 'economico',
    `quilometragem`         INT NOT NULL DEFAULT 0,
    `status`                ENUM('livre','alugado','reservado','manutencao') NOT NULL DEFAULT 'livre',
    `loja_id`               INT NOT NULL,
    `preco_diaria_7`        DECIMAL(10,2) NOT NULL,
    `preco_diaria_15`       DECIMAL(10,2) NOT NULL,
    `preco_diaria_30`       DECIMAL(10,2) NOT NULL,
    `custo_motorista_dia`   DECIMAL(10,2) NOT NULL DEFAULT 80.00,
    `imagem_url`            VARCHAR(255),
    `transmissao`           ENUM('manual','automatico') NOT NULL DEFAULT 'automatico',
    `combustivel`           ENUM('gasolina','etanol','flex','diesel','eletrico') NOT NULL DEFAULT 'flex',
    `ar_condicionado`       TINYINT(1) NOT NULL DEFAULT 1,
    `capacidade_passageiros` INT NOT NULL DEFAULT 5,
    `ativo`                 TINYINT(1) NOT NULL DEFAULT 1,
    `created_at`            TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at`            TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`loja_id`) REFERENCES `lojas`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `motoristas` (
    `id`           INT AUTO_INCREMENT PRIMARY KEY,
    `nome`         VARCHAR(100) NOT NULL,
    `cpf`          VARCHAR(14) UNIQUE NOT NULL,
    `cnh`          VARCHAR(20) NOT NULL,
    `validade_cnh` DATE,
    `telefone`     VARCHAR(20),
    `email`        VARCHAR(100),
    `ativo`        TINYINT(1) NOT NULL DEFAULT 1,
    `created_at`   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at`   TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `reservas` (
    `id`                    INT AUTO_INCREMENT PRIMARY KEY,
    `cliente_id`            INT NOT NULL,
    `veiculo_id`            INT NOT NULL,
    `loja_retirada_id`      INT NOT NULL,
    `loja_devolucao_id`     INT NOT NULL,
    `motorista_id`          INT,
    `periodo_dias`          ENUM('7','15','30') NOT NULL,
    `data_retirada_prevista` DATE NOT NULL,
    `data_devolucao_prevista` DATE NOT NULL,
    `canal`                 ENUM('internet','telefone','loja') NOT NULL DEFAULT 'internet',
    `status`                ENUM('pendente','confirmada','ativa','concluida','cancelada') NOT NULL DEFAULT 'pendente',
    `valor_veiculo`         DECIMAL(10,2) NOT NULL,
    `valor_motorista`       DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `valor_total`           DECIMAL(10,2) NOT NULL,
    `observacoes`           TEXT,
    `motivo_cancelamento`   TEXT,
    `data_cancelamento`     TIMESTAMP NULL,
    `created_at`            TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at`            TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`cliente_id`)        REFERENCES `clientes`(`id`),
    FOREIGN KEY (`veiculo_id`)        REFERENCES `veiculos`(`id`),
    FOREIGN KEY (`loja_retirada_id`)  REFERENCES `lojas`(`id`),
    FOREIGN KEY (`loja_devolucao_id`) REFERENCES `lojas`(`id`),
    FOREIGN KEY (`motorista_id`)      REFERENCES `motoristas`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `locacoes` (
    `id`                   INT AUTO_INCREMENT PRIMARY KEY,
    `reserva_id`           INT NOT NULL UNIQUE,
    `data_retirada_real`   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `data_devolucao_real`  TIMESTAMP NULL,
    `quilometragem_inicial` INT NOT NULL DEFAULT 0,
    `quilometragem_final`   INT,
    `status`               ENUM('ativa','concluida') NOT NULL DEFAULT 'ativa',
    `observacoes`          TEXT,
    `created_at`           TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at`           TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`reserva_id`) REFERENCES `reservas`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `pagamentos` (
    `id`                    INT AUTO_INCREMENT PRIMARY KEY,
    `reserva_id`            INT NOT NULL,
    `valor`                 DECIMAL(10,2) NOT NULL,
    `metodo`                ENUM('cartao_credito') NOT NULL DEFAULT 'cartao_credito',
    `status`                ENUM('pendente','aprovado','recusado','reembolsado','reembolso_parcial') NOT NULL DEFAULT 'pendente',
    `numero_cartao_mascarado` VARCHAR(20),
    `nome_titular`          VARCHAR(100),
    `data_pagamento`        TIMESTAMP NULL,
    `data_reembolso`        TIMESTAMP NULL,
    `valor_reembolso`       DECIMAL(10,2),
    `transacao_id`          VARCHAR(100),
    `created_at`            TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at`            TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`reserva_id`) REFERENCES `reservas`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE INDEX `idx_veiculos_status`    ON `veiculos`(`status`);
CREATE INDEX `idx_veiculos_loja`      ON `veiculos`(`loja_id`);
CREATE INDEX `idx_reservas_status`    ON `reservas`(`status`);
CREATE INDEX `idx_reservas_cliente`   ON `reservas`(`cliente_id`);
CREATE INDEX `idx_reservas_datas`     ON `reservas`(`data_retirada_prevista`, `data_devolucao_prevista`);
CREATE INDEX `idx_pagamentos_reserva` ON `pagamentos`(`reserva_id`);
CREATE INDEX `idx_tokens_token`       ON `tokens`(`token`);
