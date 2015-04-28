<?php
namespace SFM\IdentityMap;

use SFM\Entity;

/**
 * Interface IdentityMapStorageInterface
 * @package SFM\IdentityMap
 */
interface IdentityMapStorageInterface
{
    /**
     * Put entity to storage
     *
     * @param Entity $entity
     */
    public function put(Entity $entity);

    /**
     * Get entity from storage
     *
     * @param string $className
     * @param int $id
     * @return Entity|null
     */
    public function get($className, $id);

    /**
     * Get multiple entities from storage
     *
     * @param string $className
     * @param int[] $ids
     * @return Entity[]
     */
    public function getM($className, $ids = []);

    /**
     * Remove entity from storage
     *
     * @param string $className
     * @param int $id
     */
    public function remove($className, $id);

    /**
     * Return list of classNames
     *
     * @return string[]
     */
    public function getClassNames();

    /**
     * Flush data
     */
    public function flush();
} 