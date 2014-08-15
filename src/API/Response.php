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
        if ($this->isSuccessful()) {
            $data = [
                'meta' => ['status' => 'success'],
                'response' => $data,
            ];
        } else {
            $data = [
                'meta' => [
                    'status' => 'failure',
                    'error' => $data,
                ],
                'response' => []
            ];
        }

        parent::setData($data);
    }
}
