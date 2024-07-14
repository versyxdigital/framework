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
        $rootDir = appRootDir();

        $entitiesPaths = [
            $rootDir . '/app/Entities',
            $rootDir . '/vendor/versyx/framework/src/Entities'
        ];

        $isDevMode = env('APP_DEBUG', true);
        
        $config = ORMSetup::createAttributeMetadataConfiguration(
            paths: $entitiesPaths,
            isDevMode: $isDevMode
        );

        $sqlitePath = $rootDir . '/database/db.sqlite';
        $connection = DriverManager::getConnection([
            'driver' => env('DB_DRIVER', 'pdo_sqlite'),
            'path' => $sqlitePath
        ], $config);

        $entityManager = new EntityManager($connection, $config);

        // Bind EntityManager to the container
        $container[EntityManager::class] = $entityManager;

        return $container;
    }
}
