<?php

namespace Versyx\Session\Driver;

use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Doctrine\ORM\EntityManagerInterface;
use Versyx\Entities\Session;
use Versyx\Session\AbstractSession;

class DatabaseSessionDriver extends AbstractSession
{
    private $entityManager;
    private $sessionId;

    public function __construct(EntityManagerInterface $entityManager, string $sessionId)
    {
        $this->entityManager = $entityManager;
        $this->sessionId = $sessionId;
    }

    protected function load(): array
    {
        $session = [];

        /** @var AbstractSchemaManager $schemaManager */
        $schemaManager = $this->entityManager
            ->getConnection()
            ->createSchemaManager();

        if (in_array('sessions', $schemaManager->listTableNames())) {
            $session = $this->entityManager->getRepository(Session::class)->find($this->sessionId);
        }

        return $session ? unserialize($session->getData()) : [];
    }

    protected function persist(array $data): void
    {
        $session = $this->entityManager->getRepository(Session::class)->find($this->sessionId);

        if (!$session) {
            $session = new Session();
            $session->setId($this->sessionId);
        }

        $session->setData(serialize($data));
        $this->entityManager->persist($session);
        $this->entityManager->flush();
    }
}
