<?php

use Behat\Behat\Tester\Exception\PendingException;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;

use GuzzleHttp\Client;
use Symfony\Component\Process\Process;

use Symfony\Component\Process\ProcessBuilder;
use Symfony\Component\Process\PhpExecutableFinder;

/**
 * Behat context class.
 */
class ApiContext implements SnippetAcceptingContext
{
    /**
     * API base url
     * @var string
     */
    private $baseUrl;

    /**
     * API Server port
     * @var integer
     */
    private $port;

    /**
     * API Server address
     * @var string
     */
    private $address;

    /**
     * PHP Server process
     * @var Symfony\Component\Process\Process
     */
    private $phpProcess;

    /**
     * Last response
     * @var GuzzleHttp\Message\Response
     */
    private $response;

    private function startServer()
    {
        $finder = new PhpExecutableFinder;
        $php = $finder->find();

        $builder = new ProcessBuilder(['exec', $php, '-S', $this->address.':'.$this->port, __DIR__.'/../../web/api/index-test.php']);
        $builder->inheritEnvironmentVariables(true);

        $this->phpProcess = $builder->getProcess();
        $this->phpProcess->setTty(true);
        $this->phpProcess->start();

        sleep(2);
    }

    private function stopServer()
    {
        $this->phpProcess->stop();
    }

    /**
     * Initializes context.
     *
     * Every scenario gets its own context object.
     * You can also pass arbitrary arguments to the context constructor through behat.yml.
     */
    public function __construct($address, $port)
    {
        $this->address = $address;
        $this->port = $port;
        $this->baseUrl = 'http://'.$address.':'.$port;

        $this->startServer();
    }

    /**
     * Make an API REST request
     *
     * @When I make a :method request to :url
     */
    public function iMakeARequestTo($method, $url, PyStringNode $body = null)
    {
        $client = new Client();
        $client->setDefaultOption('exceptions', false);
        $this->response = $client->$method($this->baseUrl.$url, ['body' => (string)$body]);
    }

    /**
     * Check the response JSON
     * @Then the response should be
     */
    public function theResponseShouldBe(PyStringNode $response)
    {
        $dbResponse = $this->response->json();
        $behatResponseTemplate = json_decode((string)$response, true);

        if($behatResponseTemplate === null) {
            throw new Exception("Cannot decode behat template response : ".(string)$response);
        }

        $diff = new \Diff(
            explode(PHP_EOL, print_r($dbResponse, true)),
            explode(PHP_EOL, print_r($behatResponseTemplate, true))
        );

        $renderer = new \Diff_Renderer_Text_Unified;
        $stringDiff = $diff->render($renderer);
        if($stringDiff !== "") {
            throw new Exception('Response does not match: '. PHP_EOL . $stringDiff);
        }
    }

    /**
     * Check the response status code
     * @Then the response status code should be :code
     */
    public function theResponseStatusCodeShouldBe($code)
    {
        if($this->response->getStatusCode() !== $code) {
            throw new Exception('Response code : '.$this->response->getStatusCode().' does not match '.$code);
        }
    }

    public function __destruct()
    {
        $this->stopServer();
    }
}
