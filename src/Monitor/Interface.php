<?php
interface SFM_Monitor_Interface
{
    /**
     * @abstract
     * @param $tags
     * @return SFM_Monitor_TimerInterface
     */
    public function createTimer($tags);
}