<?php

namespace Versyx;

use Composer\ClassMapGenerator\ClassMapGenerator;
use Psr\Http\Message\ResponseInterface;
use Versyx\Service\Container;
use Versyx\Request;

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
     * This method takes classes under a given namespace located in a given
     * directory, inspects their constructors and methods, creates instances
     * with their necessary arguments and then binds them to the service
     * container.
     * 
     * @param Container $app
     * @param string $namespace
     * @param string $directory
     * @return void
     */
    public static function map(Container $app, string $namespace, string $directory)
    {
        $map = ClassMapGenerator::createMap($directory);

        $classes = [];
        foreach ($map as $class => $path) {
            if (strpos($class, $namespace) === 0) {       
                if (class_exists($class)) {
                    $classes[] = $class;
                }
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
     * @return 
     */
    public static function route(Container $app, Request $request, array $route)
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
                    // Dependency does not exist in the service container
                    throw new \RuntimeException(
                        'Cannot resolve '.$class.' make sure it is bound in the service container'
                    );
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
     * Get the necessary information from requests for the Request object.
     * 
     * Useful if a new instance of the Request object needs to be created.
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