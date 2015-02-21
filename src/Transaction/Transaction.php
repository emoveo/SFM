<?php
namespace SFM\Transaction;

/**
 * Manage transactions on DB and Cache Layers simultaneously
 */
class Transaction implements TransactionEngine
{
    /**
     * @var TransactionEngine[]
     */
    protected $engines = array();

    /**
     * @param TransactionEngine $engine
     * @return $this
     */
    public function addTransactionEngine(TransactionEngine $engine)
    {
        $this->engines[] = $engine;

        return $this;
    }

    /**
     * @return TransactionEngine[]
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
