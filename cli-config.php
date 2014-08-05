<?php
use Doctrine\ORM\Tools\Console\ConsoleRunner;

// replace with file to your own project bootstrap
require_once 'vendor/autoload.php';

use Gitory\Gitory\Application;

$app = new Application([
    'debug' => true,
    'privateDirectoryPath' => __DIR__.'/../../private/'
]);

// replace with mechanism to retrieve EntityManager in your app
$entityManager = $app['orm.em'];

return ConsoleRunner::createHelperSet($entityManager);
