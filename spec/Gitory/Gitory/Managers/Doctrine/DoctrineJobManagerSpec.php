<?php

namespace spec\Gitory\Gitory\Managers\Doctrine;

use Gitory\Gitory\Entities\Job\Job;
use Gitory\Gitory\Entities\Job\JobStatusType;
use Gitory\Gitory\Managers\Doctrine\DoctrineJobManager;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use PhpSpec\ObjectBehavior;
use Psr\Log\LoggerInterface;
use Prophecy\Argument;
use TH\Lock\LockFactory;

class DoctrineJobManagerSpec extends ObjectBehavior
{
    public function let(
        ManagerRegistry $registry,
        LoggerInterface $logger,
        EntityManagerInterface $em,
        JobStatusType $initialStatusType,
        JobStatusType $inProgressStatusType,
        JobStatusType $completedStatusType,
        JobStatusType $failedStatusType,
        LockFactory $lockFactory
    )
    {
        $this->beConstructedWith(
            $initialStatusType,
            $inProgressStatusType,
            $completedStatusType,
            $failedStatusType,
            $registry,
            $lockFactory,
            $logger
        );

        $registry->getManagerForClass(DoctrineJobManager::ENTITY_CLASS)->willReturn($em);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType('Gitory\Gitory\Managers\Doctrine\DoctrineJobManager');
        $this->shouldImplement('Gitory\Gitory\Managers\JobManager');
    }

    public function it_push_job(EntityManagerInterface $em, LoggerInterface $logger)
    {
        $em->persist(Argument::any())->shouldBeCalled();
        $em->flush()->shouldBeCalled();
        $logger->notice('Job "{service}" has been created in database', ['service' => 'doctor:regeneration'])->shouldBeCalled();

        $this->push('doctor:regeneration', [
            'previous' => 'Christopher Eccleston',
            'next' => 'David Tennant'
        ]);
    }

    public function xit_pop_job(
        EntityManagerInterface $em,
        LoggerInterface $logger,
        Job $job,
        Query $query
    )
    {
        $em->createQuery(Argument::any())->willReturn($query);
        $query->execute()->willReturn([$job]);
        $query->setMaxResults(1)->shouldBeCalledOnce();

        $this->pop()->shouldReturn($job);
    }

    public function xit_does_not_pop_job(
        EntityManagerInterface $em,
        LoggerInterface $logger,
        Query $query
    )
    {
        $em->createQuery(Argument::any())->willReturn($query);
        $query->execute()->willReturn([]);
        $query->setMaxResults(1)->shouldBeCalledOnce();

        $this->pop()->shouldReturn(false);
    }

    public function it_completes_a_job(EntityManagerInterface $em, Job $job, LoggerInterface $logger, JobStatusType $completedStatusType)
    {
        $job->setStatus($completedStatusType)->shouldBeCalled();
        $em->persist(Argument::any())->shouldBeCalled();
        $em->flush()->shouldBeCalled();
        $job->service()->willReturn('doctor:regeneration');

        $logger->notice('Job "{service}" has been completed', ['service' => 'doctor:regeneration'])->shouldBeCalled();

        $this->completeJob($job);
    }

    public function it_fails_a_job(EntityManagerInterface $em, Job $job, LoggerInterface $logger, JobStatusType $failedStatusType)
    {
        $job->setStatus($failedStatusType)->shouldBeCalled();
        $em->persist(Argument::any())->shouldBeCalled();
        $em->flush()->shouldBeCalled();
        $job->service()->willReturn('doctor:regeneration');

        $logger->notice('Job "{service}" has failed', ['service' => 'doctor:regeneration'])->shouldBeCalled();

        $this->failJob($job);
    }
}
