<?php

namespace Versyx\Session;

abstract class AbstractSession implements SessionInterface
{
    public function start(): void
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION = array_merge($_SESSION, $this->load());
    }

    public function get(string $key, $default = null)
    {
        return $_SESSION[$key] ?? $default;
    }

    public function set(string $key, $value): void
    {
        $_SESSION[$key] = $value;
        $this->save();
    }

    public function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    public function remove(string $key): void
    {
        unset($_SESSION[$key]);
        $this->save();
    }

    public function clear(): void
    {
        $_SESSION = [];
        $this->save();
    }

    public function save(): void
    {
        $this->persist($_SESSION);
    }

    abstract protected function load(): array;
    abstract protected function persist(array $data): void;
}
