<?php

namespace Gitory\Gitory\Entities\Job;

use Gedmo\Mapping\Annotation as Gedmo;
use DateTime;

/**
 * @Entity
 */
class Job
{
    /**
     * @Id @Column(type="integer")
     * @GeneratedValue
     */
    private $id;

    /**
     * @Gedmo\Timestampable(on="create")
     * @Column(type="datetime", nullable=false)
     */
    private $createdAt;

    /**
     * @Column(type="datetime", nullable=true)
     */
    private $completedAt;

    /**
     * @OneToOne(targetEntity="JobStatus", cascade={"persist"})
     */
    private $currentStatus;

    /**
     * @OneToMany(targetEntity="JobStatus", mappedBy="id", orphanRemoval=true)
     */
    private $statuses;

    /**
     * @Column(nullable=false)
     */
    private $service;

    /**
     * @Column(type="json_array", nullable=false)
     */
    private $payload;

    /**
     * @param string $service service name
     * @param array  $payload service payload
     */
    public function __construct($service, array $payload = [], JobStatus $status)
    {
        $this->service = $service;
        $this->payload = $payload;
        $this->currentStatus = $status;
        $this->createdAt = new DateTime();
    }

    /**
     * Get the current status
     * @return JobStatus
     */
    public function currentStatus()
    {
        return $this->currentStatus;
    }

    /**
     * Get the job payload
     * @return array
     */
    public function payload()
    {
        return $this->payload;
    }

    /**
     * Get the job service
     * @return string
     */
    public function service()
    {
        return $this->service;
    }

    /**
     * Set a job status
     * @param JobStatusType $jobStatusType
     */
    public function setStatus(JobStatusType $jobStatusType)
    {
        $jobStatus = new JobStatus($jobStatusType);
        $this->currentStatus = $jobStatus;
    }
}
