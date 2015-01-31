<?php

namespace Gitory\Gitory;

interface GitHosting
{
    /**
     * @param string $repositoriesFolderPath
     */
    public function __construct($repositoriesFolderPath);

    /**
     * Init a repository
     * @param  string $identifier repository identifier
     * @return void
     */
    public function init($identifier);
}
