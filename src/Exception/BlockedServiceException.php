<?php

namespace Versyx\Exception;

use RuntimeException;
use Psr\Container\ContainerExceptionInterface;

/**
 * An attempt to modify a blocked service was made.
 */
class BlockedServiceException extends RuntimeException implements ContainerExceptionInterface
{
    /**
     * @param string $id
     */
    public function __construct($id)
    {
        parent::__construct(\sprintf('Cannot override blocked service "%s".', $id));
    }
}
