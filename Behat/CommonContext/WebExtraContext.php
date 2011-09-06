<?php

namespace Behat\CommonContexts;

use Behat\Behat\Context\BehatContext,
    Behat\Behat\Context\Step;

use Behat\Mink\Exception\UnsupportedDriverActionException,
    Behat\Mink\Driver\SahiDriver;

/**
 * Provides some more steps/method for web application testing
 *
 * @author Geoffrey Bachelet <geoffrey.bachelet@gmail.com>
 */
class WebExtraContext extends BehatContext
{
    /**
     * Checks that you're not allowed to follow a link
     *
     * Provides an implementation-free but still reliable step to check a 403 page.
     * Unfortunately, SahiDriver does not allow HTTP status code retrieval.
     *
     * @Then /^I should not be allowed to go to "(?P<page>[^"]+)"$/
     */
    public function iShouldNotBeAllowedToGoTo($page)
    {
        $driver = $this->getDriver()->getSession();

        if ($driver instanceof SahiDriver) {
            throw new UnsupportedDriverActionException('You need to use a driver that allows http status code retrieval, which sahi doesn\'t');
        }

        return array(
            new Step\When('I go to "'.$page.'"'),
            new Step\Then('The response status code should be 403'),
        );
    }
}