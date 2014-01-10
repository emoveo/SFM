<?php

interface SFM_MonitorInterface
{
    /**
     * @param array $tags
     * @return SFM_TimerInterface
     */
    public function createTimer($tags);
}