<?php

interface SFM_MonitorableInterface
{
    /**
     * @param SFM_MonitorInterface $monitor
     */
    public function setMonitor(SFM_MonitorInterface $monitor);
}