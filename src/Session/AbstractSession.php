<?php

namespace Versyx\Session;

/**
 * Abstract class implementing SessionInterface for session management.
 *
 * This abstract class provides basic session management functionality
 * using PHP's $_SESSION superglobal.
 */
abstract class AbstractSession implements SessionInterface
{
    /**
     * Starts the session if not already started and loads session data.
     *
     * @return void
     */
    public function start(): void
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION = array_merge($_SESSION, $this->load());
    }

    /**
     * Retrieves a value from the session.
     *
     * @param string $key The key of the session data.
     * @param mixed $default The default value to return if the key does not exist.
     * @return mixed The value from the session or the default value.
     */
    public function get(string $key, $default = null)
    {
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Sets a value in the session.
     *
     * @param string $key The key of the session data.
     * @param mixed $value The value to set in the session.
     * @return void
     */
    public function set(string $key, $value): void
    {
        $_SESSION[$key] = $value;
        $this->save();
    }

    /**
     * Checks if a key exists in the session.
     *
     * @param string $key The key to check in the session.
     * @return bool True if the key exists, false otherwise.
     */
    public function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    /**
     * Removes a value from the session.
     *
     * @param string $key The key of the session data to remove.
     * @return void
     */
    public function remove(string $key): void
    {
        unset($_SESSION[$key]);
        $this->save();
    }

    /**
     * Clears all data from the session.
     * 
     * @return void
     */
    public function clear(): void
    {
        $_SESSION = [];
        $this->save();
    }

    /**
     * Saves the session data.
     *
     * This method should persist the session data using the implemented
     * persistence strategy.
     * 
     * @return void
     */
    public function save(): void
    {
        $this->persist($_SESSION);
    }

    /**
     * Loads session data.
     *
     * This method must be implemented by subclasses to load session data.
     *
     * @return array The loaded session data.
     */
    abstract protected function load(): array;

    /**
     * Persists session data.
     *
     * This method must be implemented by subclasses to persist session data.
     *
     * @param array $data The session data to persist.
     * @return void
     */
    abstract protected function persist(array $data): void;
}
