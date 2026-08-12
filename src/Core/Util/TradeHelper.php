<?php

namespace App\Core\Util;

/**
 * Trade Helper
 * 
 * Biblioteca de funções para lógica de trading e mercado financeiro.
 * Suporta conversões entre BRL, USD e criptomoedas, cálculos de lucro/prejuízo,
 * taxas, indicadores e análise de trading.
 */
class TradeHelper
{
    /**
     * Converte um valor de uma moeda para outra
     * 
     * @param float $amount Valor a converter
     * @param float $exchangeRate Taxa de câmbio (quanto vale 1 unidade da moeda origem na moeda destino)
     * @param int $precision Precisão decimal
     */
    public static function convert(float $amount, float $exchangeRate, int $precision = 2): float
    {
        return MathHelper::roundDecimal($amount * $exchangeRate, $precision);
    }

    /**
     * Converte BRL para USD
     */
    public static function brlToUsd(float $amountBrl, float $usdRate, int $precision = 2): float
    {
        return self::convert($amountBrl, 1 / $usdRate, $precision);
    }

    /**
     * Converte USD para BRL
     */
    public static function usdToBrl(float $amountUsd, float $usdRate, int $precision = 2): float
    {
        return self::convert($amountUsd, $usdRate, $precision);
    }

    /**
     * Converte BRL para criptomoeda
     */
    public static function brlToCrypto(float $amountBrl, float $cryptoPriceBrl, int $precision = 8): float
    {
        return self::convert($amountBrl, 1 / $cryptoPriceBrl, $precision);
    }

    /**
     * Converte criptomoeda para BRL
     */
    public static function cryptoToBrl(float $amountCrypto, float $cryptoPriceBrl, int $precision = 2): float
    {
        return self::convert($amountCrypto, $cryptoPriceBrl, $precision);
    }

    /**
     * Converte USD para criptomoeda
     */
    public static function usdToCrypto(float $amountUsd, float $cryptoPriceUsd, int $precision = 8): float
    {
        return self::convert($amountUsd, 1 / $cryptoPriceUsd, $precision);
    }

    /**
     * Converte criptomoeda para USD
     */
    public static function cryptoToUsd(float $amountCrypto, float $cryptoPriceUsd, int $precision = 2): float
    {
        return self::convert($amountCrypto, $cryptoPriceUsd, $precision);
    }

    /**
     * Calcula o lucro/prejuízo absoluto
     * 
     * @param float $buyPrice Preço de compra
     * @param float $sellPrice Preço de venda
     * @param float $quantity Quantidade
     */
    public static function profitLoss(float $buyPrice, float $sellPrice, float $quantity = 1): float
    {
        return ($sellPrice - $buyPrice) * $quantity;
    }

    /**
     * Calcula o lucro/prejuízo percentual
     */
    public static function profitLossPercent(float $buyPrice, float $sellPrice): float
    {
        if ($buyPrice == 0) {
            return 0;
        }

        return (($sellPrice - $buyPrice) / $buyPrice) * 100;
    }

    /**
     * Calcula o ROI (Return on Investment)
     */
    public static function roi(float $initialInvestment, float $finalValue): float
    {
        if ($initialInvestment == 0) {
            return 0;
        }

        return (($finalValue - $initialInvestment) / $initialInvestment) * 100;
    }

    /**
     * Calcula o valor com taxa aplicada
     * 
     * @param float $amount Valor base
     * @param float $feePercent Taxa em percentual (ex: 0.5 para 0.5%)
     * @param bool $subtract Se true, subtrai a taxa; se false, adiciona
     */
    public static function applyFee(float $amount, float $feePercent, bool $subtract = true): float
    {
        $fee = ($amount * $feePercent) / 100;
        return $subtract ? $amount - $fee : $amount + $fee;
    }

    /**
     * Calcula o valor da taxa
     */
    public static function calculateFee(float $amount, float $feePercent): float
    {
        return ($amount * $feePercent) / 100;
    }

    /**
     * Calcula o preço médio de compra
     * 
     * @param array $purchases Array de compras ['price' => float, 'quantity' => float]
     */
    public static function averageBuyPrice(array $purchases): float
    {
        $totalCost = 0;
        $totalQuantity = 0;

        foreach ($purchases as $purchase) {
            $totalCost += $purchase['price'] * $purchase['quantity'];
            $totalQuantity += $purchase['quantity'];
        }

        if ($totalQuantity == 0) {
            return 0;
        }

        return $totalCost / $totalQuantity;
    }

