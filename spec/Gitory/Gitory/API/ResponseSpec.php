<?php

namespace spec\Gitory\Gitory\API;

use PhpSpec\ObjectBehavior;

class ResponseSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType('Gitory\Gitory\API\Response');
        $this->shouldHaveType('Symfony\Component\HttpFoundation\JsonResponse');
    }

    public function it_should_wrap_a_successful_response_data()
    {
        $data = ['doctor' => 'who'];
        $content = json_encode($data);

        $this->setData($data);
        $this->getContent()->shouldBe($content);
    }

    public function it_should_wrap_an_error_response_data()
    {
        $data = [
            'id' => 'bad-wolf',
            'message' => 'Bad Wold Corp'
        ];
        $content = json_encode([
            'error' => $data
        ]);

        $this->setStatusCode(400);
        $this->setData($data);
        $this->getContent()->shouldBe($content);
    }
}
