<?php
require_once 'SFM/Interface/Singleton.php';
require_once 'SFM/Cache/Dummy.php';
require_once 'SFM/Exception/Memcached.php';

/**
 *  Class for work with daemons that use memcache protocol. Implements tags system for cache control
 */
class SFM_Cache implements SFM_Interface_Singleton//, Interface_Cacher 
{
    const KEY_DILIMITER = '@';
    
    const KEY_VALUE = 'value';
    const KEY_TAGS  = 'tags';
    
    /**
     *
     * @var SFM_Cache
     */
    protected static $instance;

    /**
     * Memcached object
     * 
     * @var Memcached
     */
    protected $driver;
    
    protected $projectPrefix = '';
    
    protected function __construct($host, $port, $projectPrefix = '', $disable = false)
    {
        //check fake mode
        $this->projectPrefix = $projectPrefix;
        if($disable !=1 ) {
            $this->driver = new Memcached();
        } else {
            $this->driver = new SFM_Cache_Dummy(); 
        }
        
        if (!$this->driver->addServer($host, $port))
            throw new SFM_Exception_Memcached('Can\'t connect to server '.$host.':'.$port);
        else
        {
            if($disable !=1 ) 
                $this->driver->setOption(Memcached::OPT_COMPRESSION, true);
        }       
    }
    
    /**
     * Singleton
     * 
     * @return SFM_Cache
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        
        return self::$instance;
    }
    
    /**
     * Get value by key from cache
     * 
     * @param string $key
     * @return mixed
     */
    public function get($key)
    {
        $arr = unserialize($this->_get($key));
        if (!is_array($arr)) {
            return null;
        }
        $result = $this->getValidObject($arr);
        if($result === null) {
            //If the object is invalid, remove it from cache 
            $this->delete($key);
        }
        return $result;
    }
    
    /**
     * Get object by array of keys
     * @param array $keys
     * @return array|null
     */
    public function getMulti( array $keys )
    {
    	$values = $this->_getMulti($keys);
//      Deep_Logger::getInstance()->debug( 'getMulti' );
//        echo 'sd';
        $result = array();
        if( false != $values ) {
            foreach ($values as $item) {
//              var_dump(unserialize($item));
                $obj = $this->getValidObject(unserialize($item));
                if( null != $obj) {
                    $result[] = $obj;
                } 
            }
        }
        
        if(sizeof($result)!=0) {
            
            return $result;
        } else {

            return null;
        }
    }
    
    /**
     * Save value to cache
     * 
     * @param string $key
     * @param mixed $value
     * @param array $tags
     * @param int $expiration
     * 
     */
    public function set($key, $value, $tags=array(), $expiration=0)
    {
//        echo "<br><br>SET - ".$key."<br>";var_dump($value);echo "<br>";var_dump($tags);echo "<br>";var_dump($this->getTags($tags));
        $arr = array(
            self::KEY_VALUE => serialize($value),
            self::KEY_TAGS  => $this->getTags($tags),
        );        
        $this->_set($key, $arr, $expiration);
    }
    /**
     * Wrapper to SetMulti 
     * Existing tags aren't reseted  
     *
     * @param array[SFM_Entities] $items
     * @param int $expiration
     */
    public function setMulti(array $items, $expiration=0)
    {
        $arr = array();
        foreach ($items as $businessObj) {
            $arr[$businessObj->getCacheKey()] = serialize( array(
                self::KEY_VALUE => serialize($businessObj),
                self::KEY_TAGS  => $this->getTags($businessObj->getCacheTags()),
            ) );     
        }        
//        var_dump($arr);
        $this->_setMulti($arr, $expiration);
    }
    
    /**
     * Deletes value by its key
     * 
     * @param string $key Cache key
     * @return bool 
     */
    public function delete($key)
    {
        return $this->_delete($key);
    }
    
    
    
