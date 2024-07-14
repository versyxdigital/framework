<?php

namespace Versyx;

use Versyx\Service\Container;
use Versyx\Http\RequestFactory;
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
     * This method will receive the incoming HTTP request and create a new server request
     * object containing additional data, it will then dispatch the router to handle the
     * request before passing it to the resolver.
     * 
     * The resolver will automatically resolve and inject any dependencies defined on the
     * route handler method, before handling the request and returning the response.
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

        // Handle request method including from forms
        $method = $request->getMethod();
        $post = $request->getParsedBody();
        if ($method === 'POST' && isset($post['_method'])) {
            $method = strtoupper($post['_method']);
        }

        // Dispatch the router to handle the request
        $route = $app['router']
            ->dispatch($method, $request->getUri()->getPath());

        // Respond to the request
        Resolver::respond($app, $request, $route);
    }
}
