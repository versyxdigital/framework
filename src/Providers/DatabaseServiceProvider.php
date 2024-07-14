<?php

namespace Versyx\Providers;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\ORMSetup;
use Doctrine\ORM\EntityManager;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\Configuration;
use Versyx\Service\Container;
use Versyx\Service\ServiceProviderInterface;

class DatabaseServiceProvider implements ServiceProviderInterface
{
    /**
     * Register the database service provider.
     *
     * @param Container $container
     * @return Container
     */
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

        $connection = $this->configureDriver($config);
        $entityManager = new EntityManager($connection, $config);

        // Bind EntityManager to the container
        $container[EntityManager::class] = $entityManager;

        return $container;
    }

    /**
     * Configure application database driver
     * 
     * @param string $driver
     * @return Connection
     */
    private function configureDriver (Configuration $config): Connection
    {
        $rootDir = appRootDir();
        
        $driver = env('DB_DRIVER', 'pdo_sqlite');
        
        $connection = [];
        if ($driver === 'pdo_sqlite') {
            $connection = [
                'driver' => 'pdo_sqlite',
                'path' => $rootDir . '/database/db.sqlite'
            ];
        } elseif ($driver === 'pdo_mysql') {
            $connection = [
                'driver' => 'pdo_mysql',
                'dbname' => env('DB_DATABASE'),
                'user' => env('DB_USERNAME'),
                'password' => env('DB_PASSWORD'),
                'host' => env('DB_HOST', 'localhost')
            ];
        }

        return DriverManager::getConnection($connection, $config);
    }
}
