<?php

namespace Gitory\Gitory\Managers\Doctrine;

use Doctrine\Common\Persistence\ManagerRegistry;

trait DoctrineRepository
{
    private $registry;

    public function __construct(ManagerRegistry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * Get entity manager
     * @return \Doctrine\Common\Persistence\ObjectManager|null entity manager
     */
    private function getManager()
    {
        return $this->registry->getManagerForClass(static::ENTITY_CLASS);
    }

    /**
     * Get doctrine repository
     * @return Doctrine\Common\Persistence\ObjectRepository
     */
    private function getRepository()
    {
        return $this->getManager()->getRepository(static::ENTITY_CLASS);
    }
}
