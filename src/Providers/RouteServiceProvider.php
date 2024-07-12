<?php

namespace Versyx\Providers;

use FastRoute\RouteCollector;
use Versyx\Service\Container;
use Versyx\Service\ServiceProviderInterface;

use function FastRoute\simpleDispatcher;

/**
 * Provides a router service.
 * 
 * This service provider creates an application router service and registers it
 * to the service container.
 * 
 * Versyx uses nikic/FastRoute by default, however you are free to switch the
 * implementation with your own custom RouteServiceProvider.
 */
class RouteServiceProvider implements ServiceProviderInterface
{
    /**
     * Register the route service provider.
     *
     * @param Container $container
     * @return Container
     */
    public function register(Container $container): Container
    {
        $container['router'] = simpleDispatcher(function(RouteCollector $rc) {
            $this->configureRoutes($rc, appRootDir() . '/routes/web.php', 'web');
            $this->configureRoutes($rc, appRootDir() . '/routes/api.php', 'api');
        });

        return $container;
    }

    /**
     * Configure application routes
     * 
     * @param RouteCollector $rc
     * @param string $file
     * @param string $type
     * @return void
     */
    private function configureRoutes(RouteCollector $rc, string $file, string $type): void
    {
        if (! is_file($file)) {
            throw new \RuntimeException('No '.$type.' routes found, please ensure '.$file.' file exists.');
        }

        $routes = require $file;
        foreach($routes as $route) {
            [$method, $path, $handler] = $route;
            
            if ($type === 'api') {
                $path = '/api/'.$path;
            }

            $rc->addRoute($method, $path, $handler);
        }
    }
}