<?php

namespace Versyx\Exception;

use InvalidArgumentException;

/**
 * The identifier of a valid service or parameter was expected.
 */
class InvalidRouteHandlerArgumentException extends InvalidArgumentException
{
    /**
     * 
     * 
     * @param string $param
     * @param string $class
     * @param string $method
     */
    public function __construct(string $param, string $class, string $method)
    {
        parent::__construct(sprintf('Cannot resolve parameter %s for method %s::%s', $param, $class, $method));
    }
}
