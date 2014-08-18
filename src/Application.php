<?php

namespace Gitory\Gitory;

use Silex\Application as SilexApplication;

class Application extends SilexApplication
{
    use DI, Controllers, Routes;

    public function __construct(array $values = array())
    {
        parent::__construct($values);

        $this->initDI($values);

        $this->initControllers();

        $this->initRoutes();
    }
}
