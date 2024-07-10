<?php

namespace Versyx\Exception;

use InvalidArgumentException;
use Psr\Container\NotFoundExceptionInterface;

/**
 * The identifier of a valid service or parameter was expected.
 */
class UnknownIdentifierException extends InvalidArgumentException implements NotFoundExceptionInterface
{
    /**
     * @param string $id
     */
    public function __construct($id)
    {
        parent::__construct(\sprintf('Identifier "%s" is not defined.', $id));
    }
}
