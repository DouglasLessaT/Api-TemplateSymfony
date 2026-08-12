# Resumo da Implementação - API OracleTGC

## ✅ O que foi criado

### 1. Entidades do Domínio

#### User (`src/Domain/Entity/User.php`)
- Tipos: `free`, `premium`, `admin`
- Sistema de autenticação com hash de senha
- Controle de limite de cards escaneados (7 por anúncio para usuários gratuitos)
- Métodos de verificação de permissões:
  - `canScanCard()` - Verifica se pode escanear mais cards
  - `canCreateCollections()` - Verifica se pode criar coleções
  - `canCreateDecks()` - Verifica se pode criar decks
  - `canManageUsers()` - Verifica se pode gerenciar usuários (admin)
  - `canGenerateReports()` - Verifica se pode gerar relatórios (admin)

### 2. Repositórios

- `UserRepository` - Gerenciamento de usuários
- `InventoryRepository` - Gerenciamento de inventários
- `CollectionRepository` - Gerenciamento de coleções
- `DeckRepository` - Gerenciamento de decks

### 3. Serviços

#### AuthService
- `register()` - Registro de novos usuários
- `login()` - Autenticação e geração de token JWT
- `validateToken()` - Validação de tokens JWT
- `refreshToken()` - Renovação de tokens

#### UserService
- `findById()` - Busca usuário por ID
- `findByEmail()` - Busca usuário por email
- `update()` - Atualização de dados do usuário
- `changeUserType()` - Alteração de tipo (admin)
- `toggleActive()` - Ativar/desativar usuário (admin)
- `delete()` - Deletar usuário (admin)

#### InventoryService
- `getOrCreateInventory()` - Cria ou retorna inventário do usuário
- `addCard()` - Adiciona card ao inventário
- `removeCard()` - Remove card do inventário
- `updateCardQuantity()` - Atualiza quantidade de um card

#### CollectionService (Premium)
- `create()` - Cria nova coleção
- `update()` - Atualiza coleção
- `addCard()` - Adiciona card à coleção
- `removeCard()` - Remove card da coleção
- `delete()` - Deleta coleção

#### DeckService (Premium)
- `create()` - Cria novo deck
- `update()` - Atualiza deck
- `addCard()` - Adiciona card ao deck
- `removeCard()` - Remove card do deck
- `validateDeck()` - Valida deck conforme regras do jogo
- `delete()` - Deleta deck

### 4. Controllers da API

#### AuthController (`/api/auth`)
- `POST /api/auth/register` - Registro
- `POST /api/auth/login` - Login
- `POST /api/auth/refresh` - Renovar token

#### UserController (`/api/users`)
- `GET /api/users/me` - Perfil do usuário autenticado
- `PUT /api/users/me` - Atualizar próprio perfil
- `GET /api/users` - Listar usuários (admin)
- `GET /api/users/{id}` - Buscar usuário
- `PUT /api/users/{id}` - Atualizar usuário (admin)
- `DELETE /api/users/{id}` - Deletar usuário (admin)

#### InventoryController (`/api/inventory`)
- `GET /api/inventory` - Obter inventário
- `POST /api/inventory/cards` - Adicionar card
- `DELETE /api/inventory/cards/{cardId}` - Remover card
- `PUT /api/inventory/cards/{cardId}` - Atualizar quantidade
- `GET /api/inventory/statistics` - Estatísticas

#### CollectionController (`/api/collections`) - Premium
- `GET /api/collections` - Listar coleções
- `POST /api/collections` - Criar coleção
- `GET /api/collections/{id}` - Buscar coleção
- `PUT /api/collections/{id}` - Atualizar coleção
- `DELETE /api/collections/{id}` - Deletar coleção
- `POST /api/collections/{id}/cards` - Adicionar card
- `DELETE /api/collections/{id}/cards/{cardId}` - Remover card

#### DeckController (`/api/decks`) - Premium
- `GET /api/decks` - Listar decks
- `POST /api/decks` - Criar deck
- `GET /api/decks/{id}` - Buscar deck
- `PUT /api/decks/{id}` - Atualizar deck
- `DELETE /api/decks/{id}` - Deletar deck
- `POST /api/decks/{id}/cards` - Adicionar card
- `DELETE /api/decks/{id}/cards/{cardId}` - Remover card
- `POST /api/decks/{id}/validate` - Validar deck

### 5. Segurança

