<?php

namespace Versyx\Session\Driver;

use Versyx\Session\AbstractSession;

/**
 * Memory session driver.
 */
class MemorySessionDriver extends AbstractSession
{
    /**
     * Load memory session driver.
     * 
     * @return array
     */
    protected function load(): array
    {
        return [];
    }

    /**
     * Perisst data to the session.
     * 
     * @param array $data
     * @return void
     */
    protected function persist(array $data): void
    {
        // Memory driver does not persist data
    }
}
