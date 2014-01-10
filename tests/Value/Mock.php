<?php

class Value_Mock extends SFM_Value
{
    /**
     * @return mixed|string
     */
    public function load()
    {
        return SFM_Manager::getInstance()->getDb()->fetchValue("SELECT `text` FROM `mock` WHERE `id` = 1");
    }

    /**
     * @return string
     */
    public function getCacheKey()
    {
        return get_class($this);
    }
}