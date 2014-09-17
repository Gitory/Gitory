<?php

namespace Gitory\Gitory;

use Dflydev\Provider\DoctrineOrm\DoctrineOrmServiceProvider;
use Saxulum\DoctrineOrmManagerRegistry\Doctrine\ManagerRegistry;
use Silex\Provider\DoctrineServiceProvider;
use Pimple\ServiceProviderInterface;
use Gitory\Gitory\Managers\Doctrine\DoctrineRepositoryManager;
use Gitory\Gitory\GitElephantGitHosting;
use Gitory\PimpleCli\ServiceCommandServiceProvider;
use Silex\Provider\MonologServiceProvider;
use Monolog\Processor\PsrLogMessageProcessor;
use Monolog\Handler\StreamHandler;

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

        $this['monolog'] = $this->extend('monolog', function ($monolog) use ($interface) {
            $monolog->pushProcessor(new PsrLogMessageProcessor());

            if($interface === 'cli') {
                $monolog->pushHandler(new StreamHandler(STDOUT, $this['monolog.level']));
            }

            return $monolog;
        });

        $this['doctrine'] = function ($container) {
            return new ManagerRegistry($container);
        };

        $this['repository.manager'] = function ($c) {
            return new DoctrineRepositoryManager($c['doctrine'], $c['monolog']);
        };

        $this['repository.hosting'] = function () use ($config) {
            return new GitElephantGitHosting($config['repositories_path']);
        };
    }

    abstract public function register(ServiceProviderInterface $provider, array $values = []);

    /**
     * @param string $id
     * @param \Closure $callback
     */
    abstract public function extend($id, $callback);
}
