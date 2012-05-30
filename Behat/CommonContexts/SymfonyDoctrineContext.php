<?php

namespace Behat\CommonContexts;

use Symfony\Component\HttpKernel\KernelInterface;
use Behat\Symfony2Extension\Context\KernelAwareInterface;

use Behat\Behat\Context\BehatContext;
use Behat\Behat\Event\ScenarioEvent;
use Doctrine\ORM\Tools\SchemaTool;

/**
 * Provides hooks for building and cleaning up a database schema with Doctrine.
 *
 * While building the schema it takes all the entity metadata known to Doctrine.
 *
 * @author Jakub Zalas <jakub@zalas.pl>
 */
class SymfonyDoctrineContext extends BehatContext implements KernelAwareInterface
{
    private $kernel;

    /**
     * Sets HttpKernel instance.
     * This method will be automatically called by Symfony2Extension ContextInitializer.
     *
     * @param KernelInterface $kernel
     */
    public function setKernel(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * @param \Behat\Behat\Event\ScenarioEvent|\Behat\Behat\Event\OutlineExampleEvent $event
     *
     * @BeforeScenario
     *
     * @return null
     */
    public function buildSchema($event)
    {
        $metadata = $this->getMetadata();

        if (!empty($metadata)) {
            $tool = new SchemaTool($this->getEntityManager());
            $tool->dropSchema($metadata);
            $tool->createSchema($metadata);
        }
    }

    /**
     * @param \Behat\Behat\Event\ScenarioEvent|\Behat\Behat\Event\OutlineExampleEvent $event
     *
     * @AfterScenario
     *
     * @return null
     */
    public function closeDBALConnections($event)
    {
        $this->getEntityManager()->clear();

        foreach ($this->getConnections() as $connection) {
            $connection->close();
        }
    }

    /**
     * @return array
     */
    protected function getMetadata()
    {
        return $this->getEntityManager()->getMetadataFactory()->getAllMetadata();
    }

    /**
     * @return \Doctrine\ORM\EntityManager
     */
    protected function getEntityManager()
    {
        return $this->kernel->getContainer()->get('doctrine.orm.entity_manager');
    }

    /**
     * @return array
     */
    protected function getConnections()
    {
        return $this->kernel->getContainer()->get('doctrine')->getConnections();
    }

    /**
     * Gets the Mink context.
     *
     * If you are using MinkContext as a subcontext instead of using it as
     * the main one, overwrite this method
     *
     * @return \Behat\Mink\Behat\Context\BaseMinkContext
     */
    protected function getMinkContext()
    {
        return $this->getMainContext();
    }
}
