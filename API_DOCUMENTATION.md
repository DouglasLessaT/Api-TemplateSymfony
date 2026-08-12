# Documentação da API OracleTGC

## 📋 Visão Geral

API REST desenvolvida em PHP Symfony 6.4 para gerenciamento de cards de jogos de cartas (Magic: The Gathering, Pokémon TCG, One Piece Card Game).

## 🔐 Autenticação

A API utiliza autenticação JWT (JSON Web Token). Para acessar endpoints protegidos, inclua o token no header:

```
Authorization: Bearer {seu_token_jwt}
```

### Endpoints de Autenticação

#### POST /api/auth/register
Registra um novo usuário.

**Request Body:**
```json
{
  "email": "usuario@example.com",
  "password": "senha123",
  "name": "Nome do Usuário"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Usuário registrado com sucesso",
  "data": {
    "id": "uuid",
    "email": "usuario@example.com",
    "name": "Nome do Usuário",
    "type": "free",
    ...
  }
}
```

#### POST /api/auth/login
Autentica um usuário e retorna o token JWT.

**Request Body:**
```json
{
  "email": "usuario@example.com",
  "password": "senha123"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Login realizado com sucesso",
  "data": {
    "token": "jwt_token_aqui",
    "user": {
      "id": "uuid",
      "email": "usuario@example.com",
      "name": "Nome do Usuário",
      "type": "free",
      ...
    }
  }
}
```

#### POST /api/auth/refresh
Renova o token JWT.

**Headers:**
```
Authorization: Bearer {token_atual}
```

## 👤 Usuários

### Tipos de Usuário

- **free**: Usuário gratuito
  - Limite de 7 cards escaneados por anúncio
  - Uma coleção por tipo de jogo (Magic, Pokémon, etc)
  - Não pode criar decks

- **premium**: Usuário premium
  - Sem limite de cards escaneados
  - Pode criar múltiplas coleções por edição
  - Pode criar decks

- **admin**: Administrador
  - Todas as funcionalidades do premium
  - Pode gerenciar usuários
  - Pode gerar relatórios

### Endpoints de Usuário

#### GET /api/users/me
Retorna informações do usuário autenticado.

#### PUT /api/users/me
Atualiza informações do usuário autenticado.

#### GET /api/users
Lista todos os usuários (apenas admin).

#### GET /api/users/{id}
Busca um usuário específico (apenas admin ou próprio perfil).

#### PUT /api/users/{id}
Atualiza um usuário (apenas admin).

#### DELETE /api/users/{id}
Deleta um usuário (apenas admin).

## 📦 Inventário

### GET /api/inventory
Retorna o inventário do usuário autenticado.

**Response:**
```json
{
  "success": true,
  "data": {
    "id": "uuid",
    "userId": "uuid",
    "name": "Meu Inventário",
    "statistics": {
      "totalCards": 150,
      "uniqueCards": 100,
      "byGame": {
        "mtg": { "cards": 50, "collections": 1, "decks": 0 },
        "pokemon": { "cards": 50, "collections": 1, "decks": 0 }
      }
    },
    "items": [...]
  }
}
```

### POST /api/inventory/cards
Adiciona um card ao inventário.

**Request Body:**
```json
{
  "cardId": "uuid_do_card",
  "quantity": 1,
  "metadata": {
    "condition": "Near Mint",
    "language": "pt-BR"
  }
}
```

**Nota:** Usuários gratuitos têm limite de 7 cards por anúncio.

### DELETE /api/inventory/cards/{cardId}
Remove um card do inventário.

### PUT /api/inventory/cards/{cardId}
Atualiza a quantidade de um card no inventário.

**Request Body:**
```json
{
  "quantity": 3
}
```

### GET /api/inventory/statistics
Retorna estatísticas do inventário.

## 🗂️ Coleções (Premium)

### GET /api/collections
Lista todas as coleções do usuário.

### POST /api/collections
Cria uma nova coleção.

**Request Body:**
```json
{
  "name": "Coleção Innistrad",
  "game": "mtg",
  "setCode": "ISD",
  "setName": "Innistrad",
  "description": "Minha coleção de Innistrad",
  "targetCount": 274
}
```

### GET /api/collections/{id}
Busca uma coleção específica.

### PUT /api/collections/{id}
Atualiza uma coleção.

### DELETE /api/collections/{id}
Deleta uma coleção.

### POST /api/collections/{id}/cards
Adiciona um card à coleção.

**Request Body:**
```json
{
  "cardId": "uuid_do_card"
}
```

