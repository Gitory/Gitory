<?php

namespace spec\Gitory\Gitory\UseCases;

use Gitory\Gitory\Entities\Job\Job;
use Gitory\Gitory\Managers\JobManager;
use Gitory\Gitory\Managers\RepositoryManager;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Exception;

class RepositoryCreationSpec extends ObjectBehavior
{

    public function let(RepositoryManager $repositoryManager, JobManager $jobManager)
    {
        $this->beConstructedWith($repositoryManager, $jobManager);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType('Gitory\Gitory\UseCases\RepositoryCreation');
    }

    public function it_creates_a_repository(RepositoryManager $repositoryManager, JobManager $jobManager)
    {
        $jobManager->push('repository:creation', ['identifier' => 'ood'])->shouldBeCalled();
        $repositoryManager->save(Argument::type('Gitory\Gitory\Entities\Repository'))->willReturnArgument();

        $this->exec('ood')->shouldHaveType('Gitory\Gitory\Entities\Repository');
    }

    public function it_does_not_create_a_job_without_a_repository(RepositoryManager $repositoryManager, JobManager $jobManager)
    {
        $jobManager->push('repository:creation', ['identifier' => 'ood'])->shouldNotBeCalled();
        $repositoryManager->save(Argument::type('Gitory\Gitory\Entities\Repository'))->willThrow(new Exception);

        $this->shouldThrow('Exception')->duringExec('ood');
    }

}
