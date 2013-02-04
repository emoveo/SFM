<?php
/**
 * Manage transactions on DB and Cache Layers simultaneously
 */
class SFM_Transaction implements SFM_Transaction_Engine
{
    /**
     * @var SFM_Transaction
     */
    protected static $instance;

    /**
     * @var SFM_Transaction_Engine[]
     */
    protected $engines = array();

    /**
     * @return SFM_Transaction
     */
    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new SFM_Transaction();
        }

        return self::$instance;
    }

    protected function __construct()
    {
        $this->engines = array(
            SFM_DB::getInstance(),
            SFM_Cache_Memory::getInstance(),
            SFM_IdentityMap::getInstance()
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
