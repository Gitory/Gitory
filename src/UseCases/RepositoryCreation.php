<?php

namespace Gitory\Gitory\UseCases;

use Gitory\Gitory\Entities\Repository;
use Gitory\Gitory\Managers\RepositoryManager;
use Gitory\Gitory\Managers\JobManager;

class RepositoryCreation
{
    /**
     * @var RepositoryManager
     */
    private $repositoryManager;

    /**
     * @var Gitory\Gitory\Managers\JobManager
     */
    private $jobManager;

    /**
     * @param RepositoryManager $repositoryManager
     * @param JobManager $jobManager
     */
    public function __construct(RepositoryManager $repositoryManager, JobManager $jobManager)
    {
        $this->repositoryManager = $repositoryManager;
        $this->jobManager = $jobManager;
    }

    public function exec($respositoryIdentifier)
    {
        $repository = new Repository($respositoryIdentifier);
        $savedRepository = $this->repositoryManager->save($repository);
        $this->jobManager->push('repository:creation', ['identifier' => $respositoryIdentifier]);

        return $savedRepository;
    }
}
