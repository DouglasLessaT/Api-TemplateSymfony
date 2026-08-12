#!/bin/bash

# Script para visualizar a documentação do CORE
# Uso: ./view-docs.sh

cd "$(dirname "$0")/docs/api"

echo "🚀 Iniciando servidor de documentação..."
echo "📚 Acesse: http://localhost:8000"
echo "⏹️  Pressione Ctrl+C para parar"
echo ""

php -S localhost:8000
