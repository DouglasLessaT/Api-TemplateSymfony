<?php

namespace App\Core\Infrastructure\Security;

use DateTimeImmutable;
use Firebase\JWT\JWT as FirebaseJWT;
use Firebase\JWT\Key;

/**
 * JWT Manager
 * 
 * Gerenciador de tokens JWT.
 * Fornece métodos para criação, validação e decodificação de tokens.
 * 
 * Nota: Requer a biblioteca firebase/php-jwt
 * Instale com: composer require firebase/php-jwt
 */
class JWTManager
{
    private string $secretKey;
    private string $algorithm;
    private int $expirationTime;
    private string $issuer;

    public function __construct(
        string $secretKey,
        string $algorithm = 'HS256',
        int $expirationTime = 3600, // 1 hora
        string $issuer = 'app'
    ) {
        $this->secretKey = $secretKey;
        $this->algorithm = $algorithm;
        $this->expirationTime = $expirationTime;
        $this->issuer = $issuer;
    }

    /**
     * Gera um token JWT
     * 
     * @param array $payload Dados a serem incluídos no token
     * @param int|null $expiresIn Tempo de expiração em segundos (null = usar padrão)
     */
    public function generate(array $payload, ?int $expiresIn = null): string
    {
        $now = new DateTimeImmutable();
        $expiresAt = $now->modify('+' . ($expiresIn ?? $this->expirationTime) . ' seconds');

        $claims = array_merge($payload, [
            'iss' => $this->issuer,
            'iat' => $now->getTimestamp(),
            'exp' => $expiresAt->getTimestamp(),
        ]);

        return FirebaseJWT::encode($claims, $this->secretKey, $this->algorithm);
    }

    /**
     * Valida e decodifica um token JWT
     * 
     * @return array|null Retorna o payload se válido, null se inválido
     */
    public function decode(string $token): ?array
    {
        try {
            $decoded = FirebaseJWT::decode($token, new Key($this->secretKey, $this->algorithm));
            return (array) $decoded;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Valida um token JWT
     */
    public function validate(string $token): bool
    {
        return $this->decode($token) !== null;
    }

    /**
     * Extrai o payload de um token sem validar a assinatura
     * ATENÇÃO: Use apenas para debug, não para validação!
     */
    public function getPayloadWithoutValidation(string $token): ?array
    {
        try {
            $parts = explode('.', $token);
            if (count($parts) !== 3) {
                return null;
            }

            $payload = json_decode(base64_decode($parts[1]), true);
            return $payload ?: null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Verifica se um token está expirado
     */
    public function isExpired(string $token): bool
    {
        $payload = $this->getPayloadWithoutValidation($token);
        
        if (!$payload || !isset($payload['exp'])) {
            return true;
        }

        return $payload['exp'] < time();
    }

    /**
     * Gera um refresh token (token de longa duração)
     */
    public function generateRefreshToken(array $payload): string
    {
        return $this->generate($payload, 30 * 24 * 3600); // 30 dias
    }

    /**
     * Extrai um claim específico do token
     */
    public function getClaim(string $token, string $claim): mixed
    {
        $payload = $this->decode($token);
        return $payload[$claim] ?? null;
    }

    /**
     * Verifica se o token pertence a um usuário específico
     */
    public function belongsToUser(string $token, string|int $userId): bool
    {
        $payload = $this->decode($token);
        
        if (!$payload) {
            return false;
        }

        return isset($payload['sub']) && $payload['sub'] == $userId;
    }
}
