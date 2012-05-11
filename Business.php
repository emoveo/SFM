<?php
/**
 * Abstract class, ANY business object (either Entity or Aggregate)
 * 
 */
abstract class SFM_Business
{
    /**
     * Mapper which created this business object
     *
     * @var SFM_Mapper
     */    
    protected $mapper;
    
    /**
     * Array of values of fields that should be loaded by means of lazy load
     *
     * @var array
     */
    protected $computed = array();
    
    /**
     * Indicates whether business object should be treated as expire object.
     * Such objects don't expire because of tags and expire only for time.
     * In seconds.
     * @var integer
     */
    protected $_expires = 0;
    
    /**
     * Returns key for storing in Cache.
     * 
     */
    public abstract function getCacheKey();
    
    /**
     *  Returns Cache tags that associates with object
     */
    public abstract function getCacheTags();
    
    /**
     * Returns lazy loaded field by its name
     *
     * @param string $fieldName
     * @return mixed
     */
    protected function getComputed($fieldName)
    {
        if (!isset($this->computed[$fieldName])) {
            $value = $this->mapper->lazyload($this, $fieldName);
            $this->computed[$fieldName]= $value;            
        }
        return $this->computed[$fieldName];
    }
    
    /**
     * Performing preloading of computed values.
     * Can be used before assigning to Template to avoid errors in view code 
     *
     * @param array $fieldNames Array of fields to be loaded
     */
    public function preloadComputed(array $fieldNames)
    {
        foreach ($fieldNames as $fieldName) {
            $this->getComputed($fieldName);
        }
    }

    public function setExpires($expires)
    {
        $this->_expires = $expires;
    }
    
    public function getExpires()
    {
        return $this->_expires;
    }
}