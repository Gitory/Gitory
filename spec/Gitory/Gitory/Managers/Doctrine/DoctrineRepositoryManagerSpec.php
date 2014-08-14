<?php

namespace spec\Gitory\Gitory\Managers\Doctrine;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

use Gitory\Gitory\Entities\Repository;
use Gitory\Gitory\Managers\Doctrine\DoctrineRepositoryManager;
use Gitory\Gitory\Exceptions\ExistingRepositoryIdentifierException;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\ObjectRepository;

class DoctrineRepositoryManagerSpec extends ObjectBehavior
{
    public function let(ManagerRegistry $registry, ObjectManager $om, ObjectRepository $repoManager)
    {
        $this->beConstructedWith($registry);
        $registry->getManagerForClass(DoctrineRepositoryManager::ENTITY_CLASS)->willReturn($om);
        $om->getRepository(DoctrineRepositoryManager::ENTITY_CLASS)->willReturn($repoManager);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType('Gitory\Gitory\Managers\Doctrine\DoctrineRepositoryManager');
        $this->shouldImplement('Gitory\Gitory\Managers\RepositoryManager');
    }

    public function it_find_all_repositories(ObjectRepository $repoManager)
    {
        $repositories = array(new Repository('gitory'));
        $repoManager->findAll()->willReturn($repositories);
        $this->findAll()->shouldReturn($repositories);
    }

    public function it_cannot_save_repository_with_an_existing_identifier(
        Repository $repo,
        Repository $existingRepo,
        ObjectRepository $repoManager
    ) {
        $repo->identifier()->willReturn('gallifrey');
        $repoManager->findOneBy(['identifier' => 'gallifrey'])->willReturn($existingRepo);
        $exception = new ExistingRepositoryIdentifierException('A repository with identifier gallifrey already exists.');
        $this->shouldThrow($exception)->duringSave($repo);
    }

    public function it_saves_a_new_repository(
        Repository $repo,
        ObjectRepository $repoManager,
        ObjectManager $om
    ) {
        $repo->identifier()->willReturn('rose');
        $repoManager->findOneBy(['identifier' => 'rose'])->willReturn(null);

        $om->persist($repo)->shouldBeCalled();
        $om->flush()->shouldBeCalled();
        $this->save($repo)->shouldReturn($repo);
    }
}
