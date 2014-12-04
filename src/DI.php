<?php

namespace Gitory\Gitory;

use Dflydev\Provider\DoctrineOrm\DoctrineOrmServiceProvider;
use Saxulum\DoctrineOrmManagerRegistry\Doctrine\ManagerRegistry;
use Silex\Provider\DoctrineServiceProvider;
use Pimple\ServiceProviderInterface;
use Gitory\Gitory\Managers\Doctrine\DoctrineRepositoryManager;
use Gitory\Gitory\Managers\Doctrine\DoctrineJobManager;
use Gitory\Gitory\GitElephantGitHosting;
use Gitory\Gitory\API\CORS;
use Gitory\PimpleCli\ServiceCommandServiceProvider;
use Gitory\Gitory\UseCases\RepositoryCreation;
use Gitory\Gitory\UseCases\JobConsummation;
use Gitory\Gitory\Entities\Job\JobStatusType;
use Silex\Provider\MonologServiceProvider;
use Monolog\Processor\PsrLogMessageProcessor;
use Monolog\Handler\StreamHandler;
use Gedmo\Timestampable\TimestampableListener;
use TH\Lock\FileFactory;

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

        $this->extend('monolog', function ($monolog) use ($interface) {
            $monolog->pushProcessor(new PsrLogMessageProcessor());

            if($interface === 'cli') {
                $monolog->pushHandler(new StreamHandler(STDOUT, $this['monolog.level']));
            }

            return $monolog;
        });

        $this['doctrine'] = function ($container) {
            $managerRegistry = new ManagerRegistry($container);
            $drivers = $managerRegistry->getManager()->getConfiguration()->getMetadataDriverImpl()->getDrivers();
            $driver = reset($drivers);
            $eventManager = $managerRegistry->getManager()->getEventManager();
            $timestampableListener = new TimestampableListener;
            $timestampableListener->setAnnotationReader($driver->getReader());
            $eventManager->addEventSubscriber($timestampableListener);


            return $managerRegistry;
        };

        $this['job.initialStatusType'] = function($c) {
            $em = $this['doctrine']->getManagerForClass('Gitory\Gitory\Entities\Job\JobStatusType');
            $repo = $em->getRepository('Gitory\Gitory\Entities\Job\JobStatusType');
            $statusType = $repo->findOneByIdentifier('pending');

            if ($statusType !== null) {
                return $statusType;
            }

            $statusType = new JobStatusType('pending', '');
            $em->persist($statusType);
            $em->flush();

            return $statusType;
        };

        $this['job.inProgressStatusType'] = function($c) {
            $em = $this['doctrine']->getManagerForClass('Gitory\Gitory\Entities\Job\JobStatusType');
            $repo = $em->getRepository('Gitory\Gitory\Entities\Job\JobStatusType');
            $statusType = $repo->findOneByIdentifier('in-progress');

            if ($statusType !== null) {
                return $statusType;
            }

            $statusType = new JobStatusType('in-progress', '');
            $em->persist($statusType);
            $em->flush();

            return $statusType;
        };

        $this['job.completedStatusType'] = function($c) {
            $em = $this['doctrine']->getManagerForClass('Gitory\Gitory\Entities\Job\JobStatusType');
            $repo = $em->getRepository('Gitory\Gitory\Entities\Job\JobStatusType');
            $statusType = $repo->findOneByIdentifier('completed');

            if ($statusType !== null) {
                return $statusType;
            }

            $statusType = new JobStatusType('completed', '');
            $em->persist($statusType);
            $em->flush();

            return $statusType;
        };

        $this['job.failedStatusType'] = function($c) {
            $em = $this['doctrine']->getManagerForClass('Gitory\Gitory\Entities\Job\JobStatusType');
            $repo = $em->getRepository('Gitory\Gitory\Entities\Job\JobStatusType');
            $statusType = $repo->findOneByIdentifier('failed');

            if ($statusType !== null) {
                return $statusType;
            }

            $statusType = new JobStatusType('failed', '');
            $em->persist($statusType);
            $em->flush();

            return $statusType;
        };

        $this['repository.manager'] = function ($c) {
            return new DoctrineRepositoryManager($c['doctrine'], $c['logger']);
        };

        $this['job.manager'] = function ($c) {
            return new DoctrineJobManager(
                $c['job.initialStatusType'],
                $c['job.inProgressStatusType'],
                $c['job.completedStatusType'],
                $c['job.failedStatusType'],
                $c['doctrine'],
                $c['lock.factory'],
                $c['monolog']
            );
        };

        $this['repository.hosting'] = function () use ($config) {
            return new GitElephantGitHosting($config['repositories_path']);
        };

        $this['repository.creation.usecase'] = function ($c) {
            return new RepositoryCreation($c['repository.manager'], $c['job.manager']);
        };

        $this['job.consummation.usecase'] = function ($c) {
            return new JobConsummation($c, $c['job.manager'], $c['logger']);
        };

        $this['api.CORS'] = function () {
            return [new CORS('*', ['GET', 'DELETE', 'POST', 'PUT'], ['Content-Type']), 'setCORSheaders'];
        };

        $this['lock.factory'] = function($c) use ($config) {
            $factory = new FileFactory($config['lock']['path']);
            $factory->setLogger($c['logger']);

            return $factory;
        };
    }

    abstract public function register(ServiceProviderInterface $provider, array $values = []);

    /**
     * @param string $id
     * @param \Closure $callback
     */
    abstract public function extend($id, $callback);
}
