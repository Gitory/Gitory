<?php

namespace Gitory\Gitory\Commands\Repository;

use Gitory\Gitory\Entities\Repository;
use Gitory\Gitory\Managers\RepositoryManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Gitory\Gitory\GitHosting;
use Exception;

class RepositoryCreateCommand extends Command
{
    /**
     * @var RepositoryManager
     */
    private $repositoryManager;

    /**
     * @var string
     */
    private $repositoriesFolderPath;

    /**
     * @var GitHosting
     */
    private $gitHosting;

    public function __construct(RepositoryManager $repositoryManager, GitHosting $gitHosting)
    {
        parent::__construct();

        $this->repositoryManager = $repositoryManager;
        $this->gitHosting = $gitHosting;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('repository:create')
            ->setDescription('Create a repository')
            ->addArgument(
                'identifier',
                InputArgument::REQUIRED
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $identifier = $input->getArgument('identifier');
        $repository = $this->repositoryManager->findByIdentifier($identifier);

        if($repository === null) {
            throw new  Exception('Repository '.$identifier.' not found in database, git repository hasn\'t been created');
        } else {
            $this->gitHosting->init($identifier);
            $output->writeln('Repository '.$identifier.' has been created');
        }
    }
}
