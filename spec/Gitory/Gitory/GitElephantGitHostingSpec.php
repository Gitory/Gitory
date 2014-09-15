<?php

namespace spec\Gitory\Gitory;

use PhpSpec\ObjectBehavior;
use Gitory\Gitory\GitElephantGitHosting;
use VirtualFileSystem\FileSystem;
use GitElephant\Repository as GitRepository;
use Exception;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class GitElephantGitHostingSpec extends ObjectBehavior
{
    /**
     * Repositories path
     * @var string
     */
    private $repositoriesPath;

    public function let()
    {
        $dir = $this->repositoriesPath = __DIR__.'/../../../private/test/repositories/';
        if(is_dir($dir)) {
            $it = new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS);
            $files = new RecursiveIteratorIterator($it, RecursiveIteratorIterator::CHILD_FIRST);

            foreach($files as $file) {
                if ($file->getFilename() === '.' || $file->getFilename() === '..') {
                    continue;
                }
                if ($file->isDir()) {
                    rmdir($file->getRealPath());
                } else {
                    unlink($file->getRealPath());
                }
            }
            rmdir($dir);
        }

        mkdir($dir, 0777, true);
        $this->beConstructedWith($this->repositoriesPath);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType('Gitory\Gitory\GitElephantGitHosting');
    }

    public function it_init_a_repository_with_an_existing_repository_folder()
    {
        mkdir($this->repositoriesPath.'/gallifrey/');
        $exception = new Exception('Repository "gallifrey" folder already exists');
        $this->shouldThrow($exception)->duringInit('gallifrey');
    }

    public function it_init_a_repository(GitRepository $gitRepository)
    {
        $identifier = 'gallifrey';
        $this->init($identifier);
        if(!file_exists($this->repositoriesPath.$identifier.'/config')) {
            throw new Exception('Repository '.$identifier.' does not exists');
        }
    }
}
