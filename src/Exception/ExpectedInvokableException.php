<?php

namespace Versyx\Exception;

use InvalidArgumentException;
use Psr\Container\ContainerExceptionInterface;

/**
 * A closure or invokable object was expected.
 */
class ExpectedInvokableException extends InvalidArgumentException implements ContainerExceptionInterface
{}
