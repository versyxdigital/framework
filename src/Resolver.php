<?php

namespace Versyx;

use Composer\ClassMapGenerator\ClassMapGenerator;
use FastRoute\Dispatcher;
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;
use Psr\Http\Message\ResponseInterface;
use Versyx\Http\Request;
use Versyx\Http\Response;
use Versyx\Service\Container;
use Versyx\View\ViewEngineInterface;

/**
 * Resolver for dependency injection.
 * 
 * This class maps and resolves dependencies for classes in a given directory,
 * adding them to the provided service container. It also resolves dependencies
 * type-hinted on route methods for the request-response cycle.
 */
class Resolver
{
    /**
     * Map classes in a given directory to the service container.
     * 
     * This method takes classes located in a given directory, inspects their
     * constructors and methods, creates instances with their necessary arguments
     * and then binds them to the service container.
     * 
     * @param Container $app
     * @param string $directory
     * @return void
     */
    public static function map (Container $app, string $directory)
    {
        $map = ClassMapGenerator::createMap($directory);

        $classes = [];
        foreach ($map as $class => $path) {
            if (class_exists($class) && file_exists($path)) {
                $classes[] = $class;
            }
        }

        foreach ($classes as $class) {
            $reflector = new \ReflectionClass($class);

            // Check if class can be instantiated (i.e. not abstract)
            if ($reflector->isInstantiable()) {

                // Get the constructor
                $constructor = $reflector->getConstructor();

                if ($constructor) {
                    $params = $constructor->getParameters();
                    
                    // Resolve dependencies for constructor parameters
                    $dependencies = [];
                    foreach ($params as $param) {
                        $type = $param->getType();

                        if ($type && ! $type->isBuiltin()) {
                            // Dependency is a class
                            $dependency = $app[$type->getName()];
                        } else {
                            // Dependency is not a class (e.g. scalar type or no typehint)
                            continue;
                        }

                        $dependencies[] = $dependency;
                    }

                    // Instantiate the class with the resolved dependencies
                    // and store the class instance in the service container
                    $app[$class] = $reflector->newInstanceArgs($dependencies);
                } else {
                    // Class has no constructor or constructor with no params
                    $app[$class] = $reflector->newInstance();
                }
            }
        }
    }

    /**
     * Resolve dependencies type-hinted on route handler methods.
     * 
     * This method will resolve dependencies type-hinted on route handler methods
     * from the service container, note there are special cases included for the
     * Request object, which isn't bound in the container but instead constructed
     * from the Kernel.
     * 
     * @param Container $app
     * @param Request $request
     * @param array $route
     * @return ResponseInterface
     */
    public static function route (Container $app, Request $request, array $route): ResponseInterface
    {
        // Get the handler from the route i.e. the controller
        $handler = $route[1];
        // Get route path parameters i.e. /user/{id}
        $routeParams = $route[2];
        // Get the handler class name and method
        [$class, $method] = $handler;
        
        // Resolve the route handler from the service container
        $instance = $app[$class];

        // Inspect the route handler method
        $reflectionMethod = new \ReflectionMethod($instance, $method);
        // Get the route handler parameters
        $methodParams = $reflectionMethod->getParameters();
        
        // Resolve the route method handler
        $resolved = [];
        foreach ($methodParams as $param) {
            $type = $param->getType();
            if ($type && ! $type->isBuiltin()) {
                $class = $type->getName();
                // Resolve route handler method dependency from the service container
                if (isset($app[$class])) {
                    $resolved[] = $app[$class];
                } elseif($class === Request::class) {
                    // Handle type-hinted Request $request argument...it is already an
                    // instance of Versyx\Request, it isn't bound in the service container
                    $resolved[] = $request;
                } else {
                    if ($type->getName() === Response::class) {
                        $resolved[] = new Response();
                    } else {
                        // Dependency does not exist in the service container
                        throw new \RuntimeException(
                            'Cannot resolve '.$class.', please make sure it is bound in the service container'
                        );
                    }
                }
            } elseif ($param->getName() === 'request') {
                // Special case for non-type hinted $request on route handler methods
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

        $response = $instance->$method(...$resolved);

        if (! $response instanceof ResponseInterface) {
            throw new \RuntimeException(
                $class.'::'.$method.' must return a valid PSR-7 response'
            );
        }

        return $response;
    }

    /**
     * Respond to HTTP requests.
     * 
     * This method responds to HTTP requests, resolving any dependencies
     * on the request route method handlers before handling the request
     * and emitting the response.
     * 
     * @param Container $app
     * @param Request $request
     * @param array $route
     * @return bool
     */
    public static function respond (Container $app, Request $request, array $route): bool
    {
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

        return (new SapiEmitter())->emit($response);
    }

    /**
     * Get necessary information from requests.
     * 
     * This method is useful for fetching information from requests required for
     * new instances of the Request object.
     * 
     * @param Request
     * @return array
     */
    public static function request(Request $request): array
    {
        return [
            'serverParams' => $_SERVER,
            'uploadedFiles' => $request->getUploadedFiles(),
            'uri' => $request->getUri(),
            'method' => $request->getMethod(),
            'body' => $request->getBody(),
            'headers' => $request->getHeaders(),
            'cookieParams' => $request->getCookieParams(),
            'queryParams' => $request->getQueryParams(),
            'parsedBody' => $request->getParsedBody(),
            'protocol' => $request->getProtocolVersion(),
        ];
    }
}