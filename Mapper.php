<?php
require_once 'SFM/Business.php';
require_once 'SFM/Entity.php';
require_once 'SFM/Aggregate.php';
require_once 'SFM/Cache/Memory.php';
require_once 'SFM/IdentityMap.php';

require_once 'SFM/Exception/Mapper.php';
require_once 'SFM/Exception/LazyLoad.php';
require_once 'SFM/DB.php';

/**
 * Abstract class for Data Mapping
 *
 */
abstract class SFM_Mapper
{
    
    /**
     * Table that contains data of Business objects
     * Notice: for the time present we assume that it is single table
     * @var string
     */
    protected $tableName;

    /**
     * Name of id field in DB
     *
     * @var string
     */
    protected $idField;

    /**
     * List of fields unique corresponds to id field
     * Useful to get entity id quickly.
     * @example andry.domain.com => domain.com/?user_id=1 and domain.com/search?test@domain.com => domain.com/search?user_id=1 and zipcode&country
     * array(array(login), array(email), array(zipcode, country))
     * 
     * @var array[array]
     */
    protected $uniqueFields = array();
    
    /**
     * Prefix for aggregates
     *
     * @var string
     */
    protected $aggregateCachePrefix;
    
    /**
     * Name of entity class that linked with mapper
     * @var string
     */
    protected $entityClassName;
    /**
     * Name of aggregate class that linked with mapper
     * @var string
     */
    protected $aggregateClassName;

    const SQL_PARAM_LIMIT = '_LIMIT_';
    const SQL_PARAM_ORDER_BY = '_ORDER_BY_';
    const SQL_FIELD = '_field_';
    const SQL_PARAM_ORDER_SORT = '_sort_';
    const SQL_PARAM_ORDER_ASC = 'ASC';
    const SQL_PARAM_ORDER_DESC = 'DESC';
    const SQL_PARAM_GROUP_BY = '_GROUP_BY_';
    const SQL_SELECT_TYPE = '_select_?_from_';
    const SQL_SELECT_ALL = '_select_*_from_';
    const SQL_SELECT_ID = '_select_id_from_';
    const SQL_PARAM_CONDITION = '_CONDITION_';

    protected function __construct()
    {
        $className = get_class($this); 
        $this->entityClassName = str_replace('Mapper', 'Entity', $className);
        $this->aggregateClassName = str_replace('Mapper', 'Aggregate', $className);
        $this->idField = 'id';
        $this->aggregateCachePrefix = $this->aggregateClassName . SFM_Cache_Memory::KEY_DILIMITER;
    }
    
    /**
     * Returns name of field that is unique to every Entity.
     * Entity has no ability to change it
     * 
     * @return string
     */
    public function getIdField()
    {
        return $this->idField;
    }
    
    /**
     * Returns list of fields that is unique to every Entity.
     * Entity has no ability to change it
     * 
     * @return array
     */
    public function getUniqueFields()
    {
        return $this->uniqueFields;
    }
    
    
    
    /**
     * Get Entity by id.
     * First tries to fetch Entity from cache, than looks to DB and caches fetched Entity
     * If Entity can't be fetched null is returned
     * 
     * @param int $id
     * @return SFM_Entity|null
     */
    public function getEntityById( $id )
    {
        if (is_numeric($id) && $id > 0) {
            //First looks to IndentityMap
            $entity = $this->getEntityFromIdentityMap($this->entityClassName, $id);
            if( null !== $entity ) {
                return $entity;
            }
            //Second looks to Cache
            $cacheKey = $this->getEntityCacheKeyById($id);
            $entity = SFM_Cache_Memory::getInstance()->get($cacheKey);
//          //aazon: check either Entity is Cacheable. We need this hack till we refactor updateEntity()
            if (null !== $entity && $entity->isCacheable()) {
                return $entity;
            }
        } else {
            throw new SFM_Exception_Mapper("Illegal argument type given; id: ".$id);
        }
        //Then looks to DB. Check that Entity exists
        $entity = $this->getEntityFromDB(array($this->getIdField()=>$id));
        
        if( null !== $entity && $entity->isCacheable() ) {
            //Store Entity in Cache. Since now we store only cacheable entities
            $this->saveCached($entity);
        }
        return $entity;
        
    }
    
