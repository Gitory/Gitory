<?php

namespace Gitory\Gitory\CLI;

use Symfony\Component\Console\Application as ConsoleApplication;
use Gitory\Gitory\Application as GitoryApplication;

class Application extends ConsoleApplication
{

    public function __construct(GitoryApplication $gitoryApp)
    {
        parent::__construct('gitory', 'dev');

        $this->addCommands($gitoryApp['command.resolver']->commands());
    }
}
