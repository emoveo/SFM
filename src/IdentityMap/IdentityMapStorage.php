<?php
namespace SFM\IdentityMap;

use SFM\Entity;

/**
 * Class IdentityMapStorage
 * @package SFM\IdentityMap
 */
class IdentityMapStorage implements IdentityMapStorageInterface
{
    protected $data = [];

    /**
     * Put entity to storage
     *
     * @param Entity $entity
     */
    public function put(Entity $entity)
    {
        $className = get_class($entity);
        if (!isset($this->data[$className])) {
            $this->data[$className] = [];
        }

        $this->data[$className][$entity->getId()] = $entity;
    }

    /**
     * Get entity from storage
     *
     * @param string $className
     * @param int $id
     * @return null|Entity
     */
    public function get($className, $id)
    {
        $entity = null;
        if (isset($this->data[$className]) && isset($this->data[$className][$id])) {
            $entity = $this->data[$className][$id];
        }

        return $entity;
    }

    /**
     * Get multiple entities from storage
     *
     * @param string $className
     * @param int[] $ids
     * @return \SFM\Entity[]
     */
    public function getM($className, $ids = [])
    {
        $entities = [];
        if (isset($this->data[$className])) {
            if (count($ids) == 0) {
                $entities = $this->data[$className];
            } else {
                $objectsByIds = array_flip($ids);
                $entities = array_intersect_key($this->data[$className], $objectsByIds);
            }
        }

        $entities = is_array($entities) ? $entities : [];
        return $entities;
    }

    /**
     * Remove entity from storage
     *
     * @param string $className
     * @param int $id
     */
    public function remove($className, $id)
    {
        if (isset($this->data[$className])) {
            if (isset($this->data[$className][$id])) {
                unset($this->data[$className][$id]);
            }
        }
    }

    /**
     * Return list of classNames
     *
     * @return \string[]
     */
    public function getClassNames()
    {
       return array_keys($this->data);
    }

    /**
     * Flush data
     */
    public function flush()
    {
        $this->data = [];
    }
} 