    /**
     * Get Entity by unique fileds.
     * Note: You can use only two ways to guarantee single object is received:
     *  - getEntityById
     *  - getEntityByUniqueFileds
     * @see getEntityById
     * @param $params
     * @return SFM_Entity|null
     */
    public function getEntityByUniqueFileds(array $params)
    {
        if ( $this->hasUniqueFields() && ($params = $this->getOneUniqueFromParams($params)) ) {
            $cacheKey = $this->getEntityCacheKeyByUniqueVals( $this->getUniqueVals($params) );
            $entityId = SFM_Cache_Memory::getInstance()->get($cacheKey);
//            var_dump($entityId);echo " -id<br>";
            if( null !== $entityId ) {
                return $this->getEntityById( $entityId );
            }
//            var_dump($entityId);
        } else {
            throw new SFM_Exception_Mapper("Unique fields aren't set");
        }
        
        $entity = $this->getEntityFromDB($params);
        
        //aazon: now we check either Entity is cacheable
        if( null !== $entity && $entity->isCacheable() ) {
            //to prevent unique fields mapping to empty cache object
            if( null === SFM_Cache_Memory::getInstance()->get($entity->getCacheKey())) {
                $this->saveCached($entity);
            }
            $uniqueKey = array_keys($params);
            $this->createUniqueFieldsCache($entity, $uniqueKey);
        }
        
        return $entity;
    }
    

    
    /**
     * Returns key for storing entity in cache.
     * Actually, this method should be called from Entity, but Entity can't know its idField, 
     * thats why Entity method calls Mapper method to fetch key. Tre relationship between Entitty and Mapper
     * is something like friendship.
     * Since Mapper must has ability to fetch Cache key before creation Entity, we have to invent 
     * protected method Mapper::getEntityCacheKeyById()
     * 
     * @param SFM_Entity $entity
     * @return string
     */
    public function getEntityCacheKey(SFM_Entity $entity)
    {
        return $this->getEntityCacheKeyById($entity->getInfo($this->idField));
    }
    /**
     * @param SFM_Entity $entity
     * @param array $uniqueKey One of the keys. It must contain only filed names
     * @return string
     * @throws SFM_Exception_Mapper
     */
    public function getEntityCacheKeyByUniqueFields(SFM_Entity $entity, array $uniqueKey)
    {
        $uniqueVals = array();
        
        foreach ($uniqueKey as $uniqueKeyItem) {
        	if(!is_array($uniqueKeyItem))
        		$uniqueKeyItem = array($uniqueKeyItem);
        	foreach ($uniqueKeyItem as $item) {
	            $val = $entity->getInfo($item);
	            if( null !== $val ) {
	                $uniqueVals[] = $val; 
	            } else {
	                throw new SFM_Exception_Mapper('Unknown field - '.$item);
	            }
        	}
        }
        return $this->getEntityCacheKeyByUniqueVals( $uniqueVals );
    }
    

    /**
     * Returns Entity object by prototype $proto
     * @param array $proto
     * @return SFM_Entity must be overrided in children
     */
    public function createEntity(array $proto)
    {
    	$className = $this->entityClassName;
    	if(array_key_exists($this->idField, $proto)) {
                $entity = $this->getEntityFromIdentityMap($className, $proto[$this->idField]);
        }
        if ($entity === null) {
        	$entity = new $className($proto, $this);
            
            //SFM_IdentityMap::addEntity($entity);
        }
        return $entity;
    }

    

    /**
     * Updates Entity in Database
     * Do not call this method directly! Use Entity::update
     * @todo Check values from $params to be in datamap
     *
     * @param array $params
     * @param SFM_Entity $entity
     * @return bool
     */
    public function updateEntity(array $params, SFM_Entity $entity)
    {
    	//Prevent changing id of Entity
        unset($params[$this->idField]);
        
        //First update the DB
        $updates = array();
        foreach ($params as $key => $value) {
            $updates []= "{$key}=:{$key}";
        }

        $params[$this->idField] = $entity->getInfo($this->idField);
        $sql = "UPDATE ".SFM_DB::getInstance()->quoteIdentifier($this->tableName, true)." SET " . implode(',', $updates) . " WHERE {$this->idField}=:{$this->idField}";
        
        $state = SFM_DB::getInstance()->update($sql, $params);
        //replace in indentityMap
        SFM_IdentityMap::addEntity($entity);
        //Then save to Cache. Tags will be reset automatically
        if ($entity->isCacheable()) {
            $this->saveCached($entity);
        }

        return $state;
    }
    
