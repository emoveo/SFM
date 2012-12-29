<?php
require_once 'Zend/Registry.php';
require_once 'SFM/Cache.php';
require_once 'SFM/Exception/Memcached.php';

/**
 *  Class for work with Memcachedb.
 */
class SFM_Cache_File extends SFM_Cache 
{
    /**
     *
     * @var SFM_Cache_File
     */
    protected static $instance;

    protected function __construct()
    {
        $Config = Zend_Registry::get(Application::CONFIG_NAME);
        $projectPrefix = '';
        if($Config->memcachedAPI->projectPrefix)
            $projectPrefix = $Config->memcachedAPI->projectPrefix;
        parent::__construct($Config->memcachedAPI->defaultFile->host, $Config->memcachedAPI->defaultFile->port,$projectPrefix,$Config->memcached->disable);
    }
    
    /**
     * Singleton
     * 
     * @return SFM_Cache_File
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        
        return self::$instance;
    }
    
    public function setMulti(array $items, $expiration=0)
    {
        throw new SFM_Exception_Memcached('Method setMulti is not implemented for memcachedb');
    }
    
    /**
     * Wrapper over Memcached get method
     * If the cache is down, throws an exception
     * 
     * @param string $key
     * @return mixed|null
     * @throws SFM_Exception_Memcached
     */
    protected function _get($key)
    {
        $value = $this->driver->get($this->generateKey($key));
        $returnValue = ($value === false) ? null : $value;
        if(($returnValue == null) && ($this->driver->getResultCode() != Memcached::RES_NOTFOUND))
            throw new SFM_Exception_Memcached('Server is down');
        else 
            return $returnValue;    
    }
}    