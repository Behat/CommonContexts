<?php

namespace Behat\CommonContext;

require_once 'PHPUnit/Autoload.php';
require_once 'PHPUnit/Framework/Assert/Functions.php';

use Behat\Behat\Context\BehatContext;

use Behat\Mink\Exception\ExpectationException,
    Behat\Mink\Exception\UnsupportedDriverActionException;

use Behat\Mink\Driver\GoutteDriver;

/**
 * Context class for managing redirects within an application.
 *
 * @author  Johannes M. Schmitt <schmittjoh@gmail.com>
 * @author  Marijn Huizendveld <marijn.huizendveld@gmail.com>
 */
class RedirectContext extends BehatContext
{
    /**
     * Prevent following redirects.
     *
     * @return  void
     *
     * @When /^I do not follow redirects$/
     */
    public function iDoNotFollowRedirects()
    {
        $this->getClient()->followRedirects(false);
    }

    /**
     * Follow redirect instructions.
     *
     * @param   string  $arg_actualPath
     *
     * @return  void

     * @throws  Behat\Mink\Exception\ExpectationException When the "Location" header is missing.
     * @throws  Behat\Mink\Exception\ExpectationException When the value for the "Location" is different from the value passed.
     *
     * @Then /^I (?:am|should be) redirected to "([^"]*)"$/
     *
     * @todo    Change from path based comparison to URI based comparison.
     */
    public function iAmRedirectedTo($arg_actualPath)
    {
        $session = $this->getSession();
        $headers = $session->getResponseHeaders();

        try {
            assertArrayHasKey('Location', $headers, 'The response contains a "Location" header');
        } catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $message = 'No "Location" header was found';

            throw new ExpectationException($message, $session, $e);
        }

        $redirectComponents = parse_url($headers['Location']);

        try {
            assertEquals($redirectComponents['path'], $arg_actualPath, 'The "Location" header points to the correct URI');
        } catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $message = sprintf('The "Location" header points to "%s"', $redirectComponents['path']);

            throw new ExpectationException($message, $session, $e);
        }

        $client = $this->getClient();

        $client->followRedirects(true);
        $client->followRedirect();
    }

    /**
     * Returns current active mink session.
     *
     * @return  Symfony\Component\BrowserKit\Client
     *
     * @throws  Behat\Mink\Exception\UnsupportedDriverActionException
     */
    protected function getClient()
    {
        $driver = $this->getSession()->getDriver();

        if ( ! $driver instanceof GoutteDriver) {
            $message = "This step is only supported by the @mink:symfony and @mink:goutte drivers";

            throw new UnsupportedDriverActionException($message, $driver);
        }

        return $driver->getClient();
    }

    /**
     * Returns current active mink session.
     *
     * @return  Behat\Mink\Session
     */
    protected function getSession()
    {
        return $this->getMainContext()->getSession();
    }
}
