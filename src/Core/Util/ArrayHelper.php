<?php

namespace App\Core\Util;

/**
 * Array Helper
 * 
 * Biblioteca de funções para manipulação de arrays.
 */
class ArrayHelper
{
    /**
     * Obtém um valor de um array usando notação de ponto
     * Exemplo: get($array, 'user.profile.name')
     */
    public static function get(array $array, string $key, mixed $default = null): mixed
    {
        if (isset($array[$key])) {
            return $array[$key];
        }

        foreach (explode('.', $key) as $segment) {
            if (!is_array($array) || !array_key_exists($segment, $array)) {
                return $default;
            }

            $array = $array[$segment];
        }

        return $array;
    }

    /**
     * Define um valor em um array usando notação de ponto
     */
    public static function set(array &$array, string $key, mixed $value): void
    {
        $keys = explode('.', $key);
        $current = &$array;

        foreach ($keys as $i => $key) {
            if ($i === count($keys) - 1) {
                $current[$key] = $value;
            } else {
                if (!isset($current[$key]) || !is_array($current[$key])) {
                    $current[$key] = [];
                }
                $current = &$current[$key];
            }
        }
    }

    /**
     * Remove um valor de um array usando notação de ponto
     */
    public static function remove(array &$array, string $key): void
    {
        $keys = explode('.', $key);
        $current = &$array;

        foreach ($keys as $i => $key) {
            if ($i === count($keys) - 1) {
                unset($current[$key]);
            } else {
                if (!isset($current[$key]) || !is_array($current[$key])) {
                    return;
                }
                $current = &$current[$key];
            }
        }
    }

    /**
     * Verifica se uma chave existe usando notação de ponto
     */
    public static function has(array $array, string $key): bool
    {
        foreach (explode('.', $key) as $segment) {
            if (!is_array($array) || !array_key_exists($segment, $array)) {
                return false;
            }

            $array = $array[$segment];
        }

        return true;
    }

    /**
     * Achata um array multidimensional
     */
    public static function flatten(array $array, string $separator = '.'): array
    {
        $result = [];

        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $flattened = self::flatten($value, $separator);
                foreach ($flattened as $subKey => $subValue) {
                    $result[$key . $separator . $subKey] = $subValue;
                }
            } else {
                $result[$key] = $value;
            }
        }

        return $result;
    }

    /**
     * Filtra um array mantendo apenas as chaves especificadas
     */
    public static function only(array $array, array $keys): array
    {
        return array_intersect_key($array, array_flip($keys));
    }

    /**
     * Filtra um array removendo as chaves especificadas
     */
    public static function except(array $array, array $keys): array
    {
        return array_diff_key($array, array_flip($keys));
    }

    /**
     * Extrai uma coluna de um array multidimensional
     */
    public static function pluck(array $array, string $column, ?string $key = null): array
    {
        $result = [];

        foreach ($array as $item) {
            $value = is_array($item) ? ($item[$column] ?? null) : (is_object($item) ? ($item->$column ?? null) : null);

            if ($key !== null) {
                $keyValue = is_array($item) ? ($item[$key] ?? null) : (is_object($item) ? ($item->$key ?? null) : null);
                $result[$keyValue] = $value;
            } else {
                $result[] = $value;
            }
        }

        return $result;
    }

    /**
     * Agrupa um array por uma chave
     */
    public static function groupBy(array $array, string $key): array
    {
        $result = [];

        foreach ($array as $item) {
            $groupKey = is_array($item) ? ($item[$key] ?? null) : (is_object($item) ? ($item->$key ?? null) : null);

            if ($groupKey !== null) {
                $result[$groupKey][] = $item;
            }
        }

        return $result;
    }

    /**
     * Ordena um array por uma chave
     */
    public static function sortBy(array $array, string $key, bool $ascending = true): array
    {
        usort($array, function ($a, $b) use ($key, $ascending) {
            $aValue = is_array($a) ? ($a[$key] ?? null) : (is_object($a) ? ($a->$key ?? null) : null);
            $bValue = is_array($b) ? ($b[$key] ?? null) : (is_object($b) ? ($b->$key ?? null) : null);

            if ($aValue === $bValue) {
                return 0;
            }

            $comparison = $aValue < $bValue ? -1 : 1;
            return $ascending ? $comparison : -$comparison;
        });

        return $array;
    }

    /**
     * Divide um array em chunks
     */
    public static function chunk(array $array, int $size): array
    {
        return array_chunk($array, $size);
    }

    /**
     * Retorna apenas valores únicos de um array
     */
    public static function unique(array $array): array
    {
        return array_values(array_unique($array, SORT_REGULAR));
    }

    /**
     * Verifica se um array é associativo
     */
    public static function isAssoc(array $array): bool
    {
        if (empty($array)) {
            return false;
        }

        return array_keys($array) !== range(0, count($array) - 1);
    }

    /**
     * Mescla arrays recursivamente
     */
    public static function mergeRecursive(array ...$arrays): array
    {
        $result = [];

        foreach ($arrays as $array) {
            foreach ($array as $key => $value) {
                if (is_array($value) && isset($result[$key]) && is_array($result[$key])) {
                    $result[$key] = self::mergeRecursive($result[$key], $value);
                } else {
                    $result[$key] = $value;
                }
            }
        }

        return $result;
    }

    /**
     * Retorna o primeiro elemento que satisfaz uma condição
     */
    public static function first(array $array, ?callable $callback = null, mixed $default = null): mixed
    {
        if ($callback === null) {
            return empty($array) ? $default : reset($array);
        }

        foreach ($array as $key => $value) {
            if ($callback($value, $key)) {
                return $value;
            }
        }

        return $default;
    }

    /**
     * Retorna o último elemento que satisfaz uma condição
     */
    public static function last(array $array, ?callable $callback = null, mixed $default = null): mixed
    {
        if ($callback === null) {
            return empty($array) ? $default : end($array);
        }

        return self::first(array_reverse($array, true), $callback, $default);
    }

    /**
     * Mapeia um array aplicando uma função
     */
    public static function map(array $array, callable $callback): array
    {
        return array_map($callback, $array, array_keys($array));
    }

    /**
     * Filtra um array aplicando uma função
     */
    public static function filter(array $array, callable $callback): array
    {
        return array_filter($array, $callback, ARRAY_FILTER_USE_BOTH);
    }

    /**
     * Reduz um array a um único valor
     */
    public static function reduce(array $array, callable $callback, mixed $initial = null): mixed
    {
        return array_reduce($array, $callback, $initial);
    }

    /**
     * Embaralha um array
     */
    public static function shuffle(array $array): array
    {
        shuffle($array);
        return $array;
    }

    /**
     * Retorna um elemento aleatório do array
     */
    public static function random(array $array, int $count = 1): mixed
    {
        $keys = array_rand($array, $count);

        if ($count === 1) {
            return $array[$keys];
        }

        return array_intersect_key($array, array_flip($keys));
    }
}
