# Implementação dos Serviços de Sincronização

## 📦 Arquivos Criados

### 1. **CardRepository** (`src/Repositories/CardRepository.php`)
Repositório para gerenciar cards no banco de dados com métodos específicos:
- `findBySetAndNumber()` - Busca card por jogo, set e número
- `findByGame()` - Busca todos os cards de um jogo
- `findBySet()` - Busca cards de um set específico
- `countByGame()` - Conta cards por jogo
- `existsBySetAndNumber()` - Verifica se card existe

### 2. **CardSyncService** (`src/Service/CardSyncService.php`)
Serviço principal que implementa a estratégia híbrida de sincronização:

**Fluxo de Busca:**
1. **Cache** (Redis/File) - Acesso ultra-rápido (24h TTL)
2. **Banco de Dados** - Cards já salvos
3. **API Externa** - Fallback quando não encontrado

**Métodos Principais:**
- `findCard()` - Busca card seguindo a estratégia híbrida
- `findMultipleCards()` - Busca múltiplos cards
- `syncCard()` - Força sincronização da API
- `clearCache()` - Limpa cache de um card

### 3. **OnePieceService** (`src/Service/OnePieceService.php`)
Serviço para buscar cards de One Piece Card Game:
- `searchCard()` - Busca por nome
- `getCardBySetAndNumber()` - Busca por set e número

**Nota:** Ajustar a URL da API conforme a API real disponível.

### 4. **SetSyncService** (`src/Service/SetSyncService.php`)
Serviço para sincronizar metadados de sets (expansões):
- `syncSets()` - Sincroniza todos os sets de um jogo
- `getSet()` - Busca um set específico
- `clearCache()` - Limpa cache de sets

### 5. **CardFactory** (Atualizado)
Adicionado método `createFromDTO()` para converter CardDTO em Entity.

### 6. **config.php** (Atualizado)
Adicionada seção `sync` com configurações:
- Estratégia de sincronização
- Configurações de cache
- Configurações de armazenamento
- Agendamento de sincronização

---

## 🚀 Como Usar

### Exemplo 1: Buscar um Card (On-Demand)

```php
use App\Service\CardSyncService;

// Injetar o serviço (via container DI)
$cardSyncService = $container->get(CardSyncService::class);

// Buscar card de Magic
$card = $cardSyncService->findCard(
    game: 'mtg',
    setCode: 'ISD',
    number: '1',
    saveToDatabase: true // Salva no banco após buscar da API
);

if ($card) {
    echo "Card encontrado: " . $card->getName();
    echo "Preço USD: $" . ($card->getPriceUsd() ?? 'N/A');
}
```

### Exemplo 2: Sincronizar Card Escaneado

```php
// Após OCR escanear um card
$scannedData = [
    'game' => 'mtg',
    'setCode' => 'ISD',
    'number' => '1'
];

// Buscar dados completos e salvar
$card = $cardSyncService->findCard(
    $scannedData['game'],
    $scannedData['setCode'],
    $scannedData['number'],
    saveToDatabase: true
);

// Adicionar ao inventário do usuário
$inventory->addCard($card);
```

### Exemplo 3: Buscar Múltiplos Cards

```php
$identifiers = [
    ['game' => 'mtg', 'setCode' => 'ISD', 'number' => '1'],
    ['game' => 'pokemon', 'setCode' => 'base1', 'number' => '1'],
    ['game' => 'onepiece', 'setCode' => 'OP01', 'number' => '1'],
];

$cards = $cardSyncService->findMultipleCards($identifiers);
```

### Exemplo 4: Forçar Sincronização

```php
// Força atualização da API (ignora cache e banco)
$card = $cardSyncService->syncCard('mtg', 'ISD', '1');
```

### Exemplo 5: Sincronizar Sets

```php
use App\Service\SetSyncService;

$setSyncService = $container->get(SetSyncService::class);

// Sincronizar todos os sets de Magic
$sets = $setSyncService->syncSets('mtg');

foreach ($sets as $set) {
    echo "Set: {$set['name']} ({$set['code']})\n";
}
```

---

## ⚙️ Configuração

### Variáveis de Ambiente

Adicione ao seu `.env`:

```env
# APIs Externas
POKEMON_TCG_API_KEY=your_api_key_here
ONEPIECE_API_KEY=your_api_key_here

# Cache
CACHE_DRIVER=file  # ou 'redis' para produção
```

### Configuração de Cache

No `config.php`, ajuste conforme necessário:

```php
'sync' => [
    'cache' => [
        'enabled' => true,
        'driver' => 'redis', // Para produção
        'ttl' => 86400, // 24 horas
    ],
],
```

---

## 🔧 Integração com Controllers

### Exemplo de Controller

```php
use App\Service\CardSyncService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class CardController
{
    public function __construct(
        private CardSyncService $cardSyncService
    ) {}

    public function getCard(Request $request): JsonResponse
    {
        $game = $request->query->get('game');
        $setCode = $request->query->get('set');
        $number = $request->query->get('number');

        $card = $this->cardSyncService->findCard($game, $setCode, $number);

        if (!$card) {
            return new JsonResponse(['error' => 'Card not found'], 404);
        }

        return new JsonResponse($card->toArray());
    }
}
```

---

## 📊 Estratégia de Armazenamento

### O Que É Armazenado

✅ **SIM:**
- Cards escaneados pelo usuário
- Metadados de sets (leve, ~500 sets)
- Top 1000 cards populares
- Inventário completo do usuário

❌ **NÃO:**
- Todos os 100.000+ cards de Magic
- Imagens (apenas URLs)
- Cards nunca acessados

### Fluxo de Dados

```
Usuário escaneia card
    ↓
CardSyncService.findCard()
    ↓
1. Cache? → SIM → Retornar ⚡
    ↓ NÃO
2. Banco? → SIM → Cache + Retornar 🚀
    ↓ NÃO
3. API Externa → Buscar 🌐
    ↓
4. Salvar no Banco + Cache
    ↓
5. Retornar ao usuário
```

---

## 🐛 Troubleshooting

### Card não encontrado no banco mesmo após salvar

**Problema:** O CardRepository pode ter problemas com herança de Doctrine.

**Solução:** Verifique se as entidades CardMTG, CardPTCG, CardOPCG estão configuradas corretamente no Doctrine com herança (Single Table Inheritance ou Joined Table Inheritance).

### Cache não está funcionando

**Problema:** Cache pode não estar configurado corretamente.

**Solução:** 
1. Verifique se o driver de cache está instalado
2. Para Redis: `composer require predis/predis`
3. Configure no `config.php`

### API externa retorna erro

**Problema:** Rate limit ou API key inválida.

**Solução:**
1. Verifique as variáveis de ambiente
2. Implemente retry logic com backoff exponencial
3. Adicione rate limiting no código

---

## 🔄 Próximos Passos

1. **Implementar busca de sets** nas APIs externas
2. **Criar comandos CLI** para sincronização em background
3. **Adicionar métricas** de performance (cache hit rate, etc)
4. **Implementar rate limiting** para APIs externas
5. **Adicionar retry logic** com backoff exponencial
6. **Criar jobs assíncronos** para sincronização em background

---

## 📝 Notas Importantes

1. **OnePieceService** precisa ser ajustado conforme a API real disponível
2. **CardRepository** pode precisar de ajustes dependendo da estratégia de herança do Doctrine
3. **Cache** deve ser configurado para produção (Redis recomendado)
4. **Rate Limits** das APIs externas devem ser respeitados

---

**Implementação concluída! 🎉**



