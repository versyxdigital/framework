<?php

namespace Versyx\Providers;

use Versyx\Service\Container;
use Versyx\Service\ServiceProviderInterface;

/**
 * Provides application services.
 * 
 * This service provider ties together all the other service providers.
 */
class AppServiceProvider implements ServiceProviderInterface
{
    /**
     * Register the application service providers.
     *
     * @param Container $container
     * @return Container|string
     */
    public function register(Container $container): Container
    {
        $container->register(new DatabaseServiceProvider());
        $container->register(new SessionServiceProvider());
        $container->register(new LogServiceProvider());
        $container->register(new RouteServiceProvider());
        $container->register(new ViewServiceProvider());

        return $container;
    }
}