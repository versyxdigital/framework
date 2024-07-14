<?php

namespace Versyx\Session;

class SessionManager
{
    private $session;

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
        $this->session->start();
    }

    public function get(string $key, $default = null)
    {
        return $this->session->get($key, $default);
    }

    public function set(string $key, $value): void
    {
        $this->session->set($key, $value);
    }

    public function has(string $key): bool
    {
        return $this->session->has($key);
    }

    public function remove(string $key): void
    {
        $this->session->remove($key);
    }

    public function clear(): void
    {
        $this->session->clear();
    }
}