    /**
     * Calcula o breakeven (ponto de equilíbrio) considerando taxas
     * 
     * @param float $buyPrice Preço de compra
     * @param float $buyFee Taxa de compra (%)
     * @param float $sellFee Taxa de venda (%)
     */
    public static function breakeven(float $buyPrice, float $buyFee = 0, float $sellFee = 0): float
    {
        $totalFee = $buyFee + $sellFee;
        return $buyPrice * (1 + ($totalFee / 100));
    }

    /**
     * Calcula o stop loss (preço de parada de perda)
     * 
     * @param float $entryPrice Preço de entrada
     * @param float $lossPercent Percentual de perda aceitável (ex: 5 para 5%)
     */
    public static function stopLoss(float $entryPrice, float $lossPercent): float
    {
        return $entryPrice * (1 - ($lossPercent / 100));
    }

    /**
     * Calcula o take profit (preço alvo de lucro)
     * 
     * @param float $entryPrice Preço de entrada
     * @param float $profitPercent Percentual de lucro desejado (ex: 10 para 10%)
     */
    public static function takeProfit(float $entryPrice, float $profitPercent): float
    {
        return $entryPrice * (1 + ($profitPercent / 100));
    }

    /**
     * Calcula o tamanho da posição baseado no risco
     * 
     * @param float $accountBalance Saldo da conta
     * @param float $riskPercent Percentual de risco (ex: 2 para 2%)
     * @param float $entryPrice Preço de entrada
     * @param float $stopLossPrice Preço do stop loss
     */
    public static function positionSize(
        float $accountBalance,
        float $riskPercent,
        float $entryPrice,
        float $stopLossPrice
    ): float {
        $riskAmount = ($accountBalance * $riskPercent) / 100;
        $priceRisk = abs($entryPrice - $stopLossPrice);

        if ($priceRisk == 0) {
            return 0;
        }

        return $riskAmount / $priceRisk;
    }

    /**
     * Calcula a relação risco/recompensa
     * 
     * @param float $entryPrice Preço de entrada
     * @param float $stopLossPrice Preço do stop loss
     * @param float $takeProfitPrice Preço do take profit
     */
    public static function riskRewardRatio(
        float $entryPrice,
        float $stopLossPrice,
        float $takeProfitPrice
    ): float {
        $risk = abs($entryPrice - $stopLossPrice);
        $reward = abs($takeProfitPrice - $entryPrice);

        if ($risk == 0) {
            return 0;
        }

        return $reward / $risk;
    }

    /**
     * Calcula a variação percentual entre dois preços
     */
    public static function priceChange(float $oldPrice, float $newPrice): float
    {
        return MathHelper::percentDifference($oldPrice, $newPrice);
    }

    /**
     * Calcula a volatilidade (desvio padrão) de uma série de preços
     * 
     * @param array $prices Array de preços
     */
    public static function volatility(array $prices): float
    {
        if (empty($prices)) {
            return 0;
        }

        $mean = MathHelper::average($prices);
        $variance = 0;

        foreach ($prices as $price) {
            $variance += pow($price - $mean, 2);
        }

        $variance /= count($prices);

        return sqrt($variance);
    }

    /**
     * Calcula a média móvel simples (SMA)
     * 
     * @param array $prices Array de preços
     * @param int $period Período da média
     */
    public static function sma(array $prices, int $period): ?float
    {
        if (count($prices) < $period) {
            return null;
        }

        $slice = array_slice($prices, -$period);
        return MathHelper::average($slice);
    }

    /**
     * Calcula a média móvel exponencial (EMA)
     * 
     * @param array $prices Array de preços (do mais antigo para o mais recente)
     * @param int $period Período da média
     */
    public static function ema(array $prices, int $period): ?float
    {
        if (count($prices) < $period) {
            return null;
        }

        $multiplier = 2 / ($period + 1);
        $ema = MathHelper::average(array_slice($prices, 0, $period));

        for ($i = $period; $i < count($prices); $i++) {
            $ema = ($prices[$i] - $ema) * $multiplier + $ema;
        }

        return $ema;
    }

