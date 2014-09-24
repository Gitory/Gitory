<?php

use Behat\Behat\Tester\Exception\PendingException;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;

use Doctrine\ORM\Tools\SchemaTool;

use Gitory\Gitory\Application;
use Gitory\Gitory\Entities\Repository;
use Gitory\Gitory\Entities\Job\Job;
use Gitory\Gitory\Entities\Job\JobStatus;
use Gitory\Gitory\Entities\Job\JobStatusType;

/**
 * Behat context class.
 */
class DomainContext implements SnippetAcceptingContext
{
    /**
     * Gitory application
     * @var Gitory\Gitory\Application
     */
    private $app;

    /**
     * Entity Manager
     * @var Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * Registry manager
     * @var \Doctrine\Common\Persistence\ManagerRegistry
     */
    private $registry;

    /**
     * Initializes context.
     *
     * Every scenario gets its own context object.
     * You can also pass arbitrary arguments to the context constructor through behat.yml.
     */
    public function __construct()
    {
        $this->app = new Application('test');

        $this->em = $this->app['orm.em'];
        $this->registry = $this->app['doctrine'];
    }

    /**
     * @BeforeSuite
     */
    public static function createDB()
    {
        $dir = __DIR__.'/../../private/test/';
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
    }

    /**
    * @BeforeScenario
    */
    public function cleanDB()
    {
        // Drop old datas & create news
        $schemaTools = new SchemaTool($this->em);
        $classes = $this->em->getMetadataFactory()->getAllMetadata();
        $schemaTools->dropSchema($classes);
        $schemaTools->createSchema($classes);
    }

    /**
     * Add a repository
     *
     * @Given there is a repository named :repositoryIdentifier
     */
    public function thereIsARepositoryNamed($repositoryIdentifier)
    {
        $repository = new Repository($repositoryIdentifier);
        $this->em->persist($repository);
        $this->em->flush();
    }

    /**
     * @Then there is a :statusName :serviceName job with payload
     */
    public function thereIsAJobWithPayload($statusName, $serviceName, PyStringNode $payloadString)
    {
        $payloadTemplate = json_decode((string)$payloadString, true);

        if($payloadTemplate === null) {
            throw new Exception("Cannot decode payload template : ".(string)$payloadString);
        }

        $jobs = $this->em->getRepository('Gitory\Gitory\Entities\Job\Job')
            ->findBy(['service' => $serviceName]);

        $foundJob = null;
        foreach ($jobs as $job) {
            if (json_encode($job->payload()) === json_encode($payloadTemplate)) {
                $foundJob = $job;
            }
        }

        if ($foundJob === null) {
            throw new Exception(
                "Job with service $serviceName and payload ". var_export($payloadTemplate, true)." does not exists."
            );
        }

        $currentStatus = $foundJob->currentStatus();
        $jobStatusTypeIdentifier = $currentStatus->statusType()->identifier();
        if($jobStatusTypeIdentifier !== $statusName) {
            throw new Exception("Job status $jobStatusTypeIdentifier does not match $statusName");
        }
    }

    /**
     * @Given there is a pending job :serviceName with payload
     */
    public function thereIsAPendingJobWithPayload($serviceName, PyStringNode $payloadString)
    {
        $payload = json_decode((string)$payloadString, true);

        if($payload === null) {
            throw new Exception("Cannot decode payload : ".(string)$payloadString);
        }

        $jobStatusType = $this->em->getRepository('Gitory\Gitory\Entities\Job\JobStatusType')
            ->findOneByIdentifier('pending');

        if ($jobStatusType === null) {
            $jobStatusType = new JobStatusType('pending', '');
            $this->em->persist($jobStatusType);
        }

        $jobStatus = new JobStatus($jobStatusType);

        $job = new Job($serviceName, $payload, $jobStatus);
        $this->em->persist($job);
        $this->em->flush();
    }

    /**
     * @Then there is no pending job
     */
    public function thereIsNoPendingJob()
    {
        throw new PendingException();
    }

    /**
     * @Then Job :serviceName status is not pending
     */
    public function jobStatusIsNotPending($serviceName)
    {
        throw new PendingException();
    }

    /**
     * @Then there is one pending job
     */
    public function thereIsOnePendingJob()
    {
        throw new PendingException();
    }
}
