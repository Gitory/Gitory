<?php

namespace spec\Gitory\Gitory\Controllers;

use Gitory\Gitory\Entities\Repository;
use Gitory\Gitory\Managers\RepositoryManager;
use Gitory\Gitory\Exceptions\ExistingRepositoryIdentifierException;
use Symfony\Component\HttpFoundation\Request;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class RepositoryControllerSpec extends ObjectBehavior
{
    public function let(RepositoryManager $repositoryManager)
    {
        $this->beConstructedWith($repositoryManager);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType('Gitory\Gitory\Controllers\RepositoryController');
    }

    public function it_list_repositories(RepositoryManager $repositoryManager)
    {
        $repositoryManager->findAll()->willReturn([new Repository('gallifrey')]);

        $response = $this->listAction();

        $response->shouldHaveType('Symfony\Component\HttpFoundation\JsonResponse');
        $response->getStatusCode()->shouldBe(200);
        $response->getContent()->shouldBe(json_encode([['identifier' => 'gallifrey']]));
    }

    public function it_create_a_repository(
        RepositoryManager $repositoryManager,
        Repository $repository
    ) {
        $repository->identifier()->willReturn('gallifrey');
        $repositoryManager->save(Argument::type('Gitory\Gitory\Entities\Repository'))->willReturn(
            $repository
        );

        $response = $this->createAction('gallifrey');

        $response->shouldHaveType('Symfony\Component\HttpFoundation\JsonResponse');
        $response->getStatusCode()->shouldBe(201);
        $response->getContent()->shouldBe(json_encode([
            'identifier' => 'gallifrey'
        ]));
    }

    public function it_does_not_create_repositories_with_existing_identifier(
        RepositoryManager $repositoryManager,
        Request $request,
        Repository $repository
    ) {
        $message = 'A repository with identifier gallifrey already exists.';
        $request->getContent()->willReturn('{"identifier": "gallifrey"}');
        $repositoryManager->save(Argument::type('Gitory\Gitory\Entities\Repository'))->willThrow(
            new ExistingRepositoryIdentifierException($message)
        );

        $response = $this->createAction($request);
        $response->shouldHaveType('Symfony\Component\HttpFoundation\JsonResponse');
        $response->getStatusCode()->shouldBe(409);
        $response->getContent()->shouldBe(json_encode([
            'error' => [
                'id' => 'existing-repository-identifier-exception',
                'message' => $message
            ]
        ]));
    }
}
