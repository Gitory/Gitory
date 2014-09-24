<?php

namespace Gitory\Gitory\Managers;

interface JobManager
{

    /**
     * Push job into queue
     * @param  string $service service name
     * @param  array  $payload service payload
     */
    public function push($service, array $payload);

    /**
     * Pop job from queue
     * @return $job | false
     */
    public function pop();

    /**
     * Set job has completed
     * @param  $job
     * @return $job
     */
    public function completeJob($job);

    /**
     * Set job has failed
     * @param  $job
     * @return $job
     */
    public function failJob($job);
}
