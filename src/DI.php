<?php

namespace Gitory\Gitory;

use Dflydev\Provider\DoctrineOrm\DoctrineOrmServiceProvider;
use Saxulum\DoctrineOrmManagerRegistry\Doctrine\ManagerRegistry;
use Silex\Provider\DoctrineServiceProvider;
use Pimple\ServiceProviderInterface;
use Pimple\Container;
use Gitory\Gitory\Managers\Doctrine\DoctrineRepositoryManager;
use Gitory\Gitory\Managers\Doctrine\DoctrineOAuth2ServerAccessTokenManager;
use Gitory\Gitory\GitElephantGitHosting;
use Gitory\Gitory\API\CORS;
use Gitory\PimpleCli\ServiceCommandServiceProvider;
use TH\OAuth2\Pimple\OAuth2ServerProvider;
use TH\OAuth2\Storage\Memory\ClientMemoryStorage;
use Silex\Provider\MonologServiceProvider;
use Monolog\Processor\PsrLogMessageProcessor;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

trait DI
{
    /**
     * Initialize services for dependency injection
     * @param  array $config config
     */
    private function initDI($config, $interface = 'api')
    {
        $this->register(new DoctrineServiceProvider, [
            "db.options" => $config['db']
        ]);

        $this->register(new DoctrineOrmServiceProvider, [
            "orm.proxies_dir" => $config['private_path'].'doctrine/proxies/',
            "orm.em.options" => [
                "mappings" => [
                    [
                        "type" => "annotation",
                        "namespace" => "Gitory\Gitory\Entities",
                        "path" => __DIR__.'/Entities',
                    ],
                ],
            ],
        ]);

        $this->register(new ServiceCommandServiceProvider());

        $this->register(new MonologServiceProvider(), [
            'monolog.logfile' => $config['log'][$interface],
        ]);

        $this->extend('monolog', function (Logger $monolog) use ($interface) {
            $monolog->pushProcessor(new PsrLogMessageProcessor());

            if($interface === 'cli') {
                $monolog->pushHandler(new StreamHandler(STDOUT, $this['monolog.level']));
            }

            return $monolog;
        });

        $this['doctrine'] = function (Container $container) {
            return new ManagerRegistry($container);
        };

        $this['repository.manager'] = function (Container $container) {
            return new DoctrineRepositoryManager($container['doctrine'], $container['logger']);
        };

        $this['oauth2_server.access_token.manager'] = function (Container $container) {
            return new DoctrineOAuth2ServerAccessTokenManager($container['doctrine']);
        };

        $this['repository.hosting'] = function () use ($config) {
            return new GitElephantGitHosting($config['repositories_path']);
        };

        $this['api.CORS'] = function () {
            $methods = ['GET', 'DELETE', 'POST', 'PUT'];
            $headers = ['Authorization', 'Content-Type'];
            return [new CORS('*', $methods, $headers), 'setCORSheaders'];
        };

        $this['oauth2.server_provider'] = function () {
            return new OAuth2ServerProvider;
        };

        $this->register($this['oauth2.server_provider'], [
            'oauth2_server.storage.client' => function () use ($config) {
                return new ClientMemoryStorage($config['api']['clients']);
            },
            'oauth2_server.storage.access_token' => function (Container $container) {
                return $container['oauth2_server.access_token.manager'];
            },
            'oauth2_server.authorize_renderer.view' => __DIR__.'/../views/authorize.php',
        ]);

        $this['users.provider'] = [
            'admin' => [
                'ROLE_ADMIN',
                '5FZ2Z8QIkA7UTZ4BYkoC+GsReLf569mSKDsfods6LYQ8t+a8EW9oaircfMpmaLbPBh4FOBiiFyLfuZmTSUwzZg=='
            ],
        ];
    }

    abstract public function register(ServiceProviderInterface $provider, array $values = []);

    /**
     * @param string $id
     * @param \Closure $callback
     */
    abstract public function extend($id, $callback);
}
