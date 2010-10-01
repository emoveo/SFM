<?php
require_once 'SFM/Business.php';
/**
 * Abstract class for single Business object
 * 
 */
abstract class SFM_Entity extends SFM_Business 
{
    /**
     * Prototype array - contains information about Business object (actually, it is retreived from DB with help of any Data Mapper)
     * @var array
     */
    protected $proto;
    
    /**
     * Constructor
     * @param array $proto Prototype array (contains information about Business object)
     */
    public function __construct($proto, SFM_Mapper $mapper)
    {
        $this->proto = $proto;
        $this->mapper = $mapper;
    }

    /**
     * This {@link http://zend.com/manual/language.oop5.overloading.php overloading method} makes possible to use 
     *   of "$obj->url" for getting any property or URL of object.
     * Don't use it directly, it is called automatically
     * @param string $name               Name of property or 'url'
     * @return mixed                     Value of property
     */
    public function __get($name)
    {
        return $this->getInfo($name);
    }
    
    /**
     * Returns the property of Business object if name of the property given, or entire info array overwise
     * @param mixed $fieldName           Name of property
     * @return mixed                     Value of property or entire info array
     */
    public function getInfo($fieldName = null)
    {
        assert('$fieldName !== "proto"');
        
        // all fields
        if ($fieldName === null) {
            return $this->proto;
        }
        
        // already computed value 
        if (isset($this->proto[$fieldName])) {
            return $this->proto[$fieldName];    
        }
         
        return null;
    }
    
    /**
     * @DEPRECATED.    
     * Returns the property of Business object if name of the property given, or entire info array overwise
     * @param mixed $fieldName           Name of property
     * @return mixed                     Value of property or entire info array
     */
    public function getProto($fieldName = null)
    {
           return $this->getInfo($fieldName);
    }
    
    /**
     * Return entity id
     * @return int
     */
    public function getId()
    {
        return $this->proto[$this->mapper->getIdField()];
    }
    
    
    /**
     * Wrapper for mapper's updateEntity method
     * @param array $params Fields to be updated and new values
     * @return mixed ID of updated entity in case of successful update, false - overwise
     */
    public function update(array $params)
    {
    	//@TODO rewrite without clone
    	if(empty($params))
    		return true;
    		
        $oldEntity = clone $this;
        foreach ($params as $key => $value) {
            //Check that field exists
            if (array_key_exists($key, $this->proto)) {
                //Prevent Entity from changing its id
                if ($key != $this->mapper->getIdField()) {
                    $this->proto[$key] = $value;
                }
                
                //if it is an some id-field...
                if(strrpos($key,'_id') !== false) {
	                //...and if there is a lazy-object loaded already...
	                if (isset($this->computed[$key])) {
	                	//...kill it. Goodbye!
	                	unset($this->computed[$key]);
	                }
                }
            } else {
                unset($params[$key]);
            }                
        }
        $this->mapper->updateUniqueFields($this, $oldEntity);
        return $this->mapper->updateEntity($params, $this);
    }
    
    /**
     * Wrapper for mapper's deleteEntity method
     * 
     * @return bool True if success, false - overwise
     */
    public function delete()
    {
        return $this->mapper->deleteEntity($this);
    }
    
    public function __sleep()
    {
        return array('proto');
    }
    
    public function __wakeup()
    {
        $mapperClassName = str_replace('Entity', 'Mapper', get_class($this));
        $this->mapper = call_user_func(array($mapperClassName, 'getInstance'));
    }
    
    /**
     * Returns key for storing Entity in Cache.
     * Entity has to call Mapper's method because it doesn't know its id field.
     * 
     * @return string
     */
    public function getCacheKey()
    {
        return $this->mapper->getEntityCacheKey($this);
    }
    
    /**
     * Return key for storing entity id value in Cache
     * 
     * @param $uniqueKey One of the keys. It must contain only filed names
     * @return string
     */
    public function getCacheKeyByUniqueFields(array $uniqueKey)
    {
        return $this->mapper->getEntityCacheKeyByUniqueFields($this, $uniqueKey);
    }
    
    /**
     * Returns Cache tags that associates with Entity
     * By default Entity has only one tag. Default Entity tag is the same as Entity key.
     * We can do this way, because Cache class automatically concatinates some prefix for tags 
     * 
     * @return array
     */
    public function getCacheTags()
    {
        return array($this->getCacheKey());
    }
    
    /**
     * Returns either Entity will be cached
     * 
     * @return bool
     */
    final public function isCacheable()
    {
        return $this->mapper->isCacheable($this);
    }
    
    /**
     *	Returns entity as an array value with 'entity' key 
     *	Needs for partials.
     *
     *	@return array
     */
    public function toArray()
    {
    	return array('entity' => $this);
    } 
    
}
