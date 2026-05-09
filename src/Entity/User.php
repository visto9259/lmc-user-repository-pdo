<?php

declare(strict_types=1);

namespace Lmc\User\Repository\Pdo\Entity;

use Lmc\User\Repository\UserInterface;
use Override;

class User implements UserInterface
{
    protected int|string|null $id  = null;
    protected ?string $username    = null;
    protected ?string $password    = null;
    protected ?string $email       = null;
    protected ?string $displayName = null;
    protected array $roles         = [];
    protected int|string|null $state = null;

    /**
     * @inheritDoc
     */
    #[Override]
    public function getDetails(): array
    {
        return [
            'id'          => $this->id,
            'username'    => $this->username,
            'email'       => $this->email,
            'displayName' => $this->displayName,
            'roles'       => $this->roles,
            'state'       => $this->state,
        ];
    }

    #[Override]
    public function getId(): string|int|null
    {
        return $this->id;
    }

    #[Override]
    public function setId(int|string $id): UserInterface
    {
        $this->id = $id;
        return $this;
    }

    #[Override]
    public function getUsername(): ?string
    {
        return $this->username;
    }

    #[Override]
    public function setUsername(string|null $username): UserInterface
    {
        $this->username = $username;
        return $this;
    }

    #[Override]
    public function getEmail(): ?string
    {
        return $this->email;
    }

    #[Override]
    public function setEmail(string|null $email): UserInterface
    {
        $this->email = $email;
        return $this;
    }

    #[Override]
    public function getDisplayName(): ?string
    {
        return $this->displayName;
    }

    #[Override]
    public function setDisplayName(string|null $displayName): UserInterface
    {
        $this->displayName = $displayName;
        return $this;
    }

    #[Override]
    public function getPassword(): ?string
    {
        return $this->password;
    }

    #[Override]
    public function setPassword(string|null $password): UserInterface
    {
        $this->password = $password;
        return $this;
    }

    #[Override]
    public function getState(): int|string|null
    {
        return $this->state;
    }

    #[Override]
    public function setState(int|string|null $state): UserInterface
    {
        $this->state = $state;
        return $this;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function getIdentity(): string
    {
        if (null === $this->id) {
            return (string) null;
        }
        return (string) $this->id;
    }

    /**
     * @inheritDoc
     */
    public function getRoles(): iterable
    {
        return $this->roles;
    }

    #[Override]
    public function setRoles(array $roles=[]): UserInterface
    {
        $this->roles = $roles;
        return $this;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function getDetail(string $name, $default = null)
    {
        return $this->getDetails()[$name] ?? $default;
    }
}
