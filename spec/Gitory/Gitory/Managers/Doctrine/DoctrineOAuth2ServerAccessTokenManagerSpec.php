<?php

namespace spec\Gitory\Gitory\Managers\Doctrine;

use Gitory\Gitory\Managers\Doctrine\DoctrineOAuth2ServerAccessTokenManager;
use Gitory\Gitory\Entities\OAuth2Server\AccessToken;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\ObjectRepository;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use DateTime;

class DoctrineOAuth2ServerAccessTokenManagerSpec extends ObjectBehavior
{
    public function let(
        ManagerRegistry $registry,
        ObjectManager $om,
        ObjectRepository $accessTokenRepository
    )
    {
        $this->beConstructedWith($registry);
        $registry->getManagerForClass(DoctrineOAuth2ServerAccessTokenManager::ENTITY_CLASS)->willReturn($om);
        $om->getRepository(DoctrineOAuth2ServerAccessTokenManager::ENTITY_CLASS)->willReturn($accessTokenRepository);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType('Gitory\Gitory\Managers\Doctrine\DoctrineOAuth2ServerAccessTokenManager');
        $this->shouldImplement('OAuth2\Storage\AccessTokenInterface');
    }

    public function it_gets_null_for_missong_token(ObjectRepository $accessTokenRepository)
    {
        $token = 'a token';
        $accessTokenRepository->find($token)->willReturn(null);
        $this->getAccessToken($token)->shouldReturn(null);;
    }

    public function it_gets_access_token(
        ObjectRepository $accessTokenRepository,
        AccessToken $accessToken,
        DateTime $expires
    ) {
        $token = 'a token';
        $accessTokenArray = [
            'expires' => 42,
            'client_id' => 43,
            'user_id' => 44,
            'scope' => 'some scopes',
        ];
        $accessToken->clientId()->willReturn($accessTokenArray['client_id']);
        $accessToken->userId()->willReturn($accessTokenArray['user_id']);
        $accessToken->scopes()->willReturn(explode(' ', $accessTokenArray['scope']));
        $expires->getTimestamp()->willReturn($accessTokenArray['expires']);
        $accessToken->expires()->willReturn($expires);
        $accessTokenRepository->find($token)->willReturn($accessToken);
        $this->getAccessToken($token)->shouldReturn($accessTokenArray);
    }

    public function it_sets_access_token_without_scopes(
        ObjectManager $om
    ) {
        $accessTokenArray = [
            'expires' => 42,
            'client_id' => 43,
            'user_id' => 44,
        ];
        $om->flush()->shouldBeCalled();
        $om->persist(Argument::that(function (AccessToken $accessToken) use ($accessTokenArray) {
            if ($accessToken->clientId() !== $accessTokenArray['client_id']) {
                return false;
            }
            if ($accessToken->userId() !== $accessTokenArray['user_id']) {
                return false;
            }
            if ($accessToken->expires()->getTimestamp() !== $accessTokenArray['expires']) {
                return false;
            }
            $scopes = $accessToken->scopes();
            if (!is_array($scopes) || !empty($scopes)) {
                return false;
            }
            return true;
        }))->shouldBeCalled();
        $this->setAccessToken(
            'a token',
            $accessTokenArray['client_id'],
            $accessTokenArray['user_id'],
            $accessTokenArray['expires']
        );
    }

    public function it_sets_access_token_with_scopes(
        ObjectManager $om
    ) {
        $accessTokenArray = [
            'expires' => 42,
            'client_id' => 43,
            'user_id' => 44,
            'scope' => 'some scopes',
        ];
        $om->flush()->shouldBeCalled();
        $om->persist(Argument::that(function (AccessToken $accessToken) use ($accessTokenArray) {
            $scopes = $accessToken->scopes();
            if (!is_array($scopes)) {
                return false;
            }
            if (count($scopes) !== 2) {
                return false;
            }
            return implode(' ', $scopes) === $accessTokenArray['scope'];
        }))->shouldBeCalled();
        $this->setAccessToken(
            'a token',
            $accessTokenArray['client_id'],
            $accessTokenArray['user_id'],
            $accessTokenArray['expires'],
            $accessTokenArray['scope']
        );
    }
}
