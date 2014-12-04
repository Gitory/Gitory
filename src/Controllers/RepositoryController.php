<?php

namespace Gitory\Gitory\Controllers;

use Gitory\Gitory\Managers\RepositoryManager;
use Gitory\Gitory\Exceptions\ExistingRepositoryIdentifierException;
use Gitory\Gitory\API\Response;
use Gitory\Gitory\UseCases\RepositoryCreation;

class RepositoryController
{
    /**
     * @var RepositoryCreation
     */
    private $repositoryCreation;

    /**
     * @var RepositoryManager
     */
    private $repositoryManager;

    /**
     * @param RepositoryCreation $repositoryCreation
     * @param RepositoryManager  $repositoryManager
     */
    public function __construct(RepositoryCreation $repositoryCreation, RepositoryManager $repositoryManager)
    {
        $this->repositoryCreation = $repositoryCreation;
        $this->repositoryManager = $repositoryManager;
    }

    public function listAction()
    {
        $repositories = $this->repositoryManager->findAll();

        return new Response(array_map(function ($repository) {
            return ['identifier' => $repository->identifier()];
        }, $repositories));
    }

    public function createAction($identifier)
    {
        try {
            $repository = $this->repositoryCreation->exec($identifier);

            return new Response([
                'identifier' => $repository->identifier()
            ], Response::HTTP_CREATED);
        } catch(ExistingRepositoryIdentifierException $e) {
            return new Response([
                'id' => 'existing-repository-identifier-exception',
                'message' => $e->getMessage()
            ], Response::HTTP_CONFLICT);
        }

    }
}
