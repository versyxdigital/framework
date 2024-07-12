<?php

namespace Versyx;

use Versyx\RequestFactory;
use Versyx\Service\Container;

/**
 * Kernel class responsible for request dispatching and handling in the application.
 *
 * The kernel handles the request-response cycle. It initializes the application,
 * handles routing, executes controllers, and emits the final HTTP response.
 */
class Kernel
{
    /**
     * Dispatch the application request-response cycle.
     *
     * @param Container $app
     * @return void
     * @throws \RuntimeException
     */
    public static function dispatch(Container $app): void
    {
        // Create a new request object from the request
        $request = RequestFactory::fromGlobals(
            $_SERVER,
            $_GET,
            $_POST,
            $_COOKIE,
            $_FILES
        );

        // Dispatch the router to handle the request
        $route = $app['router']->dispatch(
            $request->getMethod(),
            $request->getUri()->getPath()
        );

        // Respond to the request
        Resolver::respond($app, $request, $route);
    }
}
