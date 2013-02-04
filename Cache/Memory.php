<?php
require_once 'Zend/Registry.php';
require_once 'SFM/Cache.php';

/**
 *  Class for work with Memcached in memory. Implements tags system for cache control
 */
class SFM_Cache_Memory extends SFM_Cache implements SFM_Transaction_Engine
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
    
    public function cas(SFM_Business $value)
    {
        do {
            $resultValue = $this->driver->get($key, null, $cas);
            if($this->driver->getResultCode() == Memcached::RES_NOTFOUND) {
                $this->set($value);
            } else {
                //copypatse from SFM_Cache::set
                $arr = array(
                    self::KEY_VALUE => serialize($value),
                    self::KEY_TAGS  => $this->getTags($value->getCacheTags()),
                );        
                //\copypaste
                $this->driver->cas($cas,$this->generateKey($value->getCacheKey()), $arr, $expiration);
            }
        } while ($this->driver->getResultCode() != Memcached::RES_SUCCESS);
        
        return $resultValue;
    }

    /**
     * @param string $key
     * @param mixed $value
     * @param int $expiration
     * @return bool
     */
    public function setRaw($key, $value, $expiration = 0)
    {
        $cacheKey = $this->generateKey($key);

        if ($this->transaction->isStarted()) {
            $result = $this->transaction->logRaw($cacheKey, $value, $expiration);
        } else {
            $result = $this->driver->set($cacheKey, $value, $expiration);
        }

        return $result;
    }

    public function setValue($key, SFM_Value_Abstract $value, $expiration = 0)
    {
        $this->setRaw($key, $value->get(), $expiration);
        $this->transaction->logResetable($value);
    }
    
    public function getRaw($key)
    {
        $value = $this->driver->get($this->generateKey($key));
        return ($value === false) ? null : $value;
    }
    
    public function incrementRaw($key)
    {
        return $this->driver->increment($this->generateKey($key));
    }

    public function decrementRaw($key)
    {
        return $this->driver->decrement($this->generateKey($key));
    }
    
    public function deleteRaw($key)
    {
        return $this->driver->delete($this->generateKey($key));
    }
    
    public function getResultCode()
    {
        return $this->driver->getResultCode();
    }
}    