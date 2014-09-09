<?php

namespace spec\Gitory\Gitory\Controllers;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\HttpFoundation\Response;

class CORSOptionsControllerSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType('Gitory\Gitory\Controllers\CORSOptionsController');
    }

    public function it_answers_200_to_any_OPTIONS_request()
    {
        $response = $this->optionsAction();

        $response->shouldHaveType('Symfony\Component\HttpFoundation\Response');
        $response->getStatusCode()->shouldBe(Response::HTTP_OK);
        $response->getContent()->shouldBe('');
    }
}
