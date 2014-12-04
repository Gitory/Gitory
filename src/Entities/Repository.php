<?php

namespace Gitory\Gitory\Entities;

/**
 * @Entity
 */
class Repository
{

    /**
     * @Id @Column(type="integer")
     * @GeneratedValue
     */
    private $id;

    /**
     * @Column(length=128)
     */
    private $identifier;

    /**
     * @Column(length=256, nullable=true)
     */
    private $url;

    /**
     * @Column(length=256, nullable=true)
     */
    private $description;

    /**
     * @param string $identifier repository identifier
     */
    public function __construct($identifier)
    {
        $this->identifier = $identifier;
    }

    /**
     * Get repository identifier
     * @return string repository identifier
     */
    public function identifier()
    {
        return $this->identifier;
    }
}
