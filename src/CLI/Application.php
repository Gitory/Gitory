<?php

namespace Gitory\Gitory\CLI;

use Cilex\Application as CilexApplication;

class Application extends CilexApplication
{
    use DI, Commands;

    public function __construct(array $values = array())
    {
        parent::__construct('gitory', 'dev');

        $this['repositories.directory-path'] = 'private/test/repositories/';

        $this->initDI($values);
        $this->initCommands();
    }
}
