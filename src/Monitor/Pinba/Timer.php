<?php
/**
 * Pinba timer implementation
 */
class SFM_Monitor_Pinba_Timer implements SFM_Monitor_TimerInterface
{

    /**
     * @var resource $resource
     */
    private $resource;

    /**
     * @param array $tags
     */
    public function __construct($tags)
    {
        $this->start($tags);
    }

    /**
     * @param array $tags
     * @return bool
     */
    private function start($tags)
    {
        $this->resource = pinba_timer_start($tags);
        return true;
    }

    /**
     * @return bool
     */
    public function stop()
    {
        return pinba_timer_stop($this->resource);
    }

    /**
     * @return bool
     */
    public function remove()
    {
        return pinba_timer_delete($this->resource);
    }

}