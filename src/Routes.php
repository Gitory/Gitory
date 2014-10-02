<?php

namespace Gitory\Gitory;

use Pimple\ServiceProviderInterface;
use Silex\Provider\ServiceControllerServiceProvider;
use Silex\Provider\SecurityServiceProvider;
use Symfony\Component\HttpFoundation\RequestMatcher;

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

        $this->register(new SecurityServiceProvider, [
            'security.firewalls' => [
                'oauth.token' => [
                    'pattern' => '^/auth/token',
                    'security' => false,
                ],
                'oauth.authorize' => [
                    'pattern' => '^/auth/authorize',
                    'http' => true,
                    'users' => $this['users.provider'],
                ],
                'api' => [
                    'pattern' => new RequestMatcher(null, null, ['DELETE', 'GET', 'HEAD', 'POST', 'PUT']),
                    'stateless' => true,
                    'oauth2' => true,
                    'security' => true,
                    'users' => $this['users.provider'],
                ],
            ],
        ]);

        $this['security.entry_point.api.oauth2.realm'] = 'Gitory';

        $this->mount('/auth/', $this['oauth2.server_provider']);
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

    /**
     * Registers a service provider.
     *
     * @param ServiceProviderInterface $provider A ServiceProviderInterface instance
     * @param array                    $values   An array of values that customizes the provider
     *
     * @return Application
     */
    abstract public function register(ServiceProviderInterface $provider, array $values = []);

    /**
     * Mounts controllers under the given route prefix.
     *
     * @param string                                           $prefix      The route prefix
     * @param ControllerCollection|ControllerProviderInterface $controllers A ControllerCollection or a ControllerProviderInterface instance
     *
     * @return Application
     *
     * @throws \LogicException
     */
    abstract public function mount($prefix, $controllers);
}
