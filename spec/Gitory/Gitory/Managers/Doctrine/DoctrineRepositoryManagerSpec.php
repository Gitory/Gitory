<?php

namespace spec\Gitory\Gitory\Managers\Doctrine;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

use Gitory\Gitory\Entities\Repository;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ManagerRegistry;
use Gitory\Gitory\Managers\Doctrine\DoctrineRepositoryManager;
use Doctrine\Common\Persistence\ObjectRepository;

class DoctrineRepositoryManagerSpec extends ObjectBehavior
{
    public function let(ManagerRegistry $registry, ObjectManager $om, ObjectRepository $repo)
    {
        $this->beConstructedWith($registry);
        $registry->getManagerForClass(DoctrineRepositoryManager::ENTITY_CLASS)->willReturn($om);
        $om->getRepository(DoctrineRepositoryManager::ENTITY_CLASS)->willReturn($repo);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType('Gitory\Gitory\Managers\Doctrine\DoctrineRepositoryManager');
        $this->shouldImplement('Gitory\Gitory\Managers\RepositoryManager');
    }

    public function it_find_all_repositories(ObjectRepository $repo)
    {
        $repositories = array(new Repository('gitory'));
        $repo->findAll()->willReturn($repositories);
        $this->findAll()->shouldReturn($repositories);
    }
}