    /**
     * Search in $newEntity new values of unique fields and update key if needed
     * 
     * @param SFM_Entity $newEntity
     * @param SFM_entity $oldEntity
     * @return void 
     */
    public function updateUniqueFields( SFM_Entity $newEntity, SFM_Entity $oldEntity )
    {
        $changedUniqueKeys = array();
        
        if($this->hasUniqueFields()) {
            foreach ($this->uniqueFields as $uniqueKey) {
                foreach ( $uniqueKey as $field ) {
                    if( $oldEntity->getInfo($field) != $newEntity->getInfo($field)) {
                        $changedUniqueKeys[] = $uniqueKey;
                    }
                }
            }
            if( sizeof($changedUniqueKeys) != 0 ) {
//                echo "<br>chage unique key<br>";
                foreach ($changedUniqueKeys as $key) {
                    SFM_Cache_Memory::getInstance()->delete($oldEntity->getCacheKeyByUniqueFields($key));
                    $this->createUniqueFieldsCache( $newEntity, $key );
                }
            }
        }
    }

    /**
     * Deletes Entity from Database
     *
     * @param SFM_Entity $entity
     * @return bool
     */
    public function deleteEntity(SFM_Entity $entity)
    {
        //delete from identity map
        SFM_IdentityMap::deleteEntity($entity);
        //delete from Cache
        $Cache = SFM_Cache_Memory::getInstance();
        $Cache->delete($entity->getCacheKey());
        //@todo Delete tags, that are related only for this object (if we need to save memory space)
        $Cache->resetTags($entity->getCacheTags());
	    if($this->hasUniqueFields()) {
	             foreach ( $this->uniqueFields as $uniqueKey ) {
	                 $key = $entity->getCacheKeyByUniqueFields($uniqueKey);
	                 $Cache->delete( $key );
	             }
	        }
        
        //Then delete from DB
        $sql = "DELETE FROM {$this->tableName} WHERE {$this->idField}=:{$this->idField}";
        return SFM_DB::getInstance()->delete($sql, array($this->idField => $entity->getInfo($this->idField)));
    }
    
    /**
     * Executes insert SQL query and returns Entity
     * Must be extended in children to provide filtering
     * 
     * @param array $proto
     * @return SFM_Entity
     */
    public function insertEntity($proto)
    {
        unset($proto[$this->idField]);
        //$inserts = array();
        $keys = array();
        $values = array();
        foreach ($proto as $key => $value) {
            //$inserts []= "{$key}=:{$key}";
            $keys[] = $key;
            $values[] = ':'.$key;
        }
        
        //$sql = "INSERT INTO {$this->tableName} SET " . implode(',', $inserts);
        $sql = "INSERT INTO "
             . SFM_DB::getInstance()->quoteIdentifier($this->tableName, true)
             . ' (' . implode(', ', $keys) . ') '
             . 'VALUES (' . implode(', ', $values) . ')';
        return $this->getEntityById( SFM_DB::getInstance()->insert($sql, $proto, $this->tableName) );        
    }
    
    /**
     * Returns aggregate object by $proto array. Also saves the cache key for Aggregate.
     * The not null value of $cacheKey means that Aggregate in the future will be stored in Cache
     * 
     * @param array $proto
     * @param string $cacheKey
     * @return SFM_Aggregate must be overrided in childs 
     */
    public function createAggregate(array $proto, $cacheKey=null)
    {
    	$className = $this->aggregateClassName;
        return new $className($proto, $this, $cacheKey);
    }
    
    /**
     * Returns Aggregate by params.
     * By default first looks to Cache, then to DB
     * Since Aggregates have no id, keys for them must be set by developer
     * If $cacheKey is null there will be no look up to Cache
     * 
     * @param array $params
     * @param string $cacheKey
     * @return SFM_Aggregate
     */
    public function getAggregate(array $params = array(), $cacheKey=null)
    {
        //If there is a key for Cache, look to Cache
        if ($cacheKey !== null) {
            $aggregate = SFM_Cache_Memory::getInstance()->get($cacheKey);
            
            if ($aggregate !== null) {
                return $aggregate;
            }
        }
        
        //Look to DB
        $proto = $this->fetchArrayFromDB($params);
        $aggregate = $this->createAggregate($proto, $cacheKey);
        
        //If key for Cache exists, store to Caching
        if ($cacheKey !== null && $aggregate !== null) {
            $this->saveCached($aggregate);
        }
        
        return $aggregate;
        
    }
    
