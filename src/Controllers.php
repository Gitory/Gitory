<?php

namespace Gitory\Gitory;

use Gitory\Gitory\Controllers\CORSOptionsController;
use Gitory\Gitory\Controllers\RepositoryController;
use Pimple\Container;

trait Controllers
{
    public function initControllers()
    {
        $this['repository.controller'] = function (Container $c) {
            return new RepositoryController($c['repository.manager']);
        };

        $this['api.controller'] = function () {
            return new CORSOptionsController;
        };
    }
}
