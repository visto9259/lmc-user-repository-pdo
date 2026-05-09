<?php

declare(strict_types=1);

namespace Lmc\User\Repository\Pdo\Entity;

use Laminas\Hydrator\ClassMethodsHydrator;
use Lmc\User\Repository\UserInterface;
use Psr\Container\ContainerInterface;
use Webmozart\Assert\Assert;

use function count;

class UserFactoryFactory
{
    public function __invoke(ContainerInterface $container): callable
    {
        return static function (string|int|null $identity, array $roles, array $details): UserInterface {
            Assert::allString($roles);
            Assert::isMap($details);

            $user = new User();
            if (null !== $identity) {
                $user->setId($identity);
            }
            $user->setRoles($roles);
            if (count($details) > 0) {
                $hydrator = new ClassMethodsHydrator();
                $user     = $hydrator->hydrate($details, $user);
            }
            return $user;
        };
    }
}
