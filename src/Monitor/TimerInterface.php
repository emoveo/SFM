<?php
interface SFM_Monitor_TimerInterface
{
    public function __construct($tags);
    public function stop();
    public function remove();
}