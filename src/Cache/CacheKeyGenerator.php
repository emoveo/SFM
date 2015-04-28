<?php
namespace SFM\Cache;

use SFM\Entity;
use SFM\BaseException;

class CacheKeyGenerator
{
    public function getEntityCacheKeyById($id, $className)
    {
        return $className . CacheStrategy::KEY_DELIMITER . $id;
    }

    public function getEntityCacheKeyByUniqueVals(array $values, $className)
    {
        $key = $className . CacheStrategy::KEY_DELIMITER;
        foreach ($values as $item) {
            if(is_string($item)) {
                $item = mb_strtolower($item);
            }
            $key .= CacheStrategy::KEY_DELIMITER . $item;
        }
        return $key;
    }

    public function getEntityCacheKey(Entity $entity, $idField, $entityClass)
    {
        return $this->getEntityCacheKeyById($entity->getInfo($idField), $entityClass);
    }

    /**
     * @param Entity $entity
     * @param array $uniqueKey One of the keys. It must contain only field names
     * @return string
     * @throws BaseException
     */
    public function getEntityCacheKeyByUniqueFields(Entity $entity, array $uniqueKey, $entityClass)
    {
        $uniqueVals = array();

        foreach ($uniqueKey as $uniqueKeyItem) {
            if(!is_array($uniqueKeyItem))
                $uniqueKeyItem = array($uniqueKeyItem);
            foreach ($uniqueKeyItem as $item) {
                $val = $entity->getInfo($item);
                if( null !== $val ) {
                    if(is_string($val)) {
                        $val = mb_strtolower($val);
                    }
                    $uniqueVals[] = $val;
                } else {
                    throw new BaseException('Unknown field - '.$item);
                }
            }
        }
        return $this->getEntityCacheKeyByUniqueVals($uniqueVals, $entityClass);
    }

    public function getCacheKeysByEntitiesId( array $ids, $entityClass )
    {
        $result = array();
        foreach ( $ids as $item ) {
            $result[] = $this->getEntityCacheKeyById($item, $entityClass);
        }
        return $result;
    }

    public function getAggregateCacheKeyByParentEntity(Entity $entity=null, $prefix='', $aggregateCachePrefix)
    {
        $cacheKey = $aggregateCachePrefix;
        if( $prefix !== '' ) {
            $cacheKey .= $prefix . CacheStrategy::KEY_DELIMITER;
        }
        if( null != $entity ) {
            $cacheKey .= get_class($entity) . CacheStrategy::KEY_DELIMITER . $entity->getId();
        }
        return $cacheKey;
    }

    public function getAggregateCacheKeyByParentAndChildEntity(Entity $parent, Entity $child, $prefix = '', $aggregateCachePrefix)
    {
        $cacheKey = $this->getAggregateCacheKeyByParentEntity($parent,$child->getId(), $aggregateCachePrefix).CacheStrategy::KEY_DELIMITER.$prefix;
        return $cacheKey;
    }
} 