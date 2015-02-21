<?php
namespace SFM\Transaction;

/**
 * Data engine with transaction feature
 */
interface TransactionEngine
{
    public function beginTransaction();

    public function commitTransaction();

    public function rollbackTransaction();

    /**
     * @return bool
     */
    public function isTransaction();
}