    /**
     * The same as getAggregate, but by sql query
     * @see getAggregate
     * 
     * @param string $sql
     * @param array $params
     * @param string $cacheKey
     * @return SFM_Aggregate
     */
    public function getAggregateBySQL($sql, array $params = array(), $cacheKey=null)
    {
    	//If there is a key for Cache, look to Cache
        if ($cacheKey !== null) {
            $aggregate = SFM_Cache_Memory::getInstance()->get($cacheKey);
            if ($aggregate !== null) {
                return $aggregate;
            }
        }
        $db = SFM_DB::getInstance();
        $aggregate = $this->createAggregate( $db->fetchAll($sql, $params), $cacheKey );
        
        //If key for Cache exists, store to Caching
        if ($cacheKey !== null && $aggregate !== null) {
            $this->saveCached($aggregate);
        }
        
        return $aggregate;
    }
    
    /**
     * @TODO First look in indentityMap then cache ...
     * Load entities by id.
     * First get all from cache, then from DB
     * @return array of Entities 
     */
    public function getMultiEntitiesByIds( array $entityId ) 
    {
    	if( sizeof($entityId) == 0 || null == $entityId) {
    		return array();
    	}
        $cachedVals = SFM_Cache_Memory::getInstance()->getMulti( $this->getEntitiesCacheKeyByListId($entityId) );
        
        $foundedId = array();
        if( null != $cachedVals ) {
            foreach ($cachedVals as $item) {
                $foundedId[] = $item->getId();
            }
        } else {
            $cachedVals = array();
        }
        $notFoundedId = array_diff($entityId, $foundedId);
//        echo 'getMultiEntitiesByIds<br>';var_dump($cachedVals);
        $dbVals = $this->loadEntitiesFromDbByIds($notFoundedId);
        if( sizeof($dbVals) != 0 ) {
            $this->saveListOfEntitiesInCache($dbVals);
        }
        $result = array_merge($cachedVals, $dbVals);
//        var_dump($result);
        return $result;
        
    }
    
    /**
     * Save list of entities in one request
     *
     * @param array[SFM_Entity] $entities
     * @return void
     */
    public function saveListOfEntitiesInCache( array $entities )
    {
        if(sizeof($entities)>0) {
            SFM_Cache_Memory::getInstance()->setMulti($entities);
        }
    }
    
    protected function loadEntitiesFromDbByIds( array $entityId )
    {
        
    	$result = array();
        if( sizeof($entityId) != 0 ) {
            $sql = 'SELECT *';
			$calculated = $this->getCalculatedExpressions();
            if(!empty($calculated))
            	$sql.= ', '.implode(', ',$calculated);
            $sql.= ' FROM '.$this->tableName.' WHERE '. $this->getIdField() .' IN ('. implode(",",$entityId) .')';
            $data = SFM_DB::getInstance()->fetchAll($sql);
            foreach ($data as $row) {
                $result[] = $this->createEntity($row);
            }
        }
        return $result;
    }
    
    /**
     * 	returns an array containing fields that are not presented in the DB but are counted.
     * 	For instance, array('COUNT(id)', 'SUM(rating) as overallrating')
     */
    protected function getCalculatedExpressions()
    {
    	return array();
    }
    
    public function getCacheKeysByEntitiesId( array $ids )
    {
        $result = array();
        foreach ( $ids as $item ) {
            $result[] = $this->getEntityCacheKeyById($item);
        }
        return $result;
    }
    
    protected function getEntityCacheKeyById($id)
    {
        return $this->entityClassName . SFM_Cache_Memory::KEY_DILIMITER . $id;
    }
    
    protected function getEntitiesCacheKeyByListId( array $ids)
    {
        $result = array();
        foreach ($ids as $item) {
            $result[] = $this->getEntityCacheKeyById($item);
        }
        return $result;
    }

    protected function getEntityCacheKeyByUniqueVals( array $values )
    {
    	$key = $this->entityClassName . SFM_Cache_Memory::KEY_DILIMITER;
        foreach ($values as $item) {
            $key .= SFM_Cache_Memory::KEY_DILIMITER . $item;
        }
        return $key;
    }
    