    /**
     * Calcula o RSI (Relative Strength Index)
     * 
     * @param array $prices Array de preços (do mais antigo para o mais recente)
     * @param int $period Período do RSI (padrão: 14)
     */
    public static function rsi(array $prices, int $period = 14): ?float
    {
        if (count($prices) < $period + 1) {
            return null;
        }

        $gains = [];
        $losses = [];

        for ($i = 1; $i < count($prices); $i++) {
            $change = $prices[$i] - $prices[$i - 1];
            $gains[] = $change > 0 ? $change : 0;
            $losses[] = $change < 0 ? abs($change) : 0;
        }

        $avgGain = MathHelper::average(array_slice($gains, -$period));
        $avgLoss = MathHelper::average(array_slice($losses, -$period));

        if ($avgLoss == 0) {
            return 100;
        }

        $rs = $avgGain / $avgLoss;
        return 100 - (100 / (1 + $rs));
    }

    /**
     * Formata um valor como moeda BRL
     */
    public static function formatBrl(float $amount): string
    {
        return 'R$ ' . number_format($amount, 2, ',', '.');
    }

    /**
     * Formata um valor como moeda USD
     */
    public static function formatUsd(float $amount): string
    {
        return '$' . number_format($amount, 2, '.', ',');
    }

    /**
     * Formata um valor de criptomoeda
     */
    public static function formatCrypto(float $amount, int $decimals = 8, string $symbol = ''): string
    {
        $formatted = number_format($amount, $decimals, '.', ',');
        return $symbol ? $formatted . ' ' . $symbol : $formatted;
    }

    /**
     * Calcula o valor total de um portfólio
     * 
     * @param array $holdings Array de posições ['quantity' => float, 'price' => float]
     */
    public static function portfolioValue(array $holdings): float
    {
        $total = 0;

        foreach ($holdings as $holding) {
            $total += $holding['quantity'] * $holding['price'];
        }

        return $total;
    }

    /**
     * Calcula a alocação percentual de cada ativo no portfólio
     * 
     * @param array $holdings Array de posições ['asset' => string, 'value' => float]
     * @return array Array com percentuais ['asset' => percent]
     */
    public static function portfolioAllocation(array $holdings): array
    {
        $total = array_sum(array_column($holdings, 'value'));
        $allocation = [];

        if ($total == 0) {
            return $allocation;
        }

        foreach ($holdings as $holding) {
            $allocation[$holding['asset']] = MathHelper::roundDecimal(
                ($holding['value'] / $total) * 100,
                2
            );
        }

        return $allocation;
    }

    /**
     * Calcula o spread (diferença entre compra e venda)
     */
    public static function spread(float $bidPrice, float $askPrice): float
    {
        return $askPrice - $bidPrice;
    }

    /**
     * Calcula o spread percentual
     */
    public static function spreadPercent(float $bidPrice, float $askPrice): float
    {
        if ($bidPrice == 0) {
            return 0;
        }

        return (($askPrice - $bidPrice) / $bidPrice) * 100;
    }

    /**
     * Verifica se um preço está em tendência de alta
     * 
     * @param array $prices Últimos preços (do mais antigo para o mais recente)
     * @param int $minPeriod Período mínimo para análise
     */
    public static function isUptrend(array $prices, int $minPeriod = 3): bool
    {
        if (count($prices) < $minPeriod) {
            return false;
        }

        $recentPrices = array_slice($prices, -$minPeriod);
        
        for ($i = 1; $i < count($recentPrices); $i++) {
            if ($recentPrices[$i] <= $recentPrices[$i - 1]) {
                return false;
            }
        }

        return true;
    }

    /**
     * Verifica se um preço está em tendência de baixa
     */
    public static function isDowntrend(array $prices, int $minPeriod = 3): bool
    {
        if (count($prices) < $minPeriod) {
            return false;
        }

        $recentPrices = array_slice($prices, -$minPeriod);
        
        for ($i = 1; $i < count($recentPrices); $i++) {
            if ($recentPrices[$i] >= $recentPrices[$i - 1]) {
                return false;
            }
        }

        return true;
    }

    /**
     * Calcula o valor em risco (VaR - Value at Risk) simplificado
     * 
     * @param float $portfolioValue Valor do portfólio
     * @param float $volatility Volatilidade (desvio padrão)
     * @param float $confidenceLevel Nível de confiança (ex: 1.65 para 95%)
     */
    public static function valueAtRisk(
        float $portfolioValue,
        float $volatility,
        float $confidenceLevel = 1.65
    ): float {
        return $portfolioValue * $volatility * $confidenceLevel;
    }
}
