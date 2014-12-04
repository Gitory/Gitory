<?php

namespace Gitory\Gitory\Entities\Job;

/**
 * @Entity
 * @Table(uniqueConstraints={@UniqueConstraint(name="unique_identifier", columns={"identifier"})})
 */
class JobStatusType
{
    /**
     * @Id @Column(type="integer")
     * @GeneratedValue
     */
    private $id;

    /**
     * @Column(nullable=false)
     */
    private $identifier;

    /**
     * @Column(nullable=false)
     */
    private $description;

    /**
     * @param string $identifier
     * @param string $description
     */
    public function __construct($identifier, $description)
    {
        $this->identifier = $identifier;
        $this->description = $description;
    }

    /**
     * Get identifier
     * @return string
     */
    public function identifier()
    {
        return $this->identifier;
    }

}
