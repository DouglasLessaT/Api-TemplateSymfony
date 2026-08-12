<?php

namespace App\Core\Infrastructure\Session;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Session Manager
 * 
 * Wrapper para gerenciamento de sessão do Symfony.
 * Fornece métodos simplificados para manipulação de sessão.
 */
class SessionManager
{
    private ?SessionInterface $session;

    public function __construct(private RequestStack $requestStack)
    {
        $this->session = $requestStack->getSession();
    }

    /**
     * Obtém um valor da sessão
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->session?->get($key, $default);
    }

    /**
     * Define um valor na sessão
     */
    public function set(string $key, mixed $value): void
    {
        $this->session?->set($key, $value);
    }

    /**
     * Verifica se uma chave existe na sessão
     */
    public function has(string $key): bool
    {
        return $this->session?->has($key) ?? false;
    }

    /**
     * Remove um valor da sessão
     */
    public function remove(string $key): void
    {
        $this->session?->remove($key);
    }

    /**
     * Limpa toda a sessão
     */
    public function clear(): void
    {
        $this->session?->clear();
    }

    /**
     * Invalida a sessão
     */
    public function invalidate(): void
    {
        $this->session?->invalidate();
    }

    /**
     * Obtém o ID da sessão
     */
    public function getId(): ?string
    {
        return $this->session?->getId();
    }

    /**
     * Define o ID da sessão
     */
    public function setId(string $id): void
    {
        $this->session?->setId($id);
    }

    /**
     * Adiciona uma mensagem flash
     */
    public function addFlash(string $type, mixed $message): void
    {
        $this->session?->getFlashBag()->add($type, $message);
    }

    /**
     * Obtém mensagens flash
     */
    public function getFlashes(string $type): array
    {
        return $this->session?->getFlashBag()->get($type) ?? [];
    }

    /**
     * Verifica se há mensagens flash de um tipo
     */
    public function hasFlashes(string $type): bool
    {
        return $this->session?->getFlashBag()->has($type) ?? false;
    }

    /**
     * Obtém todas as mensagens flash
     */
    public function getAllFlashes(): array
    {
        return $this->session?->getFlashBag()->all() ?? [];
    }

    /**
     * Obtém um valor de um namespace específico
     */
    public function getNamespaced(string $namespace, string $key, mixed $default = null): mixed
    {
        $data = $this->get($namespace, []);
        return $data[$key] ?? $default;
    }

    /**
     * Define um valor em um namespace específico
     */
    public function setNamespaced(string $namespace, string $key, mixed $value): void
    {
        $data = $this->get($namespace, []);
        $data[$key] = $value;
        $this->set($namespace, $data);
    }

    /**
     * Remove um valor de um namespace específico
     */
    public function removeNamespaced(string $namespace, string $key): void
    {
        $data = $this->get($namespace, []);
        unset($data[$key]);
        $this->set($namespace, $data);
    }

    /**
     * Obtém todos os atributos da sessão
     */
    public function all(): array
    {
        return $this->session?->all() ?? [];
    }

    /**
     * Inicia a sessão
     */
    public function start(): bool
    {
        return $this->session?->start() ?? false;
    }

    /**
     * Verifica se a sessão foi iniciada
     */
    public function isStarted(): bool
    {
        return $this->session?->isStarted() ?? false;
    }

    /**
     * Salva a sessão
     */
    public function save(): void
    {
        $this->session?->save();
    }
}
