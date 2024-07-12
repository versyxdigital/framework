<?php

namespace Versyx;

use FastRoute\Dispatcher;
use Psr\Http\Message\ResponseInterface;
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;
use Versyx\RequestFactory;
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
     * Dispatch the application request-response cycle.
     *
     * @param Container $app
     * @return void
     * @throws \RuntimeException
     */
    public static function dispatch(Container $app): void
    {
        $request = RequestFactory::fromGlobals(
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
                $response = Resolver::route($app, $request, $route);
                break;
        }

        (new SapiEmitter())->emit($response);
    }
}
