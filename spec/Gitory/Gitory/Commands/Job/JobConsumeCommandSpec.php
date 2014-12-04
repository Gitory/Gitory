<?php

namespace spec\Gitory\Gitory\Commands\Job;

use Gitory\Gitory\Entities\Job\Job;
use Gitory\Gitory\Managers\JobManager;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Prophecy\Argument;
use Exception;
use Psr\Log\LoggerInterface;

class JobConsumeCommandSpec extends ObjectBehavior
{

    public function let(JobManager $jobManager, LoggerInterface $logger, InputInterface $input)
    {
        $this->beConstructedWith($jobManager, $logger);
        $input->bind(Argument::any())->willReturn(null);
        $input->isInteractive()->willReturn(false);
        $input->validate()->willReturn(null);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType('Gitory\Gitory\Commands\Job\JobConsumeCommand');
    }

    public function it_test_if_the_job_queue_is_empty(
        JobManager $jobManager,
        LoggerInterface $logger,
        InputInterface $input,
        OutputInterface $output
    ) {
        $jobManager->pop()->willReturn(false);

        $logger->notice('Job queue is empty')->shouldBeCalled();
        $this->run($input, $output);
    }

    public function it_consume_a_pending_job(
        Job $job,
        JobManager $jobManager,
        LoggerInterface $logger,
        InputInterface $input,
        OutputInterface $output
    ) {
        $jobManager->pop()->willReturn($job);

    }
}
