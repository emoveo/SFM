<?php
namespace SFM\IdentityMap;

use SFM\Transaction\TransactionEngine;
use SFM\Entity;

/**
 * Identity Map for already registered objects
 */
class IdentityMap implements IdentityMapInterface, TransactionEngine
{
    protected $isEnabled     = true;
    protected $isTransaction = false;
    
    protected $identityMap = array();
    protected $transactionIdentityIds = array();

    /**
     * Get entity identity storage key
     * @param Entity $entity
     * @return string
     */
    protected function getIdentityKey(Entity $entity)
    {
        $identityKey = get_class($entity);

        return $identityKey;
    }

    /**
     * @return bool
     */
    public function isTransaction()
    {
        return $this->isTransaction;
    }

    /**
     * @param Entity $entity
     * @return IdentityMap
     */
    public function addEntity(Entity $entity)
    {
        if ($this->isEnabled) {

            $className = $this->getIdentityKey($entity);

            if ($this->isTransaction) {
                $this->logTransaction($entity);
            }

            if (false === isset($this->identityMap[$className])) {
                $this->identityMap[$className] = array();
            }

            if (false === is_null($entity->id)) {
                $this->identityMap[$className][$entity->id] = $entity;
            }
        }

        return $this;
    }

    /**
     * Log transaction
     * @param Entity $entity
     */
    protected function logTransaction(Entity $entity)
    {
        $className = $this->getIdentityKey($entity);

        if (false === isset($this->transactionIdentityIds[$className])) {
            $this->transactionIdentityIds[$className] = array();
        }

        $this->transactionIdentityIds[$className][] = $entity->getId();
    }
    
    /**
     * Return Entity from map
     *
     * @param string $className
     * @param int $id
     * @return Entity|null
     */
    public function getEntity($className, $id)
    {
        $entity = isset($this->identityMap[$className][$id]) ? $this->identityMap[$className][$id] : null;

        return $entity;
    }
    
    /**
     * 
     * @param string $className
     * @param array of integer $ids
     * @return array of Entity
     */
    public function getEntityMulti($className, $ids)
    {
        $returnEntities = array();
        if(!isset($this->identityMap[$className])){
            return $returnEntities;
        }
        $objectsByIds = array_flip($ids);
        $returnEntities = array_intersect_key($this->identityMap[$className],$objectsByIds);
        return $returnEntities;
    }

    /**
     * @param Entity $entity
     * @return IdentityMap
     */
    public function deleteEntity(Entity $entity)
    {
        $className = $this->getIdentityKey($entity);
        $this->identityMap[$className][$entity->id] = null;

        return $this;
    }

    /**
     * @return IdentityMap
     */
    public function enable()
    {
        $this->isEnabled = true;

        return $this;
    }

    /**
     * @return IdentityMap
     */
    public function disable()
    {
        $this->isEnabled = false;

        return $this;
    }

    /**
     * @return IdentityMap
     */
    public function beginTransaction()
    {
        $this->isTransaction = true;

        return $this;
    }

    /**
     * @return IdentityMap
     */
    public function commitTransaction()
    {
        $this->isTransaction = false;

        return $this;
    }

    /**
     * @return IdentityMap
     */
    public function rollbackTransaction()
    {
        $this->isTransaction = false;
        foreach ($this->transactionIdentityIds as $className => $ids) {
            foreach ($ids as $id) {
                $this->identityMap[$className][$id] = null;
            }
        }

        $this->transactionIdentityIds = array();

        return $this;
    }
}