<?php

require_once __DIR__.'/../vendor/autoload.php';

use Gitory\Gitory\Application;

$app = new Application('dev', true);

$app->run();
