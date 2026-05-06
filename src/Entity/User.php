<?php

declare(strict_types=1);

namespace Lmc\User\Repository\Pdo\Entity;

use Lmc\User\Repository\UserInterface;

class User implements UserInterface
{
    protected int|string|null $id  = null;
    protected ?string $username    = null;
    protected ?string $password    = null;
    protected ?string $email       = null;
    protected ?string $displayName = null;
    protected array $roles         = [];
    protected ?int $state          = null;

    /**
     * @inheritDoc
     */
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

    public function getId(): string|int|null
    {
        return $this->id;
    }

    public function setId(int|string $id): UserInterface
    {
        $this->id = $id;
        return $this;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): UserInterface
    {
        $this->username = $username;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): UserInterface
    {
        $this->email = $email;
        return $this;
    }

    public function getDisplayName(): ?string
    {
        return $this->displayName;
    }

    public function setDisplayName(string $displayName): UserInterface
    {
        $this->displayName = $displayName;
        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): UserInterface
    {
        $this->password = $password;
        return $this;
    }

    public function getState(): ?int
    {
        return $this->state;
    }

    public function setState(int $state): UserInterface
    {
        $this->state = $state;
        return $this;
    }

    /**
     * @inheritDoc
     */
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

    public function setRoles(array $roles): UserInterface
    {
        $this->roles = $roles;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getDetail(string $name, $default = null)
    {
        return $this->getDetails()[$name] ?? $default;
    }
}
