<?php

namespace Gitory\Gitory;

use Dflydev\Silex\Provider\DoctrineOrm\DoctrineOrmServiceProvider;
use Silex\Provider\DoctrineServiceProvider;

class Application extends \Silex\Application {

    public function __construct(array $values = array()) {
        parent::__construct($values);

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
                        "type" => "yml",
                        "namespace" => "Gitory\Gitory\Entities",
                        "resources_namespace" => "Gitory\Gitory\Resources\mappings",
                    ),
                ),
            ),
        ));

        $this['debug'] = $values['debug'];
    }
}
