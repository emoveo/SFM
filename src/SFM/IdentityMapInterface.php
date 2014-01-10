<?php
interface SFM_IdentityMapInterface
{
    /**
     * @param SFM_Entity $entity
     * @return SFM_IdentityMapInterface
     */
    public function addEntity(SFM_Entity $entity);

    /**
     * @param string $className
     * @param integer $id
     * @return SFM_Entity
     */
    public function getEntity($className, $id);

    /**
     * @param SFM_Entity $entity
     * @return SFM_IdentityMapInterface
     */
    public function deleteEntity(SFM_Entity $entity);

    /**
     * @return SFM_IdentityMapInterface
     */
    public function enable();

    /**
     * @return SFM_IdentityMap
     */
    public function disable();

    /**
     * @return SFM_IdentityMap
     */
    public function beginTransaction();

    /**
     * @return SFM_IdentityMap
     */
    public function commitTransaction();

    /**
     * @return SFM_IdentityMap
     */
    public function rollbackTransaction();
}