<?php

namespace Gitory\Gitory\Entities\Job;

use Gedmo\Mapping\Annotation as Gedmo;
use DateTime;

/**
 * @Entity
 */
class JobStatus
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
     * @Column(nullable=true)
     */
    private $pid;

    /**
     * @Column(nullable=true)
     */
    private $workerId;

    /**
     * @ManyToOne(targetEntity="JobStatusType")
     */
    private $statusType;

    /**
     * @Column
     */
    private $message;

    /**
     * @param JobStatusType $statusType
     * @param string        $message
     * @param string|null   $workerId   worker identifier
     * @param string|null   $pid        unix process id
     */
    public function __construct(JobStatusType $statusType, $message = "", $workerId = null, $pid = null)
    {
        $this->statusType = $statusType;
        $this->message = $message;
        $this->workerId = $workerId;
        $this->pid = $pid;
        $this->createdAt = new DateTime();
    }

    /**
     * Get status type
     * @return JobStatusType
     */
    public function statusType()
    {
        return $this->statusType;
    }
}
