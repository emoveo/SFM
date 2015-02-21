<?php
namespace SFM\Transaction;

/**
 * Object requiring notification about transaction rollback
 */
interface RestorableInterface
{
    /**
     * @param mixed $state Object state before transaction
     * @return mixed
     */
    public function restoreObjectState($state);

    public function getObjectState();

    /**
     * @return string
     */
    public function getObjectIdentifier();
}