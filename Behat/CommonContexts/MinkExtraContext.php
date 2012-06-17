<?php

namespace Behat\CommonContexts;

use Behat\Behat\Context\BehatContext,
    Behat\Behat\Context\Step;

/**
 * Provides some more steps/method for web application testing
 *
 * Please be aware that some drivers (such as Sahi) don't support
 * http status code retrieval
 *
 * @author Geoffrey Bachelet <geoffrey.bachelet@gmail.com>
 */
class MinkExtraContext extends BehatContext
{
    /**
     * Checks that a page exists
     *
     * Provides an implementation-free but still reliable step to check a non-404 page.
     *
     * @Then /^page "(?P<page>[^"]+)" should exist$/
     */
    public function pageShouldExist($page)
    {
        return array(
            new Step\When('I go to "'.$page.'"'),
            new Step\Then('the response status code should not be 404'),
        );
    }

    /**
     * Checks that a page does not exist
     *
     * Provides an implementation-free but still reliable step to check a 404 page.
     *
     * @Then /^page "(?P<page>[^"]+)" should not exist$/
     */
    public function pageShouldNotExist($page)
    {
        return array(
            new Step\When('I go to "'.$page.'"'),
            new Step\Then('the response status code should be 404'),
        );
    }

    /**
     * Checks that you're not allowed to follow a link
     *
     * Provides an implementation-free but still reliable step to check a 403 page.
     *
     * @Then /^I should not be allowed to go to "(?P<page>[^"]+)"$/
     */
    public function iShouldNotBeAllowedToGoTo($page)
    {
        return array(
            new Step\When('I go to "'.$page.'"'),
            new Step\Then('the response status code should be 403'),
        );
    }

    /**
     * Checks that you're allowed to follow a link
     *
     * Provides an implementation-free but still reliable step to check a 200 page.
     *
     * @Then /^I should be allowed to go to "(?P<page>[^"]+)"$/
     */
    public function iShouldBeAllowedToGoTo($page)
    {
        return array(
            new Step\When('I go to "'.$page.'"'),
            new Step\Then('the response status code should be 200'),
        );
    }
}
