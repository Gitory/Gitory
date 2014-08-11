<?php

use Behat\Behat\Tester\Exception\PendingException;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;

use Doctrine\ORM\Tools\SchemaTool;

use Gitory\Gitory\Application;
use Gitory\Gitory\Entities\Repository;

/**
 * Behat context class.
 */
class DomainContext implements SnippetAcceptingContext
{
    /**
     * Gitory application
     * @var Gitory\Gitory\Application
     */
    private $app;

    /**
     * Entity Manager
     * @var Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * Initializes context.
     *
     * Every scenario gets its own context object.
     * You can also pass arbitrary arguments to the context constructor through behat.yml.
     */
    public function __construct()
    {
        $this->app = new Application([
            'debug' => true,
            'privateDirectoryPath' => __DIR__.'/../../private/test/'
        ]);

        $this->em = $this->app['orm.em'];
    }

    /**
     * @BeforeSuite
     */
    public static function createDB()
    {
        $dir = __DIR__.'/../../private/test/';
        if(is_dir($dir)) {
            $it = new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS);
            $files = new RecursiveIteratorIterator($it, RecursiveIteratorIterator::CHILD_FIRST);

            foreach($files as $file) {
                if ($file->getFilename() === '.' || $file->getFilename() === '..') {
                    continue;
                }
                if ($file->isDir()) {
                    rmdir($file->getRealPath());
                } else {
                    unlink($file->getRealPath());
                }
            }
            rmdir($dir);
        }

        mkdir($dir, 0777, true);
    }

    /**
    * @BeforeScenario
    */
    public function cleanDB()
    {
        // Drop old datas & create news
        $schemaTools = new SchemaTool($this->em);
        $classes = $this->em->getMetadataFactory()->getAllMetadata();
        $schemaTools->dropSchema($classes);
        $schemaTools->createSchema($classes);
    }

    /**
     * Add a repository
     *
     * @Given there is a repository named :repositoryIdentifier
     */
    public function thereIsARepositoryNamed($repositoryIdentifier)
    {
        $repository = new Repository($repositoryIdentifier);
        $this->em->persist($repository);
        $this->em->flush();
    }
}
