<?php
namespace SFM\IdentityMap;

use SFM\Entity;
use SFM\IdentityMap\IdentityMap;

interface IdentityMapInterface
{
    /**
     * @param Entity $entity
     * @return IdentityMapInterface
     */
    public function addEntity(Entity $entity);

    /**
     * @param string $className
     * @param integer $id
     * @return Entity
     */
    public function getEntity($className, $id);

    /**
     * @param Entity $entity
     * @return IdentityMapInterface
     */
    public function deleteEntity(Entity $entity);

    /**
     * @return IdentityMapInterface
     */
    public function enable();

    /**
     * @return IdentityMap
     */
    public function disable();

    /**
     * @return IdentityMap
     */
    public function beginTransaction();

    /**
     * @return IdentityMap
     */
    public function commitTransaction();

    /**
     * @return IdentityMap
     */
    public function rollbackTransaction();
}