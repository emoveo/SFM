<?php
namespace SFM\Transaction;

/**
 * Data engine with transaction feature
 */
interface TransactionEngineInterface
{
    public function beginTransaction();

    public function commitTransaction();

    public function rollbackTransaction();

    /**
     * @return bool
     */
    public function isTransaction();
}