<?php

namespace Versyx\Session\Driver;

use Versyx\Session\AbstractSession;

class MemorySessionDriver extends AbstractSession
{
    protected function load(): array
    {
        return [];
    }

    protected function persist(array $data): void
    {
        // Memory driver does not persist data
    }
}
