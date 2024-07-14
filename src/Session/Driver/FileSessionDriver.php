<?php

namespace Versyx\Session\Driver;

use Versyx\Session\AbstractSession;

/**
 * File session driver.
 */
class FileSessionDriver extends AbstractSession
{
    /** @var string */
    private string $savePath;

    /**
     * Create a new file session driver instance.
     * 
     * @param string $savePath
     */
    public function __construct(string $savePath)
    {
        if (!is_dir($savePath) && !mkdir($savePath, 0777, true) && !is_dir($savePath)) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $savePath));
        }
        $this->savePath = rtrim($savePath, DIRECTORY_SEPARATOR);
    }

    /**
     * Load file session driver.
     * 
     * @return array
     */
    protected function load(): array
    {
        $sessionId = session_id();
        $filePath = "$this->savePath/sess_$sessionId";

        if (file_exists($filePath)) {
            return unserialize(file_get_contents($filePath));
        }

        return [];
    }

    /**
     * Persist data to the session.
     * 
     * @param array $data
     * @return void
     */
    protected function persist(array $data): void
    {
        $sessionId = session_id();
        $filePath = "$this->savePath/sess_$sessionId";
        file_put_contents($filePath, serialize($data));
    }
}
