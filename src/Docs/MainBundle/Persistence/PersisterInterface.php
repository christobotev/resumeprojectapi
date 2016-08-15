<?php
namespace Docs\MainBundle\Persistence;

use Docs\CommonBundle\Doctrine\EntityInterface;

/**
 * An interface that must be implemented by the data persisters
 * @author h.botev
 *
 */
interface PersisterInterface
{
    /**
     * Start a transaction
     */
    public function beginTransaction();

    /**
     * Persist the entity
     * @param EntityInterface $entity
     */
    public function persist(EntityInterface $entity);

    /**
     * Delete the entity
     * @param EntityInterface $entity
     */
    public function remove(EntityInterface $entity);

    /**
     * Commit the transaction
     */
    public function finishTransaction();

    /**
     * Rollback the transaction
     */
    public function rollBack();

    /**
     * Check if there is still an open connection
     * @return bool
     */
    public function isConnectionOpen();
}
