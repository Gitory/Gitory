<?php

namespace Gitory\Gitory\CLI;

use Symfony\Component\Console\Application as ConsoleApplication;
use Symfony\Component\Console\Input\InputOption;
use Gitory\Gitory\Application as GitoryApplication;

class Application extends ConsoleApplication
{

    public function __construct(GitoryApplication $gitoryApp)
    {
        parent::__construct('gitory', 'dev');

        $this->addCommands($gitoryApp['command.resolver']->commands());
        $this->getDefinition()->addOption(new InputOption(
            '--env',
            '-e',
            InputOption::VALUE_REQUIRED,
            'The Environment name.', $gitoryApp['env'])
        );
        $this->getDefinition()->addOption(new InputOption(
            '--no-debug',
            null,
            InputOption::VALUE_NONE,
            'Switches off debug mode.')
        );
    }
}
