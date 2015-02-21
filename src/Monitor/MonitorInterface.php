<?php
namespace SFM\Monitor;

interface MonitorInterface
{
    /**
     * @param array $tags
     * @return TimerInterface
     */
    public function createTimer($tags);
}