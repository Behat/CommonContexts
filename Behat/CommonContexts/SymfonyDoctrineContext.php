<?php

namespace Behat\CommonContexts;

use Behat\Behat\Context\BehatContext;
use Behat\Behat\Event\ScenarioEvent;
use Behat\Symfony2Extension\Context\KernelAwareInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\ORM\EntityManager;
use Doctrine\DBAL\Connection;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Provides hooks for building and cleaning up a database schema with Doctrine.
 *
 * While building the schema it takes all the entity metadata known to Doctrine.
 *
 * @author Jakub Zalas <jakub@zalas.pl>
 */
class SymfonyDoctrineContext extends BehatContext implements KernelAwareInterface
{
    /**
     * @var \Symfony\Component\HttpKernel\KernelInterface $kernel
     */
    private $kernel = null;

    /**
     * @param \Behat\Behat\Event\ScenarioEvent|\Behat\Behat\Event\OutlineExampleEvent $event
     *
     * @BeforeScenario
     *
     * @return null
     */
    public function buildSchema($event)
    {
        foreach ($this->getEntityManagers() as $entityManager) {
            $metadata = $this->getMetadata($entityManager);

            if (!empty($metadata)) {
                $tool = new SchemaTool($entityManager);
                $tool->dropSchema($metadata);
                $tool->createSchema($metadata);
            }
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
        /** @var EntityManager $entityManager */
        foreach ($this->getEntityManagers() as $entityManager) {
            $entityManager->clear();
        }

        /** @var Connection $connection */
        foreach ($this->getConnections() as $connection) {
            $connection->close();
        }
    }

    /**
     * @param \Symfony\Component\HttpKernel\KernelInterface $kernel
     *
     * @return null
     */
    public function setKernel(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * @param EntityManager $entityManager
     *
     * @return array
     */
    protected function getMetadata(EntityManager $entityManager)
    {
        return $entityManager->getMetadataFactory()->getAllMetadata();
    }

    /**
     * @return array
     */
    protected function getEntityManagers()
    {
        return $this->kernel->getContainer()->get('doctrine')->getManagers();
    }

    /**
     * @return array
     */
    protected function getConnections()
    {
        return $this->kernel->getContainer()->get('doctrine')->getConnections();
    }
}
