<?php
namespace SFM;

class Repository
{
    protected $pool = array();

    /**
     * @param string $mapperClassName
     * @return Mapper
     */
    public function get($mapperClassName)
    {
        if (false === isset($this->pool[$mapperClassName])) {
            $this->pool[$mapperClassName] = new $mapperClassName($this);
        }

        return $this->pool[$mapperClassName];
    }
}