    /**
     * Get tag values by keys
     * 
     * @param array $key
     * @return array
     */
    protected function getTags($keys)
    {
        $keys = (array) $keys;
        $values = array();
        $tagValues = array();
        $tagKeys = array();
        foreach ($keys as $key) {
            $tagKeys[] = $this->getTagByKey($key);
        }
        $tagValues = $this->_getMulti($tagKeys);
        if($tagValues === null)
            $tagValues = array();
            
        $i = 0;
        foreach($tagValues as $tagValue) {
            $key = $keys[$i];
            $value = unserialize($tagValue);
            if ( false === $value) {
                $value = $this->resetTags($key);
            }
            $values[$key] = $value;
            $i++;
        }
        return $values;
    }
    
    /**
     * Resets tag values and returns new values
     * The return type depends on type of $keys
     * 
     * @param array $keys
     * @return array
     */
    public function resetTags($keys)
    {
        $keys = (array) $keys;
        $values = array();
        $tagValues = array();
        foreach ($keys as $key) {
            $tag = $this->getTagByKey($key);
            $values [$key]= $value = microtime(true);
            $tagValues[$tag] = serialize($value);
        }
        if(!empty($tagValues)) {
        	$this->_setMulti($tagValues);
    	}
        return $values;
    }
    
    /**
     * Wrapper over Memcached set method
     * 
     * @param string $key
     * @param mixed $value
     * @param int $expiration
     */
    protected function _set($key, $value, $expiration=0)
    {
        $value = serialize($value);
        $this->driver->set($this->generateKey($key), $value, $expiration);
    }
    
    /**
     * Wrapper over Memcached setMulti method
     * 
     * @param array $items
     * @param int $expiration
     */
    protected function _setMulti($items, $expiration=0)
    {
        $resultItems = array();
        foreach($items as $key => $value)
        {
            $resultItems[$this->generateKey($key)] = $value;
        }
        $this->driver->setMulti($resultItems, $expiration);
    }
    
    /**
     * Wrapper over Memcached get method
     * 
     * @param string $key
     * @return mixed|null
     */
    protected function _get($key)
    {
        $value = $this->driver->get($this->generateKey($key));
        return ($value === false) ? null : $value;
    }
    
	/**
     * Wrapper over Memcached getMulti method
     * 
     * @param array $keys
     * @return mixed|null
     */
    protected function _getMulti( array $keys )
    {
        foreach($keys as &$key)
        {
            $key = $this->generateKey($key);
        }
        $values = $this->driver->getMulti($keys);
        return ($values === false) ? null : $values;
    }    
    /**
     * Wrapper over Cache delete method
     * 
     * @param string $key key to delete
     * @return bool
     */
    protected function _delete($key)
    {
        return $this->driver->delete($this->generateKey($key));
    }
    
    /**
     * Flushes all data in Memcached.
     * For debug purposes only!
     * 
     */
    public function flush()
    {
        $this->driver->flush();
    }
    
    /**
     * Returns key for storing tags. 
     * Since tag keys must differ from object keys, method concatinates some prefix
     * 
     * @param string $key Original name of tag. Can be the same as Entity Cache key
     * @return string  
     */
    protected function getTagByKey($key)
    {
        return $this->generateKey('Tag' . self::KEY_DILIMITER . $key);
    }
    
    protected function getValidObject(array $raw)
    {
        $oldTagValues = (array) $raw[self::KEY_TAGS];
        
        $newTagValues = $this->getTags(array_keys($oldTagValues));
//        echo "<br><br>Get by key :<br>";var_dump($value);echo "<br>";var_dump($oldTagValues);var_dump($newTagValues); echo "<br><br>";
        if($oldTagValues == $newTagValues) {
            /**
             * unserialize ONLY after tags comparison to except useless work
             * @see SFM_Aggregate __wakeup
             */ 
//            echo '<br>getValidObject - yes<br>';
            return unserialize($raw[self::KEY_VALUE]);
        } else {
//            echo " <br>EMpty cache";
            return null;
        }
    }
    
    protected function generateKey($key)
    {
    	return md5($this->projectPrefix.self::KEY_DILIMITER.$key);
    }
    
}
?>