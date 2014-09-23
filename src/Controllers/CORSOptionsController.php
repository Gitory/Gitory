<?php

namespace Gitory\Gitory\Controllers;

use Symfony\Component\HttpFoundation\Response;

class CORSOptionsController
{
    public function optionsAction()
    {
        return new Response('', Response::HTTP_OK);
    }
}
