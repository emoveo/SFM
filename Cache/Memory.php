<?php
require_once 'Zend/Registry.php';
require_once 'SFM/Cache.php';

/**
 *  Class for work with Memcached in memory. Implements tags system for cache control
 */
class SFM_Cache_Memory extends SFM_Cache 
{
    /**
     *
     * @var SFM_Cache_Memory
     */
    protected static $instance;

    protected function __construct()
    {
        $Config = Zend_Registry::get(Application::CONFIG_NAME);
        $projectPrefix = '';
        if($Config->memcachedAPI->projectPrefix)
        	$projectPrefix = $Config->memcachedAPI->projectPrefix;
        parent::__construct($Config->memcachedAPI->defaultMemory->host, $Config->memcachedAPI->defaultMemory->port,$projectPrefix,$Config->memcached->disable);
    }
    
    /**
     * Singleton
     * 
     * @return SFM_Cache_Memory
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        
        return self::$instance;
    }
    
    public function cas($key, $value, $tags=array(), $expiration=0)
    {
        do {
            $resultValue = $this->driver->get($key, null, $cas);
            if($this->driver->getResultCode() == Memcached::RES_NOTFOUND) {
                $this->set($key, $value, $tags, $expiration);
            } else {
                //copypatse from SFM_Cache::set
                $arr = array(
                    self::KEY_VALUE => serialize($value),
                    self::KEY_TAGS  => $this->getTags($tags),
                );        
                //\copypaste
                $this->driver->cas($cas,$this->generateKey($key), $arr, $expiration);
            }
        } while ($this->driver->getResultCode() != Memcached::RES_SUCCESS);
    }
    
    public function setRaw($key,$value,$expiration = 0)
    {
        $this->driver->set($key, $value, $expiration);
    }
    
    public function getRaw($key)
    {
        $value = $this->driver->get($key);
        return ($value === false) ? null : $value;
    }
    
    public function deleteRaw($key)
    {
        return $this->driver->delete($key);
    }
}    