-- Criar o banco de dados
CREATE DATABASE IF NOT EXISTS projeto_estacionamento
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_general_ci;

-- Selecionar o banco de dados para uso
USE projeto_estacionamento;

-- Criar a tabela "acessos_liberados"
CREATE TABLE IF NOT EXISTS acessos_liberados (
    id INT(11) NOT NULL AUTO_INCREMENT,
    nome VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
    matricula VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
    tipo VARCHAR(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
    localizacao VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
    data_cadastro DATETIME DEFAULT CURRENT_TIMESTAMP(),
    usuario_cadastro VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
    PRIMARY KEY (id)
);

-- Criar a tabela "motoristas_oficiais"
CREATE TABLE IF NOT EXISTS motoristas_oficiais (
    id INT(11) NOT NULL AUTO_INCREMENT,
    nome VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
    matricula VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
    foto VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
    data_cadastro DATETIME DEFAULT CURRENT_TIMESTAMP(),
    usuario_cadastro VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
    PRIMARY KEY (id)
);

-- Criar a tabela "ocorrencias"
CREATE TABLE IF NOT EXISTS ocorrencias (
    id INT(11) NOT NULL AUTO_INCREMENT,
    placa VARCHAR(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
    ocorrencia TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
    horario DATETIME NOT NULL,
    usuario VARCHAR(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
    localizacao VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
    PRIMARY KEY (id)
);

-- Criar a tabela "registro_veiculos"
CREATE TABLE IF NOT EXISTS registro_veiculos (
    id INT(11) NOT NULL AUTO_INCREMENT,
    placa VARCHAR(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
    marca VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
    modelo VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
    cor VARCHAR(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
    tipo VARCHAR(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
    motorista_entrada VARCHAR(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
    motorista_saida VARCHAR(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
    horario_entrada DATETIME NOT NULL,
    horario_saida DATETIME DEFAULT NULL,
    usuario_logado VARCHAR(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
    usuario_saida VARCHAR(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
    localizacao VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
    qtd_passageiros INT(11) NOT NULL DEFAULT 0,
    qtd_passageiros_saida INT(11) NOT NULL DEFAULT 0,
    PRIMARY KEY (id)
);

-- Criar a tabela "usuarios"
CREATE TABLE IF NOT EXISTS usuarios (
    id INT(11) NOT NULL AUTO_INCREMENT,
    nome VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
    matricula VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
    senha VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
    perfil ENUM('administrador', 'vigilante') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
    data_cadastro DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP(),
    cadastrado_por VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
    PRIMARY KEY (id)
);

-- Criar a tabela "vagas"
CREATE TABLE IF NOT EXISTS vagas (
    id INT(11) NOT NULL AUTO_INCREMENT,
    nome VARCHAR(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
    vagas_particulares INT(11) NOT NULL DEFAULT 0,
    vagas_oficiais INT(11) NOT NULL DEFAULT 0,
    vagas_motos INT(11) NOT NULL DEFAULT 0,
    PRIMARY KEY (id)
);

-- Criar a tabela "veiculos"
CREATE TABLE IF NOT EXISTS veiculos (
    id INT(11) NOT NULL AUTO_INCREMENT,
    placa VARCHAR(11) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
    modelo VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
    cor VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
    tipo ENUM('OFICIAL', 'PARTICULAR', 'MOTO') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
    marca VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
    localizacao VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
    nome VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
    acesso_id INT(11) DEFAULT NULL,
    PRIMARY KEY (id)
);
