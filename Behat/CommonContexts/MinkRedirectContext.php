<?php

namespace Behat\CommonContexts;

require_once 'PHPUnit/Autoload.php';
require_once 'PHPUnit/Framework/Assert/Functions.php';

use Behat\MinkExtension\Context\RawMinkContext;

use Behat\Mink\Exception\UnsupportedDriverActionException,
    Behat\Mink\Driver\BrowserKitDriver;

/**
 * Context class for managing redirects within an application.
 *
 * @author  Johannes M. Schmitt <schmittjoh@gmail.com>
 * @author  Marijn Huizendveld <marijn.huizendveld@gmail.com>
 */
class MinkRedirectContext extends RawMinkContext
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
        if ($this->getSession()->getDriver() instanceof BrowserKitDriver) {
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
        $headers = $this->getSession()->getResponseHeaders();

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

        if (!$driver instanceof BrowserKitDriver) {
            $message = 'This step is only supported by the browserkit drivers';

            throw new UnsupportedDriverActionException($message, $driver);
        }

        return $driver->getClient();
    }
}
