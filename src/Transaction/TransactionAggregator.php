<?php
namespace SFM\Transaction;

/**
 * Transaction engines aggregator.
 *
 * Provides synchronous way to control transaction through different layers
 */
class TransactionAggregator implements TransactionEngineInterface
{
    /**
     * Transaction current state
     * @var bool
     */
    protected $state = false;

    /**
     * @var TransactionEngineInterface[]
     */
    protected $engines = array();

    /**
     * @param TransactionEngineInterface $engine
     * @return $this
     */
    public function registerTransactionEngine(TransactionEngineInterface $engine)
    {
        $this->engines[] = $engine;

        return $this;
    }

    /**
     * Begin transaction

     * @throws TransactionException
     */
    public function beginTransaction()
    {
        if ($this->state === true) {
            throw new TransactionException("Can't begin transaction while another one is running");
        }

        /** Begin transaction on every registered engine */
        foreach ($this->engines as $engine) {
            $isActive = null;
            $exception = null;

            try {
                $engine->beginTransaction();
                $isActive = $engine->isTransaction();
            } catch (TransactionException $e) {
                $isActive = false;
                $exception = $e;
            }

            if (false === $isActive) {
                throw new TransactionException(sprintf("Can't begin transaction on `%s` engine", get_class($engine)), 0, $exception);
            }
        }

        // transaction started
        $this->state = true;
    }

    /**
     * @throws TransactionException
     */
    public function commitTransaction()
    {
        if ($this->state === false) {
            throw new TransactionException("Can't commit transaction while there is no transaction running");
        }

        /** Begin transaction on every registered engine */
        foreach ($this->engines as $i => $engine) {
            $isActive = null;
            $exception = null;
            try {
                $engine->commitTransaction();
                $isActive = $engine->isTransaction();
            } catch (TransactionException $e) {
                $isActive = true;
                $exception = $e;
            }

            if (true === $isActive) {
                // TODO: flush started
                throw new TransactionException(sprintf("Can't commit transaction on `%s` engine", get_class($engine)), 0, $exception);
            }
        }

        // transaction commited
        $this->state = false;
    }

    /**
     * @throws TransactionException
     */
    public function rollbackTransaction()
    {
        if ($this->state === false) {
            throw new TransactionException("Can't rollback transaction while there is no transaction running");
        }

        /** Begin transaction on every registered engine */
        foreach ($this->engines as $i => $engine) {
            $isActive = null;
            $exception = null;
            try {
                $engine->rollbackTransaction();
                $isActive = $engine->isTransaction();
            } catch (TransactionException $e) {
                $isActive = true;
                $exception = $e;
            }

            if (true === $isActive) {
                // TODO: flush started
                throw new TransactionException(sprintf("Can't rollback transaction on `%s` engine", get_class($engine)), 0, $exception);
            }
        }

        // transaction rolled back
        $this->state = false;
    }

    /**
     * All transaction engines, registered in this aggregator,
     * must change transaction state synchronously

     * @throws TransactionException
     * @return bool
     */
    public function isTransaction()
    {
        $isTransactionStarted = null;
        foreach ($this->engines as $engine) {
            $isTransaction = $engine->isTransaction();

            // get state by first engine
            if ($isTransactionStarted === null) {
                $isTransactionStarted = $isTransaction;
            // all other must be in sync
            } else if ($isTransaction !== $isTransactionStarted) {
                throw new TransactionException(sprintf("Transaction engine `%s` is desynchronized from other last engine", get_class($engine)));
            }
        }

        return $isTransactionStarted;
    }
}
