<?php

namespace spec\Gitory\Gitory\Commands\Repository;

use Gitory\Gitory\Entities\Repository;
use Gitory\Gitory\Managers\RepositoryManager;
use Gitory\Gitory\Exceptions\ExistingRepositoryIdentifierException;
use Gitory\Gitory\GitHosting;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Prophecy\Argument;
use Exception;
use Psr\Log\LoggerInterface;

class RepositoryCreateCommandSpec extends ObjectBehavior
{

    public function let(RepositoryManager $repositoryManager, GitHosting $gitHosting, LoggerInterface $logger)
    {
        $this->beConstructedWith($repositoryManager, $gitHosting, $logger);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType('Gitory\Gitory\Commands\Repository\RepositoryCreateCommand');
    }

    public function it_does_not_create_a_repository_which_does_not_exists_in_database(
        RepositoryManager $repositoryManager,
        InputInterface $input,
        OutputInterface $output
    ) {
        $identifier = 'amy';
        $repositoryManager->findByIdentifier($identifier)->willReturn(null);
        $input->bind(Argument::any())->willReturn(null);
        $input->isInteractive()->willReturn(false);
        $input->validate()->willReturn(null);
        $input->getArgument('identifier')->willReturn($identifier);
        $exception = new Exception('Repository "'.$identifier.'" not found in database, git repository hasn\'t been created');
        $this->shouldThrow($exception)->duringRun($input, $output);
    }

    public function it_create_a_repository(
        RepositoryManager $repositoryManager,
        Repository $repository,
        GitHosting $gitHosting,
        InputInterface $input,
        OutputInterface $output,
        LoggerInterface $logger
    ) {
        $identifier = 'gallifrey';
        $repositoryManager->findByIdentifier($identifier)->willReturn($repository);
        $repository->identifier()->willReturn($identifier);

        $input->bind(Argument::any())->willReturn(null);
        $input->isInteractive()->willReturn(false);
        $input->validate()->willReturn(null);
        $input->getArgument('identifier')->willReturn($identifier);

        $gitHosting->init($identifier)->shouldBeCalled();
        $logger->notice('Repository "{identifier}" has been created', ['identifier' => $identifier])->shouldBeCalled();
        $this->run($input, $output);
    }
}
