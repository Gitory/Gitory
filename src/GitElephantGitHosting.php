<?php

namespace Gitory\Gitory;

use Gitory\Gitory\GitHosting;
use GitElephant\Repository as GitRepository;
use Exception;

class GitElephantGitHosting implements GitHosting
{


    /**
     * @var string
     */
    private $repositoriesFolderPath;

    /**
     * {@inheritdoc}
     */
    public function __construct($repositoriesFolderPath)
    {
        $this->repositoriesFolderPath = $repositoriesFolderPath;
    }

    /**
     * {@inheritdoc}
     */
    public function init($identifier)
    {
        if(file_exists($this->repositoriesFolderPath.$identifier)) {
            throw new Exception('Repository '.$identifier.' folder already exists');
        } else {
            mkdir($this->repositoriesFolderPath.$identifier, 0777, true);

            $gitRepository = $this->get($identifier);
            $gitRepository->init(true);
        }
    }

    /**
     * @param  string $identifier
     * @return GitRepository
     */
    private function get($identifier)
    {
        return new GitRepository($this->repositoriesFolderPath.$identifier);
    }
}
