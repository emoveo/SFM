<?php
namespace SFM\IdentityMap;

use SFM\Entity;
use SFM\IdentityMap\IdentityMap;

/**
 * Interface IdentityMapInterface
 * @package SFM\IdentityMap
 */
interface IdentityMapInterface
{
    /**
     * Add entity to identity map
     *
     * @param Entity $entity
     * @return IdentityMapInterface
     */
    public function addEntity(Entity $entity);

    /**
     * Get entity from identity map
     *
     * @param string $className
     * @param integer $id
     * @return null|Entity
     */
    public function getEntity($className, $id);

    /**
     * Get multiple entities from identity map
     *
     * @param string $className
     * @param int[] $ids
     * @return Entity[]
     */
    public function getEntityMulti($className, $ids);

    /**
     * Delete entity from identity map
     *
     * @param Entity $entity
     * @return IdentityMapInterface
     */
    public function deleteEntity(Entity $entity);

    /**
     * Enable identity map
     *
     * @return IdentityMapInterface
     */
    public function enable();

    /**
     * Disable identity map
     *
     * @return IdentityMap
     */
    public function disable();
}