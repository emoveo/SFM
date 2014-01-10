<?php
/**
 * Data engine with transaction feature
 */
interface SFM_Transaction_Engine
{
    public function beginTransaction();

    public function commitTransaction();

    public function rollbackTransaction();

    /**
     * @return bool
     */
    public function isTransaction();
}