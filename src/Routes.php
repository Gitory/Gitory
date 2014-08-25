<?php

namespace Gitory\Gitory;

use Pimple\ServiceProviderInterface;
use Silex\Provider\ServiceControllerServiceProvider;

trait Routes
{
    public function initRoutes()
    {
        $this->register(new ServiceControllerServiceProvider);

        $this->get('/repositories', 'repository.controller:listAction');

        $this->post('/repository', 'repository.controller:createAction');
    }

    /**
     * Maps a GET request to a callable.
     *
     * @param string $pattern Matched route pattern
     * @param string  $to     Callback that returns the response when matched
     *
     * @return Controller
     */
    abstract public function get($pattern, $to = null);

    /**
     * Maps a POST request to a callable.
     *
     * @param string $pattern Matched route pattern
     * @param string  $to     Callback that returns the response when matched
     *
     * @return Controller
     */
    abstract public function post($pattern, $to = null);

    abstract public function register(ServiceProviderInterface $provider, array $values = []);
}
