<?php

/**
 *  Class for work with daemons that use memcache protocol. Implements tags system for cache control
 */
abstract class SFM_Cache implements SFM_MonitorableInterface
{
    const KEY_DILIMITER = '@';

    const KEY_VALUE = 'value';
    const KEY_TAGS  = 'tags';
    const KEY_EXPIRES  = 'expires';

    const FORCE_TIMEOUT = 1;

    /**
     * @var Memcached
     */
    protected $driverCache;

    /**
     * @var SFM_Config_Cache
     */
    protected $config;

    /**
     * @var SFM_Cache_Transaction
     */
    protected $transactionCache;

    /**
     * @var SFM_MonitorInterface
     */
    protected $monitor;

    protected $projectPrefix = '';

    /**
     * @param SFM_Config_Cache $config
     * @return $this
     */
    public function init(SFM_Config_Cache $config)
    {
        $this->config = $config;

        return $this;
    }

    /**
     * @throws SFM_Exception_Memcached
     * @throws SFM_Exception_DB
     */
    public function connect()
    {
        if (is_null($this->config)) {
            throw new SFM_Exception_DB("SFM_Cache is not configured");
        }

        if ($this->config->isDisabled()) {
            $this->driverCache = new SFM_Cache_Dummy();
        } else {
            $this->driverCache = new Memcached();

            if (!$this->driverCache->addServer($this->config->getHost(), $this->config->getPort(), true)) {
                throw new SFM_Exception_Memcached('Can\'t connect to server '.$this->config->getHost().':'.$this->config->getPort());
            }
        }
    }

    public function __construct()
    {
        $this->transactionCache = new SFM_Cache_Transaction($this);
    }

    /**
     * @param SFM_MonitorInterface $monitor
     */
    public function setMonitor(SFM_MonitorInterface $monitor)
    {
        $this->monitor = $monitor;
    }

    /**
     * Get value by key from cache
     *
     * @param string $key
     * @return mixed
     */
    public function get($key)
    {
        $raw = $this->_get($key);
        $arr = unserialize($raw);
        if (is_array($arr)) {
            $result = $this->getValidObject($arr);
            if ($result === null) {
                //If the object is invalid, remove it from cache
                $this->_delete($key);
            }
        } else {
            $result = null;
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
        $result = array();
        if( false != $values ) {
            foreach ($values as $item) {
                $obj = $this->getValidObject(unserialize($item));
                if( null != $obj) {
                    $result[] = $obj;
                }
            }
        }

        if (sizeof($result) != 0) {
            return $result;
        } else {
            return null;
        }
    }

    /**
     * Save value to cache
     *
     * @param SFM_Business $value
     */
    public function set(SFM_Business $value)
    {
        if ($this->transactionCache->isStarted()) {
            $this->transactionCache->logBusiness($value);
        } else {

            $data = array(
                self::KEY_VALUE => serialize($value),
                self::KEY_TAGS  => $this->getTags($value->getCacheTags()),
                self::KEY_EXPIRES  => $value->getExpires(),
            );

            $this->_set($value->getCacheKey(), $data, $value->getExpires());
        }
    }

    /**
     * @param string $key
     * @param mixed $value
     * @param int $expiration
     * @return bool
     */
    public function setRaw($key, $value, $expiration = 0)
    {
        $key = $this->generateKey($key);

        if ($this->transactionCache->isStarted()) {
            $result = $this->transactionCache->logRaw($key, $value, $expiration);
        } else {
            $time = microtime(true);
            $result = $this->driverCache->set($key, $value, $expiration);
            $this->checkCacheIsAlive($time);
        }

        return $result;
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
        if ($this->transactionCache->isStarted()) {
            $this->transactionCache->logMulti($items, $expiration);
        } else {

            $arr = array();
            /** @var $businessObj SFM_Business */
            foreach ($items as $businessObj) {
                $arr[$businessObj->getCacheKey()] = serialize( array(
                    self::KEY_VALUE => serialize($businessObj),
                    self::KEY_TAGS  => $this->getTags($businessObj->getCacheTags()),
                    self::KEY_EXPIRES  => $expiration,
                ));
           }

            $this->_setMulti($arr, $expiration);
        }
    }

    /**
     * Deletes value by its key
     *
     * @param string $key Cache key
     * @return bool
     */
    public function delete($key)
    {
        if ($this->transactionCache->isStarted()) {
            $this->transactionCache->logDeleted($key);
            $result = true;
        } else {
            $result = $this->_delete($key);
        }

        return $result;
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
        $key = $this->generateKey($key);
        if ($this->monitor !== null) {
            $timer = $this->monitor->createTimer(array('db' => get_class($this), 'operation' => 'set'));
        }

        $time = microtime(true);

        if ($this->transactionCache->isStarted()) {
            $this->transactionCache->logRaw($key, $value, $expiration);
        } else {
            $this->driverCache->set($key, $value, $expiration);
            $this->checkCacheIsAlive($time);
        }

        if (isset($timer)) {
            $timer->stop();
        }
    }

    protected function checkCacheIsAlive($time)
    {
        if (microtime(true) - $time > self::FORCE_TIMEOUT) {
            $this->driverCache = new SFM_Cache_Dummy();
        }
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
            $key = $this->generateKey($key);
            $resultItems[$key] = $value;
        }
        if ($this->monitor !== null) {
            $timer = $this->monitor->createTimer(array('db' => get_class($this), 'operation' => 'setMulti'));
        }
        $time = microtime(true);

        if ($this->transactionCache->isStarted()) {
            $this->transactionCache->logRawMulti($resultItems, $expiration);
        } else {
            $this->driverCache->setMulti($resultItems, $expiration);
            $this->checkCacheIsAlive($time);
        }

        if (isset($timer)) {
            $timer->stop();
        }
    }

