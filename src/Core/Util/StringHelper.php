<?php

namespace App\Core\Util;

/**
 * String Helper
 * 
 * Biblioteca de funções para manipulação de strings.
 */
class StringHelper
{
    /**
     * Gera um slug a partir de uma string
     */
    public static function slug(string $text, string $separator = '-'): string
    {
        // Converte para minúsculas
        $text = mb_strtolower($text, 'UTF-8');

        // Remove acentos
        $text = self::removeAccents($text);

        // Remove caracteres especiais
        $text = preg_replace('/[^a-z0-9\s-]/', '', $text);

        // Substitui espaços e múltiplos hífens por um único separador
        $text = preg_replace('/[\s-]+/', $separator, $text);

        // Remove separadores do início e fim
        return trim($text, $separator);
    }

    /**
     * Remove acentos de uma string
     */
    public static function removeAccents(string $text): string
    {
        $unwanted = [
            'á' => 'a', 'à' => 'a', 'ã' => 'a', 'â' => 'a', 'ä' => 'a',
            'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e',
            'í' => 'i', 'ì' => 'i', 'î' => 'i', 'ï' => 'i',
            'ó' => 'o', 'ò' => 'o', 'õ' => 'o', 'ô' => 'o', 'ö' => 'o',
            'ú' => 'u', 'ù' => 'u', 'û' => 'u', 'ü' => 'u',
            'ç' => 'c', 'ñ' => 'n',
            'Á' => 'A', 'À' => 'A', 'Ã' => 'A', 'Â' => 'A', 'Ä' => 'A',
            'É' => 'E', 'È' => 'E', 'Ê' => 'E', 'Ë' => 'E',
            'Í' => 'I', 'Ì' => 'I', 'Î' => 'I', 'Ï' => 'I',
            'Ó' => 'O', 'Ò' => 'O', 'Õ' => 'O', 'Ô' => 'O', 'Ö' => 'O',
            'Ú' => 'U', 'Ù' => 'U', 'Û' => 'U', 'Ü' => 'U',
            'Ç' => 'C', 'Ñ' => 'N',
        ];

        return strtr($text, $unwanted);
    }

    /**
     * Trunca uma string com reticências
     */
    public static function truncate(string $text, int $length, string $suffix = '...'): string
    {
        if (mb_strlen($text) <= $length) {
            return $text;
        }

        return mb_substr($text, 0, $length - mb_strlen($suffix)) . $suffix;
    }

    /**
     * Converte uma string para camelCase
     */
    public static function camelCase(string $text): string
    {
        $text = str_replace(['-', '_'], ' ', $text);
        $text = ucwords($text);
        $text = str_replace(' ', '', $text);
        return lcfirst($text);
    }

    /**
     * Converte uma string para PascalCase
     */
    public static function pascalCase(string $text): string
    {
        return ucfirst(self::camelCase($text));
    }

    /**
     * Converte uma string para snake_case
     */
    public static function snakeCase(string $text): string
    {
        $text = preg_replace('/([a-z])([A-Z])/', '$1_$2', $text);
        $text = str_replace(['-', ' '], '_', $text);
        return mb_strtolower($text);
    }

    /**
     * Converte uma string para kebab-case
     */
    public static function kebabCase(string $text): string
    {
        return str_replace('_', '-', self::snakeCase($text));
    }

    /**
     * Verifica se uma string começa com outra
     */
    public static function startsWith(string $haystack, string $needle): bool
    {
        return str_starts_with($haystack, $needle);
    }

    /**
     * Verifica se uma string termina com outra
     */
    public static function endsWith(string $haystack, string $needle): bool
    {
        return str_ends_with($haystack, $needle);
    }

    /**
     * Verifica se uma string contém outra
     */
    public static function contains(string $haystack, string $needle): bool
    {
        return str_contains($haystack, $needle);
    }

    /**
     * Gera uma string aleatória
     */
    public static function random(int $length = 16, string $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'): string
    {
        $charactersLength = strlen($characters);
        $randomString = '';

        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[random_int(0, $charactersLength - 1)];
        }

        return $randomString;
    }

    /**
     * Mascara uma string (útil para emails, telefones, etc)
     */
    public static function mask(string $text, int $visibleStart = 3, int $visibleEnd = 3, string $mask = '*'): string
    {
        $length = mb_strlen($text);

        if ($length <= $visibleStart + $visibleEnd) {
            return $text;
        }

        $start = mb_substr($text, 0, $visibleStart);
        $end = mb_substr($text, -$visibleEnd);
        $middle = str_repeat($mask, $length - $visibleStart - $visibleEnd);

        return $start . $middle . $end;
    }

    /**
     * Remove espaços em branco extras
     */
    public static function normalizeSpaces(string $text): string
    {
        return preg_replace('/\s+/', ' ', trim($text));
    }

    /**
     * Remove tags HTML
     */
    public static function stripTags(string $text, ?string $allowedTags = null): string
    {
        return strip_tags($text, $allowedTags);
    }

    /**
     * Escapa HTML
     */
    public static function escapeHtml(string $text): string
    {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Conta palavras em uma string
     */
    public static function wordCount(string $text): int
    {
        return str_word_count($text);
    }

    /**
     * Limita o número de palavras
     */
    public static function limitWords(string $text, int $limit, string $suffix = '...'): string
    {
        $words = explode(' ', $text);

        if (count($words) <= $limit) {
            return $text;
        }

        return implode(' ', array_slice($words, 0, $limit)) . $suffix;
    }

    /**
     * Converte primeira letra de cada palavra para maiúscula
     */
    public static function title(string $text): string
    {
        return mb_convert_case($text, MB_CASE_TITLE, 'UTF-8');
    }

    /**
     * Reverte uma string
     */
    public static function reverse(string $text): string
    {
        return strrev($text);
    }

    /**
     * Verifica se a string é um JSON válido
     */
    public static function isJson(string $text): bool
    {
        json_decode($text);
        return json_last_error() === JSON_ERROR_NONE;
    }
}
