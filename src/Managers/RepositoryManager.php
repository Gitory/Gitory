<?php

namespace Gitory\Gitory\Managers;


interface RepositoryManager
{
    /**
     * Find all repositories
     * @return array array of Gitory\Gitory\Entities\Repository
     */
    public function findAll();
}
