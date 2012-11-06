Extra Contexts for Behat
========================

This additional contexts could be used as your feature suite's submodules,
giving you extra steps and hooks right out of the box.

How to use them
---------------

To use those contexts, you should simply instantiate them with needed arguments
(see constructor arguments) and pass them into `useContext()` function like
that:

``` php
<?php

namespace Acme\DemoBundle\Features\Context;

use Behat\Behat\Context\BehatContext;
use Behat\CommonContexts\SymfonyMailerContext;

/**
 * Feature context.
 */
class FeatureContext extends BehatContext
{
    public function __construct()
    {
        $this->useContext('symfony_extra', new SymfonyMailerContext());
    }
}

```

