<?php

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

    /** @var \SFM\Repository */
    protected $repository;

    /**
     * @return \SFM\Repository
     */
    public function getRepository()
    {
        return $this->repository;
    }

    /**
     * @param \SFM\Repository $repository
     */
    public function __construct(\SFM\Repository $repository)
    {
        $this->repository = $repository;

        $className = get_class($this);
        $this->entityClassName = str_replace('Mapper', 'Entity', $className);
        $this->aggregateClassName = str_replace('Mapper', 'Aggregate', $className);
        $this->idField = 'id';
        $this->aggregateCachePrefix = $this->aggregateClassName . SFM\Cache\CacheProvider::KEY_DELIMITER;
    }

    public function getTableName()
    {
        return $this->tableName;
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
            $entity = SFM_Manager::getInstance()->getCache()->get($cacheKey);
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
     * Get Entity by unique fields.
     * Note: You can use only two ways to guarantee single object is received:
     *  - getEntityById
     *  - getEntityByUniqueFields
     * @see getEntityById
     * @param $params
     * @return SFM_Entity|null
     */
    public function getEntityByUniqueFields(array $params)
    {
        if ( $this->hasUniqueFields() && ($params = $this->getOneUniqueFromParams($params)) ) {
            $cacheKey = $this->getEntityCacheKeyByUniqueVals( $this->getUniqueVals($params) );
            $entityId = SFM_Manager::getInstance()->getCache()->getRaw($cacheKey);
            if( null !== $entityId ) {
                return $this->getEntityById( $entityId );
            }
        } else {
            throw new SFM_Exception_Mapper("Unique fields aren't set");
        }

        $entity = $this->getEntityFromDB($params);

        //aazon: now we check either Entity is cacheable
        if( null !== $entity && $entity->isCacheable() ) {
            //to prevent unique fields mapping to empty cache object
            if( null === SFM_Manager::getInstance()->getCache()->get($entity->getCacheKey())) {
                $this->saveCached($entity);
            }
            $uniqueKey = array_keys($params);
            $this->createUniqueFieldsCache($entity, $uniqueKey);
        }

        return $entity;
    }


    /**
     * Wrapper. Thank the method's author for spelling mistakes!
     * @param array $params
     * @deprecated
     */
    public function getEntityByUniqueFileds(array $params)
    {
        return $this->getEntityByUniqueFields($params);
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
     * @param array $uniqueKey One of the keys. It must contain only field names
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
                    if(is_string($val)) {
                        $val = mb_strtolower($val);
                    }
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
        } else {
            $entity = null;
        }
        if ($entity === null) {
            $entity = new $className($proto, $this);
            SFM_Manager::getInstance()->getIdentityMap()->addEntity($entity);
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
            $field = SFM_Manager::getInstance()->getDb()->quoteIdentifier($key, true);
            $updates []= "{$field}=:{$key}";
        }

        $params[$this->idField] = $entity->getInfo($this->idField);
        $sql = "UPDATE ".SFM_Manager::getInstance()->getDb()->quoteIdentifier($this->tableName, true)." SET " . implode(',', $updates) . " WHERE {$this->idField}=:{$this->idField}";

        $state = SFM_Manager::getInstance()->getDb()->update($sql, $params);

        //replace in indentityMap
        SFM_Manager::getInstance()->getIdentityMap()->addEntity($entity);
        //Then save to Cache. Tags will be reset automatically
        if ($entity->isCacheable()) {
            $this->saveCached($entity);
        }

        return $state;
    }

    /**
     * Updates Agregate in Cache
     *
     * @param SFM_Aggregate $aggregate
     */
    public function updateAggregate(SFM_Aggregate $aggregate)
    {
        $this->saveCached($aggregate);
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
                foreach ($changedUniqueKeys as $key) {
                    SFM_Manager::getInstance()->getCache()->delete($oldEntity->getCacheKeyByUniqueFields($key));
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
        SFM_Manager::getInstance()->getIdentityMap()->deleteEntity($entity);
        //delete from Cache
        $Cache = SFM_Manager::getInstance()->getCache();
        $Cache->deleteEntity($entity);
        if($this->hasUniqueFields()) {
             foreach ( $this->uniqueFields as $uniqueKey ) {
                 $key = $entity->getCacheKeyByUniqueFields($uniqueKey);
                 $Cache->delete($key);
             }
        }

        //Then delete from DB
        $tableName = SFM_Manager::getInstance()->getDb()->quoteIdentifier($this->tableName, true);

        $sql = "DELETE FROM {$tableName} WHERE {$this->idField}=:{$this->idField}";
        return SFM_Manager::getInstance()->getDb()->delete($sql, array($this->idField => $entity->getInfo($this->idField)));
    }

    /**
     * Executes insert SQL query and returns Entity
     *
     * @param $proto
     * @param bool $makeValidation make constraints validation
     * @return SFM_Entity
     * @throws SFM_Exception_EntityValidation
     */
    public function insertEntity($proto, $makeValidation = true)
    {
        if ($makeValidation) {
            $handler = call_user_func("{$this->entityClassName}::getEntityHandler");
            if ($handler instanceof SFM_Entity_HandlerInterface) {
                $proto = $handler->handle($proto);
                if ($errors = $handler->getErrors()) {
                    throw new SFM_Exception_EntityValidation("Insert failed", $errors);
                }
            }
        }

        if($this->isIdAutoIncrement()){
            unset($proto[$this->idField]);
        }

        $keys = array();
        $values = array();
        foreach ($proto as $key => $value) {
            $keys[] = SFM_Manager::getInstance()->getDb()->quoteIdentifier($key, true);
            $values[] = ':'.$key;
        }

        $sql = "INSERT INTO "
             . SFM_Manager::getInstance()->getDb()->quoteIdentifier($this->tableName, true)
             . ' (' . implode(', ', $keys) . ') '
             . 'VALUES (' . implode(', ', $values) . ')';
        
        return $this->getEntityById( SFM_Manager::getInstance()->getDb()->insert($sql, $proto, $this->idField, $this->isIdAutoIncrement()) );
    }

    /**
     * Returns aggregate object by $proto array. Also saves the cache key for Aggregate.
     * The not null value of $cacheKey means that Aggregate in the future will be stored in Cache
     *
     * @param array $proto
     * @param string $cacheKey
     * @return SFM_Aggregate must be overrided in childs
     */
    public function createAggregate(array $proto, $cacheKey=null, $loadEntities=false)
    {
        $className = $this->aggregateClassName;
        return new $className($proto, $this, $cacheKey, $loadEntities);
    }

    /**
     * Returns Aggregate by params.
     * By default first looks to Cache, then to DB
     * Since Aggregates have no id, keys for them must be set by developer
     * If $cacheKey is null there will be no look up to Cache
     *
     * @param array $params
     * @param string $cacheKey
     * @param bool $loadEntities
     * @return SFM_Aggregate
     */
    public function getAggregate(array $params = array(), $cacheKey=null, $loadEntities=false)
    {
        //If there is a key for Cache, look to Cache
        $aggregate = $this->getCachedAggregate($cacheKey,$loadEntities);
        if($aggregate === null){
            //Look to DB
            $proto = $this->fetchArrayFromDB($params);
            $aggregate = $this->createAggregate($proto, $cacheKey, $loadEntities);
            $this->saveCachedAggregate($aggregate,$loadEntities,0);
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
     * @param bool $loadEntities
     * @param integer $expiration
     * @return SFM_Aggregate
     */
    public function getAggregateBySQL($sql, array $params = array(), $cacheKey=null, $loadEntities=false, $expiration = 0)
    {
        $aggregate = $this->getCachedAggregate($cacheKey,$loadEntities);
        if($aggregate === null){
            $db = SFM_Manager::getInstance()->getDb();
            $aggregate = $this->createAggregate( $db->fetchAll($sql, $params), $cacheKey, $loadEntities );
            $this->saveCachedAggregate($aggregate,$loadEntities,$expiration);
        }
        return $aggregate;
    }

    /**
     * The same as getAggregate, but by array of ids
     * @see getAggregate
     *
     * @param array $ids
     * @param string $cacheKey
     * @param bool $loadEntities
     * @param integer $expiration
     * @return SFM_Aggregate
     */
    public function getAggregateByIds(array $ids = array(), $cacheKey=null, $loadEntities=false, $expiration = 0)
    {
        $aggregate = $this->getCachedAggregate($cacheKey,$loadEntities);
        if($aggregate === null){
            $aggregate = $this->createAggregate( $ids, $cacheKey, $loadEntities );
            $this->saveCachedAggregate($aggregate,$loadEntities,$expiration);
            return $aggregate;
        }
    }

    protected function getCachedAggregate($cacheKey,$loadEntities)
    {
        if ($cacheKey !== null) {
            $aggregate = SFM_Manager::getInstance()->getCache()->get($cacheKey);
            if ($aggregate !== null) {
                if( $loadEntities ) {
                    $aggregate->loadEntities();
                }
                return $aggregate;
            }
        }
        return null;
    }

    protected function saveCachedAggregate(SFM_Aggregate $aggregate,$loadEntities,$expiration)
    {
        if($expiration){
            $aggregate->setExpires($expiration);
        }
        //If key for Cache exists, store to Caching
        if ($aggregate->getCacheKey() !== null && $aggregate !== null) {
            $this->saveCached($aggregate);
            if( $loadEntities ) {
                $this->saveListOfEntitiesInCache($aggregate->getContent());
            }
        }
    }

    /**
     * Wrapper for getAggregateBySql with load all Entities
     * @see getAggregateBySql
     *
     * @param string $sql
     * @param array $params
     * @param string $cacheKey
     * @param integer $expiration
     * @return SFM_Aggregate
     */
    public function getLoadedAggregateBySQL($sql, array $params = array(), $cacheKey=null, $expiration = 0)
    {
        $tmp = strtolower( str_replace(' ', '', $sql) );
        if( !preg_match('/select([^.]*)(\.{0,1})\*/', $tmp)) {
            throw new Exception('You must use "SELECT * FROM" to load aggregate');
        }
        return $this->getAggregateBySQL($sql, $params, $cacheKey, true, $expiration);
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
        
        //from identity map
        $cachedIdentityMapVals = $this->getEntityMultiFromIdentityMap($this->entityClassName,$entityId);
        $entityId = array_diff($entityId,array_keys($cachedIdentityMapVals));
        
        $memcachedVals = SFM_Manager::getInstance()->getCache()->getMulti( $this->getEntitiesCacheKeyByListId($entityId) );
        $cachedVals = $cachedIdentityMapVals;
        if($memcachedVals){
            $cachedVals = array_merge($cachedVals,$memcachedVals);
        }

        $foundedId = array();
        if( null != $cachedVals ) {
            foreach ($cachedVals as $item) {
                $foundedId[] = $item->getId();
            }
        } else {
            $cachedVals = array();
        }
        $notFoundedId = array_diff($entityId, $foundedId);
        $dbVals = $this->loadEntitiesFromDbByIds($notFoundedId);
        if( sizeof($dbVals) != 0 ) {
            $this->saveListOfEntitiesInCache($dbVals);
        }
        $result = array_merge($cachedVals, $dbVals);
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
            SFM_Manager::getInstance()->getCache()->setMulti($entities);
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
            $sql.= ' FROM '.SFM_Manager::getInstance()->getDb()->quoteIdentifier($this->tableName, true).' WHERE '. $this->getIdField() .' IN ('. implode(",",$entityId) .')';
            $data = SFM_Manager::getInstance()->getDb()->fetchAll($sql);
            
            foreach ($data as $row) {
                $result[] = $this->createEntity($row);
            }
        }
        return $result;
    }

    /**
     *  returns an array containing fields that are not presented in the DB but are counted.
     *  For instance, array('COUNT(id)', 'SUM(rating) as overallrating')
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
        return $this->entityClassName . SFM\Cache\CacheProvider::KEY_DELIMITER . $id;
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
        $key = $this->entityClassName . SFM\Cache\CacheProvider::KEY_DELIMITER;
        foreach ($values as $item) {
            if(is_string($item)) {
                $item = mb_strtolower($item);
            }
            $key .= SFM\Cache\CacheProvider::KEY_DELIMITER . $item;
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
        if( $prefix !== '' ) {
            $cacheKey .= $prefix . SFM\Cache\CacheProvider::KEY_DELIMITER;
        }
        if( null != $entity ) {
            $cacheKey .= get_class($entity) . SFM\Cache\CacheProvider::KEY_DELIMITER . $entity->getId();
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
        $cacheKey = $this->getAggregateCacheKeyByParentEntity($parent,$child->getId()).SFM\Cache\CacheProvider::KEY_DELIMITER.$prefix;
        return $cacheKey;
    }

    /**
     * Generate cache key basing on entity list. Aggregate is replaced by concrete child id.
     * @param SFM_Aggregate|array $entityList
     * @param $prefix Use it if you need different cache keys for same parent entity
     * @return string
     */
    public function getAggregateCacheKeyByEntities($entityList, $prefix = '')
    {
        $cacheKey = '';
        foreach($entityList as $entity){
            $cacheKey.= $this->getAggregateCacheKeyByParentEntity($entity).SFM\Cache\CacheProvider::KEY_DELIMITER;
        }
        return $cacheKey.$prefix;
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
        $quoteSymbol = SFM_Manager::getInstance()->getDb()->getQuoteSymbol();

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

        $sql = 'SELECT * FROM '.SFM_Manager::getInstance()->getDb()->quoteIdentifier($this->tableName, true) . (count($conditions) ?' WHERE ' . join(' AND ', $conditions) : '') . $groupBy . $orderBy . $limit;

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
        return SFM_Manager::getInstance()->getDb()->fetchAll($sql, $params);
    }

    /**
     * Returns Entity by array. At first looks to IdentityMap, then creates new Entity
     *
     * @param array $proto
     * @return SFM_Entity
     */
    protected function getEntityFromIdentityMap($className, $id)
    {
        return SFM_Manager::getInstance()->getIdentityMap()->getEntity($className, $id);
    }
    
    /**
     * 
     *
     * @param string $className
     * @param array $ids
     * @return array of SFM_Entity
     */
    protected function getEntityMultiFromIdentityMap($className, $ids)
    {
        return SFM_Manager::getInstance()->getIdentityMap()->getEntityMulti($className, $ids);
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
        if (null !== $cacheKey) {

            $Cache = SFM_Manager::getInstance()->getCache();
            //reset only for entities
            if($object instanceof SFM_Entity) {
                $Cache->deleteEntity($object);
            }
            $Cache->set($object);
        }
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
    public function lazyload($business, $fieldName)
    {
        if (false === $business instanceof SFM_Business) {
            throw new SFM_Exception_LazyLoad("Object `$business` is not instance of `SFM_Business` class");
        }

        if ($business instanceof SFM_Entity) {
            if (substr($fieldName, -3) == '_id') {
                //$name = ucfirst(substr($fieldName, 0, -3));
                 //fixed by A-25
                //mappers of field names with _ should have camelCase names
                //for example, street_type_id => Mapper_StreetType
                //or street_type_id => Mapper_Street_Type
                $name = substr($fieldName, 0, -3);
                $nameParts = explode('_',$name);


                foreach($nameParts as &$namePart)
                {
                    $namePart = ucfirst($namePart);
                }


                $name = implode('',$nameParts);
                $mapperClassName1Variant = 'Mapper_' . $name;
                $mapperClassName2Variant = 'Mapper_' . implode('_',$nameParts);
                if(class_exists($mapperClassName1Variant)){
                    $mapperClassName = $mapperClassName1Variant;
                } else {
                    //simply it was variant2
                    $mapperClassName = $mapperClassName2Variant;
                }

                if (class_exists($mapperClassName)) {
                    $mapper = SFM_Manager::getInstance()->getRepository()->get($mapperClassName);
                    $fieldValue = $business->getInfo($fieldName);

                    return $fieldValue !== null ? $mapper->getEntityById($fieldValue) : null;
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
            SFM_Manager::getInstance()->getCache()->setRaw($key, $entity->getId());
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

    public function isIdAutoIncrement()
    {
        return true;
    }
}