    /**
     * Returns either Entity will be cached.
     * Since Entities don't have access to Data Layer, the have to call their Mapper's method
     * By default all entities are cacheable
     * 
     * @param SFM_Entity $entity
     * @return bool
     */
    public function isCacheable(SFM_Entity $entity)
    {
        return true;
    }
    
    public function __toString()
    {
        return get_class($this);
    }
    
    /**
     * Generate default cache key name base on parent entity id and seed
     * @example usage (Entity_User $user, 'sort_by_rating') or (Entity_User $user, 'sort_by_num_posts')
     * @param SFM_Entity $entity
     * @param $prefix Use it if you need different cache keys for same parent entity
     * @return string
     */
    public function getAggregateCacheKeyByParentEntity(SFM_Entity $entity=null, $prefix='')
    {
        $cacheKey = $this->aggregateCachePrefix;
        if( !empty($prefix) ) {
            $cacheKey .= $prefix . SFM_Cache_Memory::KEY_DILIMITER;
        }
        if( null != $entity ) {
            $cacheKey .= get_class($entity) . SFM_Cache_Memory::KEY_DILIMITER . $entity->getId();
        }
        return $cacheKey;
    }
    
	/**
     * Generate cache key basing on parent and child entity. Aggregate is replaced by concrete child id.
     * @param SFM_Entity $parent
     * @param SFM_Entity $child
     * @param $prefix Use it if you need different cache keys for same parent entity
     * @return string
     */
    public function getAggregateCacheKeyByParentAndChildEntity(SFM_Entity $parent, SFM_Entity $child, $prefix = '')
    {
    	$cacheKey = $this->getAggregateCacheKeyByParentEntity($parent,$child->getId()).SFM_Cache_Memory::KEY_DILIMITER.$prefix;
    	return $cacheKey;
    }
    
   /**
     * @param array $params
     * @return SFM_Entity
     */
    protected function getEntityFromDB( array $params )
    {
        //force set select * 
        //$params[self::SQL_SELECT_TYPE] = self::SQL_SELECT_ALL;
        $data = $this->fetchArrayFromDB($params);
        if (count($data) > 1) {
            throw new SFM_Exception_Mapper('More than 1 row in result set');
        } elseif (count($data) == 0) {
            return null;
        }
        
        //So, count($data) == 1, it is our case :-)
        $proto = array_shift($data);        
        return $this->createEntity($proto);
    }
    
    /**
     * Returns text of SQL query, that should be executed to fetch data from DB
     *
     * @param array $params
     * @return string
     */
    protected function createSelectStatement(array &$params)
    {
        $quoteSymbol = SFM_DB::getInstance()->getQuoteSymbol();
    	
    	$limit = $orderBy = $groupBy = '';
        if (isset($params[self::SQL_PARAM_LIMIT])) {
            $limit = ' LIMIT ' . $params[self::SQL_PARAM_LIMIT];
            unset($params[self::SQL_PARAM_LIMIT]);
        }
        
        if (isset($params[self::SQL_PARAM_ORDER_BY])) {
            $orderBy = ' ORDER BY ' . $params[self::SQL_PARAM_ORDER_BY];
            unset($params[self::SQL_PARAM_ORDER_BY]);
        }
        
        if (isset($params[self::SQL_PARAM_GROUP_BY])) {
            $groupBy = ' GROUP BY ' . $params[self::SQL_PARAM_GROUP_BY];
            unset($params[self::SQL_PARAM_GROUP_BY]);
        }
        
        $conditions = array();
        
    	if (isset($params[self::SQL_PARAM_CONDITION])) {
    		$pConditions = (array) $params[self::SQL_PARAM_CONDITION];
    		foreach ($pConditions as $pCond) {
    			$conditions[]= $pCond;
    		}
            unset($params[self::SQL_PARAM_CONDITION]);
        }
        
    	foreach ($params as $key => $value) {
            $conditions []= $quoteSymbol."{$key}".$quoteSymbol." = :{$key}";
        }

        $sql = 'SELECT * FROM '.$this->tableName . (count($conditions) ?' WHERE ' . join(' AND ', $conditions) : '') . $groupBy . $orderBy . $limit;
        
        return $sql;
    }
        
