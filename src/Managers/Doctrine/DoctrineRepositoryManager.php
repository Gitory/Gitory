<?php

namespace Gitory\Gitory\Managers\Doctrine;

use Gitory\Gitory\Managers\RepositoryManager;
use Gitory\Gitory\Entities\Repository;
use Gitory\Gitory\Exceptions\ExistingRepositoryIdentifierException;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\ObjectRepository;

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
        return $this->getRepository()->findAll();
    }

    /**
     * {@inheritdoc}
     */
    public function save(Repository $repository)
    {
        $identifier = $repository->identifier();
        $existingRepository = $this->getRepository()->findOneBy(['identifier' => $identifier]);

        if($existingRepository !== null) {
            throw new  ExistingRepositoryIdentifierException('A repository with identifier '.$identifier.' already exists.');
        }

        $manager = $this->getManager();
        $manager->persist($repository);
        $manager->flush();

        return $repository;
    }

    /**
     * Get entity manager
     * @return \Doctrine\Common\Persistence\ObjectManager|null entity manager
     */
    private function getManager()
    {
        return $this->registry->getManagerForClass(DoctrineRepositoryManager::ENTITY_CLASS);
    }

    /**
     * Get doctrine repository
     * @return Doctrine\Common\Persistence\ObjectRepository
     */
    private function getRepository()
    {
        return $this->getManager()->getRepository(self::ENTITY_CLASS);
    }
}
