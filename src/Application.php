<?php

namespace Gitory\Gitory;

use Gitory\Gitory\Entities\Repository;
use Gitory\Gitory\Exceptions\ExistingRepositoryIdentifierException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Application extends \Silex\Application
{
    use Bootstrap;

    public function __construct(array $values = array())
    {
        parent::__construct($values);

        $this->initDI($values);

        $this->get('/repositories', function () {
            $repositories = $this['RepositoryManager']->findAll();

            return json_encode([
                'meta' => ['status' => 'success'],
                'response' => ['repositories' => array_map(function ($repository) {
                    return $repository->identifier();
                },
                $repositories)]
            ]);
        });

        $this->post('/repository', function (Request $request) {
            $requestContent = $request->getContent();

            $identifier = json_decode($requestContent)->identifier;
            $repository = new Repository($identifier);
            try {
                $repository = $this['RepositoryManager']->save($repository);
            } catch(ExistingRepositoryIdentifierException $e) {
                return new Response(json_encode([
                    'meta' => [
                        'status' => 'failure',
                        'error' => [
                            'id' => 'existing-repository-identifier-exception',
                            'message' => $e->getMessage()
                        ]
                    ],
                    'response' => []
                ]), 409);
            }

            return new Response(json_encode([
                'meta' => [
                    'status' => 'success'
                ],
                'response' => [
                    'repository' => [
                        'identifier' => $repository->identifier()
                    ]
                ]
            ]), 201);
        });
    }
}
