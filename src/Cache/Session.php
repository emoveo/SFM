<?php
namespace SFM\Cache;

class Session extends CacheProvider
{
    /**
     * @param string $key
     * @param mixed $value
     * @param int $expiration
     * @return bool
     */
    public function setRaw($key, $value, $expiration = 0)
    {
        return $this->adapter->set($this->generator->generate($key), $value, $expiration);
    }

    /**
     * @param string $key
     * @return mixed|null
     */
    public function getRaw($key)
    {
        $value = $this->adapter->get($this->generator->generate($key));

        return ($value === false) ? null : $value;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function deleteRaw($key)
    {
        return $this->adapter->delete($this->generator->generate($key));
    }
}    