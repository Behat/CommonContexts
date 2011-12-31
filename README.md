Extra Contexts for Behat
========================

This additional contexts could be used as your feature suite's submobules,
giving you extra steps and hooks right out of the box.

How to Use Them
---------------

To use those contexts, you should simply instantiate them with needed arguments
(see constructor arguments) and pass them into `useContext()` function like
that:

``` php
<?php

namespace Acme\DemoBundle\Features\Context;

use Behat\BehatBundle\Context\BehatContext,
    Behat\BehatBundle\Context\MinkContext;
use Behat\Behat\Context\ClosuredContextInterface,
    Behat\Behat\Context\TranslatedContextInterface,
    Behat\Behat\Exception\PendingException;
use Behat\Gherkin\Node\PyStringNode,
    Behat\Gherkin\Node\TableNode;

/**
 * Feature context.
 */
class FeatureContext extends MinkContext
{
    public function __construct($kernel)
    {
        $this->useContext('symfony_extra',
            new \Behat\CommonContexts\SymfonyExtraContext($kernel)
        );

        parent::__construct($kernel);
    }
}

```

