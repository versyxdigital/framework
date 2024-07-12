<?php

namespace Versyx\Providers;

use Doctrine\ORM\ORMSetup;
use Doctrine\ORM\EntityManager;
use Doctrine\DBAL\DriverManager;
use Versyx\Service\Container;
use Versyx\Service\ServiceProviderInterface;

class DatabaseServiceProvider implements ServiceProviderInterface
{
    public function register ($container): Container
    {
        $config = ORMSetup::createAttributeMetadataConfiguration(
            paths: [__DIR__ . '/../../../../../app/Entities'],
            isDevMode: env('APP_DEBUG')
        );

        $connection = DriverManager::getConnection([
            'driver' => env('DB_DRIVER', 'pdo_sqlite'),
            'path' => __DIR__ . '/../../../../../db.sqlite'
        ], $config);

        $entityManager = new EntityManager($connection, $config);

        // Bind EntityManager to the container
        $container[EntityManager::class] = $entityManager;

        return $container;
    }
}
