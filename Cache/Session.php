<?php
require_once 'Zend/Registry.php';
require_once 'SFM/Cache.php';

/**
 *  Class for work with Sessions in memory. It is just like SFM_Cache_Memory
 */
class SFM_Cache_Session extends SFM_Cache 
{
    /**
     *
     * @var SFM_Cache_Session
     */
    protected static $instance;

    protected function __construct()
    {
        $Config = Zend_Registry::get(Application::CONFIG_NAME);
        $projectPrefix = '';
        if($Config->memcachedAPI->projectPrefix)
        	$projectPrefix = $Config->memcachedAPI->projectPrefix;
        parent::__construct($Config->memcachedAPI->sessionMemory->host, $Config->memcachedAPI->sessionMemory->port,$projectPrefix,false);
    }
    
    /**
     * Singleton
     * 
     * @return SFM_Cache_Session
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        
        return self::$instance;
    }
    
    public function setRaw($key,$value,$expiration = 0)
    {
        return $this->driver->set($this->generateKey($key), $value, $expiration);
    }
    
    public function getRaw($key)
    {
        $value = $this->driver->get($this->generateKey($key));
        return ($value === false) ? null : $value;
    }
    
    public function deleteRaw($key)
    {
        return $this->driver->delete($this->generateKey($key));
    }
}    