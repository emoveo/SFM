<?php
namespace SFM\Cache;

use SFM\Cache\Driver\DummyDriver;
use SFM\Cache\Driver\MemcachedDriver;
use SFM\Cache\Generator\Md5Generator;
use SFM\Cache\Packer\TagPacker;
use SFM\Cache\Generator\GeneratorInterface;
use SFM\Cache\Packer\PackerInterface;
use SFM\Business;
use SFM\Value;
use SFM\Entity;

/**
 *  Class for work with daemons that use memcache protocol. Implements tags system for cache control
 */
class CacheProvider implements Value\ValueStorageStrategyInterface
{
    const KEY_DELIMITER = '@';

    const KEY_VALUE = 'value';
    const KEY_TAGS  = 'tags';
    const KEY_EXPIRES  = 'expires';

    /**
     * @var Adapter
     */
    protected $adapter;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var GeneratorInterface
     */
    protected $generator;

    /**
     * @var PackerInterface
     */
    protected $packer;

    /**
     * @param Config $config
     * @return $this
     */
    public function init(Config $config)
    {
        $this->config = $config;
        $this->generator = new Md5Generator($config->getPrefix());

        return $this;
    }

    /**
     * @throws CacheBaseException
     */
    public function connect()
    {
        if (is_null($this->config)) {
            throw new CacheBaseException("SFM/Cache is not configured");
        }

        $driver = !$this->config->isDisabled() ? $this->config->getDriver() : null;

        if ($driver == MemcachedDriver::DRIVER) {
            $driver = new MemcachedDriver(new \Memcached());
        } else {
            $driver = new DummyDriver();
        }

        $this->adapter = new Adapter($driver);

        if (false === $this->adapter->addServer($this->config->getHost(), $this->config->getPort(), true)) {
            throw new CacheBaseException(sprintf("SFM/Cache can't connect to server %s:%s", $this->config->getHost(), $this->config->getPort()));
        }

        $this->packer = new TagPacker($this->adapter, $this->generator);
    }

    /**
     * @return Adapter
     */
    public function getAdapter()
    {
        return $this->adapter;
    }

    /**
     * @param string $rawKey
     * @return array|null
     */
    public function get($rawKey)
    {
        $key = $this->generator->generate($rawKey);
        $rawValue = $this->adapter->get($key);

        $result = $this->packer->unpack($rawValue);
        if (null === $result) {
            $this->adapter->delete($key);
        }

        return $result;
    }

    /**
     * @param string[] $rawKeys
     * @return array[]|null
     */
    public function getMulti(array $rawKeys)
    {
        $keys = [];
        foreach ($rawKeys as $rawKey) {
            $keys[] = $this->generator->generate($rawKey);
        }
        $rawValues = $this->adapter->getMulti($keys);

        $result = $this->packer->unpack($rawValues);

        return $result ? $result : null;
    }

    /**
     * @param Business $business
     */
    public function set(Business $business)
    {
        $key = $this->generator->generate($business->getCacheKey());
        $this->adapter->set($key, $this->packer->pack($business), $business->getExpires());
    }

    /**
     * @param string $key
     * @param Value $value
     * @param int $expiration
     */
    public function setValue($key, Value $value, $expiration = 0)
    {
        $this->setRaw($key, $value->get(), $expiration);
    }

    /**
     * @param string $rawKey
     * @param mixed $value
     * @param int $expiration
     * @return bool
     */
    public function setRaw($rawKey, $value, $expiration = 0)
    {
        $key = $this->generator->generate($rawKey);
        return $this->adapter->set($key, $value, $expiration);
    }

    /**
     * @param string $rawKey
     * @return false|mixed|null
     */
    public function getRaw($rawKey)
    {
        $key = $this->generator->generate($rawKey);
        $value = $this->adapter->get($key);

        return ($value === false) ? null : $value;
    }

    /**
     * @param string $rawKey
     * @return bool
     */
    public function deleteRaw($rawKey)
    {
        $key = $this->generator->generate($rawKey);
        return $this->adapter->delete($key);
    }

    /**
     * Wrapper to SetMulti
     * Existing tags aren't reseted
     *
     * @param array[Entity] $items
     * @param int $expiration
     */
    public function setMulti(array $items, $expiration = 0)
    {
        $arr = array();
        /** @var $businessObj Business */
        foreach ($items as $businessObj) {
            $cacheKey = $this->generator->generate($businessObj->getCacheKey());
            $arr[$cacheKey] = $this->packer->pack($businessObj);
       }

       $this->adapter->setMulti($arr, $expiration);
    }

    /**
     * Deletes value by its key
     *
     * @param string $rawKey Cache key
     * @return bool
     */
    public function delete($rawKey)
    {
        $key = $this->generator->generate($rawKey);
        $result = $this->adapter->delete($key);

        return $result;
    }

    /**
     * Flushes all data in Memcached.
     * For debug purposes only!
     *
     */
    public function flush()
    {
        $this->adapter->flush();
    }

    /**
     * @param Entity $entity
     */
    public function deleteEntity(Entity $entity)
    {
        $this->delete($entity->getCacheKey());
        $this->packer->resetTags($entity->getCacheTags());
    }

    /**
     * @deprecated
     * @return bool
     */
    public function isTransaction()
    {
        return $this->adapter->isTransaction();
    }

    /**
     * @deprecated
     */
    public function commitTransaction()
    {
        $this->adapter->commitTransaction();
    }

    /**
     * @deprecated
     */
    public function rollbackTransaction()
    {
        $this->adapter->rollbackTransaction();
    }
}