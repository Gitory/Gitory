<?php

namespace Gitory\Gitory\Entities\OAuth2Server;

use DateTime;

/** @Entity */
class AccessToken
{
    /**
     * @Id @Column
     */
    private $oauthToken;

    /**
     * @Column(type="datetime")
     */
    private $expires;

    /**
     * @Column
     */
    private $clientId;

    /**
     * @Column
     */
    private $userId;

    /**
     * @Column(type="array")
     */
    private $scopes;

    public function __construct($oauthToken, $clientId, $userId, DateTime $expires, Array $scopes)
    {
        $this->oauthToken = $oauthToken;
        $this->clientId = $clientId;
        $this->userId = $userId;
        $this->expires = $expires;
        $this->scopes = $scopes;
    }

    public function expires()
    {
        return $this->expires;
    }

    public function clientId()
    {
        return $this->clientId;
    }

    public function userId()
    {
        return $this->userId;
    }

    public function scopes()
    {
        return $this->scopes;
    }
}
