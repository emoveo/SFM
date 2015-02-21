<?php
namespace SFM;

use SFM\Transaction\RestorableInterface;
use SFM\Cache\CacheProvider;

/**
 * Simplify common operations on values
 */
abstract class Value implements RestorableInterface
{
    /**
     * @var mixed
     */
    protected $value;

    /**
     * @var integer
     */
    protected $expiration = 0;

    /**
     * @var mixed
     */
    protected $objectState;
    
    /**
     * Load from storage
     *
     * @return mixed
     */
    protected abstract function load();

    /**
     * @return string
     */
    protected abstract function getCacheKey();

    /**
     * @param array $dependency
     * @param string $postfix
     * @return string
     */
    protected function getCacheKeyBy(array $dependency, $postfix = '')
    {
        $key = get_class($this) . CacheProvider::KEY_DELIMITER;

        foreach ($dependency as $item) {
           $key .= $item->getCacheKey();
        }

        return $key.$postfix;
    }

    /**
     * Transaction API: Restore value state
     *
     * @param mixed $state
     * @return mixed
     */
    public function restoreObjectState($state)
    {
        $this->value = $state;
    }

    /**
     * Transaction API: Get current value state
     *
     * @return mixed
     */
    public function getObjectState()
    {
        return $this->objectState;
    }

    /**
     * Transaction API: Get value unique identifier
     *
     * @return string
     */
    public function getObjectIdentifier()
    {
        return $this->getCacheKey();
    }

    /**
     * Get value
     *
     * @return mixed
     */
    public function get()
    {
        if (false === isset($this->value)) {
            $value = Manager::getInstance()->getCache()->getRaw($this->getCacheKey());
            if (null !== $value) {
                $this->value = $value;
            } else {
                $this->set($this->load());
            }
        }

        return $this->value;
    }

    /**
     * Set value
     *
     * @param mixed $value
     * @return mixed
     */
    protected function set($value)
    {
        $this->objectState = $this->value;
        $this->value = $value;

        Manager::getInstance()->getCache()->setValue($this->getCacheKey(), $this, $this->expiration);

        return $this->value;
    }

    /**
     * Flush value to null
     */
    public function flush()
    {
        $this->objectState = null;
        $this->value = null;

        Manager::getInstance()->getCache()->deleteRaw($this->getCacheKey());
    }

    /**
     * Set value expiration time
     * @param integer $expiration
     */
    protected function setExpiration($expiration)
    {
        $this->expiration = $expiration;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string) $this->get();
    }
}