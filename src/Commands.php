<?php

namespace Gitory\Gitory;

use Gitory\Gitory\Commands\Repository\RepositoryCreateCommand;
use Gitory\Gitory\Commands\Job\JobConsumeCommand;

trait Commands
{
    public function initCommands()
    {
        $this['repository.create.command'] = function () {
            return new RepositoryCreateCommand($this['repository.manager'], $this['repository.hosting'], $this['monolog']);
        };

        $this['job.consume.command'] = function () {
            return new JobConsumeCommand($this['job.consummation.usecase'], $this['monolog']);
        };
    }
}
