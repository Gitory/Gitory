<?php

namespace Gitory\Gitory\Managers\Doctrine;

use Gitory\Gitory\Managers\JobManager;
use Gitory\Gitory\Entities\Job\Job;
use Gitory\Gitory\Entities\Job\JobStatus;
use Gitory\Gitory\Entities\Job\JobStatusType;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\ObjectRepository;
use TH\Lock\LockFactory;
use Psr\Log\LoggerInterface;
use Exception;

class DoctrineJobManager implements JobManager
{
    /**
     * Doctrine registry
     * @var \Doctrine\Common\Persistence\ManagerRegistry
     */
    private $registry;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * New job initial status type
     * @var JobStatusType
     */
    private $initialStatusType;

    /**
     * In progress status type
     * @var JobStatusType
     */
    private $inProgressStatusType;

    /**
     * Completed status type
     * @var JobStatusType
     */
    private $completedStatusType;

    /**
     * Failed status type
     * @var JobStatusType
     */
    private $failedStatusType;

    /**
     * @var LockFactory
     */
    private $lockFactory;

    /**
     * Entity class name
     */
    const ENTITY_CLASS = 'Gitory\Gitory\Entities\Job\Job';

    /**
     * @param \Doctrine\Common\Persistence\ManagerRegistry $registry registry manager
     */
    public function __construct(
        JobStatusType $initialStatusType,
        JobStatusType $inProgressStatusType,
        JobStatusType $completedStatusType,
        JobStatusType $failedStatusType,
        ManagerRegistry $registry,
        LockFactory $lockFactory,
        LoggerInterface $logger
    ) {
        $this->registry = $registry;
        $this->logger = $logger;
        $this->initialStatusType = $initialStatusType;
        $this->inProgressStatusType = $inProgressStatusType;
        $this->completedStatusType = $completedStatusType;
        $this->failedStatusType = $failedStatusType;
        $this->lockFactory = $lockFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function push($service, array $payload)
    {
        $status = new JobStatus($this->initialStatusType);
        $job = new Job($service, $payload, $status);

        $manager = $this->getManager();
        $manager->persist($job);
        $manager->flush();

        $this->logger->notice('Job "{service}" has been created in database', ['service' => $service]);
    }

    /**
     * {@inheritdoc}
     */
    public function pop()
    {
        $manager = $this->getManager();
        $jobQuery = $manager->createQuery("SELECT j
            FROM Gitory\Gitory\Entities\Job\Job j
            JOIN j.currentStatus js
            JOIN js.statusType jst
            WHERE jst.identifier = 'pending'
            ORDER BY j.createdAt
        ");

        $jobQuery->setMaxResults(1);
        $job = $jobQuery->execute();
        $job = reset($job);

        if ($job === false) {
            throw new Exception('Job queue is empty');
        }

        $lock = $this->lockFactory->create($job);
        $lock->acquire();

        $job->setStatus($this->inProgressStatusType);

        $manager->persist($job);
        $manager->flush();

        return $job;
    }

    /**
     * @{inheritdoc}
     */
    public function completeJob($job)
    {
        $manager = $this->getManager();
        $job->setStatus($this->completedStatusType);

        $manager->persist($job);
        $manager->flush();

        $this->logger->notice('Job "{service}" has been completed', ['service' => $job->service()]);

        return $job;
    }

    /**
     * @{inheritdoc}
     */
    public function failJob($job)
    {
        $manager = $this->getManager();
        $job->setStatus($this->failedStatusType);

        $manager->persist($job);
        $manager->flush();

        $this->logger->notice('Job "{service}" has failed', ['service' => $job->service()]);

        return $job;
    }

    /**
     * Get entity manager
     * @return \Doctrine\Common\Persistence\ObjectManager|null entity manager
     */
    private function getManager()
    {
        return $this->registry->getManagerForClass(self::ENTITY_CLASS);
    }

    /**
     * Get doctrine repository
     * @return Doctrine\Common\Persistence\ObjectRepository
     */
    private function getRepository()
    {
        return $this->getManager()->getRepository(self::ENTITY_CLASS);
    }
}
