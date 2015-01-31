<?php

namespace Gitory\Gitory\Managers\Doctrine;

use Gitory\Gitory\Managers\RepositoryManager;
use Gitory\Gitory\Entities\Repository;
use Gitory\Gitory\Exceptions\ExistingRepositoryIdentifierException;
use Doctrine\Common\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;

class DoctrineRepositoryManager implements RepositoryManager
{
    use DoctrineRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Entity class name
     */
    const ENTITY_CLASS = 'Gitory\Gitory\Entities\Repository';

    /**
     * @param \Doctrine\Common\Persistence\ManagerRegistry $registry registry manager
     */
    public function __construct(ManagerRegistry $registry, LoggerInterface $logger)
    {
        $this->registry = $registry;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function findAll()
    {
        return $this->getRepository()->findAll();
    }

    /**
     * Find a repository by identifier
     * @param  integer $identifier
     * @return Gitory\Gitory\Entities\Repository
     */
    public function findByIdentifier($identifier)
    {
        return $this->getRepository()->findOneBy(['identifier' => $identifier]);
    }

    /**
     * {@inheritdoc}
     */
    public function save(Repository $repository)
    {
        $identifier = $repository->identifier();
        $existingRepository = $this->getRepository()->findOneBy(['identifier' => $identifier]);

        if($existingRepository !== null) {
            throw new  ExistingRepositoryIdentifierException('A repository with identifier "'.$identifier.'" already exists.');
        }

        $manager = $this->getManager();
        $manager->persist($repository);
        $manager->flush();

        $this->logger->notice('Repository "{identifier}" has been created in database', ['identifier' => $identifier]);

        return $repository;
    }
}
