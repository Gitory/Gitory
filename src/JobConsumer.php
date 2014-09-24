<?php

namespace Gitory\Gitory;

interface JobConsumer
{
    public function consume(array $payload);
}
