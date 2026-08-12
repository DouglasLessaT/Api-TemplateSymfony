<?php

namespace App\Domain\Entity;

use App\Core\Domain\Entity\BaseEntity;
use Doctrine\ORM\Mapping as ORM;

/**
 * User Entity
 * 
 * Representa um usuário do sistema com diferentes tipos (gratis, premium, admin).
 */
#[ORM\Entity]
#[ORM\Table(name: "users")]
class User extends BaseEntity
{
    public const TYPE_FREE = 'free';
    public const TYPE_PREMIUM = 'premium';
    public const TYPE_ADMIN = 'admin';

    #[ORM\Id]
    #[ORM\Column(type: "uuid", unique: true)]
    protected mixed $id;

    #[ORM\Column(length: 255, unique: true)]
    private string $email;

    #[ORM\Column(length: 255)]
    private string $password;

    #[ORM\Column(length: 100)]
    private string $name;

    #[ORM\Column(length: 20)]
    private string $type = self::TYPE_FREE; // free, premium, admin

    #[ORM\Column]
    private bool $isActive = true;

    #[ORM\Column(type: "datetime_immutable", nullable: true)]
    private ?\DateTimeImmutable $lastLoginAt = null;

    #[ORM\Column(type: "integer", options: ["default" => 0])]
    private int $scannedCardsCount = 0; // Contador de cards escaneados no período

    #[ORM\Column(type: "datetime_immutable", nullable: true)]
    private ?\DateTimeImmutable $lastResetAt = null; // Última vez que o contador foi resetado

    public function __construct(string $email, string $password, string $name)
    {
        parent::__construct();
        $this->email = $email;
        $this->setPassword($password);
        $this->name = $name;
        $this->lastResetAt = new \DateTimeImmutable();
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
        $this->markAsUpdated();
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): void
    {
        $this->password = password_hash($password, PASSWORD_BCRYPT);
        $this->markAsUpdated();
    }

    public function verifyPassword(string $password): bool
    {
        return password_verify($password, $this->password);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
        $this->markAsUpdated();
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        if (!in_array($type, [self::TYPE_FREE, self::TYPE_PREMIUM, self::TYPE_ADMIN])) {
            throw new \InvalidArgumentException("Invalid user type: {$type}");
        }
        $this->type = $type;
        $this->markAsUpdated();
    }

    public function isFree(): bool
    {
        return $this->type === self::TYPE_FREE;
    }

    public function isPremium(): bool
    {
        return $this->type === self::TYPE_PREMIUM;
    }

    public function isAdmin(): bool
    {
        return $this->type === self::TYPE_ADMIN;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): void
    {
        $this->isActive = $isActive;
        $this->markAsUpdated();
    }

    public function getLastLoginAt(): ?\DateTimeImmutable
    {
        return $this->lastLoginAt;
    }

    public function setLastLoginAt(?\DateTimeImmutable $lastLoginAt): void
    {
        $this->lastLoginAt = $lastLoginAt;
        $this->markAsUpdated();
    }

    public function getScannedCardsCount(): int
    {
        return $this->scannedCardsCount;
    }

    public function incrementScannedCards(): void
    {
        $this->scannedCardsCount++;
        $this->markAsUpdated();
    }

    public function resetScannedCardsCount(): void
    {
        $this->scannedCardsCount = 0;
        $this->lastResetAt = new \DateTimeImmutable();
        $this->markAsUpdated();
    }

    public function getLastResetAt(): ?\DateTimeImmutable
    {
        return $this->lastResetAt;
    }

    /**
     * Verifica se o usuário pode escanear mais cards (limite de 7 para usuários gratuitos)
     */
    public function canScanCard(): bool
    {
        if ($this->isPremium() || $this->isAdmin()) {
            return true; // Sem limite
        }

        // Usuário gratuito: máximo 7 cards por anúncio
        // Verifica se precisa resetar o contador (a cada 24 horas)
        if ($this->lastResetAt) {
            $now = new \DateTimeImmutable();
            $diff = $now->diff($this->lastResetAt);
            if ($diff->days >= 1) {
                $this->resetScannedCardsCount();
            }
        }

        return $this->scannedCardsCount < 7;
    }

    /**
     * Verifica se o usuário pode criar coleções (apenas premium)
     */
    public function canCreateCollections(): bool
    {
        return $this->isPremium() || $this->isAdmin();
    }

    /**
     * Verifica se o usuário pode criar decks (apenas premium)
     */
    public function canCreateDecks(): bool
    {
        return $this->isPremium() || $this->isAdmin();
    }

    /**
     * Verifica se o usuário pode gerenciar outros usuários (apenas admin)
     */
    public function canManageUsers(): bool
    {
        return $this->isAdmin();
    }

    /**
     * Verifica se o usuário pode gerar relatórios (apenas admin)
     */
    public function canGenerateReports(): bool
    {
        return $this->isAdmin();
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'email' => $this->email,
            'name' => $this->name,
            'type' => $this->type,
            'isActive' => $this->isActive,
            'lastLoginAt' => $this->lastLoginAt?->format('Y-m-d H:i:s'),
            'scannedCardsCount' => $this->scannedCardsCount,
            'canScanCard' => $this->canScanCard(),
            'canCreateCollections' => $this->canCreateCollections(),
            'canCreateDecks' => $this->canCreateDecks(),
        ]);
    }
}

