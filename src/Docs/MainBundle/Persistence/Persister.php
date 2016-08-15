<?php
namespace Docs\MainBundle\Persistence;

use Doctrine\ORM\EntityManager;
use Docs\CommonBundle\Doctrine\EntityInterface;

/**
 * Persist data using the entity manager
 * @author h.botev
 *
 */
class Persister implements PersisterInterface
{
    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * (non-PHPdoc)
     * @see \Docs\MainBundle\Persistence\PersisterInterface::beginTransaction()
     */
    public function beginTransaction()
    {
        $this->entityManager->beginTransaction();
    }

    /**
     * (non-PHPdoc)
     * @see \Docs\MainBundle\Persistence\PersisterInterface::persist()
     */
    public function persist(EntityInterface $entity)
    {
        $this->entityManager->persist($entity);
    }

    /**
     * (non-PHPdoc)
     * @see \Docs\MainBundle\Persistence\PersisterInterface::remove()
     */
    public function remove(EntityInterface $entity)
    {
        $this->entityManager->remove($entity);
    }

    /**
     * (non-PHPdoc)
     * @see \Docs\MainBundle\Persistence\PersisterInterface::finishTransaction()
     */
    public function finishTransaction()
    {
        $this->entityManager->flush();
        $this->entityManager->getConnection()->commit();
        $this->clear();
    }

    /**
     * (non-PHPdoc)
     * @see \Docs\MainBundle\Persistence\PersisterInterface::rollBack()
     */
    public function rollBack()
    {
        if ($this->entityManager->getConnection()->isTransactionActive()) {
            $this->entityManager->getConnection()->rollBack();
            $this->clear();
        }
    }

    /**
     * (non-PHPdoc)
     * @see \Docs\MainBundle\Persistence\PersisterInterface::isConnectionOpen()
     */
    public function isConnectionOpen()
    {
        return $this->entityManager->isOpen();
    }

    /**
     * Clear the entity manager
     * if some entites must not be cleared
     * (due to multiple use in transaction)
     * exclude them here
     */
    private function clear()
    {
        foreach ($this->entityManager->getUnitOfWork()->getIdentityMap() as $class => $objects) {
            $classParts = explode("\\", $class);
            $this->entityManager->clear($class);
        }
    }
}
