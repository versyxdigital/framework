<?php

namespace Versyx\Session\Driver;

use Versyx\Session\AbstractSession;

class FileSessionDriver extends AbstractSession
{
    private $savePath;

    public function __construct(string $savePath)
    {
        if (!is_dir($savePath) && !mkdir($savePath, 0777, true) && !is_dir($savePath)) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $savePath));
        }
        $this->savePath = rtrim($savePath, DIRECTORY_SEPARATOR);
    }

    protected function load(): array
    {
        $sessionId = session_id();
        $filePath = "$this->savePath/sess_$sessionId";

        if (file_exists($filePath)) {
            return unserialize(file_get_contents($filePath));
        }

        return [];
    }

    protected function persist(array $data): void
    {
        $sessionId = session_id();
        $filePath = "$this->savePath/sess_$sessionId";
        file_put_contents($filePath, serialize($data));
    }
}
