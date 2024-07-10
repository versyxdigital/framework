<?php

namespace Versyx\Exception;

use InvalidArgumentException;
use Psr\Container\NotFoundExceptionInterface;

/**
 * An attempt to perform an operation that requires a service identifier was made.
 */
class InvalidServiceIdentifierException extends InvalidArgumentException implements NotFoundExceptionInterface
{
    /**
     * @param string $id
     */
    public function __construct($id)
    {
        parent::__construct(\sprintf('Identifier "%s" does not contain an object definition.', $id));
    }
}
