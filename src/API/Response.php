<?php

namespace Gitory\Gitory\API;

use Symfony\Component\HttpFoundation\JsonResponse;

class Response extends JsonResponse
{
    /**
     * {@inheritdoc}
     */
    public function setData($data = [])
    {
        if (!$this->isSuccessful()) {
            $data = ['error' => $data];
        }

        parent::setData($data);
    }
}
