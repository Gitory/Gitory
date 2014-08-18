<?php

namespace Gitory\Gitory;

use Gitory\Gitory\Controllers\RepositoryController;
use Pimple as Container;

trait Controllers
{
    public function initControllers()
    {
        $this['repository.controller'] = function (Container $c) {
            return new RepositoryController($c['repository.manager']);
        };
    }
}
