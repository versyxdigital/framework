<?php

namespace Versyx\Session;

/**
 * Interface for managing session data.
 */
interface SessionInterface
{
    /**
     * Starts the session.
     * 
     * @return void
     */
    public function start(): void;

    /**
     * Gets a value from the session.
     *
     * @param string $key The key of the session data.
     * @param mixed $default The default value to return if the key does not exist.
     * @return mixed The value from the session or the default value.
     */
    public function get(string $key, $default = null);

    /**
     * Sets a value in the session.
     *
     * @param string $key The key of the session data.
     * @param mixed $value The value to set in the session.
     * @return void
     */
    public function set(string $key, $value): void;

    /**
     * Checks if a key exists in the session.
     *
     * @param string $key The key to check in the session.
     * @return bool True if the key exists, false otherwise.
     */
    public function has(string $key): bool;

    /**
     * Removes a value from the session.
     *
     * @param string $key The key of the session data to remove.
     * @return void
     */
    public function remove(string $key): void;

    /**
     * Clears all data from the session.
     * 
     * @return void
     */
    public function clear(): void;

    /**
     * Saves the session data.
     * 
     * @return void
     */
    public function save(): void;
}