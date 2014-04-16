<?php

namespace Behat\CommonContexts;

require_once 'PHPUnit/Autoload.php';
require_once 'PHPUnit/Framework/Assert/Functions.php';

use Behat\MinkExtension\Context\RawMinkContext;
use Behat\Mink\Exception\UnsupportedDriverActionException;
use Behat\Mink\Driver\BrowserKitDriver;

/**
 * Context class for managing redirects within an application.
 *
 * @author  Johannes M. Schmitt <schmittjoh@gmail.com>
 * @author  Marijn Huizendveld <marijn.huizendveld@gmail.com>
 * @author  Saša Stamenković <umpirsky@gmail.com>
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
     * Following redirects.
     *
     * @When /^I follow redirects$/
     */
    public function iFollowRedirects()
    {
        $this->getClient()->followRedirects(true);
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
            $this->iFollowRedirects();
        }
    }

    /**
     * Follow redirect instructions.
     *
     * @param   string  $page
     *
     * @return  void
     *
     * @Then /^I (?:am|should be) redirected(?: to "([^"]*)")?$/
     */
    public function iAmRedirected($page = null, $follow = true)
    {
        $headers = $this->getSession()->getResponseHeaders();

        if (empty($headers['Location']) && empty($headers['location'])) {
            throw new \RuntimeException('The response should contain a "Location" header');
        }

        if (null !== $page) {
            $header = empty($headers['Location']) ? $headers['location'] : $headers['Location'];
            if (is_array($header)) {
                $header = current($header);
            }

            assertEquals($header, $this->locatePath($page), 'The "Location" header points to the correct URI');
        }

        $client = $this->getClient();

        if ($follow) {
            $this->iFollowRedirects();
        }

        $client->followRedirect();
    }

    /**
     * Follow redirect instructions once.
     *
     * @param   string  $page
     *
     * @return  void
     *
     * @Then /^I (?:am|should be) redirected once(?: to "([^"]*)")?$/
     */
    public function iAmRedirectedOnce($page = null)
    {
        $this->iAmRedirected($page, false);
    }

    /**
     * Returns current active mink session.
     *
     * @return  \Symfony\Component\BrowserKit\Client
     *
     * @throws  \Behat\Mink\Exception\UnsupportedDriverActionException
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
