<?php
interface SFM_TimerInterface
{
    public function __construct($tags);

    public function stop();

    public function remove();
}