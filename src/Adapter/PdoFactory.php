<?php

declare(strict_types=1);

namespace Lmc\User\Repository\Pdo\Adapter;

use Laminas\Hydrator\HydratorInterface;
use Lmc\User\Repository\Pdo\Exception\ServiceNotCreatedException;
use Lmc\User\Repository\Pdo\Options\Options;
use PDO as BasePDO;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

use function gettype;
use function is_object;
use function is_string;
use function sprintf;

class PdoFactory
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): Pdo
    {
        /** @var Options $options */
        $options = $container->get(Options::class);

        $pdoOptions = $options->getPdo();

        if (
            ! isset($pdoOptions['dsn']) && ! is_string($pdoOptions['dsn'])
            || isset($pdoOptions['username']) && ! is_string($pdoOptions['username'])
            || isset($pdoOptions['password']) && ! is_string($pdoOptions['password'])
        ) {
            throw new ServiceNotCreatedException('Invalid Pdo configuration');
        }

        $basePdo = new BasePDO(
            $pdoOptions['dsn'],
            $pdoOptions['username'] ?? null,
            $pdoOptions['password'] ?? null,
            $pdoOptions['options'] ?? null,
        );

        $entityClass = $options->getUserEntityClass();

        $hydrator = $container->get('lmcuser_user_hydrator');
        if (! $hydrator instanceof HydratorInterface) {
            throw new ServiceNotCreatedException(
                sprintf(
                    "'lmcuser_user_hydrator' does not resolve is not a valid hydrator; received '%s'",
                    is_object($hydrator) ? $hydrator::class : gettype($hydrator)
                )
            );
        }

        return new Pdo(
            $basePdo,
            $hydrator,
            new $entityClass(),
            $options->getTableName(),
            $options->getIdFieldName(),
        );
    }
}
