<?php

use Behat\Behat\Tester\Exception\PendingException;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;

/**
 * Behat context class.
 */
class ApiContext implements SnippetAcceptingContext
{
    /**
     * Initializes context.
     *
     * Every scenario gets its own context object.
     * You can also pass arbitrary arguments to the context constructor through behat.yml.
     */
    public function __construct()
    {
    }

    /**
     * Make an API REST request
     *
     * @When I make a :method request to :url
     */
    public function iMakeARequestTo($method, $url)
    {
        throw new PendingException();
    }

    /**
     * Checks the response JSON
     * @Then the response should be
     */
    public function theResponseShouldBe(PyStringNode $response)
    {
        throw new PendingException();
    }

}
