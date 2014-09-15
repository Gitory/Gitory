<?php

namespace Gitory\Gitory;

use Gitory\Gitory\Commands\Repository\RepositoryCreateCommand;

trait Commands
{
    public function initCommands()
    {
        $this['repository.create.command'] = function () {
            return new RepositoryCreateCommand($this['repository.manager'], $this['repository.hosting'], $this['monolog']);
        };
    }
}
