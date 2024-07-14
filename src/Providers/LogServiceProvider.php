<?php

namespace Versyx\Providers;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Psr\Log\LoggerInterface;
use Versyx\Service\Container;
use Versyx\Service\ServiceProviderInterface;
use Composer\InstalledVersions;

/**
 * Provides a logger service.
 * 
 * This service provider creates an application logger service that implements
 * the PSR-3 Logger Interface and registers it to the service container.
 * 
 * Versyx uses monolog/monolog by default, however you are free to switch the
 * implementation with your own custom LogServiceProvider.
 */
class LogServiceProvider implements ServiceProviderInterface
{
    /**
     * Register the log service provider.
     *
     * @param Container $container
     * @return Container|string
     */
    public function register(Container $container): Container
    {
        $container[LoggerInterface::class] = new Logger('app');

        try {
            $logPath = appRootDir() . '/storage/logs/app.log';
            $container[LoggerInterface::class]
                ->pushHandler(new StreamHandler($logPath, Logger::DEBUG));
        } catch (\Exception $e) {
            return $e->getMessage();
        }

        return $container;
    }
}