### DELETE /api/collections/{id}/cards/{cardId}
Remove um card da coleção.

## 🎴 Decks (Premium)

### GET /api/decks
Lista todos os decks do usuário.

### POST /api/decks
Cria um novo deck.

**Request Body:**
```json
{
  "name": "Deck Azorius Control",
  "game": "mtg",
  "format": "Standard",
  "description": "Meu deck de controle"
}
```

### GET /api/decks/{id}
Busca um deck específico.

### PUT /api/decks/{id}
Atualiza um deck.

### DELETE /api/decks/{id}
Deleta um deck.

### POST /api/decks/{id}/cards
Adiciona um card ao deck.

**Request Body:**
```json
{
  "cardId": "uuid_do_card",
  "quantity": 4,
  "zone": "main"
}
```

**Zones:** `main` ou `sideboard`

### DELETE /api/decks/{id}/cards/{cardId}
Remove um card do deck.

**Query Parameters:**
- `zone`: `main` ou `sideboard` (padrão: `main`)

### POST /api/decks/{id}/validate
Valida um deck de acordo com as regras do jogo.

**Response:**
```json
{
  "success": true,
  "data": {
    "isLegal": true,
    "errors": [],
    "deck": {...}
  }
}
```

## 🃏 Cards

### GET /api/cards
Busca cards (já implementado no CardController existente).

### POST /api/cards/scan
Escaneia um card (já implementado no OCRController existente).

## ⚙️ Configuração

### Variáveis de Ambiente

Crie um arquivo `.env.local` com as seguintes variáveis:

```env
# Database
DATABASE_URL="mysql://user:password@127.0.0.1:3306/oracletgc?serverVersion=8.0&charset=utf8mb4"

# JWT
JWT_SECRET_KEY="sua_chave_secreta_jwt_aqui"
JWT_ALGORITHM="HS256"
JWT_EXPIRATION_TIME=3600

# APIs Externas (opcional)
POKEMON_TCG_API_KEY="sua_chave_api_pokemon"
ONEPIECE_API_KEY="sua_chave_api_onepiece"
```

### Instalação

1. Instale as dependências:
```bash
composer install
```

2. Configure o banco de dados no `.env.local`

3. Execute as migrations:
```bash
php bin/console doctrine:migrations:migrate
```

4. Gere uma chave secreta JWT:
```bash
php -r "echo bin2hex(random_bytes(32));"
```

5. Inicie o servidor:
```bash
symfony server:start
```

## 📝 Notas Importantes

1. **Limite de Cards Gratuitos**: Usuários gratuitos podem escanear apenas 7 cards por anúncio (resetado a cada 24 horas).

2. **Coleções e Decks**: Apenas usuários Premium podem criar coleções personalizadas e decks.

3. **Autenticação**: Todos os endpoints (exceto `/api/auth/*`) requerem autenticação JWT.

4. **Validação de Decks**: A API valida decks de acordo com as regras de cada jogo:
   - **MTG**: Mínimo 60 cards no deck principal, máximo 15 no sideboard
   - **Pokémon**: Exatamente 60 cards
   - **One Piece**: Exatamente 50 cards, incluindo 1 Leader

5. **Estrutura de Resposta**: Todas as respostas seguem o formato:
```json
{
  "success": true|false,
  "message": "Mensagem descritiva",
  "data": {...},
  "errors": [...] // Apenas em caso de erro
}
```

## 🔒 Segurança

- Senhas são hasheadas usando `password_hash()` com `PASSWORD_BCRYPT`
- Tokens JWT expiram após 1 hora (configurável)
- Validação de permissões em todos os endpoints
- CORS configurado via `nelmio/cors-bundle`

## 📚 Estrutura do Projeto

```
src/
├── Controller/Api/          # Controllers da API
├── Core/                    # Classes base e utilitários
├── Domain/Entity/           # Entidades do domínio
├── Repositories/            # Repositórios Doctrine
└── Service/                 # Serviços de negócio
```

## 🐛 Troubleshooting

### Token inválido
- Verifique se o token está sendo enviado no header `Authorization: Bearer {token}`
- Verifique se o token não expirou
- Verifique se a chave secreta JWT está configurada corretamente

### Erro de permissão
- Verifique se o usuário tem o tipo correto (free/premium/admin)
- Verifique se o usuário está autenticado

### Limite de cards atingido
- Usuários gratuitos têm limite de 7 cards por anúncio
- Faça upgrade para Premium para remover o limite

