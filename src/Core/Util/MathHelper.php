<?php

namespace App\Core\Util;

/**
 * Math Helper
 * 
 * Biblioteca de funções matemáticas avançadas.
 */
class MathHelper
{
    /**
     * Arredonda um número para cima com precisão decimal
     */
    public static function ceilDecimal(float $number, int $precision = 2): float
    {
        $multiplier = pow(10, $precision);
        return ceil($number * $multiplier) / $multiplier;
    }

    /**
     * Arredonda um número para baixo com precisão decimal
     */
    public static function floorDecimal(float $number, int $precision = 2): float
    {
        $multiplier = pow(10, $precision);
        return floor($number * $multiplier) / $multiplier;
    }

    /**
     * Arredonda um número com precisão decimal
     */
    public static function roundDecimal(float $number, int $precision = 2, int $mode = PHP_ROUND_HALF_UP): float
    {
        return round($number, $precision, $mode);
    }

    /**
     * Calcula a porcentagem de um valor
     */
    public static function percentage(float $value, float $total): float
    {
        if ($total == 0) {
            return 0;
        }

        return ($value / $total) * 100;
    }

    /**
     * Calcula X% de um valor
     */
    public static function percentOf(float $percent, float $value): float
    {
        return ($percent / 100) * $value;
    }

    /**
     * Calcula a diferença percentual entre dois valores
     */
    public static function percentDifference(float $oldValue, float $newValue): float
    {
        if ($oldValue == 0) {
            return 0;
        }

        return (($newValue - $oldValue) / abs($oldValue)) * 100;
    }

    /**
     * Calcula a média de um array de números
     */
    public static function average(array $numbers): float
    {
        if (empty($numbers)) {
            return 0;
        }

        return array_sum($numbers) / count($numbers);
    }

    /**
     * Calcula a mediana de um array de números
     */
    public static function median(array $numbers): float
    {
        if (empty($numbers)) {
            return 0;
        }

        sort($numbers);
        $count = count($numbers);
        $middle = floor($count / 2);

        if ($count % 2 == 0) {
            return ($numbers[$middle - 1] + $numbers[$middle]) / 2;
        }

        return $numbers[$middle];
    }

    /**
     * Limita um valor entre um mínimo e máximo
     */
    public static function clamp(float $value, float $min, float $max): float
    {
        return max($min, min($max, $value));
    }

    /**
     * Verifica se um número está entre dois valores (inclusive)
     */
    public static function between(float $value, float $min, float $max): bool
    {
        return $value >= $min && $value <= $max;
    }

    /**
     * Converte bytes para formato legível
     */
    public static function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }

    /**
     * Formata um número com separadores de milhares
     */
    public static function formatNumber(float $number, int $decimals = 2, string $decPoint = '.', string $thousandsSep = ','): string
    {
        return number_format($number, $decimals, $decPoint, $thousandsSep);
    }

    /**
     * Converte um número para formato monetário
     */
    public static function formatMoney(float $amount, string $currency = 'BRL', int $decimals = 2): string
    {
        $symbols = [
            'BRL' => 'R$',
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
        ];

        $symbol = $symbols[$currency] ?? $currency;
        $formatted = self::formatNumber($amount, $decimals, ',', '.');

        return $symbol . ' ' . $formatted;
    }

    /**
     * Calcula o máximo divisor comum (MDC)
     */
    public static function gcd(int $a, int $b): int
    {
        while ($b != 0) {
            $temp = $b;
            $b = $a % $b;
            $a = $temp;
        }

        return abs($a);
    }

    /**
     * Calcula o mínimo múltiplo comum (MMC)
     */
    public static function lcm(int $a, int $b): int
    {
        if ($a == 0 || $b == 0) {
            return 0;
        }

        return abs($a * $b) / self::gcd($a, $b);
    }

    /**
     * Verifica se um número é primo
     */
    public static function isPrime(int $number): bool
    {
        if ($number < 2) {
            return false;
        }

        if ($number == 2) {
            return true;
        }

        if ($number % 2 == 0) {
            return false;
        }

        $sqrt = sqrt($number);
        for ($i = 3; $i <= $sqrt; $i += 2) {
            if ($number % $i == 0) {
                return false;
            }
        }

        return true;
    }

    /**
     * Gera um número aleatório entre min e max
     */
    public static function random(int $min = 0, int $max = 100): int
    {
        return random_int($min, $max);
    }

    /**
     * Gera um número float aleatório entre min e max
     */
    public static function randomFloat(float $min = 0, float $max = 1, int $precision = 2): float
    {
        $random = $min + mt_rand() / mt_getrandmax() * ($max - $min);
        return self::roundDecimal($random, $precision);
    }
}
