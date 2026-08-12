# README - Oracle Cards Viewer API

Backend API para o Oracle Cards Viewer, construído com Symfony 6.4 e arquitetura hexagonal.

## 🚀 Quick Start

### Iniciar Desenvolvimento (API + Frontend)

```bash
./start-dev.sh
```

Isso irá iniciar:
- **API**: http://localhost:8000
- **Frontend**: http://localhost:3000

### Apenas API

```bash
php -S localhost:8000 -t public
```

## 📦 Instalação

```bash
# Instalar dependências
composer install

# Configurar ambiente
cp .env .env.local
# Edite .env.local com suas configurações

# Iniciar servidor
./start-dev.sh
```

## 🏗️ Arquitetura

Este projeto segue a **Arquitetura Hexagonal** (Ports & Adapters):

```
src/Core/
├── Domain/              # Regras de negócio puras
│   ├── Entity/         # Entidades
│   ├── ValueObject/    # Value Objects
│   ├── Event/          # Domain Events
│   └── Exception/      # Exceções de domínio
│
├── Application/         # Casos de uso
│   ├── DTO/            # Data Transfer Objects
│   ├── Repository/     # Interfaces de repositório
│   ├── Result/         # Result pattern
│   └── Query/          # Paginação e queries
│
├── Infrastructure/      # Implementações técnicas
│   ├── Repository/     # Repositórios Doctrine
│   ├── Session/        # Gerenciamento de sessão
│   ├── Security/       # JWT e autenticação
│   └── Cache/          # Sistema de cache
│
├── Presentation/        # Camada de apresentação
│   ├── Controller/     # Controllers base
│   └── Response/       # Formatadores de resposta
│
└── Util/               # Utilitários
    ├── MathHelper.php
    ├── StringHelper.php
    ├── ArrayHelper.php
    ├── DateTimeHelper.php
    ├── ValidationHelper.php
    └── TradeHelper.php
```

## 📚 Documentação

- **[DEV_GUIDE.md](DEV_GUIDE.md)** - Guia completo de desenvolvimento
- **[CORE_EXAMPLES.md](CORE_EXAMPLES.md)** - Exemplos de uso do CORE
- **[docs/api/](docs/api/index.html)** - Documentação API (Doctum)
- **[config.php](config.php)** - Configurações centralizadas

## 🛠️ CORE Features

### Utilities (140+ funções)

- **MathHelper**: Cálculos matemáticos, formatação, porcentagens
- **StringHelper**: Manipulação de strings, slug, case conversion
- **ArrayHelper**: Operações avançadas com arrays, dot notation
- **DateTimeHelper**: Manipulação de datas, timezone, formatação
- **ValidationHelper**: Validadores (CPF, CNPJ, email, telefone, etc)
- **TradeHelper**: Trading, conversões de moeda, indicadores técnicos

### Domain Layer

- **BaseEntity**: Classe base para entidades com timestamps e eventos
- **ValueObject**: Interfaces para value objects imutáveis
- **DomainEvent**: Sistema de eventos de domínio
- **Exceptions**: Exceções tipadas (ValidationException, EntityNotFoundException)

### Application Layer

- **BaseDTO**: DTOs com validação e conversão automática
- **Result**: Pattern de resultado (success/failure)
- **Pagination**: Sistema de paginação completo
- **RepositoryInterface**: Contratos para repositórios

### Infrastructure Layer

- **DoctrineRepository**: Implementação base para repositórios
- **SessionManager**: Gerenciamento de sessão com namespaces
- **JWTManager**: Autenticação JWT completa
- **CacheManager**: Wrapper para cache do Symfony

### Presentation Layer

- **BaseApiController**: Controller base com helpers
- **ApiResponse**: Formatador de respostas padronizadas

## 🔧 Configuração

Todas as configurações estão em `config.php`:

```php
return [
    'server' => [
        'api' => ['port' => 8000],
        'frontend' => ['port' => 3000, 'enabled' => true],
    ],
    'database' => [...],
    'jwt' => [...],
    'cors' => [...],
];
```

## 🧪 Testes

```bash
# Executar testes
php bin/phpunit

# Com cobertura
php bin/phpunit --coverage-html coverage
```

## 📖 Endpoints da API

### Cards
- `GET /api/cards/search?q={query}&game={mtg|pokemon}` - Buscar cards
- `GET /api/cards/mtg/{setCode}/{number}` - Card MTG específico
- `GET /api/cards/pokemon/{setCode}/{number}` - Card Pokémon específico

### Currency
- `GET /api/currency/rates` - Taxas de câmbio atuais

### OCR
- `POST /api/ocr/scan` - Escanear card por imagem

## 🤝 Contribuindo

1. Fork o projeto
2. Crie uma branch (`git checkout -b feature/AmazingFeature`)
3. Commit suas mudanças (`git commit -m 'Add some AmazingFeature'`)
4. Push para a branch (`git push origin feature/AmazingFeature`)
5. Abra um Pull Request

## 📄 Licença

MIT License - veja [LICENSE](LICENSE) para detalhes

## 🙏 Agradecimentos

- Symfony Framework
- Scryfall API
- Pokémon TCG API
- Todos os contribuidores

---

**Desenvolvido com ❤️ para colecionadores de cards**
