<?php

namespace spec\Gitory\Gitory\API;

use PhpSpec\ObjectBehavior;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class CORSSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedWith('http://some.domain.tld', ['GET', 'POST'], ['content-type']);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType('Gitory\Gitory\API\CORS');
    }

    public function it_set_CORS_headers_to_responses(
        Request $request,
        Response $response,
        ResponseHeaderBag $headers
    ) {
        $headers->set('Access-Control-Allow-Origin', 'http://some.domain.tld')->shouldBeCalled();
        $headers->set('Access-Control-Allow-Methods', 'GET, POST')->shouldBeCalled();
        $headers->set('Access-Control-Allow-Headers', 'content-type')->shouldBeCalled();

        $response->headers = $headers;

        $this->setCORSheaders($request, $response);
    }
}
