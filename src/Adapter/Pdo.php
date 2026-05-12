<?php

declare(strict_types=1);

namespace Lmc\User\Repository\Pdo\Adapter;

use Laminas\EventManager\EventManager;
use Laminas\EventManager\EventManagerAwareTrait;
use Laminas\Hydrator\HydratorInterface;
use Lmc\User\Repository\AdapterInterface;
use Lmc\User\Repository\UserInterface;
use Override;

use function assert;

class Pdo implements AdapterInterface
{
    use EventManagerAwareTrait;

    public function __construct(
        protected \PDO $pdo,
        protected HydratorInterface $hydrator,
        protected readonly UserInterface $entityPrototype,
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
    public function insert(UserInterface $user): mixed
    {
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
    public function update(UserInterface $user): mixed
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
    public function delete(UserInterface $user): mixed
    {
        $this->getEventManager()->trigger(__FUNCTION__ . '.pre', $this, ['entity' => $user]);
        $statement = "DELETE FROM $this->tableName WHERE $this->idColumn=:id";
        $select    = $this->pdo->prepare($statement);
        $result    = $select->execute([
            ':id' => $user->getId(),
        ]);
        if (! $result) {
            return null;
        }
        return true;
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
}
