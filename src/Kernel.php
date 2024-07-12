<?php

namespace Versyx;

use Versyx\Service\Container;
use Versyx\RequestFactory;
use Versyx\Resolver;

/**
 * Kernel class responsible for request dispatching and handling in the application.
 *
 * The kernel class handles the request-response cycle. It initializes the application,
 * handles routing, executes route method handlers, and emits the final HTTP response.
 */
class Kernel
{
    /**
     * Dispatch the application request-response cycle.
     * 
     * This method receives the incoming HTTP request and creates a new server request
     * object containing additional data, it then dispatches the router to handle the
     * request and uses the resolver to respond.
     * 
     * The resolver will automatically resolve and inject any dependencies defined on
     * route handler methods, before handling the request and returning the response.
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
