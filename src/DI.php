<?php

namespace Gitory\Gitory;

use Dflydev\Provider\DoctrineOrm\DoctrineOrmServiceProvider;
use Saxulum\DoctrineOrmManagerRegistry\Doctrine\ManagerRegistry;
use Silex\Provider\DoctrineServiceProvider;
use Pimple\ServiceProviderInterface;
use Gitory\Gitory\Managers\Doctrine\DoctrineRepositoryManager;
use Gitory\Gitory\GitElephantGitHosting;
use Gitory\PimpleCli\ServiceCommandServiceProvider;

trait DI
{
    /**
     * Initialize services for dependency injection
     * @param  array $config config
     */
    private function initDI($config)
    {
        $this->register(new DoctrineServiceProvider, array(
            "db.options" => array(
                "driver" => "pdo_sqlite",
                "path" => $config['private_path'].'gitory.db',
            ),
        ));

        $this->register(new DoctrineOrmServiceProvider, array(
            "orm.proxies_dir" => $config['private_path'].'doctrine/proxies/',
            "orm.em.options" => array(
                "mappings" => array(
                    array(
                        "type" => "annotation",
                        "namespace" => "Gitory\Gitory\Entities",
                        "path" => __DIR__.'/Entities',
                    ),
                ),
            ),
        ));

        $this->register(new ServiceCommandServiceProvider());

        $this['doctrine'] = function ($container) {
            return new ManagerRegistry($container);
        };

        $this['repository.manager'] = function ($c) {
            return new DoctrineRepositoryManager($c['doctrine']);
        };

        $this['repository.hosting'] = function () use ($config) {
            return new GitElephantGitHosting($config['repositories_path']);
        };
    }

    abstract public function register(ServiceProviderInterface $provider, array $values = []);
}
