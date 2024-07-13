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

        $entitiesPath = $rootDir . '/app/Entities';
        $isDevMode = env('APP_DEBUG');
        $config = ORMSetup::createAttributeMetadataConfiguration(
            paths: [$entitiesPath],
            isDevMode: $isDevMode
        );

        $sqlitePath = $rootDir . '/database/db.sqlite';
        $connection = DriverManager::getConnection([
            'driver' => env('DB_DRIVER'),
            'path' => $sqlitePath
        ], $config);

        $entityManager = new EntityManager($connection, $config);

        // Bind EntityManager to the container
        $container[EntityManager::class] = $entityManager;

        return $container;
    }
}
