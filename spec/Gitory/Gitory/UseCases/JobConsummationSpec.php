<?php

namespace spec\Gitory\Gitory\UseCases;

use Gitory\Gitory\JobConsumer;
use Gitory\Gitory\Entities\Job\Job;
use Gitory\Gitory\Managers\JobManager;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Exception;
use Psr\Log\LoggerInterface;
use Pimple\Container;

class JobConsummationSpec extends ObjectBehavior
{

    public function let(Container $container, JobManager $jobManager, LoggerInterface $logger)
    {
        $this->beConstructedWith($container, $jobManager, $logger);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType('Gitory\Gitory\UseCases\JobConsummation');
    }

    public function it_consumes_a_job(
        JobManager $jobManager,
        Container $container,
        LoggerInterface $logger,
        Job $job,
        JobConsumer $jobConsumer
    ) {
        $payload = ['some' => 'data'];

        $container->offsetGet('serviceName')->willReturn($jobConsumer);
        $jobConsumer->consume($payload)->shouldBeCalled();
        $job->service()->willReturn('serviceName');
        $job->payload()->willReturn($payload);
        $jobManager->pop()->willReturn($job);

        $jobManager->completeJob($job)->shouldBeCalled();

        $this->exec();
    }

    public function it_fails_a_job(
        JobManager $jobManager,
        Container $container,
        LoggerInterface $logger,
        Job $job,
        JobConsumer $jobConsumer
    ) {
        $payload = ['some' => 'data'];

        $container->offsetGet('serviceName')->willReturn($jobConsumer);
        $jobConsumer->consume($payload)->willThrow(new Exception);
        $job->service()->willReturn('serviceName');
        $job->payload()->willReturn($payload);
        $jobManager->pop()->willReturn($job);

        $jobManager->completeJob($job)->shouldNotBeCalled();
        $jobManager->failJob($job)->shouldBeCalled();

        $this->exec();
    }

    public function it_fails_an_invalid_job(
        JobManager $jobManager,
        Container $container,
        LoggerInterface $logger,
        Job $job
    ) {
        $payload = ['some' => 'data'];

        $container->offsetGet('serviceName')->willReturn(null);
        $job->service()->willReturn('serviceName');
        $jobManager->pop()->willReturn($job);

        $jobManager->completeJob($job)->shouldNotBeCalled();
        $jobManager->failJob($job)->shouldBeCalled();

        $this->shouldThrow(new Exception('serviceName is not a JobComsumer'))->duringExec();
    }

}