#### JWTManager (`src/Core/Infrastructure/Security/JWTManager.php`)
- Geração de tokens JWT
- Validação de tokens
- Decodificação de tokens
- Verificação de expiração

#### JWTAuthenticator (`src/Core/Infrastructure/Security/JWTAuthenticator.php`)
- Autenticador customizado para Symfony Security
- Extração de token do header Authorization
- Validação automática em todas as requisições

#### Configuração de Segurança
- `config/packages/security.yaml` - Configuração do firewall
- Endpoints públicos: `/api/auth/*`
- Endpoints protegidos: Todos os outros `/api/*`

### 6. Configurações

#### services.yaml
- Configuração do JWTManager com parâmetros
- Autowiring de serviços e repositórios
- Configuração de dependências

#### composer.json
- Adicionado `firebase/php-jwt` para JWT
- Adicionado `symfony/security-bundle` para autenticação

### 7. Documentação

- `API_DOCUMENTATION.md` - Documentação completa da API
- `.env.example` - Exemplo de variáveis de ambiente

## 🔑 Funcionalidades Implementadas

### Usuário Gratuito
✅ Registro e login com JWT
✅ Inventário com uma coleção por tipo de jogo (Magic, Pokémon, One Piece)
✅ Limite de 7 cards escaneados por anúncio (resetado a cada 24h)
✅ Visualização de estatísticas do inventário

### Usuário Premium
✅ Todas as funcionalidades do usuário gratuito
✅ Sem limite de cards escaneados
✅ Criação de múltiplas coleções por edição
✅ Criação de decks
✅ Validação de decks conforme regras do jogo

### Administrador
✅ Todas as funcionalidades do Premium
✅ Gerenciamento de usuários (listar, atualizar, deletar)
✅ Alteração de tipo de usuário
✅ Ativação/desativação de usuários
✅ Geração de relatórios (estrutura criada)

## 📊 Estrutura de Dados

### User
- id (UUID)
- email (único)
- password (hasheado)
- name
- type (free/premium/admin)
- isActive
- lastLoginAt
- scannedCardsCount
- lastResetAt

### Inventory
- id (UUID)
- userId
- name
- description
- items (InventoryItem[])
- collections (Collection[])
- decks (Deck[])

### Collection (Premium)
- id (UUID)
- inventory
- name
- game
- setCode
- setName
- cards (Card[])
- targetCount
- isComplete

### Deck (Premium)
- id (UUID)
- inventory
- name
- game
- format
- description
- cards (DeckCard[])
- isLegal
- validationErrors

## 🔐 Segurança Implementada

1. **Autenticação JWT**
   - Tokens com expiração configurável
   - Validação automática em todas as requisições
   - Refresh token para renovação

2. **Hash de Senhas**
   - Uso de `password_hash()` com `PASSWORD_BCRYPT`
   - Verificação com `password_verify()`

3. **Autorização**
   - Verificação de permissões por tipo de usuário
   - Validação de propriedade de recursos
   - Controle de acesso em todos os endpoints

4. **Validação**
   - Validação de dados de entrada
   - Tratamento de exceções de domínio
   - Respostas padronizadas de erro

## 🚀 Próximos Passos

1. **Executar Migrations**
   ```bash
   php bin/console doctrine:migrations:diff
   php bin/console doctrine:migrations:migrate
   ```

2. **Configurar Variáveis de Ambiente**
   - Copiar `.env.example` para `.env.local`
   - Configurar `DATABASE_URL`
   - Gerar `JWT_SECRET_KEY`

3. **Instalar Dependências**
   ```bash
   composer install
   ```

4. **Testar API**
   - Registrar um usuário
   - Fazer login
   - Testar endpoints protegidos

## 📝 Notas Técnicas

- **Symfony 6.4**: Framework PHP utilizado
- **Doctrine ORM**: Gerenciamento de banco de dados
- **JWT**: Autenticação stateless
- **MySQL**: Banco de dados (configurável)
- **Arquitetura**: DDD (Domain-Driven Design)
- **Padrões**: Repository, Service, Controller

## ✨ Melhorias Futuras

1. Implementar rate limiting por usuário
2. Adicionar cache Redis para tokens
3. Implementar refresh token rotation
4. Adicionar logs de auditoria
5. Implementar relatórios para admin
6. Adicionar testes unitários e de integração
7. Implementar busca avançada de cards
8. Adicionar suporte a imagens de cards

