<?php

namespace App\Core\Util;

use DateTimeImmutable;
use DateTimeZone;
use DateInterval;
use Exception;

/**
 * DateTime Helper
 * 
 * Biblioteca de funções para manipulação de data e hora.
 */
class DateTimeHelper
{
    /**
     * Cria um DateTimeImmutable a partir de uma string
     */
    public static function parse(string $datetime, ?DateTimeZone $timezone = null): DateTimeImmutable
    {
        return new DateTimeImmutable($datetime, $timezone);
    }

    /**
     * Cria um DateTimeImmutable para agora
     */
    public static function now(?DateTimeZone $timezone = null): DateTimeImmutable
    {
        return new DateTimeImmutable('now', $timezone);
    }

    /**
     * Cria um DateTimeImmutable para hoje (meia-noite)
     */
    public static function today(?DateTimeZone $timezone = null): DateTimeImmutable
    {
        return self::now($timezone)->setTime(0, 0, 0);
    }

    /**
     * Cria um DateTimeImmutable para amanhã (meia-noite)
     */
    public static function tomorrow(?DateTimeZone $timezone = null): DateTimeImmutable
    {
        return self::today($timezone)->modify('+1 day');
    }

    /**
     * Cria um DateTimeImmutable para ontem (meia-noite)
     */
    public static function yesterday(?DateTimeZone $timezone = null): DateTimeImmutable
    {
        return self::today($timezone)->modify('-1 day');
    }

    /**
     * Formata uma data
     */
    public static function format(DateTimeImmutable $datetime, string $format = 'Y-m-d H:i:s'): string
    {
        return $datetime->format($format);
    }

    /**
     * Formata uma data no padrão brasileiro
     */
    public static function formatBr(DateTimeImmutable $datetime, bool $includeTime = false): string
    {
        $format = $includeTime ? 'd/m/Y H:i:s' : 'd/m/Y';
        return $datetime->format($format);
    }

    /**
     * Formata uma data no padrão ISO 8601
     */
    public static function formatIso(DateTimeImmutable $datetime): string
    {
        return $datetime->format('c');
    }

    /**
     * Calcula a diferença entre duas datas em dias
     */
    public static function diffInDays(DateTimeImmutable $start, DateTimeImmutable $end): int
    {
        return (int) $start->diff($end)->format('%r%a');
    }

    /**
     * Calcula a diferença entre duas datas em horas
     */
    public static function diffInHours(DateTimeImmutable $start, DateTimeImmutable $end): int
    {
        return (int) ($start->diff($end)->h + ($start->diff($end)->days * 24));
    }

    /**
     * Calcula a diferença entre duas datas em minutos
     */
    public static function diffInMinutes(DateTimeImmutable $start, DateTimeImmutable $end): int
    {
        return (int) (self::diffInHours($start, $end) * 60 + $start->diff($end)->i);
    }

    /**
     * Calcula a diferença entre duas datas em segundos
     */
    public static function diffInSeconds(DateTimeImmutable $start, DateTimeImmutable $end): int
    {
        return $end->getTimestamp() - $start->getTimestamp();
    }

    /**
     * Adiciona dias a uma data
     */
    public static function addDays(DateTimeImmutable $datetime, int $days): DateTimeImmutable
    {
        return $datetime->modify(sprintf('%+d days', $days));
    }

    /**
     * Adiciona horas a uma data
     */
    public static function addHours(DateTimeImmutable $datetime, int $hours): DateTimeImmutable
    {
        return $datetime->modify(sprintf('%+d hours', $hours));
    }

    /**
     * Adiciona minutos a uma data
     */
    public static function addMinutes(DateTimeImmutable $datetime, int $minutes): DateTimeImmutable
    {
        return $datetime->modify(sprintf('%+d minutes', $minutes));
    }

    /**
     * Verifica se uma data é passado
     */
    public static function isPast(DateTimeImmutable $datetime): bool
    {
        return $datetime < self::now();
    }

    /**
     * Verifica se uma data é futuro
     */
    public static function isFuture(DateTimeImmutable $datetime): bool
    {
        return $datetime > self::now();
    }

