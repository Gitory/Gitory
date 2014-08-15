<?php

namespace Gitory\Gitory;

use Dflydev\Silex\Provider\DoctrineOrm\DoctrineOrmServiceProvider;
use Saxulum\DoctrineOrmManagerRegistry\Doctrine\ManagerRegistry;
use Silex\Provider\DoctrineServiceProvider;
use Silex\ServiceProviderInterface;
use Gitory\Gitory\Managers\Doctrine\DoctrineRepositoryManager;

trait DI
{
    /**
     * Initialize services for dependency injection
     * @param  array $values config
     */
    private function initDI($values)
    {
        $this->register(new DoctrineServiceProvider, array(
            "db.options" => array(
                "driver" => "pdo_sqlite",
                "path" => $values['privateDirectoryPath'].'gitory.db',
            ),
        ));

        $this->register(new DoctrineOrmServiceProvider, array(
            "orm.proxies_dir" => $values['privateDirectoryPath'].'/doctrine/proxies/',
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

        $this['debug'] = $values['debug'];

        $this['doctrine'] = function ($container) {
            return new ManagerRegistry($container);
        };

        $this['repository.manager'] = function ($c) {
            return new DoctrineRepositoryManager($c['doctrine']);
        };
    }

    abstract public function register(ServiceProviderInterface $provider, array $values = []);
}
