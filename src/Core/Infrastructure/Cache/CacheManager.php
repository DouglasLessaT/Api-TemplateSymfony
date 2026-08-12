<?php

namespace App\Core\Infrastructure\Cache;

use Psr\Cache\CacheItemPoolInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

/**
 * Cache Manager
 * 
 * Wrapper simplificado para o sistema de cache do Symfony.
 */
class CacheManager
{
    public function __construct(
        private CacheInterface $cache
    ) {
    }

    /**
     * Obtém um valor do cache ou executa o callback se não existir
     * 
     * @template T
     * @param string $key
     * @param callable $callback
     * @param int|null $ttl Tempo de vida em segundos (null = sem expiração)
     * @return T
     */
    public function get(string $key, callable $callback, ?int $ttl = null): mixed
    {
        return $this->cache->get($key, function (ItemInterface $item) use ($callback, $ttl) {
            if ($ttl !== null) {
                $item->expiresAfter($ttl);
            }
            
            return $callback($item);
        });
    }

    /**
     * Remove um item do cache
     */
    public function delete(string $key): bool
    {
        return $this->cache->delete($key);
    }

    /**
     * Armazena um valor no cache
     * 
     * @param string $key
     * @param mixed $value
     * @param int|null $ttl Tempo de vida em segundos
     */
    public function set(string $key, mixed $value, ?int $ttl = null): mixed
    {
        return $this->get($key, fn() => $value, $ttl);
    }

    /**
     * Verifica se uma chave existe no cache
     */
    public function has(string $key): bool
    {
        try {
            $this->cache->get($key, function (ItemInterface $item) {
                $item->expiresAfter(0);
                return null;
            });
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Limpa todo o cache
     */
    public function clear(): bool
    {
        if ($this->cache instanceof CacheItemPoolInterface) {
            return $this->cache->clear();
        }
        
        return false;
    }

    /**
     * Remove múltiplas chaves do cache
     * 
     * @param string[] $keys
     */
    public function deleteMultiple(array $keys): bool
    {
        $success = true;
        
        foreach ($keys as $key) {
            if (!$this->delete($key)) {
                $success = false;
            }
        }

        return $success;
    }

    /**
     * Obtém múltiplos valores do cache
     * 
     * @param string[] $keys
     * @param mixed $default Valor padrão se a chave não existir
     * @return array
     */
    public function getMultiple(array $keys, mixed $default = null): array
    {
        $result = [];

        foreach ($keys as $key) {
            try {
                $result[$key] = $this->cache->get($key, fn() => $default);
            } catch (\Exception $e) {
                $result[$key] = $default;
            }
        }

        return $result;
    }

    /**
     * Armazena um valor no cache apenas se a chave não existir
     */
    public function remember(string $key, callable $callback, ?int $ttl = 3600): mixed
    {
        return $this->get($key, $callback, $ttl);
    }

    /**
     * Armazena um valor no cache permanentemente (sem expiração)
     */
    public function rememberForever(string $key, callable $callback): mixed
    {
        return $this->get($key, $callback, null);
    }

    /**
     * Remove do cache e retorna o valor
     */
    public function pull(string $key, mixed $default = null): mixed
    {
        try {
            $value = $this->cache->get($key, fn() => $default);
            $this->delete($key);
            return $value;
        } catch (\Exception $e) {
            return $default;
        }
    }

    /**
     * Incrementa um valor numérico no cache
     */
    public function increment(string $key, int $value = 1): int
    {
        $current = (int) $this->get($key, fn() => 0);
        $new = $current + $value;
        $this->set($key, $new);
        return $new;
    }

    /**
     * Decrementa um valor numérico no cache
     */
    public function decrement(string $key, int $value = 1): int
    {
        return $this->increment($key, -$value);
    }
}
