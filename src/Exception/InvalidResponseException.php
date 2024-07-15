<?php

namespace Versyx\Exception;

use RuntimeException;

/**
 * Response is not valid.
 */
class InvalidResponseException extends RuntimeException
{
    /**
     * @param string $class
     */
    public function __construct(string $class, string $method)
    {
        parent::__construct(sprintf('%s::%s must return a valid PSR-7 response', $class, $method));
    }
}
