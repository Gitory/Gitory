<?php

namespace Gitory\Gitory\Managers\Doctrine;

use Gitory\Gitory\Managers\RepositoryManager;
use Doctrine\Common\Persistence\ManagerRegistry;

class DoctrineRepositoryManager implements RepositoryManager
{
    /**
     * Doctrine registry
     * @var \Doctrine\Common\Persistence\ManagerRegistry
     */
    private $registry;

    /**
     * Entity class name
     */
    const ENTITY_CLASS = 'Gitory\Gitory\Entities\Repository';

    /**
     * @param \Doctrine\Common\Persistence\ManagerRegistry $registry registry manager
     */
    public function __construct(ManagerRegistry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * {@inheritdoc}
     */
    public function findAll()
    {
        return $this->getManager()->getRepository(self::ENTITY_CLASS)->findAll();
    }

    /**
     * Get entity manager
     * @return \Doctrine\Common\Persistence\ObjectManager|null entity manager
     */
    private function getManager()
    {
        return $this->registry->getManagerForClass(DoctrineRepositoryManager::ENTITY_CLASS);
    }
}
