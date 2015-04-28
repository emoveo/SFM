<?php
namespace SFM\IdentityMap;

use SFM\Entity;
use SFM\Transaction\TransactionEngineInterface;
use SFM\Transaction\TransactionException;

/**
 * Identity Map for already registered objects
 */
class IdentityMap implements IdentityMapInterface, TransactionEngineInterface
{
    protected $isEnabled = true;
    protected $isTransaction = false;

    /** @var IdentityMapStorageInterface  */
    protected $storage;

    /** @var IdentityMapStorageInterface */
    protected $transactionAddStorage;

    /** @var IdentityMapStorageInterface */
    protected $transactionRemoveStorage;

    /**
     * @param IdentityMapStorageInterface $storage
     * @param IdentityMapStorageInterface $storageTransactionAdd
     * @param IdentityMapStorageInterface $storageTransactionRemove
     */
    public function __construct(IdentityMapStorageInterface $storage, IdentityMapStorageInterface $storageTransactionAdd,
                                IdentityMapStorageInterface $storageTransactionRemove)
    {
        $this->storage = $storage;
        $this->transactionAddStorage = $storageTransactionAdd;
        $this->transactionRemoveStorage = $storageTransactionRemove;
    }

    /**
     * Add entity to identity map
     *
     * @param Entity $entity
     * @return IdentityMapInterface
     */
    public function addEntity(Entity $entity)
    {
        if ($this->isEnabled) {

            if ($this->isTransaction()) {
                $this->transactionAddStorage->put($entity);
                $this->transactionRemoveStorage->remove(get_class($entity), $entity->getId());
            } else {
                $this->storage->put($entity);
            }
        }

        return $this;
    }

    /**
     * Get entity from identity map
     *
     * @param string $className
     * @param int $id
     * @return null|Entity
     */
    public function getEntity($className, $id)
    {
        if (!$this->isEnabled) {
            return null;
        }

        $entity = null;
        if ($this->isTransaction()) {
            $entity = $this->transactionAddStorage->get($className, $id);
        }

        if (!$entity instanceof Entity && !$this->transactionRemoveStorage->get($className, $id)) {
            $entity = $this->storage->get($className, $id);
        }

        return $entity;
    }

    /**
     * Get multiple entities from identity map
     *
     * @param string $className
     * @param \int[] $ids
     * @return \SFM\Entity[]
     */
    public function getEntityMulti($className, $ids)
    {
        if (!$this->isEnabled) {
            return [];
        }

        $entities = [];
        if ($this->isTransaction()) {
            $entities = $this->transactionAddStorage->getM($className, $ids);
        }

        $keysFromCache = array_merge($ids);
        $keysFromCache = array_diff($keysFromCache, array_keys($this->transactionRemoveStorage->getM($className)));

        $entities = array_merge($this->storage->getM($className, $keysFromCache), $entities);

        return $entities;
    }

    /**
     * Delete entity from identity map
     *
     * @param Entity $entity
     * @return IdentityMapInterface
     */
    public function deleteEntity(Entity $entity)
    {
        if ($this->isEnabled) {

            if ($this->isTransaction()) {
                $this->transactionRemoveStorage->put($entity);
                $this->transactionAddStorage->remove(get_class($entity), $entity->getId());
            } else {
                $this->storage->remove(get_class($entity), $entity->getId());
            }
        }

        return $this;
    }

    /**
     * Enable identity map
     *
     * @return IdentityMapInterface
     */
    public function enable()
    {
        $this->isEnabled = true;

        return $this;
    }

    /**
     * Disable identity map
     *
     * @return IdentityMapInterface
     */
    public function disable()
    {
        $this->isEnabled = false;

        return $this;
    }

    /**
     * @throws \SFM\Transaction\TransactionException
     */
    public function beginTransaction()
    {
        if ($this->isTransaction) {
            throw new TransactionException('Transaction already started');
        }

        $this->transactionAddStorage->flush();
        $this->transactionRemoveStorage->flush();

        $this->isTransaction = true;
    }

    /**
     * @throws \SFM\Transaction\TransactionException
     */
    public function commitTransaction()
    {
        if (!$this->isTransaction) {
            throw new TransactionException('Transaction already stopped');
        }

        /** @var string $className */
        foreach ($this->transactionRemoveStorage->getClassNames() as $className) {
            /** @var Entity $entity */
            foreach ($this->transactionRemoveStorage->getM($className) as $entity) {
                $this->storage->remove($className, $entity->getId());
            }
        }

        /** @var string $className */
        foreach ($this->transactionAddStorage->getClassNames() as $className) {
            /** @var Entity $entity */
            foreach ($this->transactionAddStorage->getM($className) as $entity) {
                $this->storage->put($entity);
            }
        }

        $this->transactionAddStorage->flush();
        $this->transactionRemoveStorage->flush();

        $this->isTransaction = false;
    }

    /**
     * @throws \SFM\Transaction\TransactionException
     */
    public function rollbackTransaction()
    {
        if (!$this->isTransaction) {
            throw new TransactionException('Transaction already stopped');
        }

        $this->transactionAddStorage->flush();
        $this->transactionRemoveStorage->flush();

        $this->isTransaction = false;
    }

    /**
     * @return bool
     */
    public function isTransaction()
    {
        return $this->isTransaction;
    }
}