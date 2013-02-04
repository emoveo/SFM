<?php
/**
 * Identity Map for already registered objects
 */
class SFM_IdentityMap implements SFM_IdentityMap_Interface, SFM_Transaction_Engine
{
    protected $isEnabled     = true;
    protected $isTransaction = false;
    
    protected $identityMap = array();
    protected $transactionIdentityIds = array();

    protected static $instance;

    /**
     * @return SFM_IdentityMap
     */
    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Get entity identity storage key
     * @param SFM_Entity $entity
     * @return string
     */
    protected function getIdentityKey(SFM_Entity $entity)
    {
        $identityKey = get_class($entity);

        return $identityKey;
    }

    /**
     * @param SFM_Entity $entity
     * @return SFM_IdentityMap
     */
    public function addEntity(SFM_Entity $entity)
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
     * @param SFM_Entity $entity
     */
    protected function logTransaction(SFM_Entity $entity)
    {
        $className = $this->getIdentityKey($entity);

        if (false === isset($this->transactionIdentityIds[$className])) {
            $this->transactionIdentityIds[$className] = array();
        }

        $this->transactionIdentityIds[$className][] = $entity->getId();
    }
    
    /**
     * Return SFM_Entity from map
     *
     * @param string $className
     * @param int $id
     * @return SFM_Entity|null
     */
    public function getEntity($className, $id)
    {
        $entity = isset($this->identityMap[$className][$id]) ? $this->identityMap[$className][$id] : null;

        return $entity;
    }

    /**
     * @param SFM_Entity $entity
     * @return SFM_IdentityMap
     */
    public function deleteEntity(SFM_Entity $entity)
    {
        $className = $this->getIdentityKey($entity);
        $this->identityMap[$className][$entity->id] = null;

        return $this;
    }

    /**
     * @return SFM_IdentityMap
     */
    public function enable()
    {
        $this->isEnabled = true;

        return $this;
    }

    /**
     * @return SFM_IdentityMap
     */
    public function disable()
    {
        $this->isEnabled = false;

        return $this;
    }

    /**
     * @return SFM_IdentityMap
     */
    public function beginTransaction()
    {
        $this->isTransaction = true;

        return $this;
    }

    /**
     * @return SFM_IdentityMap
     */
    public function commitTransaction()
    {
        $this->isTransaction = false;

        return $this;
    }

    /**
     * @return SFM_IdentityMap
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