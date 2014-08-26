<?php

namespace Gitory\Gitory\CLI;

use Symfony\Component\Console\Command\Command;
use Gitory\Gitory\Commands\Repository\RepositoryCreateCommand;

trait Commands
{
    public function initCommands()
    {
        $this->command(new RepositoryCreateCommand($this['repository.manager'], $this['repository.hosting']));
    }

    abstract public function command(Command $command);
}
