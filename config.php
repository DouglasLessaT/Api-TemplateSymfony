<?php

/**
 * Configuração do Projeto Oracle TGC
 * 
 * Este arquivo centraliza as configurações do projeto,
 * incluindo caminhos, portas e opções de desenvolvimento.
 */

return [
    /**
     * Configurações do Servidor de Desenvolvimento
     */
    'server' => [
        // API Backend
        'api' => [
            'host' => 'localhost',
            'port' => 8000,
            'public_dir' => __DIR__ . '/public',
        ],
        
        // Frontend Web
        'frontend' => [
            'enabled' => true, // Iniciar frontend automaticamente
            'host' => 'localhost',
            'port' => 3000,
            'root_dir' => dirname(__DIR__) . '/Web.OracleTGC',
        ],
        
        // Documentação
        'docs' => [
            'enabled' => false, // Iniciar servidor de docs automaticamente
            'host' => 'localhost',
            'port' => 8080,
            'root_dir' => __DIR__ . '/docs/api',
        ],
    ],

    /**
     * Configurações de Ambiente
     */
    'environment' => [
        'mode' => 'development', // development, production, testing
        'debug' => true,
        'log_level' => 'debug',
    ],

    /**
     * Configurações de CORS
     */
    'cors' => [
        'allowed_origins' => [
            'http://localhost:3000',
            'http://localhost:8080',
        ],
        'allowed_methods' => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
        'allowed_headers' => ['Content-Type', 'Authorization'],
    ],

    /**
     * Configurações de Banco de Dados
     */
    'database' => [
        'driver' => 'pdo_mysql',
        'host' => getenv('DB_HOST') ?: 'localhost',
        'port' => getenv('DB_PORT') ?: 3306,
        'database' => getenv('DB_NAME') ?: 'oracle_tgc',
        'username' => getenv('DB_USER') ?: 'oracle_tgc',
        'password' => getenv('DB_PASS') ?: 'oracle_tgc',
        'charset' => 'utf8mb4',
    ],

    /**
     * Configurações de Cache
     */
    'cache' => [
        'driver' => 'file', // file, redis, memcached
        'ttl' => 3600, // 1 hora
        'prefix' => 'oracle_tgc_',
    ],

    /**
     * Configurações de JWT
     */
    'jwt' => [
        'secret' => getenv('JWT_SECRET') ?: 'change-this-secret-key-in-production',
        'algorithm' => 'HS256',
        'expiration' => 3600, // 1 hora
        'refresh_expiration' => 2592000, // 30 dias
    ],

    /**
     * Configurações de API Externa
     */
    'external_apis' => [
        'scryfall' => [
            'base_url' => 'https://api.scryfall.com',
            'timeout' => 10,
        ],
        'pokemon_tcg' => [
            'base_url' => 'https://api.pokemontcg.io/v2',
            'timeout' => 10,
        ],
        'onepiece' => [
            'base_url' => 'https://api.onepiece-cardgame.com',
            'timeout' => 10,
        ],
        'currency' => [
            'base_url' => 'https://api.exchangerate-api.com/v4/latest',
            'timeout' => 5,
        ],
    ],

    /**
     * Configurações de Sincronização
     */
    'sync' => [
        'strategy' => 'on-demand', // on-demand, full, hybrid
        
        'cache' => [
            'enabled' => true,
            'driver' => 'file', // file, redis, memcached
            'ttl' => 86400, // 24 horas
            'prefix' => 'card:',
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

    /**
     * Configurações de Upload
     */
    'upload' => [
        'max_size' => 5 * 1024 * 1024, // 5MB
        'allowed_types' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
        'upload_dir' => __DIR__ . '/public/uploads',
    ],

    /**
     * Configurações de Paginação
     */
    'pagination' => [
        'default_per_page' => 20,
        'max_per_page' => 100,
    ],

    /**
     * Caminhos do Projeto
     */
    'paths' => [
        'root' => dirname(__DIR__),
        'api' => __DIR__,
        'frontend' => dirname(__DIR__) . '/Web.OracleTGC',
        'core' => __DIR__ . '/src/Core',
        'logs' => __DIR__ . '/var/log',
        'cache' => __DIR__ . '/var/cache',
        'public' => __DIR__ . '/public',
    ],
];
