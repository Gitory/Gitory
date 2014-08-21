<?php

require_once __DIR__.'/../../vendor/autoload.php';

use Gitory\Gitory\API\Application;

$app = new Application([
    'debug' => true,
    'privateDirectoryPath' => __DIR__.'/../../private/test/'
]);

$app->run();
