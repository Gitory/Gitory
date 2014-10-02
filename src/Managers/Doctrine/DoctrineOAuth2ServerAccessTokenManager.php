<?php

namespace Gitory\Gitory\Managers\Doctrine;

use OAuth2\Storage\AccessTokenInterface;
use Gitory\Gitory\Entities\OAuth2Server\AccessToken;
use DateTime;

class DoctrineOAuth2ServerAccessTokenManager implements AccessTokenInterface
{
    use DoctrineRepository;

    const ENTITY_CLASS = 'Gitory\Gitory\Entities\OAuth2Server\AccessToken';

    /**
     * @inherit
     */
    public function getAccessToken($oauth_token)
    {
        $accessToken = $this->getRepository()->find($oauth_token);
        if ($accessToken === null) {
            return null;
        }
        return [
            'expires' => $accessToken->expires()->getTimestamp(),
            'client_id' => $accessToken->clientId(),
            'user_id' => $accessToken->userId(),
            'scope' => implode(' ', $accessToken->scopes()),
        ];
    }

    /**
     * Store the supplied access token values to storage.
     *
     * We need to store access token data as we create and verify tokens.
     *
     * @param $oauth_token    oauth_token to be stored.
     * @param $client_id      client identifier to be stored.
     * @param $user_id        user identifier to be stored.
     * @param int    $expires expiration to be stored as a Unix timestamp.
     * @param string $scope   OPTIONAL Scopes to be stored in space-separated string.
     *
     * @ingroup oauth2_section_4
     */
    public function setAccessToken($oauth_token, $client_id, $user_id, $expires, $scope = null)
    {
        $datetime = new DateTime;
        $datetime->setTimestamp($expires);
        $accessToken = new AccessToken(
            $oauth_token,
            $client_id,
            $user_id,
            $datetime,
            empty($scope) ? [] : explode(' ', $scope)
        );
        $manager = $this->getManager();
        $manager->persist($accessToken);
        $manager->flush();
    }
}
