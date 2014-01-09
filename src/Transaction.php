<?php
/**
 * Manage transactions on DB and Cache Layers simultaneously
 */
class SFM_Transaction implements SFM_Transaction_Engine
{
    /**
     * @var SFM_Transaction_Engine[]
     */
    protected $engines = array();

    public function __construct()
    {
        $this->engines = array(
            SFM_Manager::getInstance()->getDb(),
            SFM_Manager::getInstance()->getCacheMemory(),
            SFM_Manager::getInstance()->getIdentityMap()
        );
    }

    public function beginTransaction()
    {
        foreach ($this->engines as $engine) {
            $engine->beginTransaction();
        }
    }   

    public function commitTransaction()
    {
        foreach ($this->engines as $engine) {
            $engine->commitTransaction();
        }
    }
    
    public function rollbackTransaction()
    {
        foreach ($this->engines as $engine) {
            $engine->rollbackTransaction();
        }
    }
}
