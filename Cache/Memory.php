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
}    