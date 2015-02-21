<?php
namespace SFM\Monitor;

interface TimerInterface
{
    public function __construct($tags);

    public function stop();

    public function remove();
}