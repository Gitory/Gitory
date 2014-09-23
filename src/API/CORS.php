<?php

namespace Gitory\Gitory\API;

use Symfony\Component\HttpFoundation\Response as BaseResponse;
use Symfony\Component\HttpFoundation\Request;

class CORS
{
    private $origin;
    private $methods;
    private $headers;

    /**
     * @param string   $origin  orgin domain
     * @param string[] $methods methods
     * @param string[] $headers headers
     */
    public function __construct($origin, Array $methods, Array $headers)
    {
        $this->origin  = $origin;
        $this->methods = implode(', ', $methods);
        $this->headers = implode(', ', $headers);
    }

    public function setCORSheaders(Request $request, BaseResponse $response)
    {
        $response->headers->set('Access-Control-Allow-Origin', $this->origin);
        $response->headers->set('Access-Control-Allow-Methods', $this->methods);
        $response->headers->set('Access-Control-Allow-Headers', $this->headers);
    }
}
