<?php

namespace Versyx;

use FastRoute\Dispatcher;
use Psr\Http\Message\ResponseInterface;
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\ServerRequestFactory;
use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;
use Versyx\Service\Container;
use Versyx\View\ViewEngineInterface;

/**
 * Kernel class responsible for request dispatching and handling in the application.
 *
 * The kernel handles the request-response cycle. It initializes the application,
 * handles routing, executes controllers, and emits the final HTTP response.
 */
class Kernel
{
    /**
     * Dispatch
     */
    public static function dispatch(Container $app): void
    {
        $request = ServerRequestFactory::fromGlobals(
            $_SERVER,
            $_GET,
            $_POST,
            $_COOKIE,
            $_FILES
        );

        $route = $app['router']->dispatch(
            $request->getMethod(),
            $request->getUri()->getPath()
        );

        switch ($route[0]) {
            case Dispatcher::NOT_FOUND:
                $response = new HtmlResponse(
                    $app[ViewEngineInterface::class]->render('error/404.twig')
                );
                break;
            case Dispatcher::METHOD_NOT_ALLOWED:
                $response = new HtmlResponse(
                    $app[ViewEngineInterface::class]->render('error/500.twig')
                );
                break;
            case Dispatcher::FOUND:
                $handler = $route[1];
                $routeParams = $route[2];
                [$class, $method] = $handler;
                
                $controller = $app[$class];
                $response = $controller->$method($request, $routeParams);

                if (! $response instanceof ResponseInterface) {
                    throw new \RuntimeException(
                        $class. '->'.$method.' must return a valid PSR-7 response'
                    );
                }

                break;
        }

        (new SapiEmitter())->emit($response);
    }
}
