<?php

declare(strict_types=1);

namespace Lmc\User\Repository\Pdo;

use Lmc\User\Repository\AdapterInterface;
use Lmc\User\Repository\Pdo\Adapter\BaseUserHydratorFactory;
use Lmc\User\Repository\Pdo\Adapter\PdoFactory;
use Lmc\User\Repository\Pdo\Adapter\UserHydrator;
use Lmc\User\Repository\Pdo\Adapter\UserHydratorFactory;
use Lmc\User\Repository\Pdo\Options\Options;
use Lmc\User\Repository\Pdo\Options\OptionsFactory;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => $this->getDependencies(),
        ];
    }

    private function getDependencies(): array
    {
        return [
            'aliases'   => [
                'lmcuser_user_hydrator' => UserHydrator::class,
                'lmcuser_base_hydrator' => 'lmcuser_default_hydrator',
            ],
            'factories' => [
                'lmcuser_default_hydrator' => BaseUserHydratorFactory::class,
                AdapterInterface::class    => PdoFactory::class,
                Options::class             => OptionsFactory::class,
                UserHydrator::class        => UserHydratorFactory::class,
            ],
        ];
    }
}
