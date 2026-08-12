# Estratégia de Sincronização de Dados - Cards API

## 🎯 Abordagem Recomendada: Híbrida

### Modelo de 3 Camadas

```
┌─────────────────────────────────────────────────┐
│  1. Cache Local (Redis/Memcached)              │
│     - Cards recentemente acessados              │
│     - TTL: 24 horas                             │
│     - Acesso ultra-rápido                       │
└─────────────────────────────────────────────────┘
                      ↓
┌─────────────────────────────────────────────────┐
│  2. Banco de Dados (MySQL/PostgreSQL)          │
│     - Cards populares/escaneados                │
│     - Metadados de sets                         │
│     - Inventário do usuário                     │
└─────────────────────────────────────────────────┘
                      ↓
┌─────────────────────────────────────────────────┐
│  3. APIs Externas (Fallback)                    │
│     - Scryfall (Magic)                          │
│     - Pokémon TCG API                           │
│     - One Piece API                             │
└─────────────────────────────────────────────────┘
```

---

## 📊 O Que Armazenar

### ✅ ARMAZENAR no Banco

1. **Cards Escaneados pelo Usuário**
   - Todos os cards que o usuário adicionou ao inventário
   - Dados completos + imagens

2. **Sets/Expansões (Metadados)**
   - Lista de todos os sets disponíveis
   - Códigos, nomes, datas de lançamento
   - ~500 sets no total (leve)

3. **Cards Populares**
   - Top 1000 cards mais buscados
   - Atualizado semanalmente

4. **Inventário do Usuário**
   - Inventory, Collections, Decks
   - Sempre no banco

### ❌ NÃO ARMAZENAR no Banco

1. **Todos os 100.000+ cards de Magic**
   - Muito espaço (gigabytes)
   - Maioria nunca será acessada

2. **Imagens de Alta Resolução**
   - Armazenar apenas URLs
   - Usar CDN das APIs (Scryfall já tem CDN)

---

## 🔄 Fluxo de Busca de Cards

```php
// Pseudo-código do fluxo
function findCard(string $game, string $setCode, string $number): Card
{
    // 1. Verificar Cache (Redis)
    $card = $cache->get("card:{$game}:{$setCode}:{$number}");
    if ($card) {
        return $card; // ⚡ Ultra-rápido
    }
    
    // 2. Verificar Banco de Dados
    $card = $repository->findBySetAndNumber($game, $setCode, $number);
    if ($card) {
        $cache->set("card:{$game}:{$setCode}:{$number}", $card, ttl: 86400);
        return $card; // 🚀 Rápido
    }
    
    // 3. Buscar na API Externa
    $card = $externalApi->fetchCard($game, $setCode, $number);
    
    // 4. Armazenar para uso futuro
    $repository->save($card);
    $cache->set("card:{$game}:{$setCode}:{$number}", $card, ttl: 86400);
    
    return $card; // 🌐 Primeira vez (mais lento)
}
```

---

## 📥 Estratégias de Sincronização

### 1. Sincronização On-Demand (Recomendado)

**Quando:** Card é escaneado ou buscado pela primeira vez

```php
// Usuário escaneia um card
$scannedData = $ocrService->scan($image);

// Buscar dados completos da API
$cardData = $scryfallApi->getCard($scannedData['setCode'], $scannedData['number']);

// Criar e salvar no banco
$card = CardFactory::createMTG($cardData);
$cardRepository->save($card);

// Adicionar ao inventário do usuário
$inventory->addCard($card);
```

**Vantagens:**
- ✅ Armazena apenas o necessário
- ✅ Sempre dados atualizados
- ✅ Baixo uso de espaço

### 2. Sincronização de Sets Completos (Opcional)

**Quando:** Usuário quer baixar um set inteiro

```php
// Comando CLI para sincronizar um set
php bin/console app:sync:set mtg ISD

// Ou via API
POST /api/admin/sync/set
{
    "game": "mtg",
    "setCode": "ISD"
}
```

**Casos de Uso:**
- Sets muito populares (Base Set Pokémon, Alpha MTG)
- Eventos/torneios específicos
- Colecionadores que querem dados offline

### 3. Sincronização Incremental (Background)

**Quando:** Diariamente via cron job

```php
// Atualizar preços de cards no inventário
php bin/console app:sync:prices

// Atualizar lista de sets
php bin/console app:sync:sets

// Atualizar cards populares
php bin/console app:sync:popular-cards
```

---

## 🗄️ Estrutura de Banco de Dados

