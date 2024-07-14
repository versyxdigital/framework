<?php

namespace Versyx\Providers;

use Doctrine\ORM\EntityManager;
use Versyx\Service\Container;
use Versyx\Service\ServiceProviderInterface;
use Versyx\Session\Driver\DatabaseSessionDriver;
use Versyx\Session\Driver\FileSessionDriver;
use Versyx\Session\Driver\MemorySessionDriver;
use Versyx\Session\SessionInterface;
use Versyx\Session\SessionManager;

/**
 * Provides a session service.
 * 
 * This service provider creates an application session service and registers it
 * to the service container.
 */
class SessionServiceProvider implements ServiceProviderInterface
{
    /** @var string array */
    private static $mappings = [
        'memory' => MemorySessionDriver::class,
        'file' => FileSessionDriver::class,
        'database' => DatabaseSessionDriver::class
    ];

    /**
     * Register the session service provider.
     *
     * @param Container $container
     * @return Container
     */
    public function register(Container $container): Container
    {
        $container[SessionManager::class] = new SessionManager(
            $this->configureDriver(env('SESSION_DRIVER', 'file'), $container)
        );

        return $container;
    }

    /**
     * Configure application session driver
     * 
     * @param string $driver
     * @return SessionInterface
     */
    private function configureDriver(string $type, Container $container): SessionInterface
    {
        $driver = static::$mappings[$type];

        if ($driver === MemorySessionDriver::class) {
            $driverInstance = new MemorySessionDriver();
        } elseif ($driver === DatabaseSessionDriver::class) {
            $em = $container[EntityManager::class];
            $driverInstance = new DatabaseSessionDriver($em, generateSecureSessionId(16));
        } else {
            $driverInstance = new FileSessionDriver(appRootDir() . '/storage/session');
        }

        return $driverInstance;
    }
}