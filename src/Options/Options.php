<?php

declare(strict_types=1);

namespace Lmc\User\Repository\Pdo\Options;

use Laminas\Stdlib\AbstractOptions;
use Lmc\User\Repository\Pdo\Entity\User;
use Lmc\User\Repository\UserInterface;
use Webmozart\Assert\Assert;

use function strlen;

/**
 * @template TValue
 * @extends AbstractOptions<TValue>
 */
final class Options extends AbstractOptions
{
    // phpcs:disable PSR2.Classes.PropertyDeclaration.Underscore,WebimpressCodingStandard.NamingConventions.ValidVariableName.NotCamelCapsProperty
    /**
     * Turn off strict options mode
     *
     * @var bool $__strictMode__
     */
    protected $__strictMode__ = false;
    // phpcs:enable

    protected string $userEntityClass = User::class;

    protected string $tableName = 'user';

    protected string $idFieldName = 'id';

    protected string $rolesDelimiter = ',';

    protected array $pdo = [];

    protected int $passwordCost = 14;

    public function setUserEntityClass(string $userEntityClass): Options
    {
        Assert::classExists($userEntityClass);
        Assert::implementsInterface($userEntityClass, UserInterface::class);
        $this->userEntityClass = $userEntityClass;
        return $this;
    }

    public function getUserEntityClass(): string
    {
        return $this->userEntityClass;
    }

    public function setTableName(string $tableName): Options
    {
        $this->tableName = $tableName;
        return $this;
    }

    public function getTableName(): string
    {
        return $this->tableName;
    }

    public function getIdFieldName(): string
    {
        return $this->idFieldName;
    }

    public function setIdFieldName(string $idFieldName): self
    {
        $this->idFieldName = $idFieldName;
        return $this;
    }

    public function getRolesDelimiter(): string
    {
        return $this->rolesDelimiter;
    }

    /**
     * @param non-empty-string $rolesDelimiter
     */
    public function setRolesDelimiter(string $rolesDelimiter): self
    {
        if (strlen($rolesDelimiter) > 0) {
            $this->rolesDelimiter = $rolesDelimiter;
        }
        return $this;
    }

    public function getPdo(): array
    {
        return $this->pdo;
    }

    public function setPdo(array $pdo): self
    {
        $this->pdo = $pdo;
        return $this;
    }

    public function getPasswordCost(): int
    {
        return $this->passwordCost;
    }

    public function setPasswordCost(int $passwordCost): self
    {
        $this->passwordCost = $passwordCost;
        return $this;
    }
}