### Tabelas Principais

```sql
-- Cards (apenas os necessários)
CREATE TABLE cards (
    id VARCHAR(36) PRIMARY KEY,
    game VARCHAR(20) NOT NULL,
    name VARCHAR(255) NOT NULL,
    set_code VARCHAR(10),
    collector_number VARCHAR(20),
    image_url TEXT,
    data JSON, -- Dados específicos do jogo
    last_synced_at TIMESTAMP,
    INDEX idx_game_set_number (game, set_code, collector_number)
);

-- Sets (metadados leves)
CREATE TABLE card_sets (
    id VARCHAR(36) PRIMARY KEY,
    game VARCHAR(20) NOT NULL,
    code VARCHAR(10) NOT NULL UNIQUE,
    name VARCHAR(255) NOT NULL,
    release_date DATE,
    card_count INT,
    icon_url TEXT,
    INDEX idx_game (game)
);

-- Inventário do usuário (sempre no banco)
CREATE TABLE inventories (
    id VARCHAR(36) PRIMARY KEY,
    user_id VARCHAR(36) NOT NULL,
    name VARCHAR(255),
    created_at TIMESTAMP,
    INDEX idx_user (user_id)
);

CREATE TABLE inventory_items (
    id VARCHAR(36) PRIMARY KEY,
    inventory_id VARCHAR(36),
    card_id VARCHAR(36),
    quantity INT DEFAULT 1,
    condition VARCHAR(50),
    purchase_price DECIMAL(10,2),
    FOREIGN KEY (inventory_id) REFERENCES inventories(id),
    FOREIGN KEY (card_id) REFERENCES cards(id)
);
```

---

## 🚀 Implementação Prática

### Fase 1: Básico (MVP)
1. ✅ Armazenar apenas cards escaneados
2. ✅ Buscar APIs em tempo real
3. ✅ Cache simples (arquivo ou Redis)

### Fase 2: Otimização
1. ✅ Sincronizar sets populares
2. ✅ Background jobs para atualização
3. ✅ Cache distribuído (Redis)

### Fase 3: Avançado
1. ✅ Sincronização completa de sets
2. ✅ CDN própria para imagens
3. ✅ Elasticsearch para busca avançada

---

## 💰 Estimativa de Espaço

### Armazenamento Mínimo (On-Demand)
- **100 cards escaneados**: ~5 MB
- **1.000 cards**: ~50 MB
- **10.000 cards**: ~500 MB

### Armazenamento Completo (Todos os Cards)
- **Magic (~100k cards)**: ~5 GB
- **Pokémon (~20k cards)**: ~1 GB
- **One Piece (~2k cards)**: ~100 MB
- **Total**: ~6-7 GB

### Imagens
- **Não armazenar**: 0 GB (usar URLs das APIs)
- **Armazenar**: +50-100 GB

**Recomendação:** Não armazenar imagens, usar CDN das APIs

---

## 🔧 Configuração Recomendada

```php
// config.php
'sync' => [
    'strategy' => 'on-demand', // on-demand, full, hybrid
    
    'cache' => [
        'enabled' => true,
        'driver' => 'redis',
        'ttl' => 86400, // 24 horas
    ],
    
    'storage' => [
        'store_images' => false, // Usar URLs das APIs
        'store_all_cards' => false, // Apenas cards escaneados
        'store_popular_cards' => true, // Top 1000
    ],
    
    'sync_schedule' => [
        'sets' => 'daily', // Atualizar lista de sets
        'prices' => 'daily', // Atualizar preços
        'popular_cards' => 'weekly', // Top cards
    ],
],
```

---

## 📝 Conclusão

### ✅ Estratégia Recomendada

1. **Armazenar:**
   - Cards escaneados pelo usuário
   - Metadados de sets
   - Top 1000 cards populares
   - Inventário completo do usuário

2. **Não Armazenar:**
   - Todos os 100k+ cards
   - Imagens (usar URLs)

3. **Cache:**
   - Redis para cards recentemente acessados
   - TTL de 24 horas

4. **Sincronização:**
   - On-demand quando card é escaneado
   - Background job diário para preços
   - Opcional: sincronizar sets completos

### 💡 Benefícios

- ⚡ Performance excelente
- 💾 Uso eficiente de espaço
- 🌐 Funciona offline (para cards salvos)
- 💰 Baixo custo de infraestrutura
- 🔄 Dados sempre atualizados

---

**Esta é a abordagem mais equilibrada entre performance, custo e manutenibilidade!**
