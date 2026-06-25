USE `falls_car`;

INSERT INTO `usuarios` (`nome`, `email`, `senha`, `tipo`) VALUES
('Administrador Falls Car', 'admin@fallscar.com',    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uHeWG/igi', 'admin'),
('Carlos Funcionário',      'carlos@fallscar.com',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uHeWG/igi', 'funcionario'),
('Ana Silva',               'ana@email.com',        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uHeWG/igi', 'cliente'),
('Bruno Costa',             'bruno@email.com',      '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uHeWG/igi', 'cliente'),
('Fernanda Lima',           'fernanda@email.com',   '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uHeWG/igi', 'cliente');

INSERT INTO `administradores` (`usuario_id`, `cargo`) VALUES
(1, 'Administrador Geral'),
(2, 'Atendente de Loja');

INSERT INTO `lojas` (`nome`, `endereco`, `cidade`, `estado`, `telefone`, `tipo`, `latitude`, `longitude`) VALUES
('Falls Car - Galeão (GIG)',         'Av. 20 de Janeiro, s/n - Ilha do Governador', 'Rio de Janeiro', 'RJ', '(21) 3398-0000', 'aeroporto', -22.809, -43.243),
('Falls Car - Santos Dumont (SDU)',  'Praça Senador Salgado Filho, s/n - Centro',   'Rio de Janeiro', 'RJ', '(21) 3814-0000', 'aeroporto', -22.910, -43.163),
('Falls Car - Centro RJ',            'Av. Rio Branco, 156 - Centro',                'Rio de Janeiro', 'RJ', '(21) 2524-0000', 'cidade',    -22.907, -43.175),
('Falls Car - Guarulhos (GRU)',      'Rod. Hélio Smidt, s/n - Guarulhos',           'São Paulo',      'SP', '(11) 2445-0000', 'aeroporto', -23.432, -46.469),
('Falls Car - Congonhas (CGH)',      'Av. Washington Luís, s/n - Santo Amaro',      'São Paulo',      'SP', '(11) 5090-0000', 'aeroporto', -23.627, -46.656),
('Falls Car - Paulista',             'Av. Paulista, 1578 - Bela Vista',             'São Paulo',      'SP', '(11) 3266-0000', 'cidade',    -23.562, -46.655),
('Falls Car - Confins (CNF)',        'Rod. MG-10, Km 39 - Confins',                'Belo Horizonte', 'MG', '(31) 3689-0000', 'aeroporto', -19.624, -43.970),
('Falls Car - BH Centro',            'Av. Afonso Pena, 1500 - Centro',              'Belo Horizonte', 'MG', '(31) 3244-0000', 'cidade',    -19.920, -43.940);

UPDATE `administradores` SET `loja_id` = 1 WHERE `usuario_id` = 2;

INSERT INTO `clientes` (`usuario_id`, `cpf`, `telefone`, `data_nascimento`, `cidade`, `estado`, `cnh`, `validade_cnh`) VALUES
(3, '123.456.789-00', '(21) 98765-4321', '1990-05-15', 'Rio de Janeiro', 'RJ', 'CNH-RJ-12345678', '2028-05-15'),
(4, '987.654.321-00', '(21) 91234-5678', '1985-11-22', 'Rio de Janeiro', 'RJ', 'CNH-RJ-87654321', '2027-11-22'),
(5, '456.789.123-00', '(11) 99999-8888', '1995-03-10', 'São Paulo',      'SP', 'CNH-SP-45678912', '2029-03-10');

INSERT INTO `veiculos`
    (`placa`,`modelo`,`marca`,`ano`,`cor`,`categoria`,`quilometragem`,`status`,`loja_id`,
     `preco_diaria_7`,`preco_diaria_15`,`preco_diaria_30`,`custo_motorista_dia`,
     `transmissao`,`combustivel`,`ar_condicionado`,`capacidade_passageiros`,`imagem_url`) VALUES

('ABC-1234','Gol',      'Volkswagen',2022,'Branco',  'economico', 12000,'livre',     1, 89.00, 79.00, 69.00, 80.00,'automatico','flex',1,5, NULL),
('DEF-5678','Hb20',     'Hyundai',   2023,'Prata',   'compacto',   8500,'livre',     1, 99.00, 89.00, 79.00, 80.00,'automatico','flex',1,5, NULL),
('GHI-9012','Corolla',  'Toyota',    2023,'Preto',   'sedan',      5200,'reservado', 1,159.00,139.00,119.00, 80.00,'automatico','flex',1,5, NULL),

('JKL-3456','T-Cross',  'Volkswagen',2023,'Cinza',   'suv',        7800,'livre',     2,189.00,169.00,149.00, 80.00,'automatico','flex',1,5, NULL),
('MNO-7890','Compass',  'Jeep',      2022,'Branco',  'suv',       14300,'manutencao',2,199.00,179.00,159.00, 80.00,'automatico','flex',1,5, NULL),

('PQR-1357','Uno',      'Fiat',      2021,'Vermelho','economico', 22000,'livre',     3, 79.00, 69.00, 59.00, 80.00,'manual',    'flex',1,5, NULL),
('STU-2468','Argo',     'Fiat',      2023,'Azul',    'compacto',   3100,'livre',     3, 95.00, 85.00, 75.00, 80.00,'automatico','flex',1,5, NULL),

('VWX-3691','Onix Plus','Chevrolet', 2023,'Prata',   'sedan',      9600,'livre',     4,139.00,119.00, 99.00, 80.00,'automatico','flex',1,5, NULL),
('YZA-4802','Fastback', 'Fiat',      2022,'Preto',   'sedan',     11200,'livre',     4,149.00,129.00,109.00, 80.00,'automatico','flex',1,5, NULL),

('BCD-5913','Tracker',  'Chevrolet', 2023,'Branco',  'suv',        4700,'livre',     5,179.00,159.00,139.00, 80.00,'automatico','flex',1,5, NULL),

('EFG-6024','Caoa Chery Tiggo 5x','Caoa Chery',2022,'Cinza','suv',18500,'livre',  6,169.00,149.00,129.00, 80.00,'automatico','flex',1,5, NULL),

('HIJ-7135','Kwid',     'Renault',   2023,'Laranja', 'economico',  6300,'livre',     7, 85.00, 75.00, 65.00, 80.00,'manual',    'flex',1,5, NULL),
('KLM-8246','Duster',   'Renault',   2022,'Bege',    'suv',       16700,'livre',     7,175.00,155.00,135.00, 80.00,'automatico','flex',1,5, NULL),

('NOP-9357','Polo',     'Volkswagen',2023,'Branco',  'compacto',   2100,'livre',     8,105.00, 95.00, 85.00, 80.00,'automatico','flex',1,5, NULL);

INSERT INTO `motoristas` (`nome`, `cpf`, `cnh`, `validade_cnh`, `telefone`, `email`) VALUES
('Roberto Alves',   '111.222.333-44', 'CNH-MOT-001', '2026-12-31', '(21) 97777-6666', 'roberto.motorista@email.com'),
('Patricia Santos', '555.666.777-88', 'CNH-MOT-002', '2027-08-15', '(11) 96666-5555', 'patricia.motorista@email.com'),
('Joaquim Ferreira','999.000.111-22', 'CNH-MOT-003', '2025-05-20', '(31) 95555-4444', 'joaquim.motorista@email.com');

INSERT INTO `reservas`
    (`cliente_id`,`veiculo_id`,`loja_retirada_id`,`loja_devolucao_id`,
     `periodo_dias`,`data_retirada_prevista`,`data_devolucao_prevista`,
     `canal`,`status`,`valor_veiculo`,`valor_motorista`,`valor_total`) VALUES
(1, 1, 1, 1, '7',  CURDATE() + INTERVAL 2 DAY, CURDATE() + INTERVAL 9 DAY,  'internet','confirmada', 623.00, 0.00, 623.00),
(2, 4, 2, 2, '15', CURDATE() + INTERVAL 5 DAY, CURDATE() + INTERVAL 20 DAY, 'internet','confirmada', 2535.00, 1200.00, 3735.00);

INSERT INTO `pagamentos`
    (`reserva_id`,`valor`,`metodo`,`status`,`numero_cartao_mascarado`,`nome_titular`,`data_pagamento`,`transacao_id`) VALUES
(1, 623.00,  'cartao_credito','aprovado','**** **** **** 1234','Ana Silva',    NOW(), 'TXN-001-2024'),
(2, 3735.00, 'cartao_credito','aprovado','**** **** **** 5678','Bruno Costa',  NOW(), 'TXN-002-2024');

UPDATE `veiculos` SET `status` = 'reservado' WHERE `id` = 3;
