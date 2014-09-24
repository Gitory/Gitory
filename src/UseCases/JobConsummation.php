<?php

namespace Gitory\Gitory\UseCases;

use Gitory\Gitory\Managers\JobManager;
use Gitory\Gitory\JobConsumer;
use Psr\Log\LoggerInterface;
use Pimple\Container;
use Exception;

class JobConsummation
{
    /**
     * @var Gitory\Gitory\Managers\JobManager
     */
    private $jobManager;

    /**
     * @var Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var Container
     */
    private $container;

    /**
     * @param RepositoryManager $repositoryManager
     * @param JobManager $jobManager
     */
    public function __construct(Container $container, JobManager $jobManager, LoggerInterface $logger)
    {
        $this->jobManager = $jobManager;
        $this->logger = $logger;
        $this->container = $container;
    }

    public function exec()
    {
        $job = $this->jobManager->pop();
        $serviceName = $job->service();
        $service = $this->container[$serviceName];

        if(!($service instanceof JobConsumer)) {
            $this->jobManager->failJob($job);
            throw new Exception($serviceName.' is not a JobComsumer');
        }

        $payload = $job->payload();
        try {
            $service->consume($payload);
            $this->jobManager->completeJob($job);
        } catch (Exception $e) {
            $this->jobManager->failJob($job);
        }
    }
}
