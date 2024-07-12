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
     * Dispatch
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
                $handler = $route[1];
                $routeParams = $route[2];
                [$class, $method] = $handler;
                
                $controller = $app[$class];

                $reflectionMethod = new \ReflectionMethod($controller, $method);
                $methodParams = $reflectionMethod->getParameters();
                
                $resolved = [];
                foreach ($methodParams as $param) {
                    $type = $param->getType();
                    if ($type && ! $type->isBuiltin()) {
                        $class = $type->getName();
                        // Resolve dependency from the service container
                        if (isset($app[$class])) {
                            $resolved[] = $app[$class];
                        } elseif($class === Request::class) {
                            // Handle type-hinted request argument...
                            // $request is already an instance of Versyx\Request, it isn't bound in the
                            // service container.
                            $resolved[] = $request;
                        } else {
                            // Dependency does not exist in the service container
                            throw new \RuntimeException(
                                'Cannot resolve '.$class.' make sure it is bound in the service container'
                            );
                        }
                    } elseif ($param->getName() === 'request') {
                        // Special case for non-type hinted request argument
                        $resolved[] = $request;
                    } elseif (isset($routeParams[$param->getName()])) {
                        // Method param matches a route param
                        $resolved[] = $routeParams[$param->getName()];
                    } elseif ($param->allowsNull()) {
                        // Method Param is nullable, pass null
                        $resolved[] = null;
                    } else {
                        // Method param type hint is built-in and not found in route params
                        throw new \RuntimeException(
                            'Cannot resolve parameter '. $param->getName().' for method '.$class.'::'.$method
                        );
                    }
                }

                $response = $controller->$method(...$resolved);

                if (! $response instanceof ResponseInterface) {
                    throw new \RuntimeException(
                        $class.'::'.$method.' must return a valid PSR-7 response'
                    );
                }

                break;
        }

        (new SapiEmitter())->emit($response);
    }
}
