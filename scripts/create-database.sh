#!/bin/bash

# Script para criar o banco de dados oracle_tgc
# Este script cria o banco de dados e o usuário se não existirem

echo "🗄️  Criando banco de dados oracle_tgc..."

# Verificar se o MySQL está rodando
if ! systemctl is-active --quiet mariadb && ! systemctl is-active --quiet mysql; then
    echo "❌ MySQL/MariaDB não está rodando!"
    echo "   Inicie o serviço com: sudo systemctl start mariadb"
    exit 1
fi

# Tentar conectar e criar o banco
mysql -u root -p <<EOF
-- Criar o banco de dados se não existir
CREATE DATABASE IF NOT EXISTS \`oracle_tgc\` 
    CHARACTER SET utf8mb4 
    COLLATE utf8mb4_unicode_ci;

-- Criar o usuário se não existir
CREATE USER IF NOT EXISTS 'oracle_tgc'@'localhost' IDENTIFIED BY 'oracle_tgc';

-- Dar todas as permissões no banco oracle_tgc
GRANT ALL PRIVILEGES ON \`oracle_tgc\`.* TO 'oracle_tgc'@'localhost';

-- Aplicar as mudanças
FLUSH PRIVILEGES;

-- Mostrar confirmação
SELECT 'Banco de dados e usuário criados com sucesso!' AS Status;
EOF

if [ $? -eq 0 ]; then
    echo "✅ Banco de dados e usuário criados com sucesso!"
    echo ""
    echo "📋 Credenciais:"
    echo "   Host: 127.0.0.1"
    echo "   Port: 3306"
    echo "   Database: oracle_tgc"
    echo "   Username: oracle_tgc"
    echo "   Password: oracle_tgc"
    echo ""
    echo "🔍 Testando conexão..."
    mysql -u oracle_tgc -poracle_tgc -e "USE oracle_tgc; SELECT 'Conexão OK!' AS Status;" 2>&1
else
    echo "❌ Erro ao criar banco de dados."
    echo "   Verifique se você tem permissões de root no MySQL."
    exit 1
fi


