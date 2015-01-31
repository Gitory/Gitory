<?php

use Gitory\Gitory\Application;
use Behat\Behat\Tester\Exception\PendingException;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use GuzzleHttp\Client;
use GuzzleHttp\Post\PostBody;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessBuilder;
use Symfony\Component\Process\PhpExecutableFinder;

/**
 * Behat context class.
 */
class ApiContext implements SnippetAcceptingContext
{
    /**
     * Gitory application
     * @var Gitory\Gitory\Application
     */
    private $app;

    /**
     * API base url
     * @var string
     */
    private $baseUrl;

    /**
     * API Server address
     * @var string
     */
    const ADDRESS = 'localhost:2025';

    /**
     * Last response
     * @var GuzzleHttp\Message\Response
     */
    private $response;

    /**
     * OAuth2 Access Token
     */
    private static $OAuth2AccessToken;

    /**
     * Initializes context.
     *
     * Every scenario gets its own context object.
     * You can also pass arbitrary arguments to the context constructor through behat.yml.
     */
    public function __construct()
    {
        $this->app = new Application('test');

        $this->baseUrl = 'http://'.self::ADDRESS;
    }

    /**
     * @BeforeScenario
     */
    public static function resetOAuth2AccessToken()
    {
        self::$OAuth2AccessToken = null;
    }

    /**
     * @When :user make a :method request to :url
     */
    public function makeARequestTo($user, $method, $url, PyStringNode $body = null)
    {
        $clientConfig = [
            'base_url' => $this->baseUrl,
            'defaults' => ['allow_redirects' => false],
        ];

        if (self::$OAuth2AccessToken) {
            $clientConfig['defaults']['headers'] = ['Authorization' => 'Bearer '.self::$OAuth2AccessToken];
        }

        $client = new Client($clientConfig);
        $client->setDefaultOption('exceptions', false);

        $body = (string) $body;
        if (strtolower($method) === 'form') {
            $method = 'post';
            $clientConfig['defaults']['headers']['Content-Type'] = 'application/x-www-form-urlencoded';
            $postBody = new PostBody;
            preg_match_all('/^([^=]+)=(.*)$/m', $body, $matches, PREG_SET_ORDER);
            $body = [];
            foreach ($matches as $match) {
                list($_, $key, $value) = $match;
                $body[$key] = $value;
            }
        }

        $this->response = $client->$method($url, [
            'auth' => $user === 'I' ? null : explode(':', $user, 2),
            'body' => $body
        ]);
    }


    /**
     * Check the response JSON
     * @Then the response should be
     */
    public function theResponseShouldBe(PyStringNode $response)
    {
        var_dump((string) $this->response->getBody());
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
            $responseCode = $this->response->getStatusCode();
            $responseReason = $this->response->getReasonPhrase();
            throw new Exception('Response code : '.$responseCode.' "'.$responseReason.'" does not match '.$code);
        }
    }

    /**
     * @Given :clientId is a valid OAuth2 client id
     */
    public function isAValidOauthClientId($clientId)
    {
        if ($this->app['oauth2_server.storage.client']->getClientDetails($clientId) === null) {
            throw new Exception("$clientId is not a valid OAuth2 client id (check your config)");
        }
    }

    /**
     * @Then the response header :name should be :value
     */
    public function theResponseHeaderShouldBe($name, $value)
    {
        if($this->response->getHeader($name) !== $value) {
            throw new Exception(
                'Response `'.$name.'` header : '.$this->response->getHeader($name).' does not match '.$value
            );
        }
    }

    /**
     * @Then the response header :name should match :pattern
     */
    public function theResponseHeaderShouldMatch($name, $pattern)
    {

        if(preg_match("/$pattern/", $this->response->getHeader($name)) !== 1) {
            throw new Exception(
                'Response `'.$name.'` header : '.$this->response->getHeader($name).' does not match '.$pattern
            );
        }
    }

    /**
     * @Given :user got an OAuth2 Access Token: :token
     */
    public function gotAnOauthAccessToken($user, $token)
    {
        $accessTokenManager = $this->app['oauth2_server.access_token.manager'];
        $accessTokenManager->setAccessToken($token, 'unknown', $user, strtotime('+1 hour'));
        static::$OAuth2AccessToken = $token;
    }

    /**
     * @Given requests are made on behalf of :user
     */
    public function requestsAreMadeOnBehalfOf($user)
    {
        return $this->gotAnOauthAccessToken($user, 'YOUR_TOKEN');
    }

    /**
     * @Then the response should not contain :text
     */
    public function theResponseShouldNotContain($text)
    {
        $responseBody = (string)$this->response->getBody();

        if (strpos($responseBody, $text) !== false) {
            throw new Exception(
                'Text `'.$text.'` should not have been found in response'
            );
        }
    }

    /**
     * @Then the response should contain :text
     */
    public function theResponseShouldContain($text)
    {
        $responseBody = (string)$this->response->getBody();

        if (strpos($responseBody, $text) === false) {
            throw new Exception(
                'Text `'.$text.'` was not found in response'
            );
        }
    }
}