    /**
     * Verifica se uma data é hoje
     */
    public static function isToday(DateTimeImmutable $datetime): bool
    {
        return $datetime->format('Y-m-d') === self::today()->format('Y-m-d');
    }

    /**
     * Verifica se uma data é amanhã
     */
    public static function isTomorrow(DateTimeImmutable $datetime): bool
    {
        return $datetime->format('Y-m-d') === self::tomorrow()->format('Y-m-d');
    }

    /**
     * Verifica se uma data é ontem
     */
    public static function isYesterday(DateTimeImmutable $datetime): bool
    {
        return $datetime->format('Y-m-d') === self::yesterday()->format('Y-m-d');
    }

    /**
     * Verifica se uma data está entre duas outras
     */
    public static function isBetween(DateTimeImmutable $datetime, DateTimeImmutable $start, DateTimeImmutable $end): bool
    {
        return $datetime >= $start && $datetime <= $end;
    }

    /**
     * Retorna o início do dia
     */
    public static function startOfDay(DateTimeImmutable $datetime): DateTimeImmutable
    {
        return $datetime->setTime(0, 0, 0);
    }

    /**
     * Retorna o fim do dia
     */
    public static function endOfDay(DateTimeImmutable $datetime): DateTimeImmutable
    {
        return $datetime->setTime(23, 59, 59);
    }

    /**
     * Retorna o início do mês
     */
    public static function startOfMonth(DateTimeImmutable $datetime): DateTimeImmutable
    {
        return $datetime->modify('first day of this month')->setTime(0, 0, 0);
    }

    /**
     * Retorna o fim do mês
     */
    public static function endOfMonth(DateTimeImmutable $datetime): DateTimeImmutable
    {
        return $datetime->modify('last day of this month')->setTime(23, 59, 59);
    }

    /**
     * Retorna o início do ano
     */
    public static function startOfYear(DateTimeImmutable $datetime): DateTimeImmutable
    {
        return $datetime->modify('first day of January')->setTime(0, 0, 0);
    }

    /**
     * Retorna o fim do ano
     */
    public static function endOfYear(DateTimeImmutable $datetime): DateTimeImmutable
    {
        return $datetime->modify('last day of December')->setTime(23, 59, 59);
    }

    /**
     * Formata uma data de forma "humana" (ex: "há 2 horas")
     */
    public static function diffForHumans(DateTimeImmutable $datetime, ?DateTimeImmutable $reference = null): string
    {
        $reference = $reference ?? self::now();
        $diff = $reference->diff($datetime);

        if ($diff->y > 0) {
            return $diff->y === 1 ? 'há 1 ano' : "há {$diff->y} anos";
        }

        if ($diff->m > 0) {
            return $diff->m === 1 ? 'há 1 mês' : "há {$diff->m} meses";
        }

        if ($diff->d > 0) {
            return $diff->d === 1 ? 'há 1 dia' : "há {$diff->d} dias";
        }

        if ($diff->h > 0) {
            return $diff->h === 1 ? 'há 1 hora' : "há {$diff->h} horas";
        }

        if ($diff->i > 0) {
            return $diff->i === 1 ? 'há 1 minuto' : "há {$diff->i} minutos";
        }

        return 'agora';
    }

    /**
     * Converte timestamp para DateTimeImmutable
     */
    public static function fromTimestamp(int $timestamp, ?DateTimeZone $timezone = null): DateTimeImmutable
    {
        return (new DateTimeImmutable('@' . $timestamp))->setTimezone($timezone ?? new DateTimeZone(date_default_timezone_get()));
    }

    /**
     * Cria um DateTimeZone
     */
    public static function timezone(string $timezone): DateTimeZone
    {
        return new DateTimeZone($timezone);
    }

    /**
     * Converte para outro timezone
     */
    public static function convertTimezone(DateTimeImmutable $datetime, string|DateTimeZone $timezone): DateTimeImmutable
    {
        if (is_string($timezone)) {
            $timezone = new DateTimeZone($timezone);
        }

        return $datetime->setTimezone($timezone);
    }
}
