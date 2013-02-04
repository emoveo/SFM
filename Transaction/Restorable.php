<?php
/**
 * Object requiring notification about transaction rollback
 */
interface SFM_Transaction_Restorable
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