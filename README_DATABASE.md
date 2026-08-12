# Configuração do Banco de Dados

## 🔧 Criar Banco de Dados e Usuário

### Opção 1: Usando o script SQL (Recomendado)

Execute o script SQL diretamente no MySQL:

```bash
mysql -u root -p < scripts/create-database.sql
```

Você será solicitado a inserir a senha do root do MySQL.

### Opção 2: Usando o script bash

Execute o script bash (solicitará senha do root):

```bash
./scripts/create-database.sh
```

### Opção 3: Manualmente via MySQL

Conecte-se ao MySQL como root:

```bash
mysql -u root -p
```

Depois execute:

```sql
-- Criar o banco de dados
CREATE DATABASE IF NOT EXISTS `oracle_tgc` 
    CHARACTER SET utf8mb4 
    COLLATE utf8mb4_unicode_ci;

-- Criar o usuário
CREATE USER IF NOT EXISTS 'oracle_tgc'@'localhost' IDENTIFIED BY 'oracle_tgc';

-- Dar permissões
GRANT ALL PRIVILEGES ON `oracle_tgc`.* TO 'oracle_tgc'@'localhost';

-- Aplicar mudanças
FLUSH PRIVILEGES;
```

## ✅ Verificar se foi criado

Teste a conexão:

```bash
mysql -u oracle_tgc -poracle_tgc -e "USE oracle_tgc; SHOW TABLES;"
```

## 📋 Credenciais Configuradas

- **Host:** 127.0.0.1
- **Port:** 3306
- **Database:** oracle_tgc
- **Username:** oracle_tgc
- **Password:** oracle_tgc

## 🚀 Após criar o banco

1. Execute as migrations do Doctrine:

```bash
php bin/console doctrine:migrations:migrate
```

2. Ou crie o schema diretamente:

```bash
php bin/console doctrine:schema:create
```

## ⚠️ Problemas Comuns

### Erro: "Access denied for user 'root'@'localhost'"

- Verifique se você tem a senha correta do root
- Tente: `sudo mysql` (sem senha no Ubuntu/Debian)

### Erro: "Access denied for user 'oracle_tgc'@'localhost'"

- O usuário não foi criado corretamente
- Execute novamente o script de criação
- Verifique se o usuário existe: `SELECT User, Host FROM mysql.user WHERE User='oracle_tgc';`

### Erro: "Unknown database 'oracle_tgc'"

- O banco não foi criado
- Execute o script de criação novamente


