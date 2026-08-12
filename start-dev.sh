#!/bin/bash

# Script para iniciar API e Frontend juntos
# Uso: ./start-dev.sh

set -e

# Cores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Diretório do script
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$SCRIPT_DIR"

# Carregar configurações
if [ ! -f "config.php" ]; then
    echo -e "${RED}❌ Arquivo config.php não encontrado!${NC}"
    exit 1
fi

# Função para parar todos os servidores
cleanup() {
    echo -e "\n${YELLOW}⏹️  Parando servidores...${NC}"
    
    # Parar PHP server (API)
    if [ ! -z "$API_PID" ]; then
        kill $API_PID 2>/dev/null || true
        echo -e "${GREEN}✓${NC} API parada"
    fi
    
    # Parar Frontend server
    if [ ! -z "$FRONTEND_PID" ]; then
        kill $FRONTEND_PID 2>/dev/null || true
        echo -e "${GREEN}✓${NC} Frontend parado"
    fi
    
    # Parar Docs server (se estiver rodando)
    if [ ! -z "$DOCS_PID" ]; then
        kill $DOCS_PID 2>/dev/null || true
        echo -e "${GREEN}✓${NC} Documentação parada"
    fi
    
    echo -e "${GREEN}✅ Todos os servidores foram parados${NC}"
    exit 0
}

# Capturar Ctrl+C
trap cleanup SIGINT SIGTERM

echo -e "${BLUE}╔════════════════════════════════════════╗${NC}"
echo -e "${BLUE}║  Oracle TGC - Dev Server              ║${NC}"
echo -e "${BLUE}╚════════════════════════════════════════╝${NC}"
echo ""

# Verificar se as portas estão disponíveis
check_port() {
    if lsof -Pi :$1 -sTCP:LISTEN -t >/dev/null 2>&1 ; then
        echo -e "${RED}❌ Porta $1 já está em uso!${NC}"
        echo -e "${YELLOW}   Execute: lsof -ti:$1 | xargs kill -9${NC}"
        return 1
    fi
    return 0
}

# Verificar porta da API (8000)
if ! check_port 8000; then
    exit 1
fi

# Verificar porta do Frontend (3000)
if ! check_port 3000; then
    exit 1
fi

# Configurar banco de dados automaticamente
echo -e "${BLUE}🗄️  Configurando banco de dados...${NC}"
cd "$SCRIPT_DIR"

# Executar script de setup do banco
SETUP_SCRIPT="$SCRIPT_DIR/scripts/setup-database.php"
if [ -f "$SETUP_SCRIPT" ]; then
    php "$SETUP_SCRIPT" 2>&1 | while IFS= read -r line; do
        if [[ "$line" == *"✅"* ]]; then
            echo -e "${GREEN}$line${NC}"
        elif [[ "$line" == *"❌"* ]] || [[ "$line" == *"Erro"* ]]; then
            echo -e "${RED}$line${NC}"
        elif [[ "$line" == *"⚠️"* ]] || [[ "$line" == *"ℹ️"* ]]; then
            echo -e "${YELLOW}$line${NC}"
        else
            echo -e "${BLUE}   $line${NC}"
        fi
    done
    SETUP_EXIT_CODE=${PIPESTATUS[0]}
    if [ $SETUP_EXIT_CODE -ne 0 ]; then
        echo -e "${YELLOW}⚠️  Setup do banco falhou, mas continuando...${NC}"
        echo -e "${YELLOW}   Você pode configurar o banco manualmente depois.${NC}"
    fi
else
    echo -e "${YELLOW}⚠️  Script de setup não encontrado: $SETUP_SCRIPT${NC}"
fi
echo ""

# Iniciar API Backend
echo -e "${BLUE}🚀 Iniciando API Backend...${NC}"
cd "$SCRIPT_DIR"
php -S localhost:8000 -t public > /dev/null 2>&1 &
API_PID=$!
sleep 1

if ps -p $API_PID > /dev/null; then
    echo -e "${GREEN}✅ API rodando em: http://localhost:8000${NC}"
else
    echo -e "${RED}❌ Falha ao iniciar API${NC}"
    exit 1
fi

# Iniciar Frontend
FRONTEND_DIR="../Web.OracleTGC"
if [ -d "$FRONTEND_DIR" ]; then
    echo -e "${BLUE}🎨 Iniciando Frontend...${NC}"
    cd "$FRONTEND_DIR"
    
    # Verificar se tem package.json (projeto Node)
    if [ -f "package.json" ]; then
        # Verificar se node_modules existe
        if [ ! -d "node_modules" ]; then
            echo -e "${YELLOW}📦 Instalando dependências...${NC}"
            npm install
        fi
        
        # Iniciar servidor de desenvolvimento
        npm run dev > /dev/null 2>&1 &
        FRONTEND_PID=$!
    else
        # Frontend estático (HTML/CSS/JS)
        php -S localhost:3000 > /dev/null 2>&1 &
        FRONTEND_PID=$!
    fi
    
    sleep 2
    
    if ps -p $FRONTEND_PID > /dev/null; then
        echo -e "${GREEN}✅ Frontend rodando em: http://localhost:3000${NC}"
    else
        echo -e "${YELLOW}⚠️  Frontend não iniciou (verifique configuração)${NC}"
        FRONTEND_PID=""
    fi
else
    echo -e "${YELLOW}⚠️  Diretório do frontend não encontrado: $FRONTEND_DIR${NC}"
fi

# Informações
echo ""
echo -e "${BLUE}╔════════════════════════════════════════╗${NC}"
echo -e "${BLUE}║           Servidores Ativos            ║${NC}"
echo -e "${BLUE}╠════════════════════════════════════════╣${NC}"
echo -e "${BLUE}║${NC} 🔧 API:      http://localhost:8000   ${BLUE}║${NC}"
echo -e "${BLUE}║${NC} 🎨 Frontend: http://localhost:3000   ${BLUE}║${NC}"
echo -e "${BLUE}║${NC} 📚 Docs:     docs/api/index.html     ${BLUE}║${NC}"
echo -e "${BLUE}╠════════════════════════════════════════╣${NC}"
echo -e "${BLUE}║${NC} Pressione ${RED}Ctrl+C${NC} para parar          ${BLUE}║${NC}"
echo -e "${BLUE}╚════════════════════════════════════════╝${NC}"
echo ""

# Manter o script rodando
wait
