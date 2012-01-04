<?php

namespace Behat\CommonContexts;

require_once 'PHPUnit/Autoload.php';
require_once 'PHPUnit/Framework/Assert/Functions.php';

use Behat\Behat\Context\BehatContext;

use Behat\Mink\Exception\UnsupportedDriverActionException,
    Behat\Mink\Driver\GoutteDriver;

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
     * @param \Behat\Behat\Event\ScenarioEvent|\Behat\Behat\Event\OutlineExampleEvent $event
     *
     * @return void
     *
     * @AfterScenario
     */
    public function afterScenario($event)
    {
        if ($this->getSession()->getDriver() instanceof GoutteDriver) {
            $this->getClient()->followRedirects(true);
        }
    }

    /**
     * Follow redirect instructions.
     *
     * @param   string  $location
     *
     * @return  void
     *
     * @Then /^I (?:am|should be) redirected(?: to "([^"]*)")?$/
     */
    public function iAmRedirected($location = null)
    {
        $session = $this->getSession();
        $headers = $session->getResponseHeaders();

        assertArrayHasKey('Location', $headers, 'The response contains a "Location" header');

        if (null !== $location) {
            // TODO: Change from path based comparison to URI based comparison
            $redirectComponents = parse_url($headers['Location']);

            assertEquals($redirectComponents['path'], $location, 'The "Location" header points to the correct URI');
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

        if (!$driver instanceof GoutteDriver) {
            $message = "This step is only supported by the @mink:symfony and @mink:goutte drivers";

            throw new UnsupportedDriverActionException($message, $driver);
        }

        return $driver->getClient();
    }

    /**
     * Returns current active mink session.
     *
     * If you are using MinkContext as a subcontext instead of using it as
      * the main one, overwrite this method
     *
     * @return  Behat\Mink\Session
     */
    protected function getSession()
    {
        return $this->getMainContext()->getSession();
    }
}
