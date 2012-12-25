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

use Behat\Behat\Context\BehatContext;
use Behat\CommonContexts\SymfonyMailerContext;

/**
 * Feature context.
 */
class FeatureContext extends BehatContext
{
    public function __construct()
    {
        // To use SymfonyMailerContext in your steps
        $this->useContext('symfony_extra', new SymfonyMailerContext());

        // To use DoctrineFixturesContext in your steps
        $this->useContext('doctrine_fixtures_context', new DoctrineFixturesContext());
    }

    /**
     * Example of using DoctrineFixturesContext in BeforeScenario hook
     *
     * @BeforeScenario
     */
    public function beforeScen()
    {
        $loader = new Loader();

        $this->getMainContext()
            ->getSubcontext('doctrine_fixtures_context')
            ->loadFixtureClasses($loader, array(
                'Acme\Bundle\DefaultBundle\DataFixtures\ORM\LoadNewsData',
                'Acme\Bundle\DefaultBundle\DataFixtures\ORM\LoadPagesData',
                'Acme\Bundle\DefaultBundle\DataFixtures\ORM\LoadReviewData',
                'Acme\Bundle\DefaultBundle\DataFixtures\ORM\LoadTicketData',
            ));

        /** @var $em \Doctrine\ORM\EntityManager */
        $em = $this->kernel->getContainer()->get('doctrine.orm.entity_manager');

        $purger = new ORMPurger();
        $executor = new ORMExecutor($em, $purger);
        $executor->purge();
        $executor->execute($loader->getFixtures(), true);
    }
}

```

