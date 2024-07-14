<?php

namespace Versyx\Session\Driver;

use Versyx\Session\AbstractSession;

class FileSessionDriver extends AbstractSession
{
    private $filePath;

    public function __construct(string $filePath)
    {
        $this->filePath = $filePath;
    }

    protected function load(): array
    {
        if (file_exists($this->filePath)) {
            return unserialize(file_get_contents($this->filePath));
        }

        return [];
    }

    protected function persist(array $data): void
    {
        file_put_contents($this->filePath, serialize($data));
    }
}
