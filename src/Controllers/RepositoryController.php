<?php

namespace Gitory\Gitory\Controllers;

use Gitory\Gitory\Entities\Repository;
use Gitory\Gitory\Managers\RepositoryManager;
use Gitory\Gitory\Exceptions\ExistingRepositoryIdentifierException;
use Gitory\Gitory\API\Response;
use Symfony\Component\HttpFoundation\Request;

class RepositoryController
{
    private $repositoryManager;

    public function __construct(RepositoryManager $repositoryManager)
    {
        $this->repositoryManager = $repositoryManager;
    }

    public function listAction()
    {
        $repositories = $this->repositoryManager->findAll();

        return new Response([
            'repositories' => array_map(function ($repository) {
                return $repository->identifier();
            },
            $repositories)
        ]);
    }

    public function createAction(Request $request)
    {
        $requestContent = $request->getContent();

        $identifier = json_decode($requestContent)->identifier;
        $repository = new Repository($identifier);
        try {
            $repository = $this->repositoryManager->save($repository);
        } catch(ExistingRepositoryIdentifierException $e) {
            return new Response([
                'id' => 'existing-repository-identifier-exception',
                'message' => $e->getMessage()
            ], 409);
        }

        return new Response([
            'repository' => [
                'identifier' => $repository->identifier()
            ]
        ], 201);
    }
}
