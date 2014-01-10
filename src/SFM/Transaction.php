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

    /**
     * @param SFM_Transaction_Engine $engine
     * @return $this
     */
    public function addTransactionEngine(SFM_Transaction_Engine $engine)
    {
        $this->engines[] = $engine;

        return $this;
    }

    /**
     * @return SFM_Transaction_Engine[]
     */
    public function getTransactionEngines()
    {
        return $this->engines;
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

    /**
     * @return bool
     */
    public function isTransaction()
    {
        $isTransactionStarted = null;
        foreach ($this->engines as $engine) {
            if ($isTransactionStarted === null) {
                $isTransactionStarted = $engine->isTransaction();
            } else {
                $isTransactionStarted = $isTransactionStarted & $engine->isTransaction();
            }
        }

        return $isTransactionStarted;
    }
}
