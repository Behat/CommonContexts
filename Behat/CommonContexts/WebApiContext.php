<?php

namespace Behat\CommonContexts;

use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Behat\Behat\Context\BehatContext;
use Buzz\Message\Request;
use Buzz\Browser;

require_once 'PHPUnit/Autoload.php';
require_once 'PHPUnit/Framework/Assert/Functions.php';

/**
 * Provides web API description definitions.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class WebApiContext extends BehatContext
{
    private $browser;
    private $baseUrl;
    private $authorization;
    private $placeHolders = array();

    /**
     * Initializes context.
     *
     * @param string  $baseUrl base API url
     * @param Browser $browser browser instance (optional)
     */
    public function __construct($baseUrl, Browser $browser = null)
    {
        $this->baseUrl = rtrim($baseUrl, '/');

        if (null === $browser) {
            $this->browser = new Browser();
        } else {
            $this->browser = $browser;
        }
    }

    /**
     * Adds Basic Authentication header to next request.
     *
     * @param string $username
     * @param string $password
     *
     * @Given /^I am authenticating as "([^"]*)" with "([^"]*)" password$/
     */
    public function iAmAuthenticatingAs($username, $password)
    {
        $this->authorization = base64_encode($username.':'.$password);
    }

    /**
     * Sends HTTP request to specific relative URL.
     *
     * @param string $method request method
     * @param string $url    relative url
     *
     * @When /^(?:I )?send a (HEAD|GET|POST|PUT|DELETE) request to "([^"]+)"$/
     */
    public function iSendARequest($method, $url)
    {
        $url = $this->baseUrl.'/'.ltrim($this->replacePlaceHolder($url), '/');

        switch ($method) {
            case 'HEAD':
                $this->browser->head($url, $this->getHeaders());
                break;
            case 'GET':
                $this->browser->get($url, $this->getHeaders());
                break;
            case 'POST':
                $this->browser->post($url, $this->getHeaders());
                break;
            case 'PUT':
                $this->browser->put($url, $this->getHeaders());
                break;
            case 'DELETE':
                $this->browser->delete($url, $this->getHeaders());
                break;
        }
    }

    /**
     * Sends HTTP request to specific URL with field values from Table.
     *
     * @param string    $method request method
     * @param string    $url    relative url
     * @param TableNode $post   table of post values
     *
     * @When /^(?:I )?send a (POST|PUT|DELETE) request to "([^"]+)" with values:$/
     */
    public function iSendARequestWithValues($method, $url, TableNode $post)
    {
        $url    = $this->baseUrl.'/'.ltrim($this->replacePlaceHolder($url), '/');
        $fields = array();

        foreach ($post->getRowsHash() as $key => $val) {
            $fields[$key] = $this->replacePlaceHolder($val);
        }

        switch ($method) {
            case 'POST':
                $this->browser->submit($url, $fields, Request::METHOD_POST, $this->getHeaders());
                break;
            case 'PUT':
                $this->browser->submit($url, $fields, Request::METHOD_PUT, $this->getHeaders());
                break;
            case 'DELETE':
                $this->browser->submit($url, $fields, Request::METHOD_DELETE, $this->getHeaders());
                break;
        }
    }

    /**
     * Sends HTTP request to specific URL with raw body from PyString.
     *
     * @param string       $method request method
     * @param string       $url    relative url
     * @param PyStringNode $string request body
     *
     * @When /^(?:I )?send a (POST|PUT|DELETE) request to "([^"]+)" with body:$/
     */
    public function iSendARequestWithBody($method, $url, PyStringNode $string)
    {
        $url    = $this->baseUrl.'/'.ltrim($this->replacePlaceHolder($url), '/');
        $string = $this->replacePlaceHolder(trim($string));

        switch ($method) {
            case 'POST':
                $this->browser->call($url, Request::METHOD_POST, $this->getHeaders(), $string);
                break;
            case 'PUT':
                $this->browser->call($url, Request::METHOD_PUT, $this->getHeaders(), $string);
                break;
            case 'DELETE':
                $this->browser->call($url, Request::METHOD_DELETE, $this->getHeaders(), $string);
                break;
        }
    }

    /**
     * Sends HTTP request to specific URL with form data from PyString.
     *
     * @param string       $method request method
     * @param string       $url    relative url
     * @param PyStringNode $string request body
     *
     * @When /^(?:I )?send a (POST|PUT|DELETE) request to "([^"]+)" with form data:$/
     */
    public function iSendARequestWithFormData($method, $url, PyStringNode $string)
    {
        $url    = $this->baseUrl.'/'.ltrim($this->replacePlaceHolder($url), '/');
        $string = $this->replacePlaceHolder(trim($string));

        parse_str(implode('&', explode("\n", $string)), $fields);

        switch ($method) {
            case 'POST':
                $this->browser->submit($url, $fields, Request::METHOD_POST, $this->getHeaders());
                break;
            case 'PUT':
                $this->browser->submit($url, $fields, Request::METHOD_PUT, $this->getHeaders());
                break;
            case 'DELETE':
                $this->browser->submit($url, $fields, Request::METHOD_DELETE, $this->getHeaders());
                break;
        }
    }

    /**
     * Checks that response has specific status code.
     *
     * @param string $code status code
     *
     * @Then /^(?:the )?response code should be (\d+)$/
     */
    public function theResponseCodeShouldBe($code)
    {
        assertSame(intval($code), $this->browser->getLastResponse()->getStatusCode());
    }

    /**
     * Checks that response body contains specific text.
     *
     * @param string $text
     *
     * @Then /^(?:the )?response should contain "([^"]*)"$/
     */
    public function theResponseShouldContain($text)
    {
        assertRegExp('/'.preg_quote($text).'/', $this->browser->getLastResponse()->getContent());
    }

    /**
     * Checks that response body doesn't contains specific text.
     *
     * @param string $text
     *
     * @Then /^(?:the )?response should not contain "([^"]*)"$/
     */
    public function theResponseShouldNotContain($text)
    {
        assertNotRegExp('/'.preg_quote($text).'/', $this->browser->getLastResponse()->getContent());
    }

    /**
     * Checks that response body contains JSON from PyString.
     *
     * @param PyStringNode $jsonString
     *
     * @Then /^(?:the )?response should contain json:$/
     */
    public function theResponseShouldContainJson(PyStringNode $jsonString)
    {
        $etalon = json_decode($this->replacePlaceHolder($jsonString->getRaw()), true);
        $actual = json_decode($this->browser->getLastResponse()->getContent(), true);

        if (null === $etalon) {
            throw new \RuntimeException(
                "Can not convert etalon to json:\n".$this->replacePlaceHolder($jsonString->getRaw())
            );
        }

        assertCount(count($etalon), $actual);
        foreach ($actual as $needle) {
            assertContains($needle, $etalon);
        }
    }

    /**
     * Prints last response body.
     *
     * @Then print response
     */
    public function printResponse()
    {
        $request  = $this->browser->getLastRequest();
        $response = $this->browser->getLastResponse();

        $this->printDebug(sprintf("%s %s => %d:\n%s",
            $request->getMethod(),
            $request->getUrl(),
            $response->getStatusCode(),
            $response->getContent()
        ));
    }

    /**
     * Returns browser instance.
     *
     * @return Browser
     */
    public function getBrowser()
    {
        return $this->browser;
    }

    /**
     * Sets place holder for replacement.
     *
     * you can specify placeholders, which will
     * be replaced in URL, request or response body.
     *
     * @param string $key   token name
     * @param string $value replace value
     */
    public function setPlaceHolder($key, $value)
    {
        $this->placeHolders[$key] = $value;
    }

    /**
     * Replaces placeholders in provided text.
     *
     * @param string $string
     *
     * @return string
     */
    public function replacePlaceHolder($string)
    {
        foreach ($this->placeHolders as $key => $val) {
            $string = str_replace($key, $val, $string);
        }

        return $string;
    }

    /**
     * Returns headers, that will be used to send requests.
     *
     * @return array
     */
    protected function getHeaders()
    {
        $headers = array();

        if (null !== $this->authorization) {
            $headers[] = 'Authorization: Basic '.$this->authorization;
        }

        return $headers;
    }
}
