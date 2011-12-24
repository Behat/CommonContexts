<?php

namespace Behat\CommonContext;

use Behat\BehatBundle\Context\BehatContext;
use Behat\Behat\Event\ScenarioEvent;
use Doctrine\ORM\Tools\SchemaTool;

/**
 * Provides hooks for building and cleaning up a database schema with Doctrine.
 *
 * While building the schema it takes all the entity metadata known to Doctrine.
 *
 * @author Jakub Zalas <jakub@zalas.pl>
 */
class SymfonyDoctrineContext extends BehatContext
{
    /**
     * @param \Behat\Behat\Event\ScenarioEvent|\Behat\Behat\Event\OutlineExampleEvent $event
     *
     * @BeforeScenario
     *
     * @return null
     */
    public function beforeScenario($event)
    {
        $this->buildSchema();
    }

    /**
     * @param \Behat\Behat\Event\ScenarioEvent|\Behat\Behat\Event\OutlineExampleEvent $event
     *
     * @AfterScenario
     *
     * @return null
     */
    public function afterScenario($event)
    {
        $this->getEntityManager()->clear();

        foreach ($this->getClientConnections() as $connection) {
            $connection->close();
        }
    }

    /**
     * @return null
     */
    protected function buildSchema()
    {
        $metadata = $this->getMetadata();

        if (!empty($metadata)) {
            $tool = new SchemaTool($this->getEntityManager());
            $tool->dropSchema($metadata);
            $tool->createSchema($metadata);
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
        return $this->getContainer()->get('doctrine.orm.entity_manager');
    }

    /**
     * @return array
     */
    protected function getClientConnections()
    {
        $driver = $this->getMainContext()->getSession()->getDriver();

        if ($driver instanceof \Behat\MinkBundle\Driver\SymfonyDriver) {
            return $driver->getClient()->getContainer()->get('doctrine')->getConnections();
        }

        return array();
    }
}
