<?php

namespace Versyx\Session;

interface SessionInterface
{
    public function start(): void;
    public function get(string $key, $default = null);
    public function set(string $key, $value): void;
    public function has(string $key): bool;
    public function remove(string $key): void;
    public function clear(): void;
    public function save(): void;
}