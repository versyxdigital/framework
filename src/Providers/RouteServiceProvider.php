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
            $rootDir = appRootDir();

            $webRoutes = require $rootDir . '/routes/web.php';
            $apiRoutes = require $rootDir . '/routes/api.php';

            $this->configureRoutes($rc, $webRoutes, 'web');
            $this->configureRoutes($rc, $apiRoutes, 'api');
        });

        return $container;
    }

    /**
     * Configure application routes.
     * 
     * @param RouteCollector $rc
     * @param string $file
     * @param string $type
     * @return void
     */
    private function configureRoutes(RouteCollector $rc, array $routes, string $type): void
    {
        $this->processRoutes($rc, $routes, $type);
    }

    /**
     * Recursively iterate through routes arrays to configure path prefixes.
     * 
     * @param RouteCollector $rc
     * @param array $routes
     * @param string $type
     * @param string $prefix
     */
    private function processRoutes(RouteCollector $rc, array $routes, string $type, string $prefix = ''): void
    {
        foreach ($routes as $key => $route) {
            if (is_string($key)) {
                $newPrefix = rtrim($prefix . '/' . trim($key, '/'), '/');
                $this->processRoutes($rc, $route, $type, $newPrefix);
            } else {
                [$method, $path, $handler] = $route;

                $fullPath = $prefix === ''
                    ? $path
                    : rtrim($prefix . '/' . ltrim($path, '/'), '/');

                if ($fullPath === '') {
                    $fullPath = '/';
                }
                
                if ($type === 'api') {
                    $fullPath = '/api' . $fullPath;
                }

                $rc->addRoute($method, $fullPath, $handler);
            }
        }
    }
}