    /**
     * Returns result set by means of which Entity will be generated
     *
     * @param array $params
     * @return Array
     */
    protected function fetchArrayFromDB(array $params)
    {
        $sql = $this->createSelectStatement($params);
        //remove all auxiliary vars
        foreach ($params as $key => $value) {
            if( strpos($key, '_')===0 && strrpos($key, '_')===strlen($key)-1 ) {
                unset($params[$key]);
            }
        }
        return SFM_DB::getInstance()->fetchAll($sql, $params);
    }

    /**
     * Returns Entity by array. At first looks to IdentityMap, then creates new Entity
     *
     * @param array $proto
     * @return SFM_Entity
     */
    protected function getEntityFromIdentityMap($className, $id)
    {
        return SFM_IdentityMap::getEntity($className, $id);
    }
    
    /**
     * Stores Aggregate or Entity in Cache
     * We don't differ Entities from Aggregates because the caching algorithm is the same
     * @see http://www.smira.ru/2008/10/29/web-caching-memcached-5/
     * 
     * @param SFM_Business $object Entity or Aggregate object
     */
    protected function saveCached(SFM_Business $object)
    {
        $cacheKey = $object->getCacheKey();
        $tags = $object->getCacheTags();
        
        $Cache = SFM_Cache_Memory::getInstance();
        //reset only for entities
        if($object instanceof SFM_Entity) {
            $Cache->resetTags($tags); 
        }
        $Cache->set($cacheKey, $object, $tags);
    }
    
    /**
     * Contains loading of fields, that initialize after object initialization (lazy load).
     * Must be overriden in child Classes
     * In this abstract class only the simplest case is implemented only
     *
     * @param SFM_Business $business
     * @param string $fieldName
     * @return mixed
     * @throws SFM_Exception_LazyLoad
     */
    public function lazyload(SFM_Business $business, $fieldName)
    {
    	
    	if ($business instanceof SFM_Entity) {
            if (substr($fieldName, -3) == '_id') {
           		$name = ucfirst(substr($fieldName, 0, -3));
           		
           		$mapperClassName = 'Mapper_' . $name;
                
                require_once "Mapper/{$name}.Mapper.php";
                
                if (class_exists($mapperClassName)) {
                    $mapper = new $mapperClassName;
                    return $mapper->getEntityById($business->getInfo($fieldName));
                } else {
                   throw new SFM_Exception_LazyLoad("{$mapperClassName} not found"); 
                }
            }
        }
        
        throw new SFM_Exception_LazyLoad("Can't lazy load field {$fieldName} in mapper {$this}");
    }
    
    
    /**
     * @param array $uniqueKey
     * @param SFM_Entity $entity
     */
    protected function createUniqueFieldsCache( SFM_Entity $entity, array $uniqueKey ) 
    {
        if($this->hasUniqueFields()) {
            $key = $entity->getCacheKeyByUniqueFields($uniqueKey);
            SFM_Cache_Memory::getInstance()->set($key, $entity->getId());
        }
    }
    
    
    /**
     * @param SFM_Entity $entity
     */
    protected function createAllUniqueFieldsCache( SFM_Entity $entity ) 
    {
        if($this->hasUniqueFields()) {
            foreach ($this->uniqueFields as $uniqueKey) {
                $this->createUniqueFieldsCache($entity, $uniqueKey);
            }
        }
    }
    
    /**
     * Check if array contains all fields of any unique keys 
     * and return first matched key or false if no key founded 
     * 
     * @param array $params
     * @return array|false
     */
    protected function getOneUniqueFromParams( array $params )
    {
    	$result = false;
        
        if(!$this->hasUniqueFields()) {
            return false;
        }

        foreach ($this->uniqueFields as $uniqueKey) {
        	$match = array();
	        foreach ($params as $key => $val) {
	            if(in_array($key, $uniqueKey)) {
	                $match[$key] = $val;
	            }
	        }
	        if( sizeof($uniqueKey) === sizeof($match) ) {
	            $result = $match;
	            break;
	        }
        }
        return $result;        
    }
    
    /**
     * @param array $params
     * @return array
     */
    protected function getUniqueVals( array $params ) 
    {
        if(!$this->hasUniqueFields()) {
            return array();
        }
        $result = array();
        foreach ($params as $field => $val) {
            $result[] = $params[$field];
        }
        return $result;
    }
    
    
    /**
     * @return bool
     */
    protected function hasUniqueFields()
    {
        if(sizeof($this->uniqueFields)!=0) {
            return true;
        } else {
            return false;
        }
    }
}