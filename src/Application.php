<?php

namespace Gitory\Gitory;

class Application extends \Silex\Application
{
    use Bootstrap;

    public function __construct(array $values = array())
    {
        parent::__construct($values);

        $this->initDI($values);

        $this->get('/repositories', function () {
            $repositories = $this['RepositoryManager']->findAll();

            return json_encode(['response' => ['repositories' => array_map(function ($repository) {
                return $repository->identifier();
            }, $repositories)]]);
        });
    }
}
