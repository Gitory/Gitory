<?php

namespace Gitory\Gitory\Commands\Job;

use Gitory\Gitory\UseCases\JobConsummation;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Exception;
use Psr\Log\LoggerInterface;

class JobConsumeCommand extends Command
{
    /**
     * @var JobConsummation
     */
    private $jobConsummation;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(JobConsummation $jobConsummation, LoggerInterface $logger)
    {
        parent::__construct();

        $this->jobConsummation = $jobConsummation;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('job:consume')
            ->setDescription('Consume pending jobs');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $this->jobConsummation->exec();
        } catch (Exception $e) {
            $this->logger->notice($e->getMessage());
        }
    }
}
