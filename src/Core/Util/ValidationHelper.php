<?php

namespace App\Core\Util;

/**
 * Validation Helper
 * 
 * Biblioteca de funções para validação de dados.
 */
class ValidationHelper
{
    /**
     * Valida um email
     */
    public static function email(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Valida uma URL
     */
    public static function url(string $url): bool
    {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }

    /**
     * Valida um CPF
     */
    public static function cpf(string $cpf): bool
    {
        // Remove caracteres não numéricos
        $cpf = preg_replace('/[^0-9]/', '', $cpf);

        // Verifica se tem 11 dígitos
        if (strlen($cpf) != 11) {
            return false;
        }

        // Verifica se todos os dígitos são iguais
        if (preg_match('/(\d)\1{10}/', $cpf)) {
            return false;
        }

        // Valida primeiro dígito verificador
        $sum = 0;
        for ($i = 0; $i < 9; $i++) {
            $sum += $cpf[$i] * (10 - $i);
        }
        $remainder = $sum % 11;
        $digit1 = $remainder < 2 ? 0 : 11 - $remainder;

        if ($cpf[9] != $digit1) {
            return false;
        }

        // Valida segundo dígito verificador
        $sum = 0;
        for ($i = 0; $i < 10; $i++) {
            $sum += $cpf[$i] * (11 - $i);
        }
        $remainder = $sum % 11;
        $digit2 = $remainder < 2 ? 0 : 11 - $remainder;

        return $cpf[10] == $digit2;
    }

    /**
     * Valida um CNPJ
     */
    public static function cnpj(string $cnpj): bool
    {
        // Remove caracteres não numéricos
        $cnpj = preg_replace('/[^0-9]/', '', $cnpj);

        // Verifica se tem 14 dígitos
        if (strlen($cnpj) != 14) {
            return false;
        }

        // Verifica se todos os dígitos são iguais
        if (preg_match('/(\d)\1{13}/', $cnpj)) {
            return false;
        }

        // Valida primeiro dígito verificador
        $sum = 0;
        $weights = [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        for ($i = 0; $i < 12; $i++) {
            $sum += $cnpj[$i] * $weights[$i];
        }
        $remainder = $sum % 11;
        $digit1 = $remainder < 2 ? 0 : 11 - $remainder;

        if ($cnpj[12] != $digit1) {
            return false;
        }

        // Valida segundo dígito verificador
        $sum = 0;
        $weights = [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        for ($i = 0; $i < 13; $i++) {
            $sum += $cnpj[$i] * $weights[$i];
        }
        $remainder = $sum % 11;
        $digit2 = $remainder < 2 ? 0 : 11 - $remainder;

        return $cnpj[13] == $digit2;
    }

    /**
     * Valida um telefone brasileiro
     */
    public static function phone(string $phone): bool
    {
        // Remove caracteres não numéricos
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // Verifica se tem 10 ou 11 dígitos (com DDD)
        if (strlen($phone) < 10 || strlen($phone) > 11) {
            return false;
        }

        // Verifica se o DDD é válido (11-99)
        $ddd = (int) substr($phone, 0, 2);
        if ($ddd < 11 || $ddd > 99) {
            return false;
        }

        return true;
    }

    /**
     * Valida um CEP
     */
    public static function cep(string $cep): bool
    {
        // Remove caracteres não numéricos
        $cep = preg_replace('/[^0-9]/', '', $cep);

        // Verifica se tem 8 dígitos
        return strlen($cep) === 8;
    }

    /**
     * Valida uma data no formato Y-m-d
     */
    public static function date(string $date, string $format = 'Y-m-d'): bool
    {
        $d = \DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }

    /**
     * Valida se uma string contém apenas letras
     */
    public static function alpha(string $value): bool
    {
        return preg_match('/^[a-zA-ZÀ-ÿ\s]+$/', $value) === 1;
    }

    /**
     * Valida se uma string contém apenas letras e números
     */
    public static function alphaNumeric(string $value): bool
    {
        return preg_match('/^[a-zA-Z0-9À-ÿ\s]+$/', $value) === 1;
    }

    /**
     * Valida se uma string contém apenas números
     */
    public static function numeric(string $value): bool
    {
        return is_numeric($value);
    }

    /**
     * Valida se um valor está entre um mínimo e máximo
     */
    public static function between(mixed $value, mixed $min, mixed $max): bool
    {
        return $value >= $min && $value <= $max;
    }

    /**
     * Valida o tamanho mínimo de uma string
     */
    public static function minLength(string $value, int $min): bool
    {
        return mb_strlen($value) >= $min;
    }

    /**
     * Valida o tamanho máximo de uma string
     */
    public static function maxLength(string $value, int $max): bool
    {
        return mb_strlen($value) <= $max;
    }

    /**
     * Valida se um valor está em uma lista
     */
    public static function in(mixed $value, array $list): bool
    {
        return in_array($value, $list, true);
    }

    /**
     * Valida se um valor não está em uma lista
     */
    public static function notIn(mixed $value, array $list): bool
    {
        return !self::in($value, $list);
    }

    /**
     * Valida uma regex
     */
    public static function regex(string $value, string $pattern): bool
    {
        return preg_match($pattern, $value) === 1;
    }

    /**
     * Valida um IP
     */
    public static function ip(string $ip): bool
    {
        return filter_var($ip, FILTER_VALIDATE_IP) !== false;
    }

    /**
     * Valida um IPv4
     */
    public static function ipv4(string $ip): bool
    {
        return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false;
    }

    /**
     * Valida um IPv6
     */
    public static function ipv6(string $ip): bool
    {
        return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== false;
    }

    /**
     * Valida um JSON
     */
    public static function json(string $value): bool
    {
        json_decode($value);
        return json_last_error() === JSON_ERROR_NONE;
    }

    /**
     * Valida um UUID
     */
    public static function uuid(string $uuid): bool
    {
        return preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $uuid) === 1;
    }

    /**
     * Valida uma senha forte
     * Mínimo 8 caracteres, pelo menos uma letra maiúscula, uma minúscula, um número e um caractere especial
     */
    public static function strongPassword(string $password): bool
    {
        return preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/', $password) === 1;
    }

    /**
     * Valida múltiplas regras
     * 
     * @param mixed $value
     * @param array $rules ['email', 'minLength:5', 'maxLength:100']
     * @return array Erros encontrados
     */
    public static function validate(mixed $value, array $rules): array
    {
        $errors = [];

        foreach ($rules as $rule) {
            $parts = explode(':', $rule, 2);
            $ruleName = $parts[0];
            $ruleParams = isset($parts[1]) ? explode(',', $parts[1]) : [];

            $valid = match ($ruleName) {
                'email' => self::email($value),
                'url' => self::url($value),
                'cpf' => self::cpf($value),
                'cnpj' => self::cnpj($value),
                'phone' => self::phone($value),
                'cep' => self::cep($value),
                'alpha' => self::alpha($value),
                'alphaNumeric' => self::alphaNumeric($value),
                'numeric' => self::numeric($value),
                'minLength' => self::minLength($value, (int) $ruleParams[0]),
                'maxLength' => self::maxLength($value, (int) $ruleParams[0]),
                'between' => self::between($value, $ruleParams[0], $ruleParams[1]),
                'in' => self::in($value, $ruleParams),
                'notIn' => self::notIn($value, $ruleParams),
                'ip' => self::ip($value),
                'ipv4' => self::ipv4($value),
                'ipv6' => self::ipv6($value),
                'json' => self::json($value),
                'uuid' => self::uuid($value),
                'strongPassword' => self::strongPassword($value),
                default => true,
            };

            if (!$valid) {
                $errors[] = "Validation failed for rule: {$rule}";
            }
        }

        return $errors;
    }
}
