<?php

namespace Versyx\Service;

use ArrayAccess;
use SplObjectStorage;
use Versyx\Exception\ExpectedInvokableException;
use Versyx\Exception\BlockedServiceException;
use Versyx\Exception\UnknownIdentifierException;
use Versyx\Service\ServiceProviderInterface;

/**
 * Dependency injection container implementation.
 *
 * This container allows storing and retrieving services and parameters.
 * It supports factory services, service providers, and service blocking.
 */
class Container implements ArrayAccess
{
    /** @var array */
    private array $values = [];

    /** @var SplObjectStorage */
    private SplObjectStorage $factories;

    /** @var SplObjectStorage */
    private SplObjectStorage $protected;

    /** @var array */
    private array $blocked = [];

    /** @var array */
    private array $raw = [];

    /** @var array */
    private array $keys = [];

    /**
     * Container constructor
     *
     * @param array $values
     */
    public function __construct(array $values = [])
    {
        $this->factories = new SplObjectStorage();
        $this->protected = new SplObjectStorage();

        foreach ($values as $key => $value) {
            $this->offsetSet($key, $value);
        }
    }

    /**
     * Sets a parameter or an object.
     *
     * @param string $id
     * @param mixed
     * @return void
     * @throws BlockedServiceException
     */
    public function offsetSet($id, $value): void
    {
        if (isset($this->blocked[$id])) {
            throw new BlockedServiceException($id);
        }

        $this->values[$id] = $value;
        $this->keys[$id] = true;
    }

    /**
     * Gets a parameter or an object.
     *
     * @param string $id 
     * @return mixed
     * @throws UnknownIdentifierException
     */
    public function offsetGet(mixed $offset): mixed
    {
        if (!isset($this->keys[$offset])) {
            throw new UnknownIdentifierException($offset);
        }

        if (
            isset($this->raw[$offset])
            || !is_object($this->values[$offset])
            || isset($this->protected[$this->values[$offset]])
            || !method_exists($this->values[$offset], '__invoke')
        ) {
            return $this->values[$offset];
        }

        if (isset($this->factories[$this->values[$offset]])) {
            return $this->values[$offset]($this);
        }

        $raw = $this->values[$offset];
        $value = $this->values[$offset] = $raw($this);

        $this->raw[$offset] = $raw;

        $this->blocked[$offset] = true;

        return $value;
    }

    /**
     * Checks if a parameter or an object is set.
     *
     * @param string $id
     * @return bool
     */
    public function offsetExists($id): bool
    {
        return isset($this->keys[$id]);
    }

    /**
     * Unsets a parameter or an object.
     *
     * @param string $id
     * @return void
     */
    public function offsetUnset($id): void
    {
        if (isset($this->keys[$id])) {
            if (is_object($this->values[$id])) {
                unset($this->factories[$this->values[$id]], $this->protected[$this->values[$id]]);
            }

            unset($this->values[$id], $this->blocked[$id], $this->raw[$id], $this->keys[$id]);
        }
    }

    /**
     * Marks a callable as being a factory service.
     *
     * @param callable $callable
     * @return callable
     * @throws ExpectedInvokableException
     */
    public function factory($callable)
    {
        if (!is_object($callable) || !method_exists($callable, '__invoke')) {
            throw new ExpectedInvokableException('Service definition is not a Closure or invokable object.');
        }

        $this->factories->attach($callable);

        return $callable;
    }

    /**
     * Returns all defined value names.
     *
     * @return array
     */
    public function keys()
    {
        return array_keys($this->values);
    }

    /**
     * Registers a service provider.
     *
     * @param array $values
     * @return static
     */
    public function register(ServiceProviderInterface $provider, array $values = [])
    {
        $provider->register($this);

        foreach ($values as $key => $value) {
            $this[$key] = $value;
        }

        return $this;
    }
}
