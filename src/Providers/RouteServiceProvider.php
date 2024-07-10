<?php

namespace Versyx\Providers;

use FastRoute\RouteCollector;
use Versyx\Service\Container;
use Versyx\Service\ServiceProviderInterface;

use function FastRoute\simpleDispatcher;

/**
 * Class RouteServiceProvider
 */
class RouteServiceProvider implements ServiceProviderInterface
{
    /**
     * Register route service provider.
     *
     * @param Container $container
     * @return Container
     */
    public function register(Container $container): Container
    {
        $container['router'] = simpleDispatcher(function(RouteCollector $rc) {
            $this->configureRoutes($rc, __DIR__ . '/../../../routes/web.php', 'web');
            $this->configureRoutes($rc, __DIR__ . '/../../../routes/api.php', 'api');
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