    /**
     * Wrapper over Memcached get method
     *
     * @param string $key
     * @return mixed|null
     */
    protected function _get($key)
    {
        if ($this->monitor !== null) {
            $timer = $this->monitor->createTimer(array('db' => get_class($this), 'operation' => 'get'));
        }

        $time = microtime(true);

        $value = null;
        if ($this->transactionCache->isStarted()) {
            $value = $this->transactionCache->getRaw($key);
        }

        if (empty($value)) {
            $value = $this->driverCache->get($this->generateKey($key));
            $this->checkCacheIsAlive($time);
        }

        if (isset($timer)) {
            $timer->stop();
        }

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
        if ($this->monitor !== null) {
            $timer = $this->monitor->createTimer(array('db' => get_class($this), 'operation' => 'getMulti'));
        }

        $time = microtime(true);

        $values = array();
        if ($this->transactionCache->isStarted()) {
            $values = array();
            foreach ($keys as $key) {
                $values[$key] = $this->transactionCache->getRaw($key);
            }
        }

        if (empty($values)) {
            $values = $this->driverCache->getMulti($keys);
            $this->checkCacheIsAlive($time);
        }

        if (isset($timer)) {
            $timer->stop();
        }

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
        if ($this->monitor !== null) {
            $timer = $this->monitor->createTimer(array('db' => get_class($this), 'operation' => 'delete'));
        }

        $time = microtime(true);
        if ($this->transactionCache->isStarted()) {
            $result = $this->transactionCache->logDeleted($key);
        } else {
            $result = $this->driverCache->delete($this->generateKey($key));
            $this->checkCacheIsAlive($time);
        }

        if (isset($timer)) {
            $timer->stop();
        }
        return $result;
    }

    /**
     * Flushes all data in Memcached.
     * For debug purposes only!
     *
     */
    public function flush()
    {
        if ($this->monitor !== null) {
            $timer = $this->monitor->createTimer(array('db' => get_class($this), 'operation' => 'flush'));
        }

        $time = microtime(true);
        $this->driverCache->flush();
        $this->checkCacheIsAlive($time);

        if (isset($timer)) {
            $timer->stop();
        }
    }

    public function getDriverCache()
    {
        return $this->driverCache;
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
        //expiration objects should expire without tags
        if($oldTagValues == $newTagValues || $raw[self::KEY_EXPIRES]) {
            return unserialize($raw[self::KEY_VALUE]);
        } else {
            return null;
        }
    }

    protected function generateKey($key)
    {
        return md5($this->config->getPrefix().self::KEY_DILIMITER.$key);
    }

    public function beginTransaction()
    {
        if ($this->monitor !== null) {
            $timer = $this->monitor->createTimer(array('db' => get_class($this), 'operation' => 'beginTransaction'));
        }

        $this->transactionCache->begin();

        if (isset($timer)) {
            $timer->stop();
        }
    }

    /**
     * @return bool
     */
    public function isTransaction()
    {
        return $this->transactionCache->isStarted();
    }

    public function commitTransaction()
    {
        if ($this->monitor !== null) {
            $timer = $this->monitor->createTimer(array('db' => get_class($this), 'operation' => 'commitTransaction'));
        }

        $this->transactionCache->commit();

        if (isset($timer)) {
            $timer->stop();
        }
    }

    public function rollbackTransaction()
    {
        if ($this->monitor !== null) {
            $timer = $this->monitor->createTimer(array('db' => get_class($this), 'operation' => 'rollbackTransaction'));
        }

        $this->transactionCache->rollback();

        if (isset($timer)) {
            $timer->stop();
        }
    }
}