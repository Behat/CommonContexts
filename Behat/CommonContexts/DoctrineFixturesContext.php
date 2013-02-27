<?php

namespace Behat\CommonContexts;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Behat\Behat\Context\BehatContext;

/**
 * Provides methods that can recursively load fixtures that implement DependentFixtureInterface
 */
class DoctrineFixturesContext extends BehatContext
{
    /**
     * Load a data fixture class
     *
     * @param \Doctrine\Common\DataFixtures\Loader $loader    Data fixtures loader
     * @param string                               $className Class name of fixture
     */
    public function loadFixtureClass($loader, $className)
    {
        $fixture = new $className();
        if ($loader->hasFixture($fixture)) {
            unset($fixture);

            return;
        }
        $loader->addFixture(new $className);
        if ($fixture instanceof DependentFixtureInterface) {
            foreach ($fixture->getDependencies() as $dependency) {
                $this->loadFixtureClass($loader, $dependency);
            }
        }
    }

    /**
     * Load a data fixture class
     *
     * @param \Doctrine\Common\DataFixtures\Loader $loader     Data fixtures loader
     * @param array                                $classNames Array of class names of fixtures
     */
    public function loadFixtureClasses($loader, array $classNames)
    {
        foreach ($classNames as $className) {
            $this->loadFixtureClass($loader, $className);
        }
    }
}
