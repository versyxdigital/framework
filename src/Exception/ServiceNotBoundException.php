<?php

namespace Versyx\Exception;

use RuntimeException;

/**
 * Service is not bound in the container.
 */
class ServiceNotBoundException extends RuntimeException
{
    /**
     * @param string $class
     */
    public function __construct(string $class)
    {
        parent::__construct(sprintf('Cannot resolve %s, please make sure it is bound in the service container', $class));
    }
}
