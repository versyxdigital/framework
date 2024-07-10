<?php

namespace Versyx;

use Composer\ClassMapGenerator\ClassMapGenerator;
use Versyx\Service\Container;

/**
 * Resolver for dependency injection.
 */
class Resolver
{
    /**
     * Map classes in a given directory to the service container.
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
}