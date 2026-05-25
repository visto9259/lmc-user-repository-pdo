<?php

declare(strict_types=1);

namespace Lmc\User\Repository\Pdo\Adapter;

use Laminas\EventManager\EventManager;
use Laminas\EventManager\EventManagerAwareTrait;
use Laminas\Hydrator\HydratorInterface;
use Lmc\User\Repository\AdapterInterface;
use Lmc\User\Repository\UserInterface;
use Override;
use Webmozart\Assert\Assert;

use function assert;
use function explode;
use function password_hash;
use function password_verify;
use function preg_match;

use const PASSWORD_BCRYPT;

class Pdo implements AdapterInterface
{
    use EventManagerAwareTrait;

    public function __construct(
        protected \PDO $pdo,
        protected HydratorInterface $hydrator,
        protected readonly UserInterface $entityPrototype,
        protected readonly int $passwordCost,
        protected readonly ?string $tableName = 'user',
        protected readonly ?string $idColumn = 'id',
    ) {
        $this->setEventManager(new EventManager());
    }

    #[Override]
    public function findById(int|string $id): ?UserInterface
    {
        $entity = $this->innerSelect($this->idColumn, $id);
        assert($entity instanceof UserInterface || null === $entity);
        $this->getEventManager()->trigger('find', $this, ['entity' => $entity]);
        return $entity;
    }

    #[Override]
    public function findByUsername(string $username): ?UserInterface
    {
        $entity = $this->innerSelect('username', $username);
        assert($entity instanceof UserInterface || null === $entity);
        $this->getEventManager()->trigger('find', $this, ['entity' => $entity]);
        return $entity;
    }

    #[Override]
    public function findByEmail(string $email): ?UserInterface
    {
        $entity = $this->innerSelect('email', $email);
        assert($entity instanceof UserInterface || null === $entity);
        $this->getEventManager()->trigger('find', $this, ['entity' => $entity]);
        return $entity;
    }

    #[Override]
    public function insert(UserInterface $user): ?UserInterface
    {
        if (! $this->isHash($user->getPassword())) {
            $user->setPassword(password_hash($user->getPassword(), PASSWORD_BCRYPT, ['cost' => $this->passwordCost]));
        }
        $this->getEventManager()->trigger(__FUNCTION__ . '.pre', $this, ['entity' => $user]);
        $data      = $this->hydrator->extract($user);
        $statement = "INSERT INTO $this->tableName (username, email, display_name, password, state, roles)
            VALUES (:username, :email, :display_name, :password, :state, :roles)";
        $select    = $this->pdo->prepare($statement);
        $result    = $select->execute([
            ':username'     => $data['username'],
            ':email'        => $data['email'],
            ':display_name' => $data['display_name'],
            ':password'     => $data['password'],
            ':state'        => $data['state'],
            ':roles'        => $data['roles'],
        ]);
        if (! $result) {
            return null;
        }
        $lastInsertId = $this->pdo->lastInsertId();
        $entity       = $this->innerSelect($this->idColumn, $lastInsertId);
        $this->getEventManager()->trigger(__FUNCTION__ . '.post', $this, ['entity' => $entity]);
        return $entity;
    }

    #[Override]
    public function update(UserInterface $user): ?UserInterface
    {
        $this->getEventManager()->trigger(__FUNCTION__ . '.pre', $this, ['entity' => $user]);
        $data      = $this->hydrator->extract($user);
        $id        = $data[$this->idColumn];
        $statement = "UPDATE $this->tableName SET
                            username     = :username,
                            email        = :email,
                            display_name = :display_name,
                            password     = :password,
                            state        = :state,
                            roles        = :roles
                            WHERE id = :id";
        $select    = $this->pdo->prepare($statement);
        $result    = $select->execute([
            ':username'     => $data['username'],
            ':email'        => $data['email'],
            ':display_name' => $data['display_name'],
            ':password'     => $data['password'],
            ':state'        => $data['state'],
            ':roles'        => $data['roles'],
            ':id'           => $id,
        ]);
        if (! $result) {
            return null;
        }
        $entity = $this->innerSelect($this->idColumn, $id);
        $this->getEventManager()->trigger(__FUNCTION__ . '.post', $this, ['entity' => $entity]);
        return $entity;
    }

    #[Override]
    public function delete(UserInterface $user): bool
    {
        $this->getEventManager()->trigger(__FUNCTION__ . '.pre', $this, ['entity' => $user]);
        $statement = "DELETE FROM $this->tableName WHERE $this->idColumn=:id";
        $select    = $this->pdo->prepare($statement);
        $result    = $select->execute([
            ':id' => $user->getId(),
        ]);
        if (! $result) {
            return false;
        }
        return true;
    }

    #[Override]
    public function validateCredential(UserInterface $user, mixed $credential): bool
    {
        Assert::string($credential);
        Assert::string($user->getPassword());
        return password_verify($credential, $user->getPassword());
    }

    #[Override]
    public function updateCredential(UserInterface $user, mixed $credential): void
    {
        Assert::string($credential);
        Assert::string($user->getPassword());
        if (
            ! $this->validateCredential($user, $credential)
            || $this->costChanged($user->getPassword(), $this->passwordCost)
        ) {
            // Password was changed or cost has changed
            $user->setPassword(password_hash((string) $credential, PASSWORD_BCRYPT, ['cost' => $this->passwordCost]));
            $statement = "UPDATE $this->tableName SET password=:credential WHERE id=:id";
            $select    = $this->pdo->prepare($statement);
            $select->execute([
                ':credential' => $user->getPassword(),
                ':id'         => $user->getId(),
            ]);
        }
    }

    private function innerSelect(string $field, int|string $value): ?UserInterface
    {
        $statement = "SELECT * FROM $this->tableName WHERE $field=:value";
        $select    = $this->pdo->prepare($statement);
        $select->execute([
            ':value' => $value,
        ]);
        $row = $select->fetch(\PDO::FETCH_ASSOC);
        if ($row === false) {
            return null;
        }
        $entity = clone $this->entityPrototype;
        $this->hydrator->hydrate($row, $entity);
        return $entity;
    }

    private function isHash(string $password): bool
    {
        $hash = [];
        return preg_match('/^\$2y\$\d+\$/', $password, $hash) === 1;
    }

    private function costChanged(string $password, int $cost): bool
    {
        $hash = explode('$', $password);
        return $hash[2] !== (string) $cost;
    }
}
