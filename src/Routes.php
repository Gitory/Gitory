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

        $this->put('/repositories/{identifier}', 'repository.controller:createAction');

        $this->match('{apiEndpoints}', 'api.controller:optionsAction')
            ->method('OPTIONS')
            ->assert('apiEndpoints', '.+');

        $this->after($this['api.CORS']);
    }

    /**
     * Maps a pattern to a callable.
     *
     * You can optionally specify HTTP methods that should be matched.
     *
     * @param string $pattern Matched route pattern
     * @param string $to      Callback that returns the response when matched
     *
     * @return Controller
     */
    abstract public function get($pattern, $to = null);

    /**
     * Maps a PUT request to a callable.
     *
     * @param string $pattern Matched route pattern
     * @param string $to      Callback that returns the response when matched
     *
     * @return Controller
     */
    abstract public function put($pattern, $to = null);

    /**
     * Maps a OPTION request to a callable.
     *
     * @param string $pattern Matched route pattern
     * @param string $to      Callback that returns the response when matched
     *
     * @return Controller
     */
    abstract public function match($pattern, $to = null);

    /**
     * Registers an after filter.
     *
     * After filters are run after the controller has been executed.
     *
     * @param mixed $callback After filter callback
     * @param int   $priority The higher this value, the earlier an event
     *                        listener will be triggered in the chain (defaults to 0)
     */
    abstract public function after($callback, $priority = 0);

    abstract public function register(ServiceProviderInterface $provider, array $values = []);
}
