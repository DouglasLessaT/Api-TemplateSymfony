-- Script para criar o banco de dados e usuário oracle_tgc
-- Execute com: mysql -u root -p < scripts/create-database.sql

-- Criar o banco de dados se não existir
CREATE DATABASE IF NOT EXISTS `oracle_tgc` 
    CHARACTER SET utf8mb4 
    COLLATE utf8mb4_unicode_ci;

-- Criar o usuário se não existir
CREATE USER IF NOT EXISTS 'oracle_tgc'@'localhost' IDENTIFIED BY 'oracle_tgc';

-- Dar todas as permissões no banco oracle_tgc
GRANT ALL PRIVILEGES ON `oracle_tgc`.* TO 'oracle_tgc'@'localhost';

-- Aplicar as mudanças
FLUSH PRIVILEGES;

-- Mostrar confirmação
SELECT 'Banco de dados e usuário criados com sucesso!' AS Status;


