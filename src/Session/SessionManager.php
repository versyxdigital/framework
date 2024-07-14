<?php

namespace Versyx\Session;

/**
 * Class for managing session data via a session driver.
 */
class SessionManager
{
    /**
     * @var SessionInterface The session driver implementation.
     */
    private $session;

    /**
     * Constructs the SessionManager and starts the session.
     *
     * @param SessionInterface $session The session driver implementation.
     */
    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
        $this->session->start();
    }

    /**
     * Gets a value from the session.
     *
     * @param string $key The key of the session data.
     * @param mixed $default The default value to return if the key does not exist.
     * @return mixed The value from the session or the default value.
     */
    public function get(string $key, $default = null)
    {
        return $this->session->get($key, $default);
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
        $this->session->set($key, $value);
    }

    /**
     * Checks if a key exists in the session.
     *
     * @param string $key The key to check in the session.
     * @return bool True if the key exists, false otherwise.
     */
    public function has(string $key): bool
    {
        return $this->session->has($key);
    }

    /**
     * Removes a value from the session.
     *
     * @param string $key The key of the session data to remove.
     * @return void
     */
    public function remove(string $key): void
    {
        $this->session->remove($key);
    }

    /**
     * Clears all data from the session.
     * 
     * @return void
     */
    public function clear(): void
    {
        $this->session->clear();
    }
}