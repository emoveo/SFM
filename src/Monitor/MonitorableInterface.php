<?php
namespace SFM\Monitor;

interface MonitorableInterface
{
    /**
     * @param MonitorInterface $monitor
     */
    public function setMonitor(MonitorInterface $